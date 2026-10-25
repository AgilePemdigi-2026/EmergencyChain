
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

// Password valid khusus untuk akun dummy yang dibuat saat testing.
const PASSWORD_VALID = 'E2ETesting!2026Aa';

function emailUnik(): string {
  return `bdd.reg.${Date.now()}.${Math.floor(Math.random() * 100000)}@example.test`;
}

// Helper untuk mengisi form registrasi.
async function isiFormRegister(
  page: Page,
  email: string,
  konfirmasiPassword: string = PASSWORD_VALID
) {
  await page.locator('#nama').fill('Pelapor BDD Testing');
  await page.locator('#email').fill(email);
  await page.locator('#no_telp').fill('081234567890');
  await page.locator('#password').fill(PASSWORD_VALID);
  await page.locator('#password_confirmation').fill(
    konfirmasiPassword
  );
  await page.locator('#agreement').check();
}

async function klikDaftar(page: Page) {
  await page.getByRole('button', {
    name: 'Daftar',
    exact: true,
  }).click();
}

// ============================
// GIVEN
// ============================

Given(
  'calon Pelapor berada di halaman registrasi',
  async ({ page }) => {
    await page.goto('/register');

    await expect(
      page.getByRole('heading', {
        name: 'Daftar Akun EmergencyChain',
      })
    ).toBeVisible();
  }
);

Given(
  'email Pelapor testing sudah terdaftar',
  async () => {
    if (!process.env.E2E_PELAPOR_EMAIL) {
      throw new Error(
        'E2E_PELAPOR_EMAIL belum diatur pada terminal Playwright.'
      );
    }
  }
);

// ============================
// WHEN
// ============================

// REG-01: Data valid
When(
  'calon Pelapor mengisi seluruh data registrasi yang valid',
  async ({ page }) => {
    await isiFormRegister(page, emailUnik());
  }
);

// REG-02: Field wajib kosong
When(
  'calon Pelapor membiarkan field wajib kosong',
  async ({ page }) => {
    await expect(page.locator('#nama')).toHaveValue('');
    await expect(page.locator('#email')).toHaveValue('');
    await expect(page.locator('#password')).toHaveValue('');
    await expect(
      page.locator('#agreement')
    ).not.toBeChecked();
  }
);

When(
  'calon Pelapor mencoba mengirimkan formulir registrasi',
  async ({ page }) => {
    await klikDaftar(page);
  }
);

// REG-03: Email duplikat
When(
  'calon Pelapor mengisi registrasi menggunakan email tersebut',
  async ({ page }) => {
    const email = process.env.E2E_PELAPOR_EMAIL!;

    await isiFormRegister(page, email);
  }
);

// REG-04: Konfirmasi password berbeda
When(
  'calon Pelapor mengisi data valid dengan konfirmasi password berbeda',
  async ({ page }) => {
    await isiFormRegister(
      page,
      emailUnik(),
      'PasswordBerbeda!2026'
    );
  }
);

// Submit untuk REG-01, REG-03, REG-04
When(
  'calon Pelapor mengirimkan formulir registrasi',
  async ({ page }) => {
    await klikDaftar(page);
  }
);

// ============================
// THEN
// ============================

// REG-01
Then(
  'akun Pelapor berhasil didaftarkan',
  async ({ page }) => {
    await expect(page).toHaveURL(
      /\/pelapor\/laporan\/buat$/
    );
  }
);

Then(
  'calon Pelapor berhasil masuk ke sistem',
  async ({ page }) => {
    // Jika sesi login aktif, Create EC dapat diakses.
    await page.goto('/pelapor/laporan/buat');

    await expect(page).toHaveURL(
      /\/pelapor\/laporan\/buat$/
    );

    await expect(
      page.locator('#emergency-report-form')
    ).toBeVisible();
  }
);

// REG-02
Then(
  'registrasi ditolak karena data wajib belum lengkap',
  async ({ page }) => {
    // Validasi native browser mencegah form kosong terkirim.
    const nama = page.locator('#nama');

    await expect(nama).toBeEmpty();

    const invalid = await nama.evaluate(
      (element: HTMLInputElement) =>
        !element.validity.valid
    );

    expect(invalid).toBe(true);
  }
);

// REG-03
Then(
  'registrasi ditolak karena email sudah terdaftar',
  async ({ page }) => {
    await expect(
      page.getByRole('alert')
    ).toContainText(
      'Periksa kembali data pendaftaran Anda.'
    );

    // Validasi ditampilkan pada field email.
    const emailField = page.locator('#email');

    await expect(emailField).toHaveClass(
      /border-red-500/
    );
  }
);

// REG-04
Then(
  'registrasi ditolak karena konfirmasi password tidak sesuai',
  async ({ page }) => {
    await expect(
      page.getByRole('alert')
    ).toContainText(
      'Periksa kembali data pendaftaran Anda.'
    );

    // Validator Laravel menggunakan aturan "confirmed"
    // pada field password.
    await expect(
      page.locator('#password')
    ).toHaveClass(/border-red-500/);
  }
);

// Dipakai REG-02, REG-03, REG-04
Then(
  'calon Pelapor tetap berada di halaman registrasi',
  async ({ page }) => {
    await expect(page).toHaveURL(/\/register$/);

    await expect(
      page.getByRole('heading', {
        name: 'Daftar Akun EmergencyChain',
      })
    ).toBeVisible();
  }
);
