
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

async function isiLogin(
  page: Page,
  email: string,
  password: string
) {
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
}

Given('pengguna berada di halaman Login', async ({ page }) => {
  await page.goto('/login');

  await expect(
    page.getByRole('heading', { name: 'Selamat Datang' })
  ).toBeVisible();
});

// LOG-01
When(
  'pengguna mengisi kredensial Pelapor yang valid',
  async ({ page }) => {
    const email = process.env.E2E_PELAPOR_EMAIL;
    const password = process.env.E2E_PELAPOR_PASSWORD;

    if (!email || !password) {
      throw new Error(
        'E2E_PELAPOR_EMAIL dan E2E_PELAPOR_PASSWORD wajib tersedia.'
      );
    }

    await isiLogin(page, email, password);
  }
);

// Shared: LOG-01, LOG-03, LOG-04
When('pengguna menekan tombol Login', async ({ page }) => {
  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();
});

Then(
  'pengguna diarahkan ke halaman sesuai role Pelapor',
  async ({ page }) => {
    await expect(page).toHaveURL(
      /\/pelapor\/laporan\/buat$/
    );

    await expect(
      page.getByRole('heading', {
        name: 'Buat Laporan Emergency',
      })
    ).toBeVisible();
  }
);

// LOG-02
When(
  'pengguna membiarkan email dan password kosong',
  async ({ page }) => {
    await expect(
      page.locator('input[name="email"]')
    ).toHaveValue('');

    await expect(
      page.locator('input[name="password"]')
    ).toHaveValue('');
  }
);

When(
  'pengguna mencoba menekan tombol Login',
  async ({ page }) => {
    await page.getByRole('button', {
      name: 'Login',
      exact: true,
    }).click();
  }
);

Then(
  'browser menolak form Login yang belum lengkap',
  async ({ page }) => {
    const email = page.locator('input[name="email"]');

    const invalid = await email.evaluate(
      (element: HTMLInputElement) =>
        !element.validity.valid
    );

    expect(invalid).toBe(true);
  }
);

// LOG-03
When(
  'pengguna mengisi email terdaftar dengan password salah',
  async ({ page }) => {
    const email = process.env.E2E_PELAPOR_EMAIL;

    if (!email) {
      throw new Error('E2E_PELAPOR_EMAIL belum tersedia.');
    }

    await isiLogin(
      page,
      email,
      'PasswordSalah!2026'
    );
  }
);

// LOG-04
When(
  'pengguna mengisi email yang belum terdaftar',
  async ({ page }) => {
    const email =
      `tidak.terdaftar.${Date.now()}@example.test`;

    await isiLogin(
      page,
      email,
      'PasswordTesting!2026'
    );
  }
);

// Shared: LOG-03, LOG-04
Then(
  'sistem menampilkan kesalahan kredensial',
  async ({ page }) => {
    await expect(
      page.getByText('Email atau password salah.')
    ).toBeVisible();
  }
);

// Shared: LOG-02, LOG-03, LOG-04
Then(
  'pengguna tetap berada di halaman Login',
  async ({ page }) => {
    await expect(page).toHaveURL(/\/login$/);

    await expect(
      page.getByRole('heading', {
        name: 'Selamat Datang',
      })
    ).toBeVisible();
  }
);
