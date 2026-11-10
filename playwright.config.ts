import { defineConfig } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';

const isDemoRun = process.argv.includes('--headed');

const testDir = defineBddConfig({
  features: 'tests/e2e/features/**/*.feature',
  steps: 'tests/e2e/steps/**/*.ts',
});

export default defineConfig({
  testDir,

  fullyParallel: false,
  workers: 1,
  retries: 0,

  timeout: isDemoRun ? 120000 : 60000,

  reporter: [
    ['list'],
    ['html', {
      open: 'never',
      outputFolder: 'playwright-report',
    }],
  ],

  use: {
    baseURL: 'http://127.0.0.1:8001',
    browserName: 'chromium',

    headless: !isDemoRun,

    launchOptions: {
      slowMo: isDemoRun ? 600 : 0,
    },

    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
});
