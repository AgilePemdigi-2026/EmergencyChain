import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

// ======================================================
// 1. KONFIGURASI PENGUJIAN
// ======================================================

type AccountKey = 'pelapor' | 'polisi' | 'ambulansRs' | 'ambulansPmi';

type Availability =
  | 'tersedia'
  | 'tidak_tersedia'
  | 'sedang_ditugaskan';

type ScenarioState = {
  caseId?: number;
  firstCaseId?: number;
  waitingCaseId?: number;
  policeAssignmentId?: number;
};

const DEMO_PASSWORD =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const ACCOUNTS: Record<
  AccountKey,
  { email: string; password: string }
> = {
  pelapor: {
    email: process.env.E2E_PELAPOR_EMAIL
      ?? 'pelapor@emergencychain.test',
    password: process.env.E2E_PELAPOR_PASSWORD
      ?? DEMO_PASSWORD,
  },

  polisi: {
    email: process.env.E2E_POLISI_EMAIL
      ?? 'polisi@emergencychain.test',
    password: process.env.E2E_POLISI_PASSWORD
      ?? DEMO_PASSWORD,
  },

  ambulansRs: {
    email: process.env.E2E_AMBULANS_RS_EMAIL
      ?? 'ambulans.rs@emergencychain.test',
    password: process.env.E2E_AMBULANS_RS_PASSWORD
      ?? DEMO_PASSWORD,
  },

  ambulansPmi: {
    email: process.env.E2E_AMBULANS_PMI_EMAIL
      ?? 'ambulans.independen@emergencychain.test',
    password: process.env.E2E_AMBULANS_PMI_PASSWORD
      ?? DEMO_PASSWORD,
  },
};

const RESOURCE_NAMES = {
  polisi: 'Unit Polisi Demo',
  ambulansRs: 'Ambulans RSUD Dr. Soetomo 01 (Simulasi)',
  ambulansPmi: 'Ambulans PMI Kota Surabaya 01 (Simulasi)',
};

const scenarioStates = new WeakMap<Page, ScenarioState>();

function getScenarioState(page: Page): ScenarioState {
  let state = scenarioStates.get(page);

  if (!state) {
    state = {};
    scenarioStates.set(page, state);
  }

  return state;
}

// ======================================================
// 2. PERSIAPAN DATABASE
// ======================================================



// ======================================================
// 3. HELPER LOGIN DAN PENGELOLAAN RESOURCE
// ======================================================

async function loginAs(
  page: Page,
  accountKey: AccountKey
): Promise<void> {
  const account = ACCOUNTS[accountKey];

  await page.context().clearCookies();
  await page.goto('/login');

  await page.locator('input[name="email"]').fill(account.email);
  await page.locator('input[name="password"]').fill(account.password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login$/);
}

async function assertResourceStatus(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  resourceName: string,
  expectedStatus: Availability
): Promise<void> {
  await loginAs(page, accountKey);
  await page.goto('/instansi/units');

  await expect(
    page.getByRole('heading', { name: 'Kelola Unit' })
  ).toBeVisible();

  const row = page.locator('tbody tr').filter({
    hasText: resourceName,
  });

  await expect(row).toHaveCount(1);

  if (expectedStatus === 'sedang_ditugaskan') {
    await expect(
      row.getByText('Sedang Ditugaskan', { exact: true })
    ).toBeVisible();

    return;
  }

  await expect(
    row.locator('select[name="status_ketersediaan"]')
  ).toHaveValue(expectedStatus);
}

async function setResourceAvailability(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  resourceName: string,
  status: 'tersedia' | 'tidak_tersedia'
): Promise<void> {
  await loginAs(page, accountKey);
  await page.goto('/instansi/units');

  const row = page.locator('tbody tr').filter({
    hasText: resourceName,
  });

  await expect(row).toHaveCount(1);

  const select = row.locator(
    'select[name="status_ketersediaan"]'
  );

  await expect(select).toHaveCount(1);

  if (await select.inputValue() !== status) {
    await select.selectOption(status);

    await expect(
      page.getByText(
        'Ketersediaan unit berhasil diperbarui.',
        { exact: true }
      )
    ).toBeVisible();
  }

  await expect(select).toHaveValue(status);
}

async function ensureAllResourcesAvailable(
  page: Page
): Promise<void> {
  await assertResourceStatus(
    page,
    'polisi',
    RESOURCE_NAMES.polisi,
    'tersedia'
  );

  await assertResourceStatus(
    page,
    'ambulansRs',
    RESOURCE_NAMES.ambulansRs,
    'tersedia'
  );

  await assertResourceStatus(
    page,
    'ambulansPmi',
    RESOURCE_NAMES.ambulansPmi,
    'tersedia'
  );
}

// ======================================================
// 4. HELPER PEMBUATAN EMERGENCY CASE
// ======================================================

async function openReportForm(page: Page): Promise<void> {
  await loginAs(page, 'pelapor');
  await page.goto('/pelapor/laporan/buat');

  await expect(
    page.getByRole('heading', {
      name: 'Buat Laporan Emergency',
    })
  ).toBeVisible();
}

async function fillAndSubmitAccidentReport(
  page: Page
): Promise<void> {
  await page.locator('#jenis_kejadian').selectOption({
    label: 'Kecelakaan Lalu Lintas',
  });

  await page.locator('#tingkat_kebutuhan').selectOption({
    label: 'Butuh Penanganan Cepat',
  });

  await page.locator('#kondisi_kejadian').fill(
    `BDD Resource Matching: kecelakaan lalu lintas ${Date.now()}.`
  );

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian Resource Matching'
  );

  await expect(
    page.locator('#location-map')
  ).toBeVisible();

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
}

async function captureLatestCaseId(
  page: Page
): Promise<number> {
  await page.goto('/pelapor/laporan/status');

  const firstCaseLink = page
    .locator('tbody tr')
    .first()
    .locator('a[href*="/pelapor/laporan/"]')
    .first();

  await expect(firstCaseLink).toBeVisible();

  const href = await firstCaseLink.getAttribute('href');

  const match = href?.match(
    /\/pelapor\/laporan\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      `Case ID tidak ditemukan dari tautan: ${href}`
    );
  }

  return Number(match[1]);
}

async function createAccidentReportAndCaptureId(
  page: Page
): Promise<number> {
  await openReportForm(page);
  await fillAndSubmitAccidentReport(page);

  return captureLatestCaseId(page);
}

// ======================================================
// 5. HELPER PEMERIKSAAN ASSIGNMENT
// ======================================================

async function countAssignmentsForCase(
  page: Page,
  accountKey: Exclude<AccountKey, 'pelapor'>,
  caseId: number
): Promise<number> {
  await loginAs(page, accountKey);
  await page.goto('/instansi/assignments');

  await expect(
    page.getByRole('heading', {
      name: 'Dashboard Penanganan',
    })
  ).toBeVisible();

  const caseCard = page.locator('article').filter({
    hasText: new RegExp(
      `Emergency Case #${caseId}\\b`
    ),
  });

  return caseCard.count();
}

async function getAssignmentIdForCase(
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
      `Assignment ID tidak ditemukan untuk Case #${caseId}.`
    );
  }

  return Number(match[1]);
}

async function countAmbulanceAssignments(
  page: Page,
  caseId: number
): Promise<number> {
  const rsCount = await countAssignmentsForCase(
    page,
    'ambulansRs',
    caseId
  );

  const pmiCount = await countAssignmentsForCase(
    page,
    'ambulansPmi',
    caseId
  );

  return rsCount + pmiCount;
}

async function assertNoDuplicateAssignments(
  page: Page,
  caseId: number
): Promise<void> {
  const policeCount = await countAssignmentsForCase(
    page,
    'polisi',
    caseId
  );

  const ambulanceCount = await countAmbulanceAssignments(
    page,
    caseId
  );

  expect(policeCount).toBeLessThanOrEqual(1);
  expect(ambulanceCount).toBeLessThanOrEqual(1);
}

async function assertReportStatus(
  page: Page,
  caseId: number,
  expectedStatus: string
): Promise<void> {
  await loginAs(page, 'pelapor');
  await page.goto(`/pelapor/laporan/${caseId}`);

  await expect(
    page.getByText(expectedStatus, {
      exact: true,
    }).first()
  ).toBeVisible();
}


// ======================================================
// 6. HELPER PENYELESAIAN ASSIGNMENT POLISI
// ======================================================

async function completePoliceAssignment(
  page: Page,
  assignmentId: number
): Promise<void> {
  await loginAs(page, 'polisi');

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
    await expect(
      page.locator('input[name="status"]')
    ).toHaveValue(status);

    await page.locator('#keterangan').fill(
      `BDD Resource Matching: pembaruan status ${status}.`
    );

    await page.getByRole('button', {
      name: 'Perbarui Status',
    }).click();

    if (index < statusSequence.length - 1) {
      await expect(
        page.locator('input[name="status"]')
      ).toHaveValue(statusSequence[index + 1]);
    }
  }

    await expect(
    page.getByText(
        'Assignment ini sudah selesai dan tidak dapat diperbarui lagi.',
        { exact: true }
    )
    ).toBeVisible();

    await expect(
    page.getByText(
        'Penanganan selesai. Resource tersedia kembali dan matching ulang telah dijalankan.',
        { exact: true }
    )
    ).toBeVisible();

}

async function assertAssignmentCompleted(
  page: Page,
  assignmentId: number
): Promise<void> {
  await loginAs(page, 'polisi');

  await page.goto(
    `/instansi/assignments/${assignmentId}`
  );

  await expect(
    page.getByText('Penanganan Selesai', {
      exact: true,
    }).first()
  ).toBeVisible();
}

// ======================================================
// 7. RM-01: SELURUH RESOURCE TERSEDIA
// ======================================================

Given(
  'saya sebagai Pelapor memiliki akses ke unit Ambulans dan Polisi yang tersedia',
  async ({ page }) => {
    await ensureAllResourcesAvailable(page);
  }
);

When(
  'saya mengirim laporan Emergency Case yang membutuhkan Ambulans dan Polisi',
  async ({ page }) => {
    await openReportForm(page);
    await fillAndSubmitAccidentReport(page);
  }
);

Then(
  'saya melihat konfirmasi bahwa laporan berhasil dikirim',
  async ({ page }) => {
    await expect(
      page.getByText('Laporan berhasil dikirim!', {
        exact: true,
      })
    ).toBeVisible();

    getScenarioState(page).caseId =
      await captureLatestCaseId(page);
  }
);

Then(
  'saya mendapatkan penugasan Ambulans dan Polisi untuk laporan saya',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId;

    expect(caseId).toBeDefined();

    const policeCount = await countAssignmentsForCase(
      page,
      'polisi',
      caseId!
    );

    const ambulanceCount =
      await countAmbulanceAssignments(page, caseId!);

    expect(policeCount).toBe(1);
    expect(ambulanceCount).toBe(1);
  }
);

Then(
  'saya tidak mendapatkan penugasan Resource yang sama lebih dari satu kali',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId;

    expect(caseId).toBeDefined();

    await assertNoDuplicateAssignments(page, caseId!);
  }
);

// ======================================================
// 8. RM-02: SEBAGIAN RESOURCE TIDAK TERSEDIA
// ======================================================

Given(
  'saya sebagai Pelapor berada pada kondisi unit Ambulans tersedia dan Polisi tidak tersedia',
  async ({ page }) => {
    await assertResourceStatus(
      page,
      'ambulansRs',
      RESOURCE_NAMES.ambulansRs,
      'tersedia'
    );

    await assertResourceStatus(
      page,
      'ambulansPmi',
      RESOURCE_NAMES.ambulansPmi,
      'tersedia'
    );

    await setResourceAvailability(
      page,
      'polisi',
      RESOURCE_NAMES.polisi,
      'tidak_tersedia'
    );
  }
);

Then(
  'saya mendapatkan penugasan Ambulans untuk laporan saya',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId;

    expect(caseId).toBeDefined();

    const ambulanceCount =
      await countAmbulanceAssignments(page, caseId!);

    expect(ambulanceCount).toBe(1);
  }
);

Then(
  'saya melihat kebutuhan Polisi pada laporan saya masih menunggu penugasan',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId;

    expect(caseId).toBeDefined();

    const policeCount = await countAssignmentsForCase(
      page,
      'polisi',
      caseId!
    );

    expect(policeCount).toBe(0);

    await assertReportStatus(
      page,
      caseId!,
      'Menunggu Assignment'
    );

    await assertResourceStatus(
      page,
      'polisi',
      RESOURCE_NAMES.polisi,
      'tidak_tersedia'
    );
  }
);

// ======================================================
// 9. RM-03: RESOURCE DILEPASKAN DAN REMATCHING
// ======================================================

Given(
  'saya sebagai Instansi sedang menangani penugasan Polisi pada kasus pertama',
  async ({ page }) => {
    await ensureAllResourcesAvailable(page);

    const firstCaseId =
      await createAccidentReportAndCaptureId(page);

    const policeAssignmentId =
      await getAssignmentIdForCase(
        page,
        'polisi',
        firstCaseId
      );

    const state = getScenarioState(page);

    state.firstCaseId = firstCaseId;
    state.policeAssignmentId = policeAssignmentId;
  }
);

Given(
  'saya memiliki kasus kedua yang sudah mendapatkan Ambulans tetapi masih menunggu Polisi',
  async ({ page }) => {
    const waitingCaseId =
      await createAccidentReportAndCaptureId(page);

    getScenarioState(page).waitingCaseId = waitingCaseId;

    await assertReportStatus(
      page,
      waitingCaseId,
      'Menunggu Assignment'
    );

    const ambulanceCount =
      await countAmbulanceAssignments(
        page,
        waitingCaseId
      );

    const policeCount =
      await countAssignmentsForCase(
        page,
        'polisi',
        waitingCaseId
      );

    expect(ambulanceCount).toBe(1);
    expect(policeCount).toBe(0);
  }
);

When(
  'saya menyelesaikan penugasan Polisi pada kasus pertama',
  async ({ page }) => {
    const assignmentId =
      getScenarioState(page).policeAssignmentId;

    expect(assignmentId).toBeDefined();

    await completePoliceAssignment(
      page,
      assignmentId!
    );
  }
);

Then(
  'saya melihat penugasan Polisi pada kasus pertama telah selesai',
  async ({ page }) => {
    const assignmentId =
      getScenarioState(page).policeAssignmentId;

    expect(assignmentId).toBeDefined();

    await assertAssignmentCompleted(
      page,
      assignmentId!
    );
  }
);

Then(
  'saya melihat unit Polisi telah digunakan untuk menangani kasus kedua',
  async ({ page }) => {
    const waitingCaseId =
      getScenarioState(page).waitingCaseId;

    expect(waitingCaseId).toBeDefined();

    const policeCount =
      await countAssignmentsForCase(
        page,
        'polisi',
        waitingCaseId!
      );

    expect(policeCount).toBe(1);

    await assertResourceStatus(
      page,
      'polisi',
      RESOURCE_NAMES.polisi,
      'sedang_ditugaskan'
    );
  }
);

Then(
  'saya melihat kebutuhan Polisi pada kasus kedua tidak lagi menunggu penugasan',
  async ({ page }) => {
    const waitingCaseId =
      getScenarioState(page).waitingCaseId;

    expect(waitingCaseId).toBeDefined();

    await assertReportStatus(
      page,
      waitingCaseId!,
      'Menunggu'
    );
  }
);

Then(
  'saya tidak menemukan penugasan Polisi ganda pada kasus kedua',
  async ({ page }) => {
    const waitingCaseId =
      getScenarioState(page).waitingCaseId;

    expect(waitingCaseId).toBeDefined();

    const policeCount =
      await countAssignmentsForCase(
        page,
        'polisi',
        waitingCaseId!
      );

    expect(policeCount).toBe(1);
  }
);
