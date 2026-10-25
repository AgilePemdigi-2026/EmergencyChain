
import { test, createBdd } from 'playwright-bdd';
import { expect } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

// Data pembaruan dibuat unik setiap eksekusi
let kondisiBaru = '';

Given('Petugas EC berada di halaman login', async ({ page }) => {
  await page.goto('/login');

  await expect(
    page.getByRole('heading', { name: 'Selamat Datang' })
  ).toBeVisible();
});

When('Petugas EC login dengan akun testing yang valid', async ({ page }) => {
  const email = process.env.E2E_PETUGAS_EMAIL;
  const password = process.env.E2E_PETUGAS_PASSWORD;

  if (!email || !password) {
    throw new Error(
      'E2E_PETUGAS_EMAIL dan E2E_PETUGAS_PASSWORD wajib diatur.'
    );
  }

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();
});

Then('Petugas EC diarahkan ke halaman Manage Emergency Case',
  async ({ page }) => {
    await expect(page).toHaveURL(
      /\/petugas-ec\/emergency-cases$/
    );

    await expect(
      page.getByRole('heading', {
        name: 'Emergency Case',
        exact: true,
      })
    ).toBeVisible();
  }
);

When('Petugas EC membuka detail Emergency Case', async ({ page }) => {
  // Cari tautan detail dari daftar laporan.
  // Tidak menggunakan case_id tetap agar fleksibel.
  const detailLink = page.locator(
    'a[href*="/petugas-ec/emergency-cases/"]'
  ).first();

  await expect(detailLink).toBeVisible();

  await detailLink.click();

  await expect(page).toHaveURL(
    /\/petugas-ec\/emergency-cases\/\d+$/
  );
});

Then('informasi Emergency Case ditampilkan', async ({ page }) => {
  await expect(
    page.getByText('Informasi Kejadian').first()
  ).toBeVisible();

  await expect(
    page.getByRole('button', { name: 'Edit Informasi' })
  ).toBeVisible();
});

When('Petugas EC memperbarui kondisi kejadian', async ({ page }) => {
  kondisiBaru = `BDD Petugas EC: Pembaruan kondisi ${Date.now()}`;

  // Masuk ke mode edit
  await page.getByRole('button', {
    name: 'Edit Informasi',
  }).click();

  // Ubah kondisi kejadian
  await page.locator('#edit-kondisi').fill(kondisiBaru);

  // Simpan perubahan
  await page.getByRole('button', {
    name: 'Simpan Perubahan',
  }).click();
});

Then('perubahan Emergency Case berhasil disimpan',
  async ({ page }) => {
    await expect(
      page.getByText('Emergency Case berhasil diperbarui')
    ).toBeVisible();

    await expect(
      page.locator('#case-view-mode').getByText(
        kondisiBaru,
        { exact: true }
      )
    ).toBeVisible();
  }
);


Then('perubahan tetap tampil setelah halaman dimuat ulang',
  async ({ page }) => {
    await page.reload();

    await expect(
      page.locator('#case-view-mode').getByText(
        kondisiBaru,
        { exact: true }
      )
    ).toBeVisible();
  }
);

When('Petugas EC melakukan logout', async ({ page }) => {
  await page.getByRole('button', {
    name: /logout/i,
  }).click();

  // Konfirmasi logout sesuai modal UI terbaru
  await expect(
    page.getByText('Yakin ingin keluar?')
  ).toBeVisible();

  await page.getByRole('button', {
    name: 'Ya, Keluar',
  }).click();
});

Then('Petugas EC kembali ke halaman login', async ({ page }) => {
  await expect(page).toHaveURL(/\/login$/);

  await expect(
    page.getByRole('heading', { name: 'Selamat Datang' })
  ).toBeVisible();

  // Pastikan halaman Petugas EC tidak bisa diakses
  // setelah logout
  await page.goto('/petugas-ec/emergency-cases');

  await expect(page).toHaveURL(/\/login$/);
});
