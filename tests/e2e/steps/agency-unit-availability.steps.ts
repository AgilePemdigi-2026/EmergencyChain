
import { execFileSync } from 'node:child_process';
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

type UnitInfo = {
  id: number;
  name: string;
  status: string;
};

type ScenarioState = {
  available?: UnitInfo;
  unavailable?: UnitInfo;
  assigned?: UnitInfo;
  caseId?: number;
};

const states = new WeakMap<Page, ScenarioState>();

function getState(page: Page): ScenarioState {
  let state = states.get(page);

  if (!state) {
    state = {};
    states.set(page, state);
  }

  return state;
}

const demoPassword =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const accounts = {
  instansi: {
    email:
      process.env.E2E_POLISI_EMAIL ??
      'polisi@emergencychain.test',
    password:
      process.env.E2E_POLISI_PASSWORD ?? demoPassword,
  },
  pelapor: {
    email:
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test',
    password:
      process.env.E2E_PELAPOR_PASSWORD ?? demoPassword,
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

function assertTestingDatabase(): void {
  const result = runTinker(`
    echo '__AUA_DB__' .
      app()->environment() . '|' .
      config('database.default') . '|' .
      \\Illuminate\\Support\\Facades\\DB::selectOne(
        'select current_database() as name'
      )->name .
      '__END__';
  `);

  if (
    !result.includes(
      '__AUA_DB__testing|pgsql|emergency_chain_test__END__'
    )
  ) {
    throw new Error(
      'Fixture AUA ditolak: database aktif bukan emergency_chain_test.'
    );
  }
}

function readJson<T>(code: string): T {
  assertTestingDatabase();

  const result = runTinker(`
    ${code}

    echo '__AUA_JSON__' .
      json_encode(
        $bddResult,
        JSON_UNESCAPED_UNICODE
      ) .
      '__END__';
  `);

  const json = result.match(
    /__AUA_JSON__(.*?)__END__/s
  )?.[1];

  if (!json) {
    throw new Error(
      `Hasil Tinker tidak dapat dibaca: ${result}`
    );
  }

  return JSON.parse(json) as T;
}

async function loginAs(
  page: Page,
  account: AccountKey
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

async function openUnits(page: Page): Promise<void> {
  await loginAs(page, 'instansi');

  await page.goto('/instansi/units');

  await expect(
    page.getByRole('heading', {
      name: 'Kelola Unit',
      exact: true,
    })
  ).toBeVisible();
}

function unitRow(page: Page, name: string) {
  return page.locator('tbody tr').filter({
    has: page.getByText(name, { exact: true }),
  });
}

function availabilitySelect(
  page: Page,
  unit: UnitInfo
) {
  return unitRow(page, unit.name).locator(
    'select[name="status_ketersediaan"]'
  );
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

  await page.locator('#jenis_kejadian')
    .selectOption({
      label: 'Kecelakaan Lalu Lintas',
    });

  await page.locator('#tingkat_kebutuhan')
    .selectOption({
      label: 'Butuh Penanganan Cepat',
    });

  await page.locator('#kondisi_kejadian')
    .fill(`BDD AUA ${Date.now()}`);

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Pengujian AUA'
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
    page.getByText(
      'Laporan berhasil dikirim!',
      { exact: true }
    )
  ).toBeVisible();

  await page.goto('/pelapor/laporan/status');

  const firstRow = page.locator('tbody tr').first();

  await expect(firstRow).toBeVisible();

  const href = await firstRow
    .locator('a[href*="/pelapor/laporan/"]')
    .getAttribute('href');

  const match = href?.match(
    /\/pelapor\/laporan\/(\d+)$/
  );

  if (!match) {
    throw new Error(
      'Case ID AUA tidak berhasil ditemukan.'
    );
  }

  return Number(match[1]);
}

// ======================================================
// AUA-01: DATA RESOURCE TERSEDIA / TIDAK TERSEDIA
// ======================================================

Given(
  'saya sebagai Instansi memiliki Resource yang tersedia dan tidak tersedia',
  async ({ page }) => {
    const email = JSON.stringify(
      accounts.instansi.email
    );

    const units = readJson<UnitInfo[]>(`
      $user = \\App\\Models\\Pengguna::query()
        ->where('email', ${email})
        ->firstOrFail();

      $units = \\App\\Models\\Resource::query()
        ->where('instansi_id', $user->instansi_id)
        ->orderBy('resource_id')
        ->get();

      if ($units->isEmpty()) {
        throw new \\RuntimeException(
          'Seeder belum menyediakan Resource milik Instansi.'
        );
      }

      if ($units->count() < 2) {
        $copy = $units->first()->replicate();
        $copy->nama_resource =
          'Unit BDD AUA Cadangan ' . uniqid();
        $copy->status_ketersediaan =
          \\App\\Models\\Resource::STATUS_TIDAK_TERSEDIA;
        $copy->save();

        $units = \\App\\Models\\Resource::query()
          ->where('instansi_id', $user->instansi_id)
          ->orderBy('resource_id')
          ->get();
      }

      $available = $units[0];
      $unavailable = $units[1];

      $available->update([
        'status_ketersediaan' =>
          \\App\\Models\\Resource::STATUS_TERSEDIA,
      ]);

      $unavailable->update([
        'status_ketersediaan' =>
          \\App\\Models\\Resource::STATUS_TIDAK_TERSEDIA,
      ]);

      $bddResult = [
        [
          'id' => (int) $available->resource_id,
          'name' => $available->nama_resource,
          'status' => $available->status_ketersediaan,
        ],
        [
          'id' => (int) $unavailable->resource_id,
          'name' => $unavailable->nama_resource,
          'status' => $unavailable->status_ketersediaan,
        ],
      ];
    `);

    const state = getState(page);

    state.available = units[0];
    state.unavailable = units[1];

    await loginAs(page, 'instansi');
  }
);


Then(
  'saya melihat daftar Resource beserta status ketersediaannya',
  async ({ page }) => {
    const state = getState(page);

    if (!state.available || !state.unavailable) {
      throw new Error(
        'Fixture AUA-01 belum disiapkan.'
      );
    }

    await expect(
      unitRow(page, state.available.name)
    ).toHaveCount(1);

    await expect(
      availabilitySelect(page, state.available)
    ).toHaveValue('tersedia');

    await expect(
      unitRow(page, state.unavailable.name)
    ).toHaveCount(1);

    await expect(
      availabilitySelect(page, state.unavailable)
    ).toHaveValue('tidak_tersedia');
  }
);

When(
  'saya mengubah status Resource dari tersedia menjadi tidak tersedia',
  async ({ page }) => {
    const unit = getState(page).available;

    if (!unit) {
      throw new Error(
        'Resource tersedia belum disiapkan.'
      );
    }

    await availabilitySelect(
      page,
      unit
    ).selectOption('tidak_tersedia');
  }
);

Then(
  'saya melihat status Resource berubah menjadi tidak tersedia',
  async ({ page }) => {
    const unit = getState(page).available!;

    await expect(
      availabilitySelect(page, unit)
    ).toHaveValue('tidak_tersedia');

    await expect(
      page.getByText(
        'Ketersediaan unit berhasil diperbarui.'
      )
    ).toBeVisible();
  }
);

When(
  'saya mengubah kembali status Resource menjadi tersedia',
  async ({ page }) => {
    const unit = getState(page).available!;

    await availabilitySelect(
      page,
      unit
    ).selectOption('tersedia');
  }
);

Then(
  'saya melihat status Resource berubah menjadi tersedia',
  async ({ page }) => {
    const unit = getState(page).available!;

    await expect(
      availabilitySelect(page, unit)
    ).toHaveValue('tersedia');

    await expect(
      page.getByText(
        'Ketersediaan unit berhasil diperbarui.'
      )
    ).toBeVisible();
  }
);

// ======================================================
// AUA-02: RESOURCE SEDANG DITUGASKAN
// ======================================================

Given(
  'saya sebagai Instansi memiliki Resource yang sedang ditugaskan',
  async ({ page }) => {
    const state = getState(page);

    state.caseId = await createEmergencyCase(page);

    const email = JSON.stringify(
      accounts.instansi.email
    );

    const assigned = readJson<UnitInfo | null>(`
      $user = \\App\\Models\\Pengguna::query()
        ->where('email', ${email})
        ->firstOrFail();

      $assignment = \\App\\Models\\Assignment::query()
        ->with('resource')
        ->where('case_id', ${state.caseId})
        ->whereHas('resource', function ($query) use ($user) {
          $query->where(
            'instansi_id',
            $user->instansi_id
          );
        })
        ->first();

      $bddResult = $assignment ? [
        'id' => (int) $assignment->resource->resource_id,
        'name' => $assignment->resource->nama_resource,
        'status' => $assignment->resource->status_ketersediaan,
      ] : null;
    `);

    if (!assigned) {
      throw new Error(
        `Tidak ada Assignment Polisi untuk EC-${state.caseId}.`
      );
    }

    if (assigned.status !== 'sedang_ditugaskan') {
    throw new Error(
        `Resource ${assigned.name} belum berstatus sedang_ditugaskan. ` +
        `Status aktual: ${assigned.status}`
    );
    }

    state.assigned = assigned;

    await loginAs(page, 'instansi');
  }
);

Then(
  'saya tidak dapat mengubah status ketersediaan Resource yang sedang ditugaskan',
  async ({ page }) => {
    const unit = getState(page).assigned;

    if (!unit) {
      throw new Error(
        'Fixture AUA-02 belum disiapkan.'
      );
    }

    const row = unitRow(page, unit.name);

    await expect(row).toHaveCount(1);

    await expect(
      row.getByText(
        'Sedang Ditugaskan',
        { exact: true }
      )
    ).toBeVisible();

    await expect(
      row.locator(
        'select[name="status_ketersediaan"]'
      )
    ).toHaveCount(0);
  }
);

Then(
  'saya tidak melihat fitur untuk menambah Resource',
  async ({ page }) => {
    await expect(
      page.locator('form').filter({
        has: page.locator(
          'input[name="nama_resource"]'
        ),
      })
    ).toHaveCount(0);

    await expect(
      page.getByRole('button', {
        name: /tambah (unit|resource)/i,
      })
    ).toHaveCount(0);
  }
);

Then(
  'saya tidak melihat fitur untuk mengedit data dasar Resource',
  async ({ page }) => {
    await expect(
      page.locator(
        'input[name="nama_resource"]'
      )
    ).toHaveCount(0);

    await expect(
      page.getByRole('link', {
        name: /edit|ubah data/i,
      })
    ).toHaveCount(0);
  }
);

Then(
  'saya tidak melihat fitur untuk menghapus Resource',
  async ({ page }) => {
    await expect(
      page.getByRole('button', {
        name: /hapus|delete/i,
      })
    ).toHaveCount(0);

    await expect(
      page.locator(
        'form[action*="/delete"]'
      )
    ).toHaveCount(0);
  }
);
