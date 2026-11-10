
import { execFileSync } from 'node:child_process';
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';
import { readFileSync } from 'node:fs';

const { Given, When, Then } = createBdd(test);

// ======================================================
// 1. KONFIGURASI PENGUJIAN
// ======================================================

type AccountKey = 'pelapor' | 'petugasEc' | 'rsud' | 'rsUnair';
type HospitalAccountKey = 'rsud' | 'rsUnair';

type HospitalRequestSnapshot = {
  id: number;
  hospitalId: number;
  hospitalName: string;
  attemptOrder: number;
  status: string;
  requestedAt: string | null;
  expiresAt: string | null;
  timeoutSeconds: number | null;
};

type ReferralSnapshot = {
  status: string;
  failureReason: string | null;
  acceptedHospitalName: string | null;
  requests: HospitalRequestSnapshot[];
};

type CandidateSnapshot = {
  id: number;
  name: string;
};

type ScenarioState = {
  caseId?: number;
  activeRequestId?: number;
  activeHospitalName?: string;
  activeHospitalAccount?: HospitalAccountKey;
};

const DEMO_PASSWORD =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const ACCOUNTS: Record<
  AccountKey,
  { email: string; password: string }
> = {
  pelapor: {
    email:
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test',
    password:
      process.env.E2E_PELAPOR_PASSWORD ?? DEMO_PASSWORD,
  },

  petugasEc: {
    email:
      process.env.E2E_PETUGAS_EMAIL ??
      'petugas@emergencychain.test',
    password:
      process.env.E2E_PETUGAS_PASSWORD ?? DEMO_PASSWORD,
  },

  rsud: {
    email:
      process.env.E2E_RSUD_EMAIL ??
      'rs@emergencychain.test',
    password:
      process.env.E2E_RSUD_PASSWORD ?? DEMO_PASSWORD,
  },

  rsUnair: {
    email:
      process.env.E2E_RS_UNAIR_EMAIL ??
      'rs.unair@emergencychain.test',
    password:
      process.env.E2E_RS_UNAIR_PASSWORD ?? DEMO_PASSWORD,
  },
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
// 2. DATABASE TESTING
// ======================================================

// ======================================================
// 3. HELPER TINKER DATABASE
// ======================================================

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
      encoding: 'utf8',
      env: {
        ...process.env,
        APP_ENV: 'testing',
      },
    }
  );
}

function runTinkerJson<T>(code: string): T {
  const startMarker = '__BDD_JSON_START__';
  const endMarker = '__BDD_JSON_END__';

  const output = runTinker(`
    ${code}

    echo '${startMarker}' .
        json_encode($bddResult, JSON_UNESCAPED_UNICODE) .
        '${endMarker}';
  `);

  const match = output.match(
    /__BDD_JSON_START__(.*?)__BDD_JSON_END__/s
  );

  if (!match) {
    throw new Error(
      `Tidak dapat membaca hasil Tinker: ${output}`
    );
  }

  return JSON.parse(match[1]) as T;
}

function getReferralSnapshot(
  caseId: number
): ReferralSnapshot | null {
  return runTinkerJson<ReferralSnapshot | null>(`
    $referral = \\App\\Models\\HospitalReferral::query()
        ->with([
            'requests.rumahSakit',
            'acceptedHospital'
        ])
        ->where('case_id', ${caseId})
        ->first();

    $bddResult = $referral ? [
        'status' => $referral->status,
        'failureReason' => $referral->failure_reason,
        'acceptedHospitalName' =>
            $referral->acceptedHospital?->nama_rs,

        'requests' => $referral->requests
            ->sortBy('attempt_order')
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'hospitalId' => $request->rumah_sakit_id,
                    'hospitalName' =>
                        $request->rumahSakit?->nama_rs,
                    'attemptOrder' => $request->attempt_order,
                    'status' => $request->status,
                    'requestedAt' =>
                        $request->requested_at?->toDateTimeString(),
                    'expiresAt' =>
                        $request->expires_at?->toDateTimeString(),
                    'timeoutSeconds' =>
                        $request->requested_at && $request->expires_at
                            ? (int) $request->requested_at
                                ->diffInSeconds($request->expires_at)
                            : null,
                ];
            })
            ->values()
            ->all(),
    ] : null;
  `);
}

function getHospitalCandidates(
  caseId: number
): CandidateSnapshot[] {
  return runTinkerJson<CandidateSnapshot[]>(`
    $case = \\App\\Models\\EmergencyCase::query()
        ->with('lokasi')
        ->findOrFail(${caseId});

    $bddResult = app(
        \\App\\Services\\Hospital\\HospitalMatchingService::class
    )
        ->findCandidates($case)
        ->map(function ($candidate) {
            return [
                'id' => $candidate['rumah_sakit_id'],
                'name' => $candidate['nama_rs'],
            ];
        })
        ->values()
        ->all();
  `);
}

function setAllHospitalsUnavailable(): void {
  runTinker(`
    \\App\\Models\\RumahSakit::query()
        ->update(['status' => 'nonaktif']);
  `);
}

function forceRequestTimeout(requestId: number): void {
  runTinker(`
    $request = \\App\\Models\\HospitalReferralRequest::query()
        ->findOrFail(${requestId});

    $request->update([
        'expires_at' => now()->subSecond()
    ]);

    app(
        \\App\\Services\\Hospital\\HospitalReferralService::class
    )->processTimeout(${requestId});
  `);
}

// ======================================================
// 4. HELPER LOGIN
// ======================================================

async function loginAs(
  page: Page,
  accountKey: AccountKey
): Promise<void> {
  const account = ACCOUNTS[accountKey];

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

function getHospitalAccount(
  hospitalName: string
): HospitalAccountKey {
  const normalized = hospitalName.toLowerCase();

  if (normalized.includes('soetomo')) {
    return 'rsud';
  }

  if (normalized.includes('universitas airlangga')) {
    return 'rsUnair';
  }

  throw new Error(
    `Akun demo tidak ditemukan untuk Rumah Sakit: ${hospitalName}`
  );
}

function getActiveRequest(
  snapshot: ReferralSnapshot
): HospitalRequestSnapshot {
  const request = snapshot.requests.find(
    (item) => item.status === 'menunggu'
  );

  if (!request) {
    throw new Error(
      'Tidak ditemukan permintaan rujukan yang masih menunggu.'
    );
  }

  return request;
}

// ======================================================
// 5. HELPER MEMBUAT EMERGENCY CASE
// ======================================================

async function createEmergencyCase(
  page: Page,
  condition: string
): Promise<number> {
  await loginAs(page, 'pelapor');
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
    `${condition} BDD ${Date.now()}.`
  );

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian Hospital Matching'
  );

  await expect(
    page.locator('#location-map')
  ).toBeVisible();

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
    page.getByText('Laporan berhasil dikirim!', {
        exact: true,
    })
    ).toBeVisible();

  return captureLatestCaseId(page);
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
      `Tidak dapat membaca Case ID dari tautan: ${href}`
    );
  }

  return Number(match[1]);
}

// ======================================================
// 6. HELPER HALAMAN HOSPITAL MATCHING
// ======================================================

async function openHospitalMatching(
  page: Page,
  caseId: number
): Promise<void> {
  await loginAs(page, 'petugasEc');

  await page.goto(
    `/petugas-ec/emergency-cases/${caseId}/hospital-matching`
  );

  await expect(
    page.getByRole('heading', {
      name: 'Hospital Matching',
      exact: true,
    })
  ).toBeVisible();
}

function getHistoryRows(page: Page) {
  return page
    .locator('table tbody tr')
    .filter({
      hasText: /\b(Menunggu|Diterima|Ditolak|Timeout)\b/,
    });
}

async function assertRequestHistory(
  page: Page,
  snapshot: ReferralSnapshot
): Promise<void> {
  const rows = getHistoryRows(page);

  await expect(rows).toHaveCount(
    snapshot.requests.length
  );

  for (let i = 0; i < snapshot.requests.length; i++) {
    const request = snapshot.requests[i];
    const row = rows.nth(i);

    await expect(
      row.locator('td').nth(0)
    ).toHaveText(String(request.attemptOrder));

    await expect(
      row.locator('td').nth(1)
    ).toContainText(request.hospitalName);

    const expectedStatus =
      request.status.charAt(0).toUpperCase() +
      request.status.slice(1);

    await expect(
      row.locator('td').nth(2)
    ).toHaveText(expectedStatus);
  }
}

// ======================================================
// 7. HELPER DASHBOARD DAN RESPONS RUMAH SAKIT
// ======================================================

async function openHospitalDashboard(
  page: Page,
  caseId: number,
  request: HospitalRequestSnapshot
): Promise<void> {
  const accountKey = getHospitalAccount(
    request.hospitalName
  );

  const state = getScenarioState(page);
  state.activeRequestId = request.id;
  state.activeHospitalName = request.hospitalName;
  state.activeHospitalAccount = accountKey;

  await loginAs(page, accountKey);
  await page.goto('/hospital/referrals');

  await expect(page).toHaveURL(
    /\/hospital\/referrals$/
  );

    await expect(
    page.getByText(
        `EC-${String(caseId).padStart(4, '0')}`,
        { exact: true }
    )
    ).toBeVisible();
}

async function openHospitalRequestDetail(
  page: Page
): Promise<void> {
  const requestId =
    getScenarioState(page).activeRequestId;

  expect(requestId).toBeDefined();

  await page.goto(
    `/hospital/referrals/${requestId}`
  );

  await expect(
    page.getByRole('heading', {
      name: 'Detail Permintaan Rujukan',
    })
  ).toBeVisible();
}

async function respondToHospitalRequest(
  page: Page,
  request: HospitalRequestSnapshot,
  decision: 'diterima' | 'ditolak'
): Promise<void> {
  const accountKey = getHospitalAccount(
    request.hospitalName
  );

  await loginAs(page, accountKey);

  await page.goto(
    `/hospital/referrals/${request.id}`
  );

  await expect(
    page.getByRole('heading', {
      name: 'Detail Permintaan Rujukan',
    })
  ).toBeVisible();

  if (decision === 'diterima') {
    await page.getByRole('button', {
      name: 'Terima Permintaan',
      exact: true,
    }).click();
  } else {
    await page.locator('.reason-input').fill(
      'Kapasitas Rumah Sakit tidak tersedia.'
    );

    await page.getByRole('button', {
      name: 'Tolak Permintaan',
      exact: true,
    }).click();
  }

  await expect(page).toHaveURL(
    /\/hospital\/referrals$/
  );

  await expect(
    page.getByText(
      'Respons rujukan berhasil disimpan.',
      { exact: true }
    )
  ).toBeVisible();
}

// ======================================================
// 8. HM-01
// MELIHAT HASIL PENCARIAN RUMAH SAKIT
// ======================================================

Given(
  'saya memiliki Emergency Case yang membutuhkan rujukan dan Rumah Sakit kandidat yang valid',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const candidates = getHospitalCandidates(caseId);
    expect(candidates.length).toBeGreaterThan(0);

    const snapshot = getReferralSnapshot(caseId);

    expect(snapshot).not.toBeNull();
    expect(snapshot!.status).toBe('mencari');
    expect(snapshot!.requests).toHaveLength(1);

    getScenarioState(page).caseId = caseId;
  }
);

When(
  'saya membuka halaman Hospital Matching untuk Emergency Case tersebut',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId;
    expect(caseId).toBeDefined();

    await openHospitalMatching(page, caseId!);
  }
);

Then(
  'saya melihat permintaan rujukan dikirim kepada Rumah Sakit terdekat',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;
    const candidates = getHospitalCandidates(caseId);

    const firstRequest = snapshot.requests[0];

    // Memastikan kandidat pertama benar-benar
    // merupakan hasil urutan jarak HospitalMatchingService.
    expect(firstRequest.hospitalId).toBe(
      candidates[0].id
    );

    const rows = getHistoryRows(page);

    await expect(rows).toHaveCount(1);

    await expect(
      rows.first().locator('td').nth(1)
    ).toContainText(candidates[0].name);
  }
);

Then(
  'saya melihat urutan permintaan dan batas waktu responsnya',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;
    const request = snapshot.requests[0];

    expect(request.attemptOrder).toBe(1);
    expect(request.status).toBe('menunggu');
    expect(request.requestedAt).not.toBeNull();
    expect(request.expiresAt).not.toBeNull();

    expect(request.timeoutSeconds).toBe(100);

    const firstRow = getHistoryRows(page).first();

    await expect(
      firstRow.locator('td').nth(0)
    ).toHaveText('1');

    await expect(
      firstRow.locator('td').nth(4)
    ).not.toHaveText('-');
  }
);

// ======================================================
// 9. HM-02
// MENERIMA PERMINTAAN RUJUKAN
// ======================================================

Given(
  'saya memiliki permintaan rujukan aktif yang ditujukan kepada Rumah Sakit saya',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const snapshot = getReferralSnapshot(caseId)!;
    const request = getActiveRequest(snapshot);

    getScenarioState(page).caseId = caseId;

    const candidates = getHospitalCandidates(caseId);
    expect(candidates.length).toBeGreaterThan(0);

    await openHospitalDashboard(
      page,
      caseId,
      request
    );
  }
);

When(
  'saya membuka dashboard permintaan rujukan',
  async ({ page }) => {
    const state = getScenarioState(page);

    if (!state.activeHospitalAccount) {
      throw new Error(
        'Akun Rumah Sakit belum ditentukan.'
      );
    }

    await loginAs(
      page,
      state.activeHospitalAccount
    );

    await page.goto('/hospital/referrals');

    await expect(page).toHaveURL(
      /\/hospital\/referrals$/
    );
  }
);

When(
  'saya membuka detail permintaan rujukan tersebut',
  async ({ page }) => {
    await openHospitalRequestDetail(page);
  }
);

When(
  'saya menerima permintaan rujukan',
  async ({ page }) => {
    const state = getScenarioState(page);
    const snapshot = getReferralSnapshot(state.caseId!)!;

    const request = snapshot.requests.find(
      (item) => item.id === state.activeRequestId
    );

    if (!request) {
      throw new Error(
        'Permintaan aktif tidak ditemukan.'
      );
    }

    await respondToHospitalRequest(
      page,
      request,
      'diterima'
    );
  }
);

Then(
  'saya melihat status permintaan menjadi Diterima',
  async ({ page }) => {
    const state = getScenarioState(page);
    const snapshot = getReferralSnapshot(state.caseId!)!;

    expect(snapshot.status).toBe('diterima');

    await openHospitalMatching(
      page,
      state.caseId!
    );

    const acceptedRow = getHistoryRows(page)
      .filter({ hasText: 'Diterima' });

    await expect(acceptedRow).toHaveCount(1);
  }
);


Then(
  'saya melihat Rumah Sakit saya ditetapkan sebagai tujuan rujukan',
  async ({ page }) => {
    const state = getScenarioState(page);

    if (!state.caseId || !state.activeHospitalName) {
      throw new Error(
        'Informasi Emergency Case atau Rumah Sakit tujuan belum tersedia.'
      );
    }

    await openHospitalMatching(
      page,
      state.caseId
    );

    await expect(
      page.getByRole('heading', {
        name: 'Rumah Sakit Tujuan',
        exact: true,
      })
    ).toBeVisible();

    await expect(
      page.getByRole('heading', {
        name: state.activeHospitalName,
        exact: true,
      })
    ).toBeVisible();
  }
);

// ======================================================
// 10. HM-03
// VERIFIKASI KEBUTUHAN RUJUKAN
// ======================================================

Given(
  'saya memiliki Emergency Case yang memerlukan verifikasi kebutuhan rujukan',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Terjadi tabrakan kendaraan dan kondisi korban belum diketahui.'
    );

    // Kasus ambigu belum boleh membuat referral otomatis.
    const snapshot = getReferralSnapshot(caseId);

    expect(snapshot).toBeNull();

    getScenarioState(page).caseId = caseId;
  }
);

When(
  'saya memverifikasi bahwa Emergency Case membutuhkan rujukan Rumah Sakit',
  async ({ page }) => {
    await page.getByRole('button', {
      name: 'Verifikasi & Cari Rumah Sakit',
      exact: true,
    }).click();

    await expect(
      page.getByText(
        'Pencarian Rumah Sakit Sedang Berlangsung'
      )
    ).toBeVisible();
  }
);

Then(
  'saya melihat permintaan rujukan dikirim kepada Rumah Sakit kandidat pertama',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;
    const candidates = getHospitalCandidates(caseId);

    expect(snapshot.status).toBe('mencari');
    expect(snapshot.requests).toHaveLength(1);

    expect(snapshot.requests[0].hospitalId).toBe(
      candidates[0].id
    );

    await expect(
      getHistoryRows(page).first().locator('td').nth(1)
    ).toContainText(candidates[0].name);
  }
);

Then(
  'saya melihat proses rujukan berada dalam status Sedang Mencari Rumah Sakit',
  async ({ page }) => {
    await expect(
      page.getByText(
        'Sedang Mencari',
        { exact: true }
      )
    ).toBeVisible();
  }
);

// ======================================================
// 11. HM-04
// PENOLAKAN DAN MATCHING ULANG
// ======================================================

Given(
  'saya memiliki proses rujukan dengan permintaan aktif kepada Rumah Sakit kandidat pertama',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const candidates = getHospitalCandidates(caseId);
    expect(candidates.length).toBeGreaterThanOrEqual(2);

    const snapshot = getReferralSnapshot(caseId)!;
    const firstRequest = getActiveRequest(snapshot);

    expect(firstRequest.attemptOrder).toBe(1);

    getScenarioState(page).caseId = caseId;
    getScenarioState(page).activeRequestId =
      firstRequest.id;
  }
);

When(
  'Petugas Rumah Sakit kandidat pertama menolak permintaan rujukan',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;
    const request = getActiveRequest(snapshot);

    await respondToHospitalRequest(
      page,
      request,
      'ditolak'
    );
  }
);

Then(
  'saya melihat permintaan pertama berstatus Ditolak',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.requests[0].status).toBe('ditolak');

    await openHospitalMatching(page, caseId);

    await expect(
      getHistoryRows(page).first().locator('td').nth(2)
    ).toHaveText('Ditolak');
  }
);

Then(
  'saya melihat permintaan berikutnya dikirim kepada Rumah Sakit kandidat selanjutnya',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.requests).toHaveLength(2);

    const first = snapshot.requests[0];
    const second = snapshot.requests[1];

    expect(second.attemptOrder).toBe(2);
    expect(second.status).toBe('menunggu');

    expect(second.hospitalId).not.toBe(
      first.hospitalId
    );

    await assertRequestHistory(page, snapshot);
  }
);

// ======================================================
// 12. HM-05
// TIMEOUT DAN MATCHING ULANG
// ======================================================

Given(
  'saya merupakan Petugas Rumah Sakit kandidat berikutnya',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const candidates = getHospitalCandidates(caseId);
    expect(candidates.length).toBeGreaterThanOrEqual(2);

    const snapshot = getReferralSnapshot(caseId)!;
    const firstRequest = getActiveRequest(snapshot);

    getScenarioState(page).caseId = caseId;
    getScenarioState(page).activeRequestId =
      firstRequest.id;
  }
);

Given(
  'permintaan rujukan kepada kandidat sebelumnya masih menunggu respons',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    const request = getActiveRequest(snapshot);

    expect(request.status).toBe('menunggu');
    expect(request.expiresAt).not.toBeNull();
  }
);

When(
  'batas waktu permintaan sebelumnya berakhir',
  async ({ page }) => {
    const state = getScenarioState(page);
    const snapshot = getReferralSnapshot(state.caseId!)!;

    const previousRequest = getActiveRequest(snapshot);

    // Simulasi waktu habis tanpa menunggu 100 detik.
    forceRequestTimeout(previousRequest.id);

    const updated = getReferralSnapshot(state.caseId!)!;

    expect(updated.requests[0].status).toBe('timeout');

    const nextRequest = getActiveRequest(updated);

    expect(nextRequest.attemptOrder).toBe(2);
    expect(nextRequest.hospitalId).not.toBe(
      previousRequest.hospitalId
    );

    state.activeRequestId = nextRequest.id;
    state.activeHospitalName = nextRequest.hospitalName;
    state.activeHospitalAccount =
      getHospitalAccount(nextRequest.hospitalName);
  }
);

Then(
  'saya melihat permintaan baru untuk Emergency Case tersebut',
  async ({ page }) => {
    const state = getScenarioState(page);
    const snapshot = getReferralSnapshot(state.caseId!)!;

    const nextRequest = snapshot.requests.find(
      (item) => item.id === state.activeRequestId
    );

    expect(nextRequest).toBeDefined();
    expect(nextRequest!.status).toBe('menunggu');

    await expect(
    page.getByText(
        `EC-${String(state.caseId).padStart(4, '0')}`,
        { exact: true }
    )
    ).toBeVisible();
  }
);

// ======================================================
// 13. HM-06
// TIDAK ADA KANDIDAT RUMAH SAKIT
// ======================================================

Given(
  'saya memiliki Emergency Case yang membutuhkan rujukan tanpa Rumah Sakit kandidat yang valid',
  async ({ page }) => {
    // Nonaktifkan semua RS hanya pada database testing.
    setAllHospitalsUnavailable();

    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const candidates = getHospitalCandidates(caseId);
    expect(candidates).toHaveLength(0);

    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.status).toBe('gagal');
    expect(snapshot.requests).toHaveLength(0);

    getScenarioState(page).caseId = caseId;
  }
);

Then(
  'saya melihat proses rujukan berakhir dengan status Gagal',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.status).toBe('gagal');

    await expect(
      page.getByText(
        'Rujukan Gagal',
        { exact: true }
      )
    ).toBeVisible();
  }
);

Then(
  'saya melihat informasi penyebab kegagalan rujukan',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.failureReason).toContain(
      'Tidak ada Rumah Sakit'
    );

    await expect(
      page.getByText(
        /Tidak ada Rumah Sakit dengan kapasitas dan koordinat valid/
      )
    ).toBeVisible();
  }
);

// ======================================================
// 14. HM-07
// SELURUH RUMAH SAKIT MENOLAK
// ======================================================

Given(
  'saya memiliki proses rujukan dengan beberapa Rumah Sakit kandidat',
  async ({ page }) => {
    const caseId = await createEmergencyCase(
      page,
      'Korban mengalami luka berat dan membutuhkan rujukan rumah sakit.'
    );

    const candidates = getHospitalCandidates(caseId);

    expect(candidates.length).toBeGreaterThanOrEqual(2);

    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.status).toBe('mencari');
    expect(snapshot.requests).toHaveLength(1);

    getScenarioState(page).caseId = caseId;
  }
);

When(
  'seluruh Rumah Sakit kandidat menolak permintaan rujukan',
  async ({ page }) => {
    const state = getScenarioState(page);
    const candidates = getHospitalCandidates(state.caseId!);

    // Setiap kandidat menolak secara berurutan.
    for (let i = 0; i < candidates.length; i++) {
      const snapshot = getReferralSnapshot(state.caseId!)!;

      if (snapshot.status === 'gagal') {
        break;
      }

      const activeRequest = getActiveRequest(snapshot);

      await respondToHospitalRequest(
        page,
        activeRequest,
        'ditolak'
      );
    }

    const finalSnapshot = getReferralSnapshot(
      state.caseId!
    )!;

    expect(finalSnapshot.status).toBe('gagal');
    expect(finalSnapshot.requests.length).toBe(
      candidates.length
    );

    expect(
      finalSnapshot.requests.every(
        (request) => request.status === 'ditolak'
      )
    ).toBe(true);

    expect(finalSnapshot.acceptedHospitalName).toBeNull();
  }
);

Then(
  'saya melihat seluruh permintaan rujukan berstatus Ditolak',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(
      snapshot.requests.every(
        (request) => request.status === 'ditolak'
      )
    ).toBe(true);

    await openHospitalMatching(page, caseId);

    await assertRequestHistory(page, snapshot);
  }
);

Then(
  'saya melihat informasi bahwa tidak ada Rumah Sakit yang menerima rujukan',
  async ({ page }) => {
    const caseId = getScenarioState(page).caseId!;
    const snapshot = getReferralSnapshot(caseId)!;

    expect(snapshot.status).toBe('gagal');
    expect(snapshot.acceptedHospitalName).toBeNull();

    expect(snapshot.failureReason).toContain(
      'Tidak ada kandidat Rumah Sakit berikutnya'
    );

    await expect(
      page.getByText(
        /Tidak ada kandidat Rumah Sakit berikutnya yang tersedia/
      )
    ).toBeVisible();
  }
);
