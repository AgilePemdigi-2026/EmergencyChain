
import { execFileSync } from 'node:child_process';
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

type CompletedAssignment = {
  caseId: number;
  assignmentId: number;
  resourceName: string;
  caseType: string;
  location: string;
  month: string;
  notes: string[];
};

type ScenarioState = {
  assignments: CompletedAssignment[];
  selected?: CompletedAssignment;
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

const password =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const accounts = {
  pelapor: {
    email:
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test',
    password:
      process.env.E2E_PELAPOR_PASSWORD ?? password,
  },
  polisi: {
    email:
      process.env.E2E_POLISI_EMAIL ??
      'polisi@emergencychain.test',
    password:
      process.env.E2E_POLISI_PASSWORD ?? password,
  },
};

type AccountKey = keyof typeof accounts;

function runTinker(code: string): string {
  return execFileSync(
    'php',
    [
      'artisan',
      '--env=testing',
      'tinker',
      `--execute=${code}`,
    ],
    {
      cwd: process.cwd(),
      env: {
        ...process.env,
        APP_ENV: 'testing',
        DB_CONNECTION: 'pgsql',
        DB_DATABASE: 'emergency_chain_test',
      },
      encoding: 'utf8',
      maxBuffer: 5 * 1024 * 1024,
    }
  );
}

function readDatabase<T>(code: string): T {
  const output = runTinker(`
    $dbName = \\Illuminate\\Support\\Facades\\DB::selectOne(
      'select current_database() as name'
    )->name;

    if (
      app()->environment() !== 'testing' ||
      config('database.default') !== 'pgsql' ||
      $dbName !== 'emergency_chain_test'
    ) {
      throw new \\RuntimeException(
        'Database ATH bukan emergency_chain_test.'
      );
    }

    ${code}

    echo '__ATH_JSON__' .
      json_encode(
        $bddResult,
        JSON_UNESCAPED_UNICODE
      ) .
      '__ATH_END__';
  `);

  const json = output.match(
    /__ATH_JSON__(.*?)__ATH_END__/s
  )?.[1];

  if (!json) {
    throw new Error(
      `Gagal membaca fixture ATH: ${output}`
    );
  }

  return JSON.parse(json) as T;
}

async function loginAs(
  page: Page,
  account: AccountKey
): Promise<void> {
  // Akhiri sesi browser sebelumnya melalui aplikasi.
  await page.goto('/login');

  // Jika Laravel mengarahkan ke dashboard,
  // berarti pengguna sebelumnya masih login.
  if (!/\/login(?:\?|$)/.test(page.url())) {
    const logoutButton = page.getByRole('button', {
      name: 'Logout',
      exact: true,
    });

    if (await logoutButton.isVisible()) {
      await logoutButton.click();
    } else {
      throw new Error(
        `Tidak bisa logout sebelum login sebagai ${account}. ` +
        `Halaman saat ini: ${page.url()}`
      );
    }

    await page.goto('/login');
  }

  // Pastikan benar-benar berada di form login.
  await expect(page).toHaveURL(/\/login(?:\?|$)/);

  await expect(
    page.locator('input[name="email"]')
  ).toBeVisible();

  await page.locator('input[name="email"]')
    .fill(accounts[account].email);

  await page.locator('input[name="password"]')
    .fill(accounts[account].password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login(?:\?|$)/);
}

async function createCase(
  page: Page,
  suffix: string
): Promise<number> {
  await loginAs(page, 'pelapor');
  await page.goto('/pelapor/laporan/buat');

  await expect(
    page.getByRole('heading', {
      name: 'Buat Laporan Emergency',
    })
  ).toBeVisible();

  await page.locator('#jenis_kejadian')
    .selectOption({
      label: 'Kecelakaan Lalu Lintas',
    });

  await page.locator('#tingkat_kebutuhan')
    .selectOption({
      label: 'Butuh Penanganan Cepat',
    });

  await page.locator('#kondisi_kejadian')
    .fill(`BDD ATH ${suffix}`);

  await page.locator('#alamat')
    .fill(
      `Jl. Keputih, Surabaya - Lokasi ATH ${suffix}`
    );

  await page.locator('#location-map').click({
    position: { x: 250, y: 180 },
  });

  await expect(page.locator('#latitude'))
    .not.toHaveValue('');

  await expect(page.locator('#longitude'))
    .not.toHaveValue('');

  await page.locator('#submit-report').click();

  await expect(
    page.getByText(
      'Laporan berhasil dikirim!',
      { exact: true }
    )
  ).toBeVisible();

  await page.goto('/pelapor/laporan/status');

  const row = page.locator('tbody tr').first();
  await expect(row).toBeVisible();

  const href = await row
    .locator('a[href*="/pelapor/laporan/"]')
    .getAttribute('href');

  const match = href?.match(
    /\/pelapor\/laporan\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      'Case ID ATH tidak ditemukan.'
    );
  }

  return Number(match[1]);
}

async function getPoliceAssignment(
  page: Page,
  caseId: number
): Promise<number> {
  await loginAs(page, 'polisi');
  await page.goto('/instansi/assignments');

  const card = page.locator('article').filter({
    hasText: new RegExp(
      `Emergency Case #${caseId}\\b`
    ),
  });

  await expect(card).toHaveCount(1);

  const href = await card
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
      `Assignment Polisi EC-${caseId} tidak ditemukan.`
    );
  }

  return Number(match[1]);
}

async function completeAssignment(
  page: Page,
  assignmentId: number
): Promise<string[]> {
  await loginAs(page, 'polisi');

  await page.goto(
    `/instansi/assignments/${assignmentId}`
  );

  const statuses = [
    'menuju_lokasi',
    'tiba_di_lokasi',
    'dalam_penanganan',
    'selesai',
  ];

  const notes: string[] = [];

  for (const status of statuses) {
    await expect(
      page.locator('input[name="status"]')
    ).toHaveValue(status);

    const note =
      `Catatan ATH ${assignmentId}: ${status}`;

    notes.push(note);

    await page.locator('#keterangan')
      .fill(note);

    await page.getByRole('button', {
      name: 'Perbarui Status',
    }).click();
  }

  await expect(
    page.getByText(
      'Penanganan Selesai',
      { exact: true }
    ).last()
  ).toBeVisible();

  return notes;
}

function getCompletedAssignment(
  caseId: number,
  assignmentId: number,
  notes: string[]
): CompletedAssignment {
  const result = readDatabase<Omit<
    CompletedAssignment,
    'notes'
  > | null>(`
    $assignment = \\App\\Models\\Assignment::query()
      ->with([
        'resource',
        'emergencyCase.lokasi',
        'statusPenanganan'
      ])
      ->where('assignment_id', ${assignmentId})
      ->where('case_id', ${caseId})
      ->first();

    $finished = $assignment?->statusPenanganan
      ->where('status', 'selesai')
      ->sortByDesc('waktu_update')
      ->first();

    $bddResult = $assignment ? [
      'caseId' => (int) $assignment->case_id,
      'assignmentId' =>
        (int) $assignment->assignment_id,
      'resourceName' =>
        (string) ($assignment->resource?->nama_resource ?? ''),
      'caseType' =>
        (string) ($assignment->emergencyCase?->jenis_kejadian ?? ''),
      'location' =>
        (string) ($assignment->emergencyCase?->lokasi?->alamat ?? ''),
      'month' => $finished
        ? \\Carbon\\Carbon::parse(
            $finished->waktu_update
          )->format('Y-m')
        : '',
      'status' => $assignment->status_assign,
    ] : null;

    if (
      $bddResult &&
      $bddResult['status'] !== 'selesai'
    ) {
      throw new \\RuntimeException(
        'Assignment ATH belum berstatus selesai.'
      );
    }
  `);

  if (!result || !result.month) {
    throw new Error(
      `Data penyelesaian Assignment #${assignmentId} tidak lengkap.`
    );
  }

  return { ...result, notes };
}

async function prepareCompleted(
  page: Page,
  suffix: string
): Promise<CompletedAssignment> {
  const caseId = await createCase(page, suffix);

  const assignmentId = await getPoliceAssignment(
    page,
    caseId
  );

  const notes = await completeAssignment(
    page,
    assignmentId
  );

  return getCompletedAssignment(
    caseId,
    assignmentId,
    notes
  );
}

async function openHistory(page: Page): Promise<void> {
  await loginAs(page, 'polisi');
  await page.goto('/instansi/history');

  await expect(
    page.getByRole('heading', {
      name: /Arsip Penanganan/i,
    })
  ).toBeVisible();
}

function historyRows(page: Page) {
  return page.locator(
    'table tbody tr'
  ).filter({
    has: page.locator(
      'a[href*="/instansi/history/"]'
    ),
  });
}

function historyRow(
  page: Page,
  assignment: CompletedAssignment
) {
  return historyRows(page).filter({
    hasText: `Assignment #${assignment.assignmentId}`,
  });
}

async function assertOnlyCompleted(
  page: Page
): Promise<void> {
  const rows = historyRows(page);
  const count = await rows.count();

  expect(
    count,
    'Daftar riwayat harus berisi Assignment selesai.'
  ).toBeGreaterThan(0);

  for (let i = 0; i < count; i++) {
    await expect(
      rows.nth(i).locator('td').nth(4)
    ).toHaveText('Selesai');
  }
}

// ======================================================
// ATH-01
// ======================================================

Given(
  'saya sebagai Instansi memiliki Assignment yang sedang dalam penanganan',
  async ({ page }) => {
    const caseId = await createCase(
      page,
      'ATH-01'
    );

    const assignmentId =
      await getPoliceAssignment(page, caseId);

    const state = stateFor(page);

    state.assignments = [{
      caseId,
      assignmentId,
      resourceName: '',
      caseType: '',
      location: '',
      month: '',
      notes: [],
    }];

    await page.goto(
      `/instansi/assignments/${assignmentId}`
    );

    await expect(
      page.locator('input[name="status"]')
    ).toHaveValue('menuju_lokasi');

    await page.locator('#keterangan')
      .fill('Catatan ATH-01: menuju lokasi');

    await page.getByRole('button', {
      name: 'Perbarui Status',
    }).click();

    await expect(
      page.locator('input[name="status"]')
    ).toHaveValue('tiba_di_lokasi');
  }
);

When(
  'saya menyelesaikan Assignment melalui halaman Status Emergency',
  async ({ page }) => {
    const state = stateFor(page);
    const assignment = state.assignments[0];

    if (!assignment) {
      throw new Error(
        'Fixture ATH-01 belum tersedia.'
      );
    }

    const notes = await completeAssignmentFromCurrent(
      page,
      assignment.assignmentId
    );

    const completed = getCompletedAssignment(
      assignment.caseId,
      assignment.assignmentId,
      [
        'Catatan ATH-01: menuju lokasi',
        ...notes,
      ]
    );

    state.assignments = [completed];
    state.selected = completed;
  }
);

async function completeAssignmentFromCurrent(
  page: Page,
  assignmentId: number
): Promise<string[]> {
  await page.goto(
    `/instansi/assignments/${assignmentId}`
  );

  const statuses = [
    'tiba_di_lokasi',
    'dalam_penanganan',
    'selesai',
  ];

  const notes: string[] = [];

  for (const status of statuses) {
    await expect(
      page.locator('input[name="status"]')
    ).toHaveValue(status);

    const note =
      `Catatan ATH ${assignmentId}: ${status}`;

    notes.push(note);

    await page.locator('#keterangan')
      .fill(note);

    await page.getByRole('button', {
      name: 'Perbarui Status',
    }).click();
  }

  return notes;
}

Then(
  'saya melihat status Assignment berubah menjadi selesai',
  async ({ page }) => {
    await expect(
      page.getByText(
        'Penanganan Selesai',
        { exact: true }
      ).last()
    ).toBeVisible();

    expect(
      stateFor(page).selected
    ).toBeDefined();
  }
);

When(
  'saya membuka Dashboard Penanganan',
  async ({ page }) => {
    await page.goto('/instansi/assignments');
  }
);

Then(
  'saya tidak melihat card Emergency Case yang sudah selesai',
  async ({ page }) => {
    const selected = stateFor(page).selected;

    if (!selected) {
      throw new Error(
        'Assignment selesai ATH-01 belum tersedia.'
      );
    }

    const card = page.locator('article').filter({
      hasText: new RegExp(
        `Emergency Case #${selected.caseId}\\b`
      ),
    });

    await expect(card).toHaveCount(0);
  }
);

When(
  'saya membuka halaman Riwayat Penanganan',
  async ({ page }) => {
    await openHistory(page);
  }
);

Then(
  'saya melihat Assignment yang telah selesai pada daftar riwayat',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await expect(
      historyRow(page, selected)
    ).toHaveCount(1);
  }
);

Then(
  'saya hanya melihat Assignment berstatus selesai pada daftar riwayat',
  async ({ page }) => {
    await assertOnlyCompleted(page);
  }
);

// ======================================================
// ATH-02
// ======================================================

Given(
  'saya sebagai Instansi memiliki Assignment yang telah selesai',
  async ({ page }) => {
    const assignment = await prepareCompleted(
      page,
      'ATH-02'
    );

    const state = stateFor(page);
    state.assignments = [assignment];
    state.selected = assignment;
  }
);

When(
  'saya membuka detail Assignment pada halaman Riwayat Penanganan',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await openHistory(page);

    await historyRow(page, selected)
      .getByRole('link', {
        name: /Detail/,
      })
      .click();

    await expect(page).toHaveURL(
      new RegExp(
        `/instansi/history/${selected.assignmentId}$`
      )
    );
  }
);

Then(
  'saya melihat informasi Emergency Case dan Resource yang ditugaskan',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await expect(
      page.getByRole('heading', {
        name: 'Informasi Emergency Case',
      })
    ).toBeVisible();

    await expect(
      page.getByRole('heading', {
        name: 'Informasi Assignment',
      })
    ).toBeVisible();

    await expect(
      page.getByText(
        `Assignment #${selected.assignmentId}`,
        { exact: false }
      ).first()
    ).toBeVisible();

    await expect(
      page.getByText(
        selected.resourceName,
        { exact: true }
      )
    ).toBeVisible();

    await expect(
      page.getByText(
        selected.caseType,
        { exact: true }
      ).first()
    ).toBeVisible();
  }
);

Then(
  'saya melihat waktu penyelesaian Assignment',
  async ({ page }) => {
    const section = page.locator('section').filter({
      has: page.getByText(
        'Waktu Selesai',
        { exact: true }
      ),
    });

    await expect(section).toHaveCount(1);

    await expect(section).toContainText(
      /\d{1,2}\s+\w+\s+\d{4},\s+\d{2}:\d{2}/
    );
  }
);

Then(
  'saya melihat timeline perubahan status penanganan',
  async ({ page }) => {
    const section = page.locator('section').filter({
      has: page.getByRole('heading', {
        name: 'Timeline Penanganan',
      }),
    });

    await expect(section).toHaveCount(1);

    for (const status of [
      'Assignment Dibuat',
      'Menuju Lokasi',
      'Tiba di Lokasi',
      'Dalam Penanganan',
      'Penanganan Selesai',
    ]) {
      await expect(section).toContainText(status);
    }
  }
);

Then(
  'saya melihat catatan lapangan yang tercatat selama penanganan',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    const section = page.locator('section').filter({
      has: page.getByRole('heading', {
        name: 'Catatan Lapangan',
      }),
    });

    await expect(section).toHaveCount(1);

    for (const note of selected.notes) {
      await expect(section).toContainText(note);
    }
  }
);

// ======================================================
// ATH-03
// ======================================================

Given(
  'saya sebagai Instansi memiliki beberapa Assignment yang telah selesai',
  async ({ page, $test }) => {
    $test.setTimeout(180000);

    const first = await prepareCompleted(
      page,
      'ATH-03-A'
    );

    const second = await prepareCompleted(
      page,
      'ATH-03-B'
    );

    const state = stateFor(page);

    state.assignments = [first, second];
    state.selected = second;

    await openHistory(page);

    for (const assignment of state.assignments) {
      await expect(
        historyRow(page, assignment)
      ).toHaveCount(1);
    }
  }
);

When(
  'saya mencari Riwayat Penanganan berdasarkan Case ID, jenis kejadian, atau lokasi',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    const search = page.locator(
      'input[name="search"]'
    );

    // Cari berdasarkan Case ID.
    await search.fill(String(selected.caseId));

    await page.getByRole('button', {
      name: 'Filter',
    }).click();

    await expect(
      historyRow(page, selected)
    ).toHaveCount(1);

    // Cari berdasarkan jenis kejadian.
    await search.fill(selected.caseType);

    await page.getByRole('button', {
      name: 'Filter',
    }).click();

    await expect(
      historyRow(page, selected)
    ).toHaveCount(1);

    // Cari berdasarkan lokasi yang unik.
    await search.fill(selected.location);

    await page.getByRole('button', {
      name: 'Filter',
    }).click();
  }
);

Then(
  'saya melihat daftar riwayat yang sesuai dengan pencarian',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await expect(
      page.locator('input[name="search"]')
    ).toHaveValue(selected.location);

    await expect(
      historyRow(page, selected)
    ).toHaveCount(1);

    await expect(
      historyRows(page)
    ).toHaveCount(1);
  }
);

When(
  'saya memfilter Riwayat Penanganan berdasarkan bulan penyelesaian',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await page.locator('input[name="search"]')
      .fill('');

    await page.locator('input[name="bulan"]')
      .fill(selected.month);

    await page.getByRole('button', {
      name: 'Filter',
    }).click();
  }
);

Then(
  'saya melihat daftar riwayat yang sesuai dengan bulan yang dipilih',
  async ({ page }) => {
    const selected = stateFor(page).selected!;

    await expect(
      page.locator('input[name="bulan"]')
    ).toHaveValue(selected.month);

    await expect(
      historyRow(page, selected)
    ).toHaveCount(1);

    const count = await historyRows(page).count();

    expect(count).toBeGreaterThan(0);
  }
);

Then(
  'saya hanya melihat Assignment berstatus selesai pada hasil filter',
  async ({ page }) => {
    await assertOnlyCompleted(page);
  }
);

// ======================================================
// ATH-04
// ======================================================

Given(
  'saya sebagai Instansi belum memiliki Assignment yang selesai',
  async ({ page }) => {
    const total = readDatabase<number>(`
      $user = \\App\\Models\\Pengguna::query()
        ->where(
          'email',
          ${JSON.stringify(accounts.polisi.email)}
        )
        ->firstOrFail();

      $bddResult = (int)
        \\App\\Models\\Assignment::query()
          ->where('status_assign', 'selesai')
          ->whereHas('resource', function ($query) use ($user) {
            $query->where(
              'instansi_id',
              $user->instansi_id
            );
          })
          ->count();
    `);

    expect(
      total,
      'Fixture ATH-04 harus tanpa Assignment selesai.'
    ).toBe(0);

    await loginAs(page, 'polisi');
  }
);

Then(
  'saya melihat pesan bahwa belum ada Riwayat Penanganan',
  async ({ page }) => {
    await expect(
      page.getByText(
        'Belum Ada Riwayat Penanganan',
        { exact: true }
      )
    ).toBeVisible();
  }
);

Then(
  'saya tidak melihat data Assignment pada tabel riwayat',
  async ({ page }) => {
    await expect(
      historyRows(page)
    ).toHaveCount(0);

    await expect(
      page.locator('tbody tr')
    ).toHaveCount(1);

    await expect(
      page.locator('tbody tr').first()
    ).toContainText(
      'Belum ada Assignment selesai'
    );
  }
);
