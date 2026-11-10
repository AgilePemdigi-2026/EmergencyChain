
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

// ======================================================
// 1. KONFIGURASI DAN DATA PENGUJIAN
// ======================================================

type AccountKey =
  | 'pelapor'
  | 'polisi'
  | 'ambulansRs'
  | 'ambulansPmi';

type ScenarioState = {
  caseId?: number;
  policeAssignmentId?: number;
  ambulanceAssignmentId?: number;
  ambulanceAccountKey?: 'ambulansRs' | 'ambulansPmi';
};

const commonPassword =
  process.env.E2E_DEMO_PASSWORD ??
  process.env.E2E_PELAPOR_PASSWORD;

const accounts: Record<
  AccountKey,
  { email: string; password?: string }
> = {
  pelapor: {
    email:
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test',
    password: process.env.E2E_PELAPOR_PASSWORD,
  },

  polisi: {
    email:
      process.env.E2E_POLISI_EMAIL ??
      'polisi@emergencychain.test',
    password:
      process.env.E2E_POLISI_PASSWORD ?? commonPassword,
  },

  ambulansRs: {
    email:
      process.env.E2E_AMBULANS_RS_EMAIL ??
      'ambulans.rs@emergencychain.test',
    password:
      process.env.E2E_AMBULANS_RS_PASSWORD ?? commonPassword,
  },

  ambulansPmi: {
    email:
      process.env.E2E_AMBULANS_PMI_EMAIL ??
      'ambulans.independen@emergencychain.test',
    password:
      process.env.E2E_AMBULANS_PMI_PASSWORD ?? commonPassword,
  },
};

const RESOURCE_NAMES = {
  polisi: 'Unit Polisi Demo',
  ambulansRs: 'Ambulans RSUD Dr. Soetomo 01 (Simulasi)',
  ambulansPmi: 'Ambulans PMI Kota Surabaya 01 (Simulasi)',
};

const scenarioStates = new WeakMap<Page, ScenarioState>();

function getState(page: Page): ScenarioState {
  let state = scenarioStates.get(page);

  if (!state) {
    state = {};
    scenarioStates.set(page, state);
  }

  return state;
}

// ======================================================
// 2. HELPER LOGIN
// ======================================================

async function loginAs(
  page: Page,
  accountKey: AccountKey
): Promise<void> {
  const account = accounts[accountKey];

  if (!account.password) {
    throw new Error(
      `Password akun ${accountKey} belum dikonfigurasi.`
    );
  }

  await page.context().clearCookies();
  await page.goto('/login');

  await page.locator('input[name="email"]')
    .fill(account.email);

  await page.locator('input[name="password"]')
    .fill(account.password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login$/);
}

// ======================================================
// 3. HELPER PERSIAPAN EMERGENCY CASE
// ======================================================

// Persiapan data pengujian.
// Pelapor hanya digunakan secara internal untuk membuat
// Emergency Case sebelum pengujian oleh Instansi dimulai.


async function createEmergencyCase(
  page: Page
): Promise<number> {
  await loginAs(page, 'pelapor');

  await page.goto('/pelapor/laporan/buat');

  await expect(
    page.getByRole('heading', {
      name: 'Buat Laporan Emergency',
    })
  ).toBeVisible();

  // Isi jenis kejadian sesuai opsi aktual form.
  await page.locator('#jenis_kejadian').selectOption({
    label: 'Kecelakaan Lalu Lintas',
  });

  await page.locator('#tingkat_kebutuhan').selectOption({
    label: 'Butuh Penanganan Cepat',
  });

  await page.locator('#kondisi_kejadian').fill(
    `BDD Assignment Status: ${Date.now()}.`
  );

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian'
  );

  await page.locator('#location-map').click({
    position: { x: 250, y: 180 },
  });

  await expect(
    page.locator('#latitude')
  ).not.toHaveValue('');

  await expect(
    page.locator('#longitude')
  ).not.toHaveValue('');

  await page.locator('#submit-report').click();

  await expect(
    page.getByText('Laporan berhasil dikirim!', {
      exact: true,
    })
  ).toBeVisible();

  // Ambil Case ID dari laporan terbaru.
  await page.goto('/pelapor/laporan/status');

  const firstRow = page.locator('tbody tr').first();

  await expect(firstRow).toBeVisible();

  const reportHref = await firstRow
    .locator('a[href*="/pelapor/laporan/"]')
    .getAttribute('href');

  const match = reportHref?.match(
    /\/pelapor\/laporan\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      'Case ID tidak berhasil diperoleh dari laporan terbaru.'
    );
  }

  return Number(match[1]);
}

// ======================================================
// 4. HELPER MENGAMBIL ASSIGNMENT
// ======================================================

async function getAssignmentId(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  caseId: number
): Promise<number> {
  await loginAs(page, accountKey);
  await page.goto('/instansi/assignments');

  const caseCard = page.locator('article').filter({
    hasText: new RegExp(
      `Emergency Case #${caseId}\\b`
    ),
  });

  await expect(caseCard).toHaveCount(1);

  const href = await caseCard
    .getByRole('link', {
      name: 'Buka Penanganan',
      exact: true,
    })
    .getAttribute('href');

  const match = href?.match(
    /\/instansi\/assignments\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      `Assignment untuk Case #${caseId} tidak ditemukan.`
    );
  }

  return Number(match[1]);
}

// Ambulans dapat berasal dari RSUD Dr. Soetomo atau PMI.
async function getAmbulanceAssignment(
  page: Page,
  caseId: number
): Promise<{
  assignmentId: number;
  accountKey: 'ambulansRs' | 'ambulansPmi';
}> {
  const ambulanceAccounts = [
    'ambulansRs',
    'ambulansPmi',
  ] as const;

  for (const accountKey of ambulanceAccounts) {
    await loginAs(page, accountKey);
    await page.goto('/instansi/assignments');

    const caseCard = page.locator('article').filter({
      hasText: new RegExp(
        `Emergency Case #${caseId}\\b`
      ),
    });

    if (await caseCard.count() === 0) {
      continue;
    }

    const href = await caseCard
      .getByRole('link', {
        name: 'Buka Penanganan',
        exact: true,
      })
      .getAttribute('href');

    const match = href?.match(
      /\/instansi\/assignments\/(\d+)$/
    );

    if (!match) {
      throw new Error(
        `Assignment Ambulans untuk Case #${caseId} tidak valid.`
      );
    }

    return {
      assignmentId: Number(match[1]),
      accountKey,
    };
  }

  throw new Error(
    `Assignment Ambulans untuk Case #${caseId} tidak ditemukan.`
  );
}

// ======================================================
// 5. HELPER MENYELESAIKAN ASSIGNMENT
// ======================================================

async function completeAssignment(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  assignmentId: number
): Promise<void> {
  await loginAs(page, accountKey);

  await page.goto(
    `/instansi/assignments/${assignmentId}`
  );

  const statusSequence = [
    'menuju_lokasi',
    'tiba_di_lokasi',
    'dalam_penanganan',
    'selesai',
  ];

  for (const [index, status] of statusSequence.entries()) {
    const statusInput = page.locator(
      'input[name="status"]'
    );

    await expect(statusInput).toHaveValue(status);

    await page.locator('#keterangan').fill(
      `BDD Assignment Status: pembaruan ${status}.`
    );

    await page.getByRole('button', {
      name: 'Perbarui Status',
    }).click();

    if (index < statusSequence.length - 1) {
      await expect(statusInput).toHaveValue(
        statusSequence[index + 1]
      );
    }
  }

  await expect(
    page.getByText('Penanganan Selesai', {
      exact: true,
    }).last()
  ).toBeVisible();
}

// ======================================================
// 6. HELPER MEMERIKSA STATUS EMERGENCY CASE
// ======================================================

async function assertEmergencyCaseStatus(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  assignmentId: number,
  expectedStatus: string
): Promise<void> {
  await loginAs(page, accountKey);

  await page.goto(
    `/instansi/assignments/${assignmentId}`
  );

  await expect(
    page.getByText(expectedStatus, {
      exact: true,
    }).last()
  ).toBeVisible();
}

// ======================================================
// 7. AS-01: BASIC ASSIGNMENT STATUS LIFECYCLE
// ======================================================

Given(
  'saya memiliki penugasan Polisi yang aktif untuk ditangani',
  async ({ page }) => {
    const caseId = await createEmergencyCase(page);

    const assignmentId = await getAssignmentId(
      page,
      'polisi',
      caseId
    );

    const state = getState(page);

    state.caseId = caseId;
    state.policeAssignmentId = assignmentId;
  }
);

When(
  'saya membuka dashboard penugasan',
  async ({ page }) => {
    await loginAs(page, 'polisi');

    await page.goto('/instansi/assignments');

    await expect(
      page.getByRole('heading', {
        name: 'Dashboard Penanganan',
      })
    ).toBeVisible();
  }
);

When(
  'saya membuka detail penugasan Polisi tersebut',
  async ({ page }) => {
    const assignmentId =
      getState(page).policeAssignmentId;

    expect(assignmentId).toBeDefined();

    await page.goto(
      `/instansi/assignments/${assignmentId}`
    );

    await expect(page).toHaveURL(
      /\/instansi\/assignments\/\d+$/
    );
  }
);

When(
  'saya memperbarui status penanganan secara berurutan hingga selesai',
  async ({ page }) => {
    const statusSequence = [
      'menuju_lokasi',
      'tiba_di_lokasi',
      'dalam_penanganan',
      'selesai',
    ];

    for (const [index, status] of statusSequence.entries()) {
      const statusInput = page.locator(
        'input[name="status"]'
      );

      await expect(statusInput).toHaveValue(status);

      await page.locator('#keterangan').fill(
        `BDD AS-01: pembaruan status ${status}.`
      );

      await page.getByRole('button', {
        name: 'Perbarui Status',
      }).click();

      if (index < statusSequence.length - 1) {
        await expect(statusInput).toHaveValue(
          statusSequence[index + 1]
        );
      }
    }
  }
);

Then(
  'saya melihat penugasan Polisi telah selesai',
  async ({ page }) => {
    await expect(
      page.getByText('Penanganan Selesai', {
        exact: true,
      }).last()
    ).toBeVisible();

    await expect(
      page.getByText(
        'Assignment ini sudah selesai dan tidak dapat diperbarui lagi.',
        { exact: true }
      )
    ).toBeVisible();
  }
);

// ======================================================
// 8. AS-02: SINKRONISASI STATUS EMERGENCY CASE
// ======================================================

Given(
  'saya memiliki Emergency Case dengan penugasan Ambulans dan Polisi',
  async ({ page }) => {
    const caseId = await createEmergencyCase(page);

    const policeAssignmentId = await getAssignmentId(
      page,
      'polisi',
      caseId
    );

    const ambulance = await getAmbulanceAssignment(
      page,
      caseId
    );

    const state = getState(page);

    state.caseId = caseId;
    state.policeAssignmentId = policeAssignmentId;
    state.ambulanceAssignmentId = ambulance.assignmentId;
    state.ambulanceAccountKey = ambulance.accountKey;
  }
);

When(
  'saya menyelesaikan penugasan Ambulans',
  async ({ page }) => {
    const state = getState(page);

    expect(state.ambulanceAssignmentId).toBeDefined();
    expect(state.ambulanceAccountKey).toBeDefined();

    await completeAssignment(
      page,
      state.ambulanceAccountKey!,
      state.ambulanceAssignmentId!
    );
  }
);

Then(
  'saya melihat Emergency Case masih berstatus Menunggu',
  async ({ page }) => {
    const state = getState(page);

    expect(state.ambulanceAssignmentId).toBeDefined();
    expect(state.ambulanceAccountKey).toBeDefined();

    await assertEmergencyCaseStatus(
      page,
      state.ambulanceAccountKey!,
      state.ambulanceAssignmentId!,
      'Menunggu'
    );
  }
);

When(
  'saya menyelesaikan penugasan Polisi',
  async ({ page }) => {
    const state = getState(page);

    expect(state.policeAssignmentId).toBeDefined();

    await completeAssignment(
      page,
      'polisi',
      state.policeAssignmentId!
    );
  }
);

Then(
  'saya melihat Emergency Case berstatus Selesai',
  async ({ page }) => {
    const state = getState(page);

    expect(state.policeAssignmentId).toBeDefined();

    await assertEmergencyCaseStatus(
      page,
      'polisi',
      state.policeAssignmentId!,
      'Selesai'
    );
  }
);

// ======================================================
// 9. AS-03: RESOURCE KEMBALI TERSEDIA
// ======================================================

Given(
  'saya memiliki unit Polisi yang sedang ditugaskan',
  async ({ page }) => {
    const caseId = await createEmergencyCase(page);

    const assignmentId = await getAssignmentId(
      page,
      'polisi',
      caseId
    );

    const state = getState(page);

    state.caseId = caseId;
    state.policeAssignmentId = assignmentId;

    await loginAs(page, 'polisi');

    await page.goto('/instansi/units');

    const row = page.locator('tbody tr').filter({
      hasText: RESOURCE_NAMES.polisi,
    });

    await expect(row).toHaveCount(1);

    await expect(
      row.getByText('Sedang Ditugaskan', {
        exact: true,
      })
    ).toBeVisible();
  }
);

When(
  'saya menyelesaikan penugasan Polisi tersebut',
  async ({ page }) => {
    const assignmentId =
      getState(page).policeAssignmentId;

    expect(assignmentId).toBeDefined();

    await completeAssignment(
      page,
      'polisi',
      assignmentId!
    );
  }
);


Then(
  'saya melihat unit Polisi kembali tersedia',
  async ({ page }) => {
    await loginAs(page, 'polisi');

    await page.goto('/instansi/units');

    const row = page.locator('tbody tr').filter({
      hasText: RESOURCE_NAMES.polisi,
    });

    await expect(row).toHaveCount(1);

    const availabilitySelect = row.locator(
      'select[name="status_ketersediaan"]'
    );

    await expect(availabilitySelect).toHaveCount(1);

    await expect(availabilitySelect).toHaveValue(
      'tersedia'
    );
  }
);

