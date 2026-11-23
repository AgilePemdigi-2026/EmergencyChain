import { execFileSync } from 'node:child_process';
import { createBdd, test } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

type ReferralState = {
  caseId?: number;
  requestId?: number;
  referralId?: number;
  hospitalId?: number;
  patientId?: number;
  originalHistoryId?: number;
  nextRequestId?: number;
  newHistoryId?: number;
  staffId?: number;
  note?: string;
};

const states = new WeakMap<Page, ReferralState>();
function state(page: Page): ReferralState {
  let value = states.get(page);
  if (!value) { value = {}; states.set(page, value); }
  return value;
}

const demoPassword = process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';
const accounts = {
  pelapor: {
    email: process.env.E2E_PELAPOR_EMAIL ?? 'pelapor@emergencychain.test',
    password: process.env.E2E_PELAPOR_PASSWORD ?? demoPassword,
  },
  hospital: {
    email: process.env.E2E_RSUD_EMAIL ?? 'rs@emergencychain.test',
    password: process.env.E2E_RSUD_PASSWORD ?? demoPassword,
  },
};

/** All fixture and assertion queries are restricted to the isolated test database. */
function db<T>(code: string): T {
  const php = `
    $databaseName = \\Illuminate\\Support\\Facades\\DB::selectOne(
      'select current_database() as name'
    )->name;
    if (app()->environment() !== 'testing' ||
        config('database.default') !== 'pgsql' ||
        $databaseName !== 'emergency_chain_test') {
      throw new \\RuntimeException('RS BDD menolak database selain emergency_chain_test.');
    }
    ${code}
    echo '__RS_JSON_START__' .
       json_encode($bddResult, JSON_UNESCAPED_UNICODE) .
       '__RS_JSON_END__';
  `;
  const output = execFileSync(
    'php', ['artisan', '--env=testing', 'tinker', `--execute=${php}`],
    {
      encoding: 'utf8',
      cwd: process.cwd(),
      env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'pgsql', DB_DATABASE: 'emergency_chain_test' },
      timeout: 30000,
    },
  );
  const raw = output.match(/__RS_JSON_START__(.*?)__RS_JSON_END__/s)?.[1];
  if (raw === undefined) throw new Error(`Tidak bisa membaca fixture RS: ${output}`);
  return JSON.parse(raw) as T;
}

async function loginAs(page: Page, account: keyof typeof accounts): Promise<void> {
  await page.goto('/login');
  if (!/\/login(?:\?|$)/.test(page.url())) {
    // Login redirect indicates that Laravel still has an authenticated session.
    const logout = page.getByRole('button', { name: 'Logout', exact: true });
    if (await logout.isVisible()) {
      await logout.click();
    } else {
      const logoutLink = page.getByRole('link', { name: 'Logout', exact: true });
      if (await logoutLink.isVisible()) await logoutLink.click();
      else throw new Error(`Tidak menemukan kontrol Logout pada ${page.url()}`);
    }
    await page.goto('/login');
  }
  await expect(page).toHaveURL(/\/login(?:\?|$)/);
  await expect(page.locator('input[name="email"]')).toBeVisible();
  await page.locator('input[name="email"]').fill(accounts[account].email);
  await page.locator('input[name="password"]').fill(accounts[account].password);
  await page.getByRole('button', { name: 'Login', exact: true }).click();
  await expect(page).not.toHaveURL(/\/login(?:\?|$)/);
}

async function createCase(page: Page): Promise<number> {
  await loginAs(page, 'pelapor');
  await page.goto('/pelapor/laporan/buat');
  await expect(page.getByRole('heading', { name: 'Buat Laporan Emergency' })).toBeVisible();
  await page.locator('#jenis_kejadian').selectOption({ label: 'Kecelakaan Lalu Lintas' });
  await page.locator('#tingkat_kebutuhan').selectOption({ label: 'Butuh Penanganan Cepat' });
  await page.locator('#kondisi_kejadian').fill(`Pasien RS BDD ${Date.now()}`);
  await page.locator('#alamat').fill('Jl. Keputih, Surabaya - pengujian pasien RS');
  await expect(page.locator('#location-map')).toBeVisible();
  await page.locator('#location-map').click({ position: { x: 250, y: 180 } });
  await expect(page.locator('#latitude')).not.toHaveValue('');
  await expect(page.locator('#longitude')).not.toHaveValue('');
  await page.locator('#submit-report').click();
  await expect(page.getByText('Laporan berhasil dikirim!', { exact: true })).toBeVisible();
  await page.goto('/pelapor/laporan/status');
  const link = page.locator('tbody tr').first().locator('a[href*="/pelapor/laporan/"]').first();
  await expect(link).toBeVisible();
  const href = await link.getAttribute('href');
  const id = href?.match(/\/pelapor\/laporan\/(\d+)(?:\?.*)?$/)?.[1];
  if (!id) throw new Error(`Case ID tidak ditemukan dari ${href}`);
  return Number(id);
}

/** Create just the prerequisite pending request; real accept/reject remains a browser action. */
async function makePendingRequest(page: Page): Promise<void> {
  const s = state(page);
  s.caseId = await createCase(page);
  const ids = db<{ referralId: number; requestId: number; hospitalId: number; staffId: number }>(`
    $rs = \\App\\Models\\RumahSakit::where('nama_rs', 'RSUD Dr. Soetomo')->firstOrFail();
    $other = \\App\\Models\\RumahSakit::where('nama_rs', 'RS Universitas Airlangga')->firstOrFail();
    $rs->update(['status' => 'aktif', 'kapasitas_tersedia' => 80]);
    $other->update(['status' => 'aktif', 'kapasitas_tersedia' => 80]);
    $staff = \\App\\Models\\Pengguna::where('email', 'rs@emergencychain.test')->firstOrFail();
    if ((int)$staff->rumah_sakit_id !== (int)$rs->rumah_sakit_id) {
      throw new \\RuntimeException('Akun RSUD tidak cocok dengan master Rumah Sakit.');
    }
    $ref = \\App\\Models\\HospitalReferral::create([
      'case_id' => ${s.caseId}, 'status' => 'mencari',
      'accepted_hospital_id' => null, 'failure_reason' => null,
    ]);
    $req = $ref->requests()->create([
      'rumah_sakit_id' => $rs->rumah_sakit_id,
      'attempt_order' => 1,
      'status' => 'menunggu',
      'requested_at' => now(),
      'expires_at' => now()->addMinutes(10),
    ]);
    $bddResult = [
      'referralId' => (int)$ref->id,
      'requestId' => (int)$req->id,
      'hospitalId' => (int)$rs->rumah_sakit_id,
      'staffId' => (int)$staff->pengguna_id,
    ];
  `);
  Object.assign(s, ids);
  await loginAs(page, 'hospital');
}

function requestCard(page: Page) {
  const id = state(page).requestId;
  if (!id) throw new Error('requestId belum disiapkan');
  return page.locator('article').filter({ hasText: `Permintaan Rujukan #${id}` });
}

function snapshot(page: Page) {
  const s = state(page);
  if (!s.referralId) throw new Error('referralId belum disiapkan');
  return db<{
    referralStatus: string; acceptedHospitalId: number | null;
    requests: { id: number; hospitalId: number; status: string; order: number; responded: boolean }[];
    patientId: number | null; histories: { id: number; status: string; note: string | null; staffId: number | null; timestamp: string | null }[];
  }>(`
    $ref = \\App\\Models\\HospitalReferral::with('requests')
      ->findOrFail(${s.referralId});
    $patient = \\App\\Models\\InformasiKorban::with('treatmentHistories')
      ->where('case_id', ${s.caseId})->where('rumah_sakit_id', ${s.hospitalId})
      ->first();
    $bddResult = [
      'referralStatus' => $ref->status,
      'acceptedHospitalId' => $ref->accepted_hospital_id,
      'requests' => $ref->requests->sortBy('attempt_order')->map(fn($r) => [
        'id' => (int)$r->id,
        'hospitalId' => (int)$r->rumah_sakit_id,
        'status' => $r->status,
        'order' => (int)$r->attempt_order,
        'responded' => $r->responded_at !== null,
      ])->values()->all(),
      'patientId' => $patient?->informasi_id,
      'histories' => $patient ? $patient->treatmentHistories->sortBy('id')->map(fn($h) => [
        'id' => (int)$h->id,
        'status' => $h->status,
        'note' => $h->keterangan,
        'staffId' => $h->diubah_oleh,
        'timestamp' => $h->created_at?->toDateTimeString(),
      ])->values()->all() : [],
    ];
  `);
}

async function acceptRequest(page: Page): Promise<void> {
  await page.goto('/hospital/referrals');
  await expect(requestCard(page).getByText('Menunggu Konfirmasi')).toBeVisible();
  await requestCard(page).getByRole('button', { name: 'Terima', exact: true }).click();
  await expect(page).toHaveURL(/\/hospital\/referrals(?:\?|$)/);
  await expect(requestCard(page).getByText('Diterima', { exact: true })).toBeVisible();
  const result = snapshot(page);
  if (!result.patientId) throw new Error('Service tidak membuat pasien setelah penerimaan');
  state(page).patientId = result.patientId;
  state(page).originalHistoryId = result.histories[0]?.id;
}

async function ensureAccepted(page: Page): Promise<void> {
  await makePendingRequest(page);
  await acceptRequest(page);
}

// Background. The test database must be the same as the server on port 8001.
Given('pengguna telah login sebagai petugas rumah sakit', async ({ page }) => {
  // Fail early if local PHP is not using the isolated E2E database.
  db(`$bddResult = ['ok' => true];`);
  await loginAs(page, 'hospital');
});

Given('terdapat permintaan rujukan yang ditujukan kepada rumah sakit pengguna', async ({ page }) => {
  await makePendingRequest(page);
});

When('petugas membuka halaman permintaan rujukan', async ({ page }) => {
  await page.goto('/hospital/referrals');
  await expect(page.getByRole('heading', { name: 'Dashboard Rumah Sakit' })).toBeVisible();
});

Then('sistem menampilkan permintaan rujukan yang ditujukan kepada rumah sakit tersebut', async ({ page }) => {
  const s = state(page);
  await expect(requestCard(page)).toContainText(`EC-${String(s.caseId).padStart(4, '0')}`);
  await expect(requestCard(page).getByText('Menunggu Konfirmasi')).toBeVisible();
});

When('petugas menerima permintaan rujukan berstatus menunggu keputusan', async ({ page }) => {
  await requestCard(page).getByRole('button', { name: 'Terima', exact: true }).click();
  await expect(page).toHaveURL(/\/hospital\/referrals(?:\?|$)/);
});

Then('sistem menyimpan keputusan penerimaan rujukan', async ({ page }) => {
  const result = snapshot(page);
  expect(result.referralStatus).toBe('diterima');
  expect(result.requests.find(r => r.id === state(page).requestId)?.status).toBe('diterima');
  expect(result.requests.find(r => r.id === state(page).requestId)?.responded).toBe(true);
});

Then('sistem mengaitkan rujukan dengan rumah sakit pengguna', async ({ page }) => {
  expect(snapshot(page).acceptedHospitalId).toBe(state(page).hospitalId);
});

Then('sistem membuat data pasien dari rujukan yang diterima', async ({ page }) => {
  const result = snapshot(page);
  expect(result.patientId).toBeTruthy();
  state(page).patientId = result.patientId!;
});

Then('sistem membuat riwayat awal pasien dengan status {string}', async ({ page }, status: string) => {
  const result = snapshot(page);
  expect(result.histories[0]?.status).toBe(status);
  expect(result.histories[0]?.staffId).toBe(state(page).staffId);
  expect(result.histories[0]?.timestamp).toBeTruthy();
});

When('petugas menolak permintaan rujukan berstatus menunggu keputusan', async ({ page }) => {
  await requestCard(page).getByRole('button', { name: 'Tolak', exact: true }).click();
  await expect(page).toHaveURL(/\/hospital\/referrals(?:\?|$)/);
});

Then('sistem memproses penolakan rujukan', async ({ page }) => {
  const result = snapshot(page);
  expect(result.requests.find(r => r.id === state(page).requestId)?.status).toBe('ditolak');
  expect(result.requests.find(r => r.id === state(page).requestId)?.responded).toBe(true);
  expect(result.acceptedHospitalId).toBeNull();
  expect(result.patientId).toBeNull();
});

Then('sistem meneruskan proses pencarian rumah sakit tujuan berikutnya', async ({ page }) => {
  const result = snapshot(page);
  expect(result.referralStatus).toBe('mencari');
  const next = result.requests.find(r => r.order === 2);
  expect(next, 'Tidak ada permintaan ke RS kedua').toBeDefined();
  expect(next!.status).toBe('menunggu');
  expect(next!.hospitalId).not.toBe(state(page).hospitalId);
});

Given('terdapat rujukan yang telah diterima oleh rumah sakit pengguna', async ({ page }) => {
  await ensureAccepted(page);
});

Given('data pasien dari rujukan tersebut telah dibuat', async ({ page }) => {
  expect(snapshot(page).patientId).toBe(state(page).patientId);
});

When('petugas membuka halaman daftar pasien', async ({ page }) => {
  await page.goto('/hospital/patients');
  await expect(page.getByRole('heading', { name: 'Daftar Pasien' })).toBeVisible();
});

Then('sistem menampilkan pasien yang diterima oleh rumah sakit pengguna', async ({ page }) => {
  const id = state(page).patientId;

  await expect(
    page.locator('tbody tr td:first-child')
      .getByText(`#${id}`, { exact: true })
  ).toHaveCount(1);
});

Then('sistem menampilkan pasien yang status penanganan terakhirnya {string}', async ({ page }, status: string) => {
  const id = state(page).patientId;
  await expect(page.locator('tbody tr').filter({ hasText: `#${id}` })).toContainText(status);
  expect(snapshot(page).histories.at(-1)?.status).toBe(status);
});

Given('terdapat pasien yang dirujuk dan diterima oleh rumah sakit pengguna', async ({ page }) => {
  await ensureAccepted(page);
});

When('petugas membuka detail pasien tersebut', async ({ page }) => {
  await page.goto(`/hospital/patients/${state(page).patientId}`);
  await expect(page.getByRole('heading', { name: `Detail Pasien #${state(page).patientId}` })).toBeVisible();
});

Then('sistem menampilkan informasi pasien', async ({ page }) => {
  await expect(page.getByRole('heading', { name: 'Informasi Pasien' })).toBeVisible();
  await expect(page.getByText('ID Informasi Pasien')).toBeVisible();
  await expect(page.getByText('Status Penerimaan')).toBeVisible();
});

Then('sistem menampilkan informasi emergency case terkait', async ({ page }) => {
  await expect(
    page.getByRole('heading', {
      name: 'Informasi Emergency Case',
    })
  ).toBeVisible();

  const caseId = state(page).caseId;

  await expect(
    page.getByText(
      new RegExp(`(?:EC-0*|#\\s*)${caseId}\\b`)
    ).first()
  ).toBeVisible();
});

Then('sistem menampilkan kondisi korban dan informasi rujukan', async ({ page }) => {
  await expect(page.getByText('Kondisi Korban')).toBeVisible();
  await expect(page.getByText('Belum diperiksa oleh petugas rumah sakit')).toBeVisible();
  await expect(page.getByText('Waktu Rujukan')).toBeVisible();
});

Then('sistem menampilkan riwayat penanganan pasien', async ({ page }) => {
  await expect(page.getByRole('heading', { name: 'Riwayat Penanganan' })).toBeVisible();
  await expect(page.getByText('Rujukan diterima oleh rumah sakit.')).toBeVisible();
});

Given('terdapat pasien yang sedang ditangani oleh rumah sakit pengguna', async ({ page }) => {
  await ensureAccepted(page);
});

Given('status penanganan terakhir pasien adalah {string}', async ({ page }, status: string) => {
  expect(snapshot(page).histories.at(-1)?.status).toBe(status);
});

When('petugas mengubah status pasien menjadi {string}', async ({ page }, status: string) => {
  await page.goto(`/hospital/patients/${state(page).patientId}`);
  await page.locator('#status').selectOption({ label: status });
  const note = `Perubahan status otomatis RS-05 ${Date.now()}`;
  state(page).note = note;
  await page.locator('#keterangan').fill(note);
});

When('petugas menyimpan perubahan status', async ({ page }) => {
  await page.getByRole('button', { name: 'Simpan Perubahan Status' }).click();
});

Then('sistem menyimpan status penanganan terakhir sebagai {string}', async ({ page }, status: string) => {
  expect(snapshot(page).histories.at(-1)?.status).toBe(status);
});

Then('sistem menyimpan catatan riwayat perubahan status pasien', async ({ page }) => {
  const histories = snapshot(page).histories;
  expect(histories.length).toBe(2);
  expect(histories.at(-1)?.id).not.toBe(state(page).originalHistoryId);
  expect(histories.at(-1)?.note).toBe(state(page).note);
});

Then('sistem mencatat waktu perubahan status', async ({ page }) => {
  const timestamp = snapshot(page).histories.at(-1)?.timestamp;
  expect(timestamp).toBeTruthy();
  expect(Number.isNaN(Date.parse(timestamp!))).toBe(false);
});

Then('sistem mencatat petugas yang melakukan perubahan', async ({ page }) => {
  expect(snapshot(page).histories.at(-1)?.staffId).toBe(state(page).staffId);
});

Then('sistem mengarahkan petugas ke halaman Riwayat Penanganan', async ({ page }) => {
  await expect(page).toHaveURL(/\/hospital\/patients\/history(?:\?|$)/);
});

Then('sistem menampilkan pasien tersebut di Riwayat Penanganan', async ({ page }) => {
  await expect(page.getByText(`#${state(page).patientId}`, { exact: true })).toBeVisible();
});

Then('sistem tidak menampilkan pasien tersebut di daftar pasien aktif', async ({ page }) => {
  await page.goto('/hospital/patients');

  await expect(
    page.getByRole('heading', {
      name: 'Daftar Pasien',
    })
  ).toBeVisible();

  const patientId = state(page).patientId;

  await expect(
    page.locator('tbody tr td:first-child')
      .getByText(`#${patientId}`, { exact: true })
  ).toHaveCount(0);
});