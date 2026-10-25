
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

let kondisiBaru = '';
let kebutuhanBaru = '';
let detailUrl = '';

async function loginPetugas(page: Page) {
  const email = process.env.E2E_PETUGAS_EMAIL;
  const password = process.env.E2E_PETUGAS_PASSWORD;

  if (!email || !password) {
    throw new Error(
      'E2E_PETUGAS_EMAIL dan E2E_PETUGAS_PASSWORD wajib tersedia.'
    );
  }

  await page.goto('/login');

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).toHaveURL(
    /\/petugas-ec\/emergency-cases$/
  );
}

// =====================================
// GIVEN
// =====================================

Given(
  'Petugas EC telah login dan membuka detail laporan testing',
  async ({ page }) => {
    // Reset data sementara setiap skenario.
    kondisiBaru = '';
    kebutuhanBaru = '';
    detailUrl = '';

    await loginPetugas(page);

    // Memilih laporan yang tersedia pada database testing.
    const detailLink = page.locator(
      'a[href*="/petugas-ec/emergency-cases/"]'
    ).first();

    await expect(detailLink).toBeVisible();
    await detailLink.click();

    await expect(page).toHaveURL(
      /\/petugas-ec\/emergency-cases\/\d+$/
    );

    detailUrl = page.url();

    await expect(
      page.locator('#edit-case-button')
    ).toBeVisible();
  }
);

// =====================================
// WHEN - MEMBUKA FORM EDIT
// =====================================

When(
  'Petugas EC membuka formulir edit informasi',
  async ({ page }) => {
    await page.locator('#edit-case-button').click();

    await expect(
      page.locator('#case-edit-form')
    ).toBeVisible();

    await expect(
      page.locator('#edit-kondisi')
    ).toBeVisible();

    await expect(
      page.locator('#edit-kebutuhan')
    ).toBeVisible();
  }
);

// =====================================
// MEC-01: UPDATE KEBUTUHAN
// =====================================

When(
  'Petugas EC mengubah kebutuhan penanganan',
  async ({ page }) => {
    const kebutuhan = page.locator('#edit-kebutuhan');

    const kebutuhanAwal = await kebutuhan.inputValue();

    const opsiKebutuhan = [
      'Butuh Penanganan Cepat',
      'Butuh Penanganan Segera',
    ];

    // Pilih opsi berbeda dari nilai sebelumnya.
    const opsiBaru = opsiKebutuhan.find(
      (opsi) => opsi !== kebutuhanAwal
    );

    if (!opsiBaru) {
      throw new Error(
        `Nilai kebutuhan tidak dikenali: ${kebutuhanAwal}`
      );
    }

    await kebutuhan.selectOption({
      label: opsiBaru,
    });

    kebutuhanBaru = await kebutuhan.inputValue();

    expect(kebutuhanBaru).not.toBe(kebutuhanAwal);
  }
);

// =====================================
// MEC-02 DAN MEC-03: KONDISI KOSONG
// =====================================

When(
  'Petugas EC mengosongkan kondisi kejadian',
  async ({ page }) => {
    await page.locator('#edit-kondisi').fill('');

    await expect(
      page.locator('#edit-kondisi')
    ).toHaveValue('');
  }
);

// =====================================
// MEC-02: MELENGKAPI INFORMASI
// =====================================

When(
  'Petugas EC melengkapi kondisi kejadian',
  async ({ page }) => {
    kondisiBaru =
      `BDD MEC-02: Kondisi dilengkapi ${Date.now()}`;

    await page.locator('#edit-kondisi').fill(kondisiBaru);

    await expect(
      page.locator('#edit-kondisi')
    ).toHaveValue(kondisiBaru);
  }
);

// =====================================
// MEC-04: MENGUBAH KONDISI
// =====================================

When(
  'Petugas EC mengubah kondisi kejadian',
  async ({ page }) => {
    kondisiBaru =
      `BDD MEC-04: Kondisi diperbarui ${Date.now()}`;

    await page.locator('#edit-kondisi').fill(kondisiBaru);

    await expect(
      page.locator('#edit-kondisi')
    ).toHaveValue(kondisiBaru);
  }
);

// =====================================
// MENYIMPAN PERUBAHAN
// =====================================

When(
  'Petugas EC menyimpan perubahan informasi',
  async ({ page }) => {
    await page.getByRole('button', {
      name: 'Simpan Perubahan',
    }).click();
  }
);

// =====================================
// MEC-03: MENCOBA SIMPAN DATA TIDAK VALID
// =====================================

When(
  'Petugas EC mencoba menyimpan perubahan tidak valid',
  async ({ page }) => {
    await page.getByRole('button', {
      name: 'Simpan Perubahan',
    }).click();
  }
);

// =====================================
// THEN - PEMBARUAN BERHASIL
// =====================================

Then(
  'sistem menampilkan pesan pembaruan berhasil',
  async ({ page }) => {
    await expect(
      page.getByText(
        'Emergency Case berhasil diperbarui'
      )
    ).toBeVisible();

    await expect(
      page.locator('#case-view-mode')
    ).toBeVisible();
  }
);

// =====================================
// MEC-01: KEBUTUHAN TERBARU
// =====================================

Then(
  'kebutuhan penanganan terbaru ditampilkan',
  async ({ page }) => {
    await expect(
      page.locator('#case-view-mode').getByText(
        kebutuhanBaru,
        { exact: true }
      )
    ).toBeVisible();
  }
);

// =====================================
// MEC-02: KONDISI SUDAH DILENGKAPI
// =====================================

Then(
  'kondisi kejadian yang dilengkapi ditampilkan',
  async ({ page }) => {
    await expect(
      page.locator('#case-view-mode').getByText(
        kondisiBaru,
        { exact: true }
      )
    ).toBeVisible();
  }
);

// =====================================
// MEC-03: VALIDASI INPUT KOSONG
// =====================================

Then(
  'browser menolak pembaruan Emergency Case',
  async ({ page }) => {
    const kondisi = page.locator('#edit-kondisi');

    const tidakValid = await kondisi.evaluate(
      (element: HTMLTextAreaElement) =>
        !element.validity.valid
    );

    expect(tidakValid).toBe(true);

    // Form masih dalam mode edit karena submit diblokir.
    await expect(
      page.locator('#case-edit-form')
    ).toBeVisible();
  }
);

Then(
  'Petugas EC tetap berada di halaman detail laporan',
  async ({ page }) => {
    expect(page.url()).toBe(detailUrl);

    await expect(
      page.locator('#edit-kondisi')
    ).toHaveValue('');
  }
);

// =====================================
// MEC-04: RELOAD & VERIFIKASI
// =====================================

When(
  'Petugas EC memuat ulang halaman detail laporan',
  async ({ page }) => {
    await page.reload();
  }
);

Then(
  'kondisi kejadian terbaru tetap ditampilkan',
  async ({ page }) => {
    await expect(
      page.locator('#case-view-mode').getByText(
        kondisiBaru,
        { exact: true }
      )
    ).toBeVisible();
  }
);
