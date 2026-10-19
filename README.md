# 🚨 EmergencyChain

### Integrated Digital System for Emergency Service Coordination

**EmergencyChain** merupakan sistem berbasis web yang dirancang untuk mengintegrasikan proses pelaporan kejadian darurat dan koordinasi penanganan antarinstansi. Sistem menghubungkan masyarakat, Petugas EmergencyChain, Instansi Penanganan, dan Rumah Sakit dalam satu platform terpusat.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwindcss&logoColor=white)
![Status](https://img.shields.io/badge/Status-In_Development-orange?style=flat-square)

---

## 📖 Tentang Proyek

EmergencyChain dikembangkan untuk mendukung koordinasi penanganan kejadian darurat melalui proses digital yang terintegrasi.

Pelapor dapat mengirimkan informasi kejadian beserta lokasi dan kebutuhan penanganan. Sistem dirancang untuk melakukan pencocokan sumber daya (*Resource Matching*), membuat penugasan (*Assignment*), serta melakukan pencocokan rumah sakit (*Hospital Matching*) secara otomatis sesuai kondisi kejadian.

Petugas EmergencyChain bertugas memantau perkembangan kasus dan memperbaiki informasi kejadian apabila diperlukan. Instansi Penanganan dan Rumah Sakit dapat menerima penugasan atau rujukan serta memperbarui status penanganan melalui sistem.

> **Catatan:** EmergencyChain merupakan prototipe akademik untuk mata kuliah Pembangunan Perangkat Lunak. Sistem belum terhubung dengan layanan darurat resmi dan tidak ditujukan untuk digunakan pada situasi darurat nyata.

## ✨ Fitur Utama

| Fitur | Deskripsi | Target Sprint |
|---|---|---|
| Authentication & Authorization | Login, registrasi Pelapor, logout, dan hak akses berdasarkan role | Sprint 1 |
| Emergency Case Reporting | Pelaporan kejadian dengan lokasi GPS atau input manual | Sprint 1 |
| Emergency Case Management | Pemantauan dan koreksi informasi kasus oleh Petugas EC | Sprint 1 |
| Resource Matching | Pencocokan otomatis kebutuhan penanganan dengan resource yang tersedia | Sprint 2 |
| Assignment Management | Penugasan otomatis serta konfirmasi penerimaan atau penolakan oleh instansi | Sprint 2 |
| Emergency Status Tracking | Pemantauan perkembangan status penanganan | Sprint 2 |
| Hospital Matching | Pencocokan rumah sakit berdasarkan kebutuhan, lokasi, fasilitas, dan kapasitas | Sprint 2 |
| Victim Reception | Pengelolaan penerimaan korban dan status penanganan rumah sakit | Sprint 3 |
| Real-time Updates | Sinkronisasi informasi dashboard melalui polling berkala | Sprint 3 |
| Dashboard & History | Ringkasan operasional dan riwayat penanganan | Sprint 3 |

## 👥 User Roles

EmergencyChain memiliki empat kategori pengguna:

**1. Pelapor**

Masyarakat yang melakukan registrasi, membuat laporan Emergency Case, dan memantau perkembangan penanganan laporan miliknya.

**2. Petugas EmergencyChain**

Petugas yang memantau informasi dan perkembangan Emergency Case, serta melakukan koreksi data apabila diperlukan. Proses matching dan assignment dijalankan secara otomatis oleh sistem.

**3. Instansi Penanganan**

Instansi terkait, seperti ambulans, kepolisian, pemadam kebakaran, dan BPBD, yang menerima penugasan serta memperbarui status penanganan kejadian.

**4. Rumah Sakit**

Pihak rumah sakit yang menerima permintaan rujukan, mengonfirmasi penerimaan atau penolakan, dan memperbarui perkembangan penanganan korban.

## 🛠️ Technology Stack

| Category | Technology |
|---|---|
| Backend Framework | Laravel 13 |
| Programming Language | PHP 8.5 |
| Database | MySQL 8.4 LTS |
| Object-Relational Mapping | Laravel Eloquent ORM |
| Frontend | Laravel Blade, HTML, JavaScript |
| Styling | Tailwind CSS |
| Asset Bundler | Vite |
| Maps | Leaflet.js & OpenStreetMap |
| Geolocation | Browser Geolocation API |
| Distance Calculation | Haversine Formula |
| Real-time Updates | Periodic Polling |
| Unit & Integration Testing | PHPUnit |
| BDD & E2E Testing | Playwright + playwright-bdd |
| Code Formatting | Laravel Pint |
| Continuous Integration | GitHub Actions |
| Version Control | Git & GitHub |

## 🏗️ Project Architecture

EmergencyChain menggunakan pendekatan **Modular Monolith** dengan pemisahan tanggung jawab berdasarkan konsep *Boundary–Control–Entity (BCE)*.

- **Boundary:** antarmuka, Blade templates, dan penanganan interaksi HTTP.
- **Control:** controller, service, dan proses bisnis sistem.
- **Entity:** Eloquent Models dan relasi database.

Struktur proyek dirancang agar fitur dapat dikembangkan secara bertahap, tetap mudah diuji, dan dapat dipelihara oleh seluruh anggota tim.

### Directory Structure

```text
EmergencyChain/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Pelapor/
│   │   │   ├── PetugasEC/
│   │   │   ├── Instansi/
│   │   │   └── RumahSakit/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   │   ├── EmergencyCase/
│   │   ├── Matching/
│   │   ├── Assignment/
│   │   └── Hospital/
│   ├── Jobs/
│   ├── Events/
│   └── Policies/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── e2e/
│       ├── features/
│       ├── steps/
│       └── fixtures/
├── docs/
│   ├── requirements/
│   ├── diagrams/
│   ├── decisions/
│   └── testing/
├── .github/
│   └── workflows/
└── README.md
```

## 🚀 Getting Started

Bagian berikut digunakan untuk menyiapkan lingkungan pengembangan lokal.

### Prerequisites

Pastikan perangkat telah memiliki:

- PHP 8.3 atau lebih baru yang kompatibel dengan Laravel 13
- Composer
- Node.js dan npm
- MySQL Server
- Git

### Clone Repository

```bash
git clone https://github.com/AgilePemdigi-2026/EmergencyChain.git
cd EmergencyChain
```

### Install Dependencies

```bash
composer install
npm install
```

### Environment Configuration

Salin konfigurasi environment:

```bash
cp .env.example .env
```

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### Database Configuration

Konfigurasikan koneksi database pada file `.env` menggunakan kredensial MySQL lokal.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emergency_chain
DB_USERNAME=root
DB_PASSWORD=
```

Nilai tersebut hanyalah contoh konfigurasi lokal. Database harus dibuat terlebih dahulu dan kredensial perlu disesuaikan dengan perangkat masing-masing.

Setelah skema database proyek tersedia, jalankan:

```bash
php artisan migrate
```

Untuk menambahkan data awal setelah seeder siap:

```bash
php artisan db:seed
```

### Run Application

Jalankan Laravel:

```bash
php artisan serve
```

Di terminal lain, jalankan Vite:

```bash
npm run dev
```

Aplikasi biasanya dapat diakses melalui:

```text
http://127.0.0.1:8000
```

> **Development Note:** Konfigurasi MySQL, migration EmergencyChain, seeder, dan testing sedang disiapkan selama tahap Pre-Sprint. Instruksi terkait dapat digunakan sepenuhnya setelah konfigurasi tersebut selesai.

## 🧪 Software Testing

Pengujian dirancang berdasarkan user story, acceptance criteria, dan kebutuhan fungsional dalam Software Requirements Specification (SRS).

### Testing Strategy

| Testing | Tools | Purpose |
|---|---|---|
| Unit Testing | PHPUnit | Menguji logika individual, perhitungan jarak, dan matching |
| Integration Testing | PHPUnit | Menguji interaksi antar komponen backend dan database |
| BDD Testing | playwright-bdd | Menguji skenario Given, When, Then |
| End-to-End Testing | Playwright | Menguji alur pengguna melalui browser |
| Code Formatting | Laravel Pint | Menjaga konsistensi gaya kode |
| CI | GitHub Actions | Otomatisasi pemeriksaan dan pengujian kode |

Contoh skenario BDD:

```gherkin
Feature: Login EmergencyChain

  Scenario: Pelapor berhasil login
    Given Pelapor memiliki akun terdaftar
    And Pelapor berada pada halaman login
    When Pelapor memasukkan kredensial yang benar
    And Pelapor menekan tombol Login
    Then Pelapor diarahkan ke dashboard Pelapor
```

### Testing Commands

Pengujian dasar Laravel:

```bash
php artisan test
```

Pemeriksaan format kode:

```bash
./vendor/bin/pint --test
```

Pengujian BDD dan Playwright akan dilengkapi setelah konfigurasi `playwright-bdd`, step definitions, dan test environment selesai.

## 📅 Development Roadmap

EmergencyChain dikembangkan menggunakan pendekatan Agile melalui tiga sprint.

| Phase | Scope | Status |
|---|---|---|
| Pre-Sprint | Environment, repository, project structure, database, dan testing setup | In Progress |
| Sprint 1 | Authentication, registration, master data, emergency reporting, case management | Planned |
| Sprint 2 | Resource Matching, Assignment, status tracking, Hospital Matching | Planned |
| Sprint 3 | Victim Reception, real-time updates, dashboard, handling history | Planned |

## 🌿 Git Workflow

Setiap pengembangan dilakukan melalui branch terpisah dengan format:

```text
main
feature/nama-fitur
fix/nama-bug
chore/nama-pekerjaan
```

Alur kolaborasi:

1. Buat branch berdasarkan tugas atau fitur.
2. Kembangkan fitur sesuai sprint backlog.
3. Jalankan pengujian sebelum melakukan commit.
4. Push branch ke GitHub.
5. Buat Pull Request menuju `main`.
6. Lakukan code review dan pemeriksaan otomatis.
7. Merge setelah perubahan dinyatakan siap.

## 📐 Software Quality

Pengembangan EmergencyChain mengacu pada sembilan karakteristik kualitas dalam ISO/IEC 25010:

- Functional Suitability
- Performance Efficiency
- Compatibility
- Interaction Capability
- Reliability
- Security
- Maintainability
- Flexibility
- Safety

Pemenuhan karakteristik tersebut akan dievaluasi melalui pengujian fungsional, pengujian nonfungsional, code review, dan dokumentasi hasil pengembangan.

## 📚 Documentation

Dokumentasi proyek disimpan pada folder `docs/`:

| Directory | Contents |
|---|---|
| `docs/requirements/` | SRS, perubahan requirement, dan acceptance criteria |
| `docs/diagrams/` | Diagram perancangan sistem |
| `docs/decisions/` | Keputusan arsitektur dan teknologi |
| `docs/testing/` | Rencana pengujian dan bukti hasil testing |

SRS versi awal menjadi baseline. Penyesuaian requirement akan dicatat dan didokumentasikan selama pelaksanaan sprint.

## 👨‍💻 Development Team

**Kelompok 5 — Pembangunan Perangkat Lunak**

Program Studi S1 Sistem Informasi  
Fakultas Sains dan Teknologi  
Universitas Airlangga

| NIM | Anggota |
|---|---|
| 187241009 | Veriska Zidane Haqiqi |
| 187241016 | Lintang Hassya Arundaya W. |
| 187241068 | Isyana Azhara Fajrizqi |

## 📄 Project Notice

EmergencyChain dikembangkan sebagai proyek pembelajaran akademik. Seluruh data kejadian, resource, instansi, dan rumah sakit yang digunakan selama pengembangan merupakan data simulasi atau data pengujian yang sesuai dengan kebutuhan proyek.

Sistem tidak boleh digunakan sebagai pengganti layanan darurat resmi.

---

**EmergencyChain — Connecting Emergency Reports with Coordinated Response.**