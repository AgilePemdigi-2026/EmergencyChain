
import { execFileSync } from 'node:child_process';
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

const DEMO_PASSWORD =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const PELAPOR = {
  email:
    process.env.E2E_PELAPOR_EMAIL ??
    'pelapor@emergencychain.test',
  password:
    process.env.E2E_PELAPOR_PASSWORD ?? DEMO_PASSWORD,
};

const PELAPOR_LAIN = {
  email: 'pelapor.lain@emergencychain.test',
  password: DEMO_PASSWORD,
};

const REPORT_ADDRESS =
  'Jl. Keputih, Surabaya - Lokasi pengujian Status Laporan';

type ReportData = {
  caseId?: number;
  caseNumber?: string;
  otherCaseId?: number;
  responseStatus?: number;
};

const scenarioStates = new WeakMap<Page, ReportData>();

function getState(page: Page): ReportData {
  let state = scenarioStates.get(page);

  if (!state) {
    state = {};
    scenarioStates.set(page, state);
  }

  return state;
}

// =====================================================
// LOGIN
// =====================================================

async function loginAs(
  page: Page,
  email: string,
  password: string
): Promise<void> {
  await page.context().clearCookies();
  await page.goto('/login');

  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login$/);
}

async function loginPelapor(page: Page): Promise<void> {
  await loginAs(page, PELAPOR.email, PELAPOR.password);
}

// =====================================================
// MEMBUAT EMERGENCY CASE UNTUK DATA PENGUJIAN
// =====================================================

async function submitAccidentReport(
  page: Page
): Promise<void> {
  await page.goto('/pelapor/laporan/buat');

  await expect(
    page.getByRole('heading', {
      name: 'Buat Laporan Emergency',
    })
  ).toBeVisible();

  await page.locator('#jenis_kejadian').selectOption({
    label: 'Kecelakaan Lalu Lintas',
  });

  await page.locator('#tingkat_kebutuhan').selectOption({
    label: 'Butuh Penanganan Cepat',
  });

  await page.locator('#kondisi_kejadian').fill(
    `BDD Status Laporan: kecelakaan lalu lintas ${Date.now()}.`
  );

  await page.locator('#alamat').fill(REPORT_ADDRESS);

  await expect(page.locator('#location-map')).toBeVisible();

  await page.locator('#location-map').click({
    position: {
      x: 250,
      y: 180,
    },
  });

  await expect(page.locator('#latitude')).not.toHaveValue('');
  await expect(page.locator('#longitude')).not.toHaveValue('');

  await page.locator('#submit-report').click();

  await expect(
    page.getByText('Laporan berhasil dikirim!', {
      exact: true,
    })
  ).toBeVisible();
}

// =====================================================
// MENGAMBIL CASE ID DARI DAFTAR LAPORAN
// =====================================================

async function captureLatestReport(
  page: Page
): Promise<{ caseId: number; caseNumber: string }> {
  await page.goto('/pelapor/laporan/status');

  const firstReportLink = page
    .locator('tbody tr')
    .first()
    .locator('a[href*="/pelapor/laporan/"]')
    .first();

  await expect(firstReportLink).toBeVisible();

  const href = await firstReportLink.getAttribute('href');
  const caseNumber = (await firstReportLink.innerText()).trim();

  const match = href?.match(/\/pelapor\/laporan\/(\d+)$/);

  if (!match) {
    throw new Error(
      `Tidak dapat membaca Case ID dari tautan: ${href}`
    );
  }

  return {
    caseId: Number(match[1]),
    caseNumber,
  };
}

// =====================================================
// MEMBUAT LAPORAN DENGAN AKUN TERTENTU
// =====================================================

async function createReportAs(
  page: Page,
  email: string,
  password: string
): Promise<{ caseId: number; caseNumber: string }> {
  await loginAs(page, email, password);

  await submitAccidentReport(page);

  return captureLatestReport(page);
}

// =====================================================
// MENYIAPKAN AKUN PELAPOR KEDUA
// =====================================================

function createSecondPelaporAccount(): void {
  const expression = String.raw`
    \App\Models\Pengguna::firstOrCreate(
      ['email' => 'pelapor.lain@emergencychain.test'],
      [
        'nama' => 'Pelapor Lain Demo',
        'no_telp' => '081200000099',
        'role' => 'pelapor',
        'password' => \Illuminate\Support\Facades\Hash::make('Demo12345!')
      ]
    );
  `;

  execFileSync(
    'php',
    [
      'artisan',
      `--env=testing`,
      'tinker',
      `--execute=${expression}`,
    ],
    {
      cwd: process.cwd(),
      stdio: 'pipe',
      env: {
        ...process.env,
        APP_ENV: 'testing',
      },
    }
  );
}

// =====================================================
// PR-01: DAFTAR LAPORAN
// =====================================================

Given(
  'Pelapor telah memiliki laporan Emergency Case',
  async ({ page }) => {
    const report = await createReportAs(
      page,
      PELAPOR.email,
      PELAPOR.password
    );

    const state = getState(page);

    state.caseId = report.caseId;
    state.caseNumber = report.caseNumber;
  }
);

When(
  'Pelapor membuka halaman Status Laporan',
  async ({ page }) => {
    await loginPelapor(page);
    await page.goto('/pelapor/laporan/status');

    await expect(
      page.getByRole('heading', {
        name: 'Status Laporan Emergency Case',
      })
    ).toBeVisible();
  }
);

Then(
  'daftar laporan milik Pelapor ditampilkan',
  async ({ page }) => {
    const state = getState(page);

    const reportRow = page
      .locator('tbody tr')
      .filter({ hasText: state.caseNumber! });

    await expect(reportRow).toBeVisible();

    await expect(
      reportRow.getByText('Kecelakaan Lalu Lintas', {
        exact: true,
      })
    ).toBeVisible();
  }
);

Then(
  'informasi laporan ditampilkan dengan lengkap',
  async ({ page }) => {
    const state = getState(page);

    const reportRow = page
      .locator('tbody tr')
      .filter({ hasText: state.caseNumber! });

    await expect(
      reportRow.getByText(state.caseNumber!, {
        exact: true,
      })
    ).toBeVisible();

    await expect(reportRow).toContainText(REPORT_ADDRESS);

    await expect(
      reportRow.getByText('Menunggu', {
        exact: true,
      })
    ).toBeVisible();
  }
);

// =====================================================
// PR-02: PENCARIAN CASE ID
// =====================================================

When(
  'Pelapor mencari laporan berdasarkan Case ID',
  async ({ page }) => {
    const state = getState(page);

    await page.goto('/pelapor/laporan/status');

    await page.locator('#search').fill(state.caseNumber!);

    await page.getByRole('button', {
      name: 'Cari',
      exact: true,
    }).click();
  }
);

Then(
  'laporan dengan Case ID yang dicari ditampilkan',
  async ({ page }) => {
    const state = getState(page);

    await expect(
      page.getByRole('link', {
        name: state.caseNumber!,
        exact: true,
      })
    ).toBeVisible();
  }
);

// =====================================================
// PR-03: PENCARIAN JENIS KEJADIAN
// =====================================================

When(
  'Pelapor mencari laporan berdasarkan jenis kejadian',
  async ({ page }) => {
    await page.goto('/pelapor/laporan/status');

    await page.locator('#search').fill('Kecelakaan');

    await page.getByRole('button', {
      name: 'Cari',
      exact: true,
    }).click();
  }
);

Then(
  'laporan dengan jenis kejadian yang sesuai ditampilkan',
  async ({ page }) => {
    const state = getState(page);

    const reportRow = page
      .locator('tbody tr')
      .filter({ hasText: state.caseNumber! });

    await expect(reportRow).toBeVisible();

    await expect(
      reportRow.getByText('Kecelakaan Lalu Lintas', {
        exact: true,
      })
    ).toBeVisible();
  }
);

// =====================================================
// PR-04: FILTER STATUS
// =====================================================

When(
  'Pelapor memfilter laporan dengan status Menunggu',
  async ({ page }) => {
    await page.goto('/pelapor/laporan/status');

    await page.locator('#status').selectOption('Menunggu');

    await page.getByRole('button', {
      name: 'Cari',
      exact: true,
    }).click();
  }
);

Then(
  'hanya laporan dengan status Menunggu yang ditampilkan',
  async ({ page }) => {
    const state = getState(page);

    const reportRow = page
      .locator('tbody tr')
      .filter({ hasText: state.caseNumber! });

    await expect(reportRow).toBeVisible();

    await expect(
      reportRow.getByText('Menunggu', {
        exact: true,
      })
    ).toBeVisible();

    await expect(
      page.locator('#status')
    ).toHaveValue('Menunggu');
  }
);

// =====================================================
// PR-05: DETAIL LAPORAN MILIK SENDIRI
// =====================================================

When(
  'Pelapor memilih laporan miliknya',
  async ({ page }) => {
    const state = getState(page);

    await page.getByRole('link', {
      name: state.caseNumber!,
      exact: true,
    }).click();
  }
);

Then(
  'halaman detail laporan berhasil ditampilkan',
  async ({ page }) => {
    const state = getState(page);

    // Memastikan URL detail sesuai Case ID.
    await expect(page).toHaveURL(
      new RegExp(`/pelapor/laporan/${state.caseId}$`)
    );

    // Memastikan halaman detail ditampilkan.
    await expect(
      page.getByRole('heading', {
        name: 'Detail Emergency Case',
        exact: true,
      })
    ).toBeVisible();

    // Memastikan Case ID pada Informasi Laporan benar.
    const caseIdValue = page
      .getByText('Case ID', { exact: true })
      .locator('xpath=following-sibling::p[1]');

    await expect(caseIdValue).toHaveText(state.caseNumber!);
  }
);

// =====================================================
// PR-06: AKSES LAPORAN PELAPOR LAIN
// =====================================================

Given(
  'Pelapor lain telah memiliki laporan Emergency Case',
  async ({ page }) => {
    createSecondPelaporAccount();

    const report = await createReportAs(
      page,
      PELAPOR_LAIN.email,
      PELAPOR_LAIN.password
    );

    getState(page).otherCaseId = report.caseId;
  }
);

When(
  'Pelapor membuka detail laporan milik Pelapor lain',
  async ({ page }) => {
    const state = getState(page);

    await loginPelapor(page);

    const response = await page.goto(
      `/pelapor/laporan/${state.otherCaseId}`
    );

    state.responseStatus = response?.status();
  }
);

Then(
  'sistem menolak akses ke laporan tersebut',
  async ({ page }) => {
    const state = getState(page);

    expect(state.responseStatus).toBe(404);
  }
);

// =====================================================
// PR-07: LAPORAN TIDAK DITEMUKAN
// =====================================================

When(
  'Pelapor mencari laporan yang tidak tersedia',
  async ({ page }) => {
    await page.goto('/pelapor/laporan/status');

    await page.locator('#search').fill(
      'Data Laporan Tidak Ditemukan 999999'
    );

    await page.getByRole('button', {
      name: 'Cari',
      exact: true,
    }).click();
  }
);

Then(
  'sistem menampilkan pesan Belum ada laporan ditemukan',
  async ({ page }) => {
    await expect(
      page.getByText('Belum ada laporan ditemukan', {
        exact: true,
      })
    ).toBeVisible();
  }
);

