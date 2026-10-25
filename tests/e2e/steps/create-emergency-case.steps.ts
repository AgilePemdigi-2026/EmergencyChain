
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

const KONDISI = 'BDD Functional: Terjadi kebakaran pada bangunan di Surabaya.';

async function loginPelapor(page: Page) {
  const email = process.env.E2E_PELAPOR_EMAIL;
  const password = process.env.E2E_PELAPOR_PASSWORD;

  if (!email || !password) {
    throw new Error(
      'E2E_PELAPOR_EMAIL dan E2E_PELAPOR_PASSWORD wajib tersedia.'
    );
  }

  await page.goto('/login');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).toHaveURL(/\/pelapor\/laporan\/buat$/);
}

async function isiDataDasar(page: Page) {
  await page.locator('#jenis_kejadian').selectOption({
    label: 'Kebakaran',
  });

  await page.locator('#tingkat_kebutuhan').selectOption({
    label: 'Butuh Penanganan Cepat',
  });

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian otomatis'
  );
}

async function tentukanLokasi(page: Page) {
  await page.locator('#location-map').click({
    position: { x: 250, y: 180 },
  });

  await expect(page.locator('#latitude')).not.toHaveValue('');
  await expect(page.locator('#longitude')).not.toHaveValue('');
}

Given(
  'Pelapor telah login dan berada di halaman Buat Laporan',
  async ({ page }) => {
    await loginPelapor(page);

    await expect(
      page.getByRole('heading', {
        name: 'Buat Laporan Emergency',
      })
    ).toBeVisible();
  }
);

// EC-01
When(
  'Pelapor mengisi seluruh data laporan yang valid',
  async ({ page }) => {
    await isiDataDasar(page);

    await page.locator('#kondisi_kejadian').fill(KONDISI);

    await expect(
      page.locator('#kondisi_kejadian')
    ).toHaveValue(KONDISI);
  }
);

When(
  'Pelapor menentukan lokasi kejadian pada peta',
  async ({ page }) => {
    await tentukanLokasi(page);
  }
);

// Shared EC-01 dan EC-03
When(
  'Pelapor menekan tombol Kirim Laporan',
  async ({ page }) => {
    // EC-03 juga membutuhkan koordinat, tetapi di
    // skenarionya tidak ada langkah memilih lokasi.
    if (
      (await page.locator('#latitude').inputValue()) === '' ||
      (await page.locator('#longitude').inputValue()) === ''
    ) {
      await tentukanLokasi(page);
    }

    await page.locator('#submit-report').click();
  }
);

// EC-02 dan EC-03
When(
  'Pelapor mengisi laporan tanpa kondisi kejadian',
  async ({ page }) => {
    await isiDataDasar(page);
    await tentukanLokasi(page);
    await page.locator('#kondisi_kejadian').fill('');
  }
);

When(
  'Pelapor mencoba menekan tombol Kirim Laporan',
  async ({ page }) => {
    await page.locator('#submit-report').click();
  }
);

Then(
  'browser menolak laporan yang belum lengkap',
  async ({ page }) => {
    const kondisi = page.locator('#kondisi_kejadian');

    await expect(kondisi).toHaveValue('');

    const invalid = await kondisi.evaluate(
      (element: HTMLTextAreaElement) =>
        !element.validity.valid
    );

    expect(invalid).toBe(true);
  }
);

Then(
  'Pelapor tetap berada di halaman Buat Laporan',
  async ({ page }) => {
    await expect(page).toHaveURL(
      /\/pelapor\/laporan\/buat$/
    );
  }
);

// EC-03
When(
  'Pelapor melengkapi kondisi kejadian',
  async ({ page }) => {
    await page.locator('#kondisi_kejadian').fill(KONDISI);

    await expect(
      page.locator('#kondisi_kejadian')
    ).toHaveValue(KONDISI);
  }
);

// EC-01 dan EC-03
Then(
  'laporan Emergency Case berhasil dibuat',
  async ({ page }) => {
    await expect(
      page.getByText('Laporan berhasil dikirim!', {
        exact: true,
      })
    ).toBeVisible();
  }
);
