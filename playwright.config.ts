
import { defineConfig } from '@playwright/test';
import { defineBddConfig } from 'playwright-bdd';

const testDir = defineBddConfig({
  features: 'tests/e2e/features/**/*.feature',
  steps: 'tests/e2e/steps/**/*.ts',
});

export default defineConfig({
  testDir,

  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 60000,

  reporter: [
    ['list'],
    ['html', { open: 'never' }],
  ],

  use: {
    baseURL: 'http://127.0.0.1:8001',
    browserName: 'chromium',

    // Browser terlihat saat demonstrasi
    headless: false,

    // Perlambat gerakan agar mudah diamati
    launchOptions: {
      slowMo: 500,
    },

    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
});
