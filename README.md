# BAKAYUH — Sistem Terpadu E-Performance & E-RB

> Basis Akuntabilitas Kanwil untuk Meningkatkan Kinerja Yang Unggul dan Harmonis  
> Kantor Wilayah Kementerian Hukum Kalimantan Selatan

---

## Tentang Proyek

**BAKAYUH** adalah platform aplikasi web terpadu satu pintu yang dikembangkan untuk mengintegrasikan dua pilar tata kelola strategis di lingkungan Kantor Wilayah Kementerian Hukum Kalimantan Selatan beserta Satuan Kerja (Satker / UPT) binaannya:

1. **Akuntabilitas Kinerja Organisasi (E-Performance / SAKIP):** Pemantauan Indikator Kinerja Utama (IKU), pelaporan target dan realisasi Rencana Aksi Perjanjian Kinerja (Renaksi PK), monitoring bukti dukung (Monitoring Daduk), penilaian berkala Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP), pelaporan matriks Capaian Perjanjian Kinerja (PK), visualisasi hierarki Pohon Kinerja cascading, serta pemantauan Indikator Kinerja Pelaksanaan Anggaran (IKPA).
2. **Pembangunan Reformasi Birokrasi & Zona Integritas (E-RB / ZI):** Pelaksanaan Rencana Kerja Tahunan Reformasi Birokrasi (RKT RB General, Tematik, dan Meso periode B03/B06/B09/B12) serta pemenuhan Lembar Kerja Evaluasi (LKE) Zona Integritas menuju Wilayah Bebas dari Korupsi (WBK) dan Wilayah Birokrasi Bersih dan Melayani (WBBM).

---

## Modul & Fitur Utama

### 1. Dashboard Eksekutif
- **Metrik Kinerja Real-time:** Menampilkan ringkasan KPI, total dokumen, status verifikasi, dan progres capaian tahun berjalan secara langsung.
- **Kartu Perlu Tindakan:** Antarmuka dengan kontainer scrollable yang mengelompokkan antrean verifikasi bukti dukung, deteksi keterlambatan pelaporan, dan evaluasi capaian indikator bulanan.
- **Progres Renaksi per Unit:** Pemantauan kelengkapan pemenuhan data dukung per triwulan (TW I s.d. TW IV) secara simetris untuk setiap unit kerja eselon.
- **Kelengkapan Capaian PK:** Matriks visual status pengisian capaian 12 bulan (Januari s.d. Desember) untuk seluruh unit eselon (Setjen, AHU, KI, PP, BPHN, BSK) dilengkapi persentase kelengkapan pelaporan.
- **IKPA Terkini (Speedometer & Target):** Gauge speedometer interaktif nilai IKPA dengan kategori predikat kinerja anggaran, progress bar per unit satker, dan grafik sebaran nilai terhadap batas target nasional (95,00).

### 2. Pilar E-Performance (Kinerja)
- **Sasaran & Rencana Aksi (Renaksi PK):** Penyusunan target triwulanan dan evaluasi rencana aksi per unit kerja, dilengkapi fitur ekspor rekapitulasi data renaksi ke berkas CSV (UTF-8 BOM).
- **Monitoring Data Dukung (Daduk):** Pemantauan status unggahan berkas bukti dukung renaksi dengan filter kartu metrik interaktif (*Semua*, *Terverifikasi*, *Menunggu Verifikasi*, *Ditolak*), pop-up modal *Atur Tenggat Pengumpulan*, dan fitur ekspor rekap data dukung CSV.
- **Verifikasi Data Dukung:** Antarmuka verifikasi terpadu dengan penelaahan berkas PDF bukti dukung, riwayat klarifikasi dua arah, dan persetujuan bertingkat.
- **Capaian PK (Perjanjian Kinerja):** Matriks multi-kolom terang berstandar Kemenkumham yang memuat Sasaran Kinerja, Indikator Kinerja Kegiatan, Target, capaian bulanan (Jan–Des), Rekap Triwulan (TW I–TW IV otomatis dengan opsi timpa manual), Capaian Kumulatif (*s.d. Semester I*, *s.d. TW III*, *s.d. TW IV*), kolom Keterangan triwulanan, modal input/edit nilai sel, modal konfigurasi indikator, dan ekspor CSV.
- **Modul Laporan (3 Sub-Item):**
  - *Matriks Laporan:* Matriks pemantauan status dan kepatuhan penyampaian laporan berkala unit kerja per triwulan.
  - *Verifikasi Laporan:* Antarmuka penelaahan dokumen laporan berkala, telaah berkas, dan histori catatan perbaikan.
  - *Jenis Laporan:* Pengelolaan master katalog jenis dokumen pelaporan (LHKPN, SPIP, SAKIP, Renaksi, dll.) beserta konfigurasi periode dan frekuensi penyampaian.
- **Pohon Kinerja Hierarkis:** Visualisasi cascading hierarki kinerja yang responsif (bebas horizontal overflow / tata letak fleksibel) memetakan alur Sasaran Strategis (SS), Sasaran Program (SP), hingga Sasaran Kegiatan (SK) dan IKK seluruh unit/badan binaan.
- **IKPA (Indikator Kinerja Pelaksanaan Anggaran):** Pemantauan nilai kinerja pelaksanaan anggaran dengan pembagian multi-satker (Kanwil, Ditjen AHU, Ditjen KI, Ditjen PP, BPHN, BSK), dialog input realisasi anggaran per triwulan, konfigurasi target anggaran, dan ekspor data ke format CSV.
- **Evaluasi SAKIP:** Rekapitulasi penilaian 4 komponen SAKIP (Perencanaan, Pengukuran, Pelaporan, Evaluasi Internal) dengan tabel nilai Mandiri, Pembina, Itjen, dan Panel; ringkasan kriteria bobot nilai; serta indikator visual ringkas *Nilai Akhir SAKIP* horizontal.

### 3. Pilar E-RB (Reformasi Birokrasi) & ZI
- **LKE ZI (WBK / WBBM):** Instrumen penilaian mandiri 6 Area Perubahan Komponen Pengungkit (Manajemen Perubahan, Penataan Tatalaksana, Penataan Sistem Manajemen SDM, Penguatan Akuntabilitas, Penguatan Pengawasan, Peningkatan Kualitas Pelayanan Publik) serta Komponen Hasil.
- **RKT RB (B03, B06, B09, B12):** Pelaporan rencana aksi reformasi birokrasi kategori General, Tematik, dan Meso sesuai timeline periode pelaporan Kementerian.

### 4. Portal Publik (Akses Tanpa Login)
- Halaman transparansi publik yang menyajikan capaian indikator kinerja, rekapitulasi dokumen SAKIP, progres reformasi birokrasi, dan rekapitulasi rencana aksi secara interaktif tanpa memerlukan autentikasi.

### 5. Manajemen Akun & Hak Akses
- Pengelolaan pengguna berbasis hak akses bertingkat (Super Admin, Admin Kanwil, Operator Satker, Pimpinan/Viewer).
- Pengelolaan master data Satker, Tahun Anggaran, dan Indikator Kinerja.

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
│   │   ├── components/        # Komponen antarmuka (Navbar, AppSidebar, Modal, dll.)
│   │   ├── composables/       # Vue composables (useAuth, useApi, usePagination, useToast)
│   │   ├── pages/             # Halaman modul (Dashboard, Kinerja, Renaksi, Laporan, Publik, dll.)
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

- Akun pengguna dan hak akses diatur melalui seeder database pada direktori `database/seeders/UserSeeder.php` atau didaftarkan langsung oleh administrator berwenang melalui menu **Kelola Akun** di dalam sistem.
- Untuk menjaga keamanan dan kepatuhan privasi instansi, detail kredensial akun pengguna tidak dicantumkan di dalam dokumentasi repositori publik ini.
- Pengguna umum dapat langsung mengakses **Portal Publik** tanpa memerlukan proses login melalui tombol *Portal Publik* pada halaman utama atau navigasi publik.

---

## Rujukan Dokumentasi Tambahan

- [PRD-BAKAYUH.md](PRD-BAKAYUH.md) — Product Requirements Document (Spesifikasi Fungsional Lengkap)
- [BAKAYUH-ERD.md](BAKAYUH-ERD.md) — Entity Relationship Diagram & Rincian Skema Database

---

## Lisensi & Hak Cipta

Dikembangkan untuk kebutuhan operasional **Kantor Wilayah Kementerian Hukum Kalimantan Selatan**.  
Hak Cipta © 2026 Tim Pengembang BAKAYUH.
