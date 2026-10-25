import { test, createBdd } from 'playwright-bdd';
import { expect } from '@playwright/test';


const { Given, When, Then } = createBdd(test);

Given('Pelapor berada di halaman login', async ({ page }) => {
  await page.goto('/login');

  await expect(
    page.getByRole('heading', { name: 'Selamat Datang' })
  ).toBeVisible();
});

When('Pelapor login dengan akun testing yang valid', async ({ page }) => {
  const email = process.env.E2E_PELAPOR_EMAIL;
  const password = process.env.E2E_PELAPOR_PASSWORD;

  if (!email || !password) {
    throw new Error(
      'E2E_PELAPOR_EMAIL dan E2E_PELAPOR_PASSWORD wajib diatur.'
    );
  }

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();
});

Then('Pelapor diarahkan ke halaman Buat Laporan Emergency', async ({ page }) => {
  await expect(page).toHaveURL(
    /\/pelapor\/laporan\/buat$/
  );

  await expect(
    page.getByRole('heading', { name: 'Buat Laporan Emergency' })
  ).toBeVisible();
});


When('Pelapor mengisi informasi Emergency Case yang valid', async ({ page }) => {
  // Pilih berdasarkan label yang ditampilkan pada dropdown.
  await page.locator('#jenis_kejadian').selectOption({
    label: 'Kebakaran',
  });

  await page.locator('#tingkat_kebutuhan').selectOption({
    label: 'Butuh Penanganan Cepat',
  });

  await page.locator('#kondisi_kejadian').fill(
    'BDD Testing: Terjadi kebakaran pada bangunan di Surabaya.'
  );

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian otomatis'
  );
});

When('Pelapor menentukan titik lokasi kejadian', async ({ page }) => {
  const map = page.locator('#location-map');

  await expect(map).toBeVisible();

  // Klik peta pada titik tertentu untuk menentukan lokasi.
  await map.click({
    position: {
      x: 250,
      y: 180,
    },
  });

  // Pastikan peta menghasilkan koordinat.
  await expect(page.locator('#latitude')).not.toHaveValue('');
  await expect(page.locator('#longitude')).not.toHaveValue('');
});

When('Pelapor mengirimkan laporan', async ({ page }) => {
  await page.locator('#submit-report').click();
});

Then('Emergency Case berhasil dibuat', async ({ page }) => {
  // Periksa pesan keberhasilan yang tampil pada aplikasi.
  await expect(
    page.getByText(/laporan berhasil|berhasil dikirim/i).first()
  ).toBeVisible();
});


When('Pelapor melakukan logout', async ({ page }) => {
  // Klik tombol Logout pada sidebar
  await page.getByRole('button', {
    name: /logout/i,
  }).click();

  // Pastikan modal konfirmasi muncul
  await expect(
    page.getByText('Yakin ingin keluar?')
  ).toBeVisible();

  // Konfirmasi proses logout
  await page.getByRole('button', {
    name: 'Ya, Keluar',
  }).click();
});

Then('Pelapor kembali ke halaman login', async ({ page }) => {
  await expect(page).toHaveURL(/\/login$/);

  await expect(
    page.getByRole('heading', { name: 'Selamat Datang' })
  ).toBeVisible();

  // Pastikan halaman terlindungi tidak lagi bisa diakses.
  await page.goto('/pelapor/laporan/buat');

  await expect(page).toHaveURL(/\/login$/);
});
