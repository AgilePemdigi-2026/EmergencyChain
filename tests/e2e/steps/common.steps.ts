
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { test, createBdd } from 'playwright-bdd';

const { Given } = createBdd(test);

Given(
  'database pengujian telah direset dan akun demo tersedia',
  async () => {
    const testingEnvPath = resolve(
      process.cwd(),
      '.env.testing'
    );

    const testingEnv = readFileSync(
      testingEnvPath,
      'utf8'
    );

    const appEnv = testingEnv
      .match(/^APP_ENV=(.+)$/m)?.[1].trim();

    const databaseName = testingEnv
      .match(/^DB_DATABASE=(.+)$/m)?.[1].trim();

    if (
      appEnv !== 'testing' ||
      !databaseName?.endsWith('_test')
    ) {
      throw new Error(
        'Reset dibatalkan. Periksa APP_ENV dan DB_DATABASE pada .env.testing.'
      );
    }

    execFileSync(
      'php',
      [
        'artisan',
        'migrate:fresh',
        '--seed',
        '--env=testing',
        '--force',
      ],
      {
        cwd: process.cwd(),
        stdio: 'inherit',
      }
    );
  }
);
