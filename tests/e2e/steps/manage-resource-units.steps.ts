
import { test, createBdd } from 'playwright-bdd';
import { expect, type Page } from '@playwright/test';

const { Given, When, Then } = createBdd(test);

// ======================================================
// 1. KONFIGURASI PENGUJIAN
// ======================================================

const DEMO_PASSWORD =
  process.env.E2E_DEMO_PASSWORD ?? 'Demo12345!';

const INSTANSI_ACCOUNT = {
  email:
    process.env.E2E_POLISI_EMAIL ??
    'polisi@emergencychain.test',

  password:
    process.env.E2E_POLISI_PASSWORD ??
    DEMO_PASSWORD,
};

const RESOURCE_NAMES = {
  polisi: 'Unit Polisi Demo',
};

const POLICE_BASE_ADDRESS =
  'Jl. Sikatan No. 1, Krembangan Selatan, Surabaya, Jawa Timur 60175';

type ScenarioState = {
  unitName?: string;
  updatedUnitName?: string;
  caseId?: number;
  assignmentId?: number;
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
// 2. HELPER LOGIN DAN NAVIGASI
// ======================================================

async function loginAsInstansi(page: Page): Promise<void> {
  await page.context().clearCookies();
  await page.goto('/login');

  await page.locator('input[name="email"]')
    .fill(INSTANSI_ACCOUNT.email);

  await page.locator('input[name="password"]')
    .fill(INSTANSI_ACCOUNT.password);

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login$/);
}

async function loginAsPelapor(page: Page): Promise<void> {
  await page.context().clearCookies();
  await page.goto('/login');

  await page.locator('input[name="email"]')
    .fill(
      process.env.E2E_PELAPOR_EMAIL ??
      'pelapor@emergencychain.test'
    );

  await page.locator('input[name="password"]')
    .fill(
      process.env.E2E_PELAPOR_PASSWORD ??
      DEMO_PASSWORD
    );

  await page.getByRole('button', {
    name: 'Login',
    exact: true,
  }).click();

  await expect(page).not.toHaveURL(/\/login$/);
}

async function openManageUnits(page: Page): Promise<void> {
  await loginAsInstansi(page);

  await page.goto('/instansi/units');

  await expect(
    page.getByRole('heading', {
      name: 'Kelola Unit',
      exact: true,
    })
  ).toBeVisible();
}

function findUnitRow(
  page: Page,
  unitName: string
) {
  return page.locator('tbody tr').filter({
    hasText: unitName,
  });
}

// ======================================================
// 3. HELPER MODAL TAMBAH UNIT
// ======================================================

async function openCreateUnitModal(page: Page): Promise<void> {
  await page.locator(
    '[data-modal-open="create-unit-modal"]'
  ).click();

  await expect(
    page.locator('#create-unit-modal')
  ).toBeVisible();
}

async function fillCreateUnitForm(
  page: Page,
  unitName: string
): Promise<void> {
  const modal = page.locator('#create-unit-modal');

  await modal.locator(
    'input[name="nama_resource"]'
  ).fill(unitName);

  await modal.locator(
    'input[name="jenis_resource"]'
  ).fill('Polisi');

  await modal.locator(
    'input[name="kapasitas_personel"]'
  ).fill('6');

  await modal.locator(
    'select[name="status_ketersediaan"]'
  ).selectOption('tersedia');

  getScenarioState(page).unitName = unitName;
}

async function submitCreateUnitForm(page: Page): Promise<void> {
  await page.locator('#create-unit-modal')
    .getByRole('button', {
      name: 'Tambah Unit',
      exact: true,
    })
    .click();

  await expect(
    page.getByText('Unit berhasil ditambahkan.', {
      exact: true,
    })
  ).toBeVisible();
}

async function createUnitForScenario(
  page: Page
): Promise<string> {
  await openManageUnits(page);

  await openCreateUnitModal(page);

  const unitName = `BDD Unit ${Date.now()}`;

  await fillCreateUnitForm(page, unitName);
  await submitCreateUnitForm(page);

  await expect(
    findUnitRow(page, unitName)
  ).toHaveCount(1);

  return unitName;
}

// ======================================================
// 4. HELPER PEMBUATAN EMERGENCY CASE
//    UNTUK PERSIAPAN UNIT SEDANG DITUGASKAN
// ======================================================

async function createAccidentReportAndCaptureId(
  page: Page
): Promise<number> {
  await loginAsPelapor(page);

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
    `BDD US-04: kecelakaan lalu lintas ${Date.now()}.`
  );

  await page.locator('#alamat').fill(
    'Jl. Keputih, Surabaya - Lokasi pengujian US-04'
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

  // Ambil Case ID terbaru dari daftar laporan.
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
      `Case ID tidak ditemukan. Href: ${href}`
    );
  }

  return Number(match[1]);
}

async function getAssignmentIdForCase(
  page: Page,
  caseId: number
): Promise<number> {
  await loginAsInstansi(page);

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

// ======================================================
// 5. RU-01: MELIHAT DAFTAR UNIT
// ======================================================

Given(
  'saya login sebagai Instansi',
  async ({ page }) => {
    await loginAsInstansi(page);
  }
);

When(
  'saya membuka halaman Kelola Unit',
  async ({ page }) => {
    await page.goto('/instansi/units');

    await expect(
      page.getByRole('heading', {
        name: 'Kelola Unit',
      })
    ).toBeVisible();
  }
);

Then(
  'saya melihat daftar unit operasional milik instansi',
  async ({ page }) => {
    await expect(
      page.getByRole('heading', {
        name: 'Daftar Unit Operasional',
      })
    ).toBeVisible();

    await expect(
      findUnitRow(page, RESOURCE_NAMES.polisi)
    ).toHaveCount(1);
  }
);

Then(
  'saya melihat ringkasan jumlah unit berdasarkan ketersediaannya',
  async ({ page }) => {
    const summaryLabels = [
      'Seluruh Unit',
      'Unit Tersedia',
      'Sedang Ditugaskan',
      'Tidak Tersedia',
    ];

    for (const label of summaryLabels) {
      const summaryLabel = page.locator('p').filter({
        hasText: new RegExp(
          `^\\s*${label}\\s*$`
        ),
      });

      await expect(summaryLabel).toBeVisible();
    }
  }
);

// ======================================================
// 6. RU-02: MENAMBAHKAN UNIT
// ======================================================

Given(
  'saya berada di halaman Kelola Unit',
  async ({ page }) => {
    await openManageUnits(page);
  }
);

When(
  'saya membuka form Tambah Unit',
  async ({ page }) => {
    await openCreateUnitModal(page);
  }
);

When(
  'saya mengisi informasi unit operasional yang baru',
  async ({ page }) => {
    const unitName = `BDD Unit ${Date.now()}`;

    await fillCreateUnitForm(page, unitName);
  }
);

When(
  'saya menyimpan unit tersebut',
  async ({ page }) => {
    await submitCreateUnitForm(page);
  }
);

Then(
  'saya melihat unit baru pada daftar unit operasional',
  async ({ page }) => {
    const unitName = getScenarioState(page).unitName;

    expect(unitName).toBeDefined();

    await expect(
      findUnitRow(page, unitName!)
    ).toHaveCount(1);
  }
);

Then(
  'saya melihat unit menggunakan lokasi pangkalan instansi',
  async ({ page }) => {
    const unitName = getScenarioState(page).unitName;

    expect(unitName).toBeDefined();

    const row = findUnitRow(page, unitName!);

    await expect(row).toContainText(
      POLICE_BASE_ADDRESS
    );
  }
);

// ======================================================
// 7. RU-03: MENGEDIT UNIT
// ======================================================

Given(
  'saya memiliki unit yang dapat diedit',
  async ({ page }) => {
    await createUnitForScenario(page);
  }
);

When(
  'saya membuka form Edit pada unit tersebut',
  async ({ page }) => {
    const unitName = getScenarioState(page).unitName;

    expect(unitName).toBeDefined();

    const row = findUnitRow(page, unitName!);

    await expect(row).toHaveCount(1);

    await row.locator('[data-edit-unit]').click();

    await expect(
      page.locator('#edit-unit-modal')
    ).toBeVisible();
  }
);

When(
  'saya memperbarui informasi unit operasional',
  async ({ page }) => {
    const state = getScenarioState(page);

    expect(state.unitName).toBeDefined();

    const updatedName =
      `${state.unitName} Diperbarui`;

    const modal = page.locator('#edit-unit-modal');

    await modal.locator('#edit-name').fill(updatedName);
    await modal.locator('#edit-type').fill('Polisi');
    await modal.locator('#edit-capacity').fill('8');

    state.updatedUnitName = updatedName;
  }
);

When(
  'saya menyimpan perubahan unit',
  async ({ page }) => {
    await page.locator('#edit-unit-modal')
      .getByRole('button', {
        name: 'Simpan Perubahan',
        exact: true,
      })
      .click();

    await expect(
      page.getByText('Unit berhasil diperbarui.', {
        exact: true,
      })
    ).toBeVisible();
  }
);

Then(
  'saya melihat informasi unit telah diperbarui',
  async ({ page }) => {
    const updatedName =
      getScenarioState(page).updatedUnitName;

    expect(updatedName).toBeDefined();

    const row = findUnitRow(page, updatedName!);

    await expect(row).toHaveCount(1);
    await expect(row).toContainText('Polisi');
    await expect(row).toContainText('8');
  }
);

// ======================================================
// 8. RU-04: MENGHAPUS UNIT
// ======================================================

Given(
  'saya memiliki unit yang dapat dihapus',
  async ({ page }) => {
    await createUnitForScenario(page);
  }
);

When(
  'saya membuka konfirmasi penghapusan unit',
  async ({ page }) => {
    const unitName = getScenarioState(page).unitName;

    expect(unitName).toBeDefined();

    const row = findUnitRow(page, unitName!);

    await expect(row).toHaveCount(1);

    await row.locator('[data-delete-unit]').click();

    await expect(
      page.locator('#delete-unit-modal')
    ).toBeVisible();
  }
);

When(
  'saya mengonfirmasi penghapusan unit',
  async ({ page }) => {
    await page.locator('#delete-unit-modal')
      .getByRole('button', {
        name: 'Hapus Unit',
        exact: true,
      })
      .click();

    await expect(
      page.getByText('Unit berhasil dihapus.', {
        exact: true,
      })
    ).toBeVisible();
  }
);

Then(
  'saya tidak lagi melihat unit tersebut pada daftar operasional',
  async ({ page }) => {
    const unitName = getScenarioState(page).unitName;

    expect(unitName).toBeDefined();

    await expect(
      findUnitRow(page, unitName!)
    ).toHaveCount(0);
  }
);

// ======================================================
// 9. RU-05: MENGUBAH KETERSEDIAAN UNIT
// ======================================================

Given(
  'saya memiliki unit yang tidak sedang ditugaskan',
  async ({ page }) => {
    await openManageUnits(page);

    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    await expect(row).toHaveCount(1);

    await expect(
      row.locator('select[name="status_ketersediaan"]')
    ).toHaveValue('tersedia');
  }
);

When(
  'saya mengubah ketersediaan unit menjadi Tidak Tersedia',
  async ({ page }) => {
    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    await row.locator(
      'select[name="status_ketersediaan"]'
    ).selectOption('tidak_tersedia');

    await expect(
      page.getByText(
        'Ketersediaan unit berhasil diperbarui.',
        { exact: true }
      )
    ).toBeVisible();
  }
);

Then(
  'saya melihat status ketersediaan unit telah berubah menjadi Tidak Tersedia',
  async ({ page }) => {
    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    await expect(row).toHaveCount(1);

    await expect(
      row.locator('select[name="status_ketersediaan"]')
    ).toHaveValue('tidak_tersedia');
  }
);

// ======================================================
// 10. RU-06: PEMBATASAN EDIT DAN HAPUS
//     SAAT UNIT SEDANG DITUGASKAN
// ======================================================

Given(
  'saya memiliki unit yang sedang ditugaskan',
  async ({ page }) => {
    // Database sudah direset melalui Background.
    // Semua Resource demo awalnya tersedia.

    const caseId =
      await createAccidentReportAndCaptureId(page);

    const assignmentId =
      await getAssignmentIdForCase(page, caseId);

    const state = getScenarioState(page);

    state.caseId = caseId;
    state.assignmentId = assignmentId;

    // Pastikan unit Polisi benar-benar sudah ditugaskan.
    await page.goto('/instansi/units');

    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    await expect(row).toHaveCount(1);

    await expect(
      row.getByText('Sedang Ditugaskan', {
        exact: true,
      })
    ).toBeVisible();
  }
);

Then(
  'saya melihat tombol Edit pada unit tersebut tidak dapat digunakan',
  async ({ page }) => {
    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    const editButton = row.getByRole('button', {
      name: /Edit/,
    });

    await expect(editButton).toBeVisible();
    await expect(editButton).toBeDisabled();
  }
);

Then(
  'saya melihat tombol Hapus pada unit tersebut tidak dapat digunakan',
  async ({ page }) => {
    const row = findUnitRow(
      page,
      RESOURCE_NAMES.polisi
    );

    const deleteButton = row.getByRole('button', {
      name: /Hapus/,
    });

    await expect(deleteButton).toBeVisible();
    await expect(deleteButton).toBeDisabled();
  }
);
