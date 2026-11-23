
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

type AssignmentInfo = {
  id: number;
  caseId: number;
  resourceName: string;
  resourceType: string;
  status: string;
};

type ScenarioState = {
  caseId?: number;
  assignments: AssignmentInfo[];
  detailAssignment?: AssignmentInfo;
};

const states = new WeakMap<Page, ScenarioState>();

function stateFor(page: Page): ScenarioState {
  let state = states.get(page);

  if (!state) {
    state = { assignments: [] };
    states.set(page, state);
  }

  return state;
}

const demoPassword =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const accounts = {
  petugas: {
    email:
      process.env.E2E_PETUGAS_EMAIL ??
      'petugas@emergencychain.test',
    password:
      process.env.E2E_PETUGAS_PASSWORD ?? demoPassword,
  },
  pelapor: {
    email:
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test',
    password:
      process.env.E2E_PELAPOR_PASSWORD ?? demoPassword,
  },
  polisi: {
    email:
      process.env.E2E_POLISI_EMAIL ??
      'polisi@emergencychain.test',
    password:
      process.env.E2E_POLISI_PASSWORD ?? demoPassword,
  },
  ambulansRs: {
    email:
      process.env.E2E_AMBULANS_RS_EMAIL ??
      'ambulans.rs@emergencychain.test',
    password:
      process.env.E2E_AMBULANS_RS_PASSWORD ?? demoPassword,
  },
  ambulansPmi: {
    email:
      process.env.E2E_AMBULANS_PMI_EMAIL ??
      'ambulans.independen@emergencychain.test',
    password:
      process.env.E2E_AMBULANS_PMI_PASSWORD ?? demoPassword,
  },
};

type Account = keyof typeof accounts;

const testingEnv = {
  ...process.env,
  APP_ENV: 'testing',
  DB_CONNECTION: 'pgsql',
  DB_DATABASE: 'emergency_chain_test',
};

function artisan(args: string[]): string {
  return execFileSync('php', ['artisan', ...args], {
    cwd: process.cwd(),
    env: testingEnv,
    encoding: 'utf8',
    maxBuffer: 1024 * 1024 * 5,
  });
}

function assertSafeTestingDatabase(): void {
  if (process.env.E2E_ALLOW_DB_RESET !== '1') {
    throw new Error(
      'Reset BDD ditolak: set E2E_ALLOW_DB_RESET=1 hanya untuk database E2E yang boleh dihapus.'
    );
  }

  const file = readFileSync(
    resolve(process.cwd(), '.env.testing'),
    'utf8'
  );

  const value = (key: string) =>
    file
      .match(new RegExp(`^${key}=(.*)$`, 'm'))?.[1]
      ?.trim()
      .replace(/^['"]|['"]$/g, '');

  if (
    value('APP_ENV') !== 'testing' ||
    value('DB_CONNECTION') !== 'pgsql' ||
    value('DB_DATABASE') !== 'emergency_chain_test'
  ) {
    throw new Error(
      'Reset BDD ditolak: .env.testing tidak menunjuk emergency_chain_test (pgsql/testing).'
    );
  }

  const actual = artisan([
    '--env=testing',
    'tinker',
    '--execute=echo app()->environment()."|".config("database.default")."|".DB::selectOne("select current_database() as name")->name;',
  ]).trim();

  if (
    !actual.endsWith(
      'testing|pgsql|emergency_chain_test'
    )
  ) {
    throw new Error(
      `Reset BDD ditolak: koneksi aktual tidak sesuai (${actual}).`
    );
  }
}

function resetTestingDatabase(): void {
  assertSafeTestingDatabase();

  artisan([
    'migrate:fresh',
    '--seed',
    '--env=testing',
    '--force',
  ]);
}

function assignmentsForCase(
  caseId: number
): AssignmentInfo[] {
  if (
    !Number.isSafeInteger(caseId) ||
    caseId <= 0
  ) {
    throw new Error('Case ID tidak valid.');
  }

  const code = `
    $items = \\App\\Models\\Assignment::query()
      ->with('resource')
      ->where('case_id', ${caseId})
      ->orderBy('assignment_id')
      ->get()
      ->map(fn ($a) => [
        'id' => (int) $a->assignment_id,
        'caseId' => (int) $a->case_id,
        'resourceName' => (string) ($a->resource?->nama_resource ?? ''),
        'resourceType' => (string) ($a->resource?->jenis_resource ?? ''),
        'status' => (string) $a->status_assign,
      ])->all();

    echo '__ASG_START__'.
      json_encode($items).
      '__ASG_END__';
  `;

  const output = artisan([
    '--env=testing',
    'tinker',
    `--execute=${code}`,
  ]);

  const json = output.match(
    /__ASG_START__(.*?)__ASG_END__/s
  )?.[1];

  if (!json) {
    throw new Error(
      `Tidak dapat membaca Assignment dari database E2E: ${output}`
    );
  }

  return JSON.parse(json) as AssignmentInfo[];
}

async function loginAs(
  page: Page,
  account: Account
): Promise<void> {
  await page.context().clearCookies();

  await page.goto('/login');

  await page
    .locator('input[name="email"]')
    .fill(accounts[account].email);

  await page
    .locator('input[name="password"]')
    .fill(accounts[account].password);

  await page
    .getByRole('button', {
      name: 'Login',
      exact: true,
    })
    .click();

  await expect(page).not.toHaveURL(/\/login$/);
}

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

  await page
    .locator('#jenis_kejadian')
    .selectOption({
      label: 'Kecelakaan Lalu Lintas',
    });

  await page
    .locator('#tingkat_kebutuhan')
    .selectOption({
      label: 'Butuh Penanganan Cepat',
    });

  await page
    .locator('#kondisi_kejadian')
    .fill(`BDD ASG ${Date.now()}`);

  await page
    .locator('#alamat')
    .fill(
      'Jl. Keputih, Surabaya - Lokasi pengujian ASG'
    );

  await page.locator('#location-map').click({
    position: {
      x: 250,
      y: 180,
    },
  });

  await expect(
    page.locator('#latitude')
  ).not.toHaveValue('');

  await expect(
    page.locator('#longitude')
  ).not.toHaveValue('');

  await page.locator('#submit-report').click();

  await expect(
    page.getByText(
      'Laporan berhasil dikirim!',
      { exact: true }
    )
  ).toBeVisible();

  await page.goto('/pelapor/laporan/status');

  const firstRow = page
    .locator('tbody tr')
    .first();

  await expect(firstRow).toBeVisible();

  const href = await firstRow
    .locator('a[href*="/pelapor/laporan/"]')
    .getAttribute('href');

  const match = href?.match(
    /\/pelapor\/laporan\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      'Gagal membaca Case ID dari laporan yang baru dibuat.'
    );
  }

  return Number(match[1]);
}

async function prepareAssignments(
  page: Page,
  minimum: number
): Promise<ScenarioState> {
  const state = stateFor(page);

  state.caseId = await createEmergencyCase(page);

  state.assignments = assignmentsForCase(
    state.caseId
  );

  if (state.assignments.length < minimum) {
    throw new Error(
      `Fixture ASG memerlukan ${minimum} Assignment, ` +
      `tetapi Case ${state.caseId} hanya menghasilkan ` +
      `${state.assignments.length}. ` +
      'Periksa resource seeder dan matching.'
    );
  }

  await loginAs(page, 'petugas');

  return state;
}

function assignmentRow(
  page: Page,
  assignment: AssignmentInfo
) {
  return page
    .locator('tbody tr')
    .filter({
      has: page
        .locator('td')
        .first()
        .getByText(
          `#${assignment.id}`,
          { exact: true }
        ),
    });
}

const statusLabel: Record<string, string> = {
  diterima: 'Diterima',
  selesai: 'Selesai',
  ditolak: 'Ditolak',
  dibatalkan: 'Dibatalkan',
};

async function completeOneAssignment(
  page: Page,
  caseId: number
): Promise<void> {
  const agencyAccounts: Array<
    'polisi' | 'ambulansRs' | 'ambulansPmi'
  > = [
    'polisi',
    'ambulansRs',
    'ambulansPmi',
  ];

  for (const account of agencyAccounts) {
    await loginAs(page, account);

    await page.goto('/instansi/assignments');

    const card = page
      .locator('article')
      .filter({
        hasText: new RegExp(
          `Emergency Case #${caseId}\\b`
        ),
      });

    if (await card.count() === 0) {
      continue;
    }

    const href = await card
      .first()
      .getByRole('link', {
        name: 'Buka Penanganan',
        exact: true,
      })
      .getAttribute('href');

    const id = href?.match(
      /\/instansi\/assignments\/(\d+)$/
    )?.[1];

    if (!id) {
      throw new Error(
        'Assignment Instansi tidak memiliki URL yang valid.'
      );
    }

    await page.goto(
      `/instansi/assignments/${id}`
    );

    for (const status of [
      'menuju_lokasi',
      'tiba_di_lokasi',
      'dalam_penanganan',
      'selesai',
    ]) {
      await expect(
        page.locator('input[name="status"]')
      ).toHaveValue(status);

      await page
        .locator('#keterangan')
        .fill(`BDD ASG: ${status}`);

      await page
        .getByRole('button', {
          name: 'Perbarui Status',
        })
        .click();
    }

    await loginAs(page, 'petugas');

    return;
  }

  throw new Error(
    `Tidak ada Assignment Instansi yang dapat diselesaikan untuk EC-${caseId}.`
  );
}

// ======================================================
// BACKGROUND
// ======================================================

Given(
  'Petugas EC telah login',
  async ({ page }) => {
    resetTestingDatabase();

    await loginAs(page, 'petugas');
  }
);

// ======================================================
// ASG-01: MENAMPILKAN DAFTAR ASSIGNMENT
// ======================================================

Given(
  'terdapat beberapa data Assignment',
  async ({ page }) => {
    await prepareAssignments(page, 2);
  }
);

When(
  'Petugas EC membuka halaman Assignment',
  async ({ page }) => {
    await page.goto('/petugas-ec/assignments');

    await expect(
      page.getByRole('heading', {
        name: 'Assignment Management',
      })
    ).toBeVisible();
  }
);

Then(
  'sistem menampilkan daftar Assignment',
  async ({ page }) => {
    const state = stateFor(page);

    await expect(
      page.getByRole('heading', {
        name: 'Daftar Assignment',
      })
    ).toBeVisible();

    for (const assignment of state.assignments) {
      await expect(
        assignmentRow(page, assignment)
      ).toHaveCount(1);
    }
  }
);

Then(
  'sistem menampilkan informasi Emergency Case',
  async ({ page }) => {
    for (
      const assignment of
      stateFor(page).assignments
    ) {
      await expect(
        assignmentRow(page, assignment)
      ).toContainText(
        `EC-${assignment.caseId}`
      );
    }
  }
);

Then(
  'sistem menampilkan informasi Resource',
  async ({ page }) => {
    for (
      const assignment of
      stateFor(page).assignments
    ) {
      if (!assignment.resourceType) {
        throw new Error(
          `Resource Assignment #${assignment.id} kosong.`
        );
      }

      await expect(
        assignmentRow(page, assignment)
      ).toContainText(
        assignment.resourceType
      );
    }
  }
);

Then(
  'sistem menampilkan status masing-masing Assignment',
  async ({ page }) => {
    for (
      const assignment of
      stateFor(page).assignments
    ) {
      await expect(
        assignmentRow(page, assignment)
          .locator('td')
          .nth(5)
      ).toHaveText(
        statusLabel[assignment.status] ??
        assignment.status
      );
    }
  }
);

// ======================================================
// ASG-02: DETAIL ASSIGNMENT
// ======================================================

Given(
  'terdapat Assignment yang sudah terbentuk',
  async ({ page }) => {
    await prepareAssignments(page, 1);
  }
);

When(
  'Petugas EC membuka detail Assignment',
  async ({ page }) => {
    const state = stateFor(page);

    const assignment = state.assignments[0];

    if (!assignment) {
      throw new Error(
        'Belum ada Assignment untuk ASG-02.'
      );
    }

    state.detailAssignment = assignment;

    await page.goto('/petugas-ec/assignments');

    await assignmentRow(page, assignment)
      .getByRole('link', {
        name: 'Detail',
      })
      .click();

    await expect(page).toHaveURL(
      new RegExp(
        `/petugas-ec/emergency-cases/${assignment.caseId}/resource-matching`
      )
    );
  }
);

Then(
  'sistem menampilkan informasi Emergency Case terkait',
  async ({ page }) => {
    const assignment =
      stateFor(page).detailAssignment;

    if (!assignment) {
      throw new Error(
        'Belum membuka detail Assignment.'
      );
    }

    await expect(
      page.getByText(
        'ID Emergency Case',
        { exact: true }
      )
    ).toBeVisible();

    await expect(
      page.getByText(
        `#${assignment.caseId}`,
        { exact: true }
      )
    ).toBeVisible();

    await expect(
      page.getByRole('heading', {
        name: 'Informasi Kasus',
      })
    ).toBeVisible();
  }
);

Then(
  'sistem menampilkan informasi Resource yang ditugaskan',
  async ({ page }) => {
    const assignment =
      stateFor(page).detailAssignment;

    if (!assignment?.resourceName) {
      throw new Error(
        'Nama resource fixture tidak tersedia.'
      );
    }

    await expect(
      page.locator('body')
    ).toContainText(
      assignment.resourceName
    );
  }
);

Then(
  'sistem menampilkan informasi status Assignment',
  async ({ page }) => {
    const assignment =
      stateFor(page).detailAssignment;

    if (!assignment) {
      throw new Error(
        'Belum membuka detail Assignment.'
      );
    }

    await expect(
      page.locator('body')
    ).toContainText(
      statusLabel[assignment.status] ??
      assignment.status
    );
  }
);

// ======================================================
// ASG-03: MENAMPILKAN STATUS ASSIGNMENT
// ======================================================

Given(
  'terdapat Assignment dengan status yang berbeda',
  async ({ page }) => {
    const state = await prepareAssignments(
      page,
      2
    );

    await completeOneAssignment(
      page,
      state.caseId!
    );

    state.assignments = assignmentsForCase(
      state.caseId!
    );

    if (
      new Set(
        state.assignments.map(
          (assignment) => assignment.status
        )
      ).size < 2
    ) {
      throw new Error(
        'Fixture tidak menghasilkan dua status Assignment yang berbeda.'
      );
    }
  }
);

Then(
  'sistem menampilkan status setiap Assignment',
  async ({ page }) => {
    for (
      const assignment of
      stateFor(page).assignments
    ) {
      await expect(
        assignmentRow(page, assignment)
          .locator('td')
          .nth(5)
      ).toHaveText(
        statusLabel[assignment.status] ??
        assignment.status
      );
    }
  }
);

Then(
  'status yang ditampilkan sesuai dengan data tersimpan',
  async ({ page }) => {
    const state = stateFor(page);

    const actual = assignmentsForCase(
      state.caseId!
    );

    for (
      const expected of state.assignments
    ) {
      const inDb = actual.find(
        (assignment) =>
          assignment.id === expected.id
      );

      expect(
        inDb,
        `Assignment #${expected.id} tidak ditemukan di database`
      ).toBeDefined();

      await expect(
        assignmentRow(page, expected)
          .locator('td')
          .nth(5)
      ).toHaveText(
        statusLabel[inDb!.status] ??
        inDb!.status
      );
    }
  }
);

// ======================================================
// ASG-04: TANPA ASSIGNMENT
// ======================================================

Given(
  'belum terdapat data Assignment',
  async ({ page }) => {
    const count = artisan([
      '--env=testing',
      'tinker',
      '--execute=echo \\App\\Models\\Assignment::query()->count();',
    ]).trim();

    if (!/^0$/.test(count)) {
      throw new Error(
        'ASG-04 memerlukan database E2E tanpa Assignment, ' +
        `tetapi jumlahnya ${count}.`
      );
    }
  }
);

Then(
  'sistem menampilkan informasi bahwa belum ada Assignment',
  async ({ page }) => {
    await expect(
      page.getByText(
        'Belum ada Assignment',
        { exact: true }
      )
    ).toBeVisible();
  }
);

Then(
  'sistem tidak menampilkan data Assignment',
  async ({ page }) => {
    await expect(
      page.locator('tbody tr')
    ).toHaveCount(1);

    await expect(
      page.locator('tbody tr').first()
    ).toContainText(
      'Belum ada Assignment'
    );

    await expect(
      page.locator(
        'tbody tr a[href*="resource-matching"]'
      )
    ).toHaveCount(0);
  }
);

Then(
  'halaman tetap dapat diakses tanpa error',
  async ({ page }) => {
    await expect(page).toHaveURL(
      /\/petugas-ec\/assignments$/
    );

    await expect(
      page.getByRole('heading', {
        name: 'Assignment Management',
      })
    ).toBeVisible();

    await expect(
      page.getByText('Server Error')
    ).toHaveCount(0);
  }
);
