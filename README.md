# BAKAYUH — Sistem Terpadu E-Performance & E-RB

> Basis Akuntabilitas Kanwil untuk Meningkatkan Kinerja Yang Unggul dan Harmonis  
> Kantor Wilayah Kementerian Hukum Kalimantan Selatan

---

## Tentang Proyek

BAKAYUH adalah platform aplikasi web terpadu satu pintu yang dikembangkan untuk mengintegrasikan dua pilar tata kelola strategis di lingkungan Kantor Wilayah Kementerian Hukum Kalimantan Selatan beserta Satuan Kerja (Satker / UPT) binaannya:

1. **Akuntabilitas Kinerja Organisasi (E-Performance / SAKIP):** Pemantauan Indikator Kinerja Utama (IKU), pelaporan target dan realisasi Rencana Aksi Perjanjian Kinerja (Renaksi PK), penilaian berkala Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP), monitoring Capaian Perjanjian Kinerja (PK), Pohon Kinerja hierarkis, serta pemantauan Indikator Kinerja Pelaksanaan Anggaran (IKPA).
2. **Pembangunan Reformasi Birokrasi & Zona Integritas (E-RB / ZI):** Pelaksanaan Rencana Kerja Tahunan Reformasi Birokrasi (RKT RB General, Tematik, dan Meso target B03/B06/B09/B12) serta pemenuhan Lembar Kerja Evaluasi (LKE) Zona Integritas menuju Wilayah Bebas dari Korupsi (WBK) dan Wilayah Birokrasi Bersih dan Melayani (WBBM).

---

## Modul & Fitur Utama (v1.0.0)

- **Dashboard Admin Eksekutif:**
  - Ringkasan KPI dan matriks capaian kinerja organisasi secara realtime.
  - Kartu Perlu Tindakan dengan kontainer scrollable yang memuat antrean verifikasi, deteksi keterlambatan pelaporan, dan capaian indikator bulanan.
  - Kartu Progres Renaksi per Unit dengan pelacakan pemenuhan data dukung per triwulan (TW I s.d. TW IV) yang selaras secara simetris.
  - Tombol pintas navigasi cepat ke seluruh modul operasional.

- **Pilar E-Performance (Kinerja):**
  - **Sasaran & Rencana Aksi (Renaksi PK):** Penyusunan target triwulanan dan evaluasi rencana aksi per unit kerja.
  - **Monitoring Data Dukung:** Pemantauan status unggahan dan rekapitulasi data dukung renaksi.
  - **Verifikasi Data Dukung:** Antarmuka verifikasi berfilter tunggal dengan telaah berkas PDF bukti dukung, catatan klarifikasi dua arah, dan persetujuan bertingkat.
  - **Capaian PK (Perjanjian Kinerja):** Pelaporan dan pemantauan realisasi indikator kinerja bulanan per unit kerja.
  - **Modul Laporan:** Pengelolaan dan rekapitulasi pelaporan berkala unit kerja (bulanan, triwulanan, tahunan).
  - **Pohon Kinerja:** Visualisasi hierarki cascading kinerja dari Sasaran Strategis (SS), Indikator Sasaran Strategis (IKSS), Sasaran Program (SP), Indikator Sasaran Program (ISP), hingga Sasaran Kegiatan (SK) dan Indikator Kinerja Kegiatan (IKK) seluruh Ditjen/Badan dan Kanwil.
  - **IKPA (Indikator Kinerja Pelaksanaan Anggaran):** Monitoring nilai kinerja pelaksanaan anggaran dengan multi-tier satker (Kanwil, Ditjen AHU, Ditjen KI, Ditjen PP, BPHN, BSK), dialog input realisasi anggaran per triwulan, konfigurasi target, dan ekspor data ke format CSV.

- **Pilar E-RB (Reformasi Birokrasi) & SAKIP:**
  - **LKE ZI (WBK / WBBM):** Instrumen evaluasi 6 Area Perubahan Komponen Pengungkit (Pemenuhan & Reform) serta Komponen Hasil.
  - **RKT RB Periode B03, B06, B09, B12:** Pelaporan aksi RB General, Tematik, dan Meso dengan batas waktu unggah.
  - **Evaluasi SAKIP:** Rekapitulasi penilaian 4 komponen SAKIP (Perencanaan, Pengukuran, Pelaporan, Evaluasi Internal) beserta rekomendasi perbaikan.

- **Portal Publik (Akses Tanpa Login):**
  - Halaman transparansi publik yang menyajikan capaian indikator kinerja, rekapitulasi dokumen SAKIP, progres reformasi birokrasi, dan rekapitulasi renaksi secara interaktif.

- **Manajemen Akun & Master Data:**
  - Pengelolaan data pengguna berdasarkan hak akses bertingkat (Super Admin, Admin Kanwil, Operator Satker, Pimpinan/Viewer).
  - Manajemen master data Satker, Tahun Anggaran, dan IKU.

---

## Tech Stack & Arsitektur

Arsitektur aplikasi menggunakan pemisahan *Client-Server* (Monorepo):

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 13 (PHP 8.2+) | RESTful API Engine |
| **Authentication** | Laravel Sanctum | Token-based Authentication |
| **Database** | MySQL / SQLite | Relational Database |
| **Export Engines** | Barryvdh DomPDF & Maatwebsite Excel | Generator PDF & Excel |
| **API Documentation** | L5-Swagger (OpenAPI 3.0) | Swagger UI di `/api/documentation` |
| **Frontend Framework** | Vue.js 3.5 (Composition API) | SPA dengan `<script setup>` |
| **Language & Typings** | TypeScript 6 | Strict type-checking (`vue-tsc`) |
| **Build Tool** | Vite 8 | Build tool & Development server |
| **Styling** | Tailwind CSS | Utility-first responsive UI styling |
| **State Management** | Pinia 4 | Global client state |
| **Routing** | Vue Router 5 | Client-side routing & navigation guard |
| **Charts & Icons** | ApexCharts & Heroicons | Visualisasi data dan ikon antarmuka |

---

## Struktur Direktori

```text
Bakayuh/
├── bakayuh-backend/           # Aplikasi REST API Laravel 13
│   ├── app/
│   │   ├── Http/Controllers/  # Controller API (Auth, Renaksi, SAKIP, LKE, RKT, IKPA, dll.)
│   │   ├── Models/            # Eloquent Models & Relasi Database
│   │   └── Enums/             # Enums PHP (UserRole, StatusVerifikasi, dll.)
│   ├── database/
│   │   ├── migrations/        # Migrasi skema database
│   │   └── seeders/           # Master data & user seeders
│   ├── routes/
│   │   └── api.php            # Endpoint route API terproteksi Sanctum & publik
│   └── config/                # Konfigurasi aplikasi
│
├── bakayuh-frontend/          # Aplikasi Klien Vue 3 + TypeScript
│   ├── src/
│   │   ├── components/        # Komponen antarmuka (Navbar, Sidebar, Modal, dll.)
│   │   ├── composables/       # Vue composables (useAuth, useApi, usePagination)
│   │   ├── pages/             # Halaman modul (Dashboard, Kinerja, Renaksi, Publik, dll.)
│   │   ├── router/            # Definisi route & route guards
│   │   ├── stores/            # Pinia stores
│   │   └── types/             # TypeScript interface & type definitions
│   └── package.json           # Dependensi frontend
│
├── BAKAYUH-ERD.md             # Dokumentasi Entity Relationship Diagram
├── PRD-BAKAYUH.md             # Product Requirements Document
├── bakayuh.sql                # SQL dump database awal
└── README.md                  # Panduan dokumentasi proyek ini
```

---

## Panduan Instalasi & Menjalankan Aplikasi

Ikuti langkah-langkah berikut untuk menjalankan program di komputer lokal baru:

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- **PHP** >= 8.2 (disarankan PHP 8.3) dengan ekstensi aktif: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo`.
- **Composer** >= 2.x
- **Node.js** >= 20.x atau 22.x
- **NPM** >= 10.x
- **MySQL / MariaDB** (atau SQLite jika tidak menggunakan MySQL)
- **Git**

---

### 2. Langkah Menjalankan Backend (`bakayuh-backend`)

1. Buka terminal dan arahkan ke direktori backend:
   ```bash
   cd bakayuh-backend
   ```

2. Pasang seluruh dependensi PHP melalui Composer:
   ```bash
   composer install
   ```

3. Salin berkas konfigurasi lingkungan:
   - Di Windows (PowerShell / CMD):
     ```bash
     copy .env.example .env
     ```
   - Di Linux / macOS:
     ```bash
     cp .env.example .env
     ```

4. Buat kunci aplikasi (Application Key):
   ```bash
   php artisan key:generate
   ```

5. Konfigurasi Database pada file `.env`:
   Buka file `.env` dan sesuaikan pengaturan database dengan sistem lokal Anda, contoh:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bakayuh
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Pastikan database dengan nama tersebut telah dibuat di MySQL lokal Anda).*

6. Jalankan Migrasi dan Pengisian Data Awal (Seeder):
   ```bash
   php artisan migrate --seed
   ```

   > **Opsi Alternatif Import Database:**  
   > Anda juga dapat mengimpor file dump `bakayuh.sql` yang berada di root direktori proyek langsung ke database MySQL Anda:
   > ```bash
   > mysql -u <username> -p bakayuh < ../bakayuh.sql
   > ```

7. Buat symlink storage untuk berkas data dukung:
   ```bash
   php artisan storage:link
   ```

8. Jalankan server backend:
   ```bash
   php artisan serve --port=8000
   ```
   Backend API akan berjalan di: `http://127.0.0.1:8000`  
   Dokumentasi Swagger API dapat diakses di: `http://127.0.0.1:8000/api/documentation`

---

### 3. Langkah Menjalankan Frontend (`bakayuh-frontend`)

1. Buka jendela terminal baru dan masuk ke folder frontend:
   ```bash
   cd bakayuh-frontend
   ```

2. Pasang paket dependensi Node.js:
   ```bash
   npm install
   ```

3. Jalankan server development:
   ```bash
   npm run dev
   ```
   Aplikasi antarmuka akan berjalan dan dapat dibuka melalui peramban web di: `http://localhost:5173` (atau port yang tertera pada terminal).

4. Untuk melakukan kompilasi build produksi:
   ```bash
   npm run build
   ```

---

### 4. Manajemen Akun Pengguna

- Akun pengguna dan hak akses diatur melalui seeder database pada direktori `database/seeders/UserSeeder.php` atau didaftarkan langsung oleh administrator melalui menu **Kelola Akun** di dalam aplikasi.
- Untuk alasan keamanan dan kepatuhan privasi, detail kredensial akun tidak dipublikasikan pada repositori ini.
- Pengguna umum dapat langsung mengakses **Portal Publik** tanpa memerlukan proses login melalui tombol *Portal Publik* pada halaman utama atau navigasi publik.

---

## Rujukan Dokumentasi Tambahan

- [PRD-BAKAYUH.md](PRD-BAKAYUH.md) — Product Requirements Document (Spesifikasi Fungsional Lengkap)
- [BAKAYUH-ERD.md](BAKAYUH-ERD.md) — Entity Relationship Diagram & Rincian Skema Database

---

## Lisensi & Hak Cipta

Dikembangkan untuk kebutuhan operasional **Kantor Wilayah Kementerian Hukum Kalimantan Selatan**.  
Hak Cipta © 2026 Tim Pengembang BAKAYUH.
