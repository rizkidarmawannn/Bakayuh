# BAKAYUH — Sistem Terpadu E-Performance & E-RB

> **Basis Akuntabilitas Kanwil untuk Meningkatkan Kinerja Yang Unggul dan Harmonis**  
> Kantor Wilayah Kementerian Hukum Kalimantan Selatan

---

## 📌 Tentang Proyek

**BAKAYUH** adalah platform aplikasi web terpadu satu pintu yang dikembangkan untuk mengintegrasikan dua pilar tata kelola strategis di lingkungan Kantor Wilayah Kementerian Hukum Kalimantan Selatan beserta Satuan Kerja (Satker / UPT) binaannya:

1. **Akuntabilitas Kinerja Organisasi (E-Performance / SAKIP):** Pemantauan Indikator Kinerja Utama (IKU), pelaporan target dan realisasi Rencana Aksi Perjanjian Kinerja (Renaksi PK), serta penilaian berkala Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP).
2. **Pembangunan Reformasi Birokrasi & Zona Integritas (E-RB / ZI):** Pelaksanaan Rencana Kerja Tahunan Reformasi Birokrasi (RKT RB General, Tematik, dan Meso target B03/B06/B09/B12) serta pemenuhan Lembar Kerja Evaluasi (LKE) Zona Integritas menuju Wilayah Bebas dari Korupsi (WBK) dan Wilayah Birokrasi Bersih dan Melayani (WBBM).

Aplikasi ini mengatasi fragmentasi pelaporan dan menyediakan alur verifikasi bertingkat, asistensi, serta transparansi capaian melalui portal publik.

---

## ✨ Fitur Utama

- **Pilar E-Performance (Kinerja):**
  - **Master & Realisasi IKU:** Input target & realisasi indikator kinerja dengan kalkulasi capaian otomatis (polaritas positif/negatif) dan visualisasi status (Hijau/Kuning/Merah).
  - **Renaksi Perjanjian Kinerja:** Pelaporan rencana aksi per triwulan (TW I - TW IV), unggah berkas bukti dukung, dan telaah verifikasi Kanwil.
  - **Evaluasi SAKIP:** Input dan rekapitulasi penilaian 4 komponen SAKIP (Perencanaan, Pengukuran, Pelaporan, Evaluasi Internal) beserta rekomendasi perbaikan.

- **Pilar E-RB (Reformasi Birokrasi):**
  - **LKE ZI (WBK / WBBM):** Instrumen evaluasi 6 Area Perubahan Komponen Pengungkit (Pemenuhan & Reform) serta Komponen Hasil.
  - **RKT RB Periode B03, B06, B09, B12:** Pelaporan aksi RB General, RB Tematik, dan RB Meso dengan kontrol countdown batas waktu unggah.
  - **Workspace Bukti Dukung (Daduk):** Manajemen berkas PDF bukti dukung (hingga 50MB), validasi kelayakan berkas, dan auto-lock setelah disetujui.
  - **Thread Catatan & Klarifikasi Verifikator:** Ruang dialog dua arah per indikator antara tim verifikator Kanwil dan operator satker untuk perbaikan berkas sebelum batas waktu berakhir.

- **Modul Terintegrasi & Publik:**
  - **Executive Dashboard:** Statistik ringkasan, grafik capaian, dan progress pemenuhan dokumen secara realtime.
  - **Portal Publik Transparansi:** Akses publik tanpa login untuk memantau capaian kinerja dan progres reformasi birokrasi Kanwil Kalsel.
  - **Ekspor Dokumen:** Cetak rekapitulasi kinerja dan dokumen evaluasi ke format PDF (DomPDF) dan Microsoft Excel.
  - **Dokumentasi API Terintegrasi:** OpenAPI / Swagger UI interaktif di endpoint `/api/documentation`.

---

## 🛠️ Tech Stack & Arsitektur

Proyek ini menggunakan arsitektur pemisahan *Client-Server* (Monorepo):

| Komponen | Teknologi | Keterangan |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 13 (PHP 8.3+) | RESTful API Engine |
| **Authentication** | Laravel Sanctum | Token-based Authentication |
| **Database** | SQLite (Default Dev) / MySQL | Relasional Database |
| **Export Engines** | Barryvdh DomPDF & Maatwebsite Excel | Generator PDF & Excel |
| **API Documentation**| L5-Swagger (OpenAPI 3.0) | Swagger UI |
| **Frontend Framework**| Vue.js 3.5 (Composition API) | SPA dengan `<script setup>` |
| **Language & Typings**| TypeScript 6 | Strict type-checking (`vue-tsc`) |
| **Build Tool** | Vite 8 | Fast HMR & Bundler |
| **Styling** | Tailwind CSS 3 | Utility-first styling & UI responsif |
| **State Management** | Pinia 4 | Global client state |
| **Routing** | Vue Router 5 | Client-side routing & auth guard |
| **Charts & Icons** | ApexCharts & Heroicons | Visualisasi data dan ikon antarmuka |

---

## 📁 Struktur Direktori

```text
Bakayuh/
├── bakayuh-backend/           # Laravel 13 REST API Application
│   ├── app/
│   │   ├── Http/Controllers/  # Controller API (Auth, IKU, Renaksi, LKE, RKT, SAKIP, dll.)
│   │   ├── Models/            # Eloquent Models & Relationships
│   │   └── Enums/             # Enums PHP (UserRole, StatusVerifikasi, dll.)
│   ├── database/
│   │   ├── migrations/        # Migrasi skema database
│   │   └── seeders/           # Master data & dummy user seeders
│   ├── routes/
│   │   └── api.php            # Endpoint route API terproteksi Sanctum & publik
│   └── config/                # Konfigurasi sistem (L5-Swagger, Sanctum, CORS)
│
├── bakayuh-frontend/          # Vue 3 + TypeScript Client Application
│   ├── src/
│   │   ├── components/        # Komponen UI modular (Navbar, Sidebar, Modal, dll.)
│   │   ├── composables/       # Vue composables (auth, alert, fetcher)
│   │   ├── pages/             # Halaman antarmuka (Dashboard, IKU, Renaksi, LKE, RKT, dll.)
│   │   ├── router/            # Route definition & navigation guards
│   │   ├── stores/            # Pinia stores (auth store, app store)
│   │   └── types/             # TypeScript interface & type definitions
│   └── package.json           # Dependensi frontend
│
├── BAKAYUH-ERD.md             # Dokumentasi Entity Relationship Diagram & Skema DB
├── PRD-BAKAYUH.md             # Dokumen Spesifikasi Produk Lengkap (PRD v0.3)
└── README.md                  # Panduan dokumentasi proyek (file ini)
```

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
Pastikan telah menginstal:
- **PHP** >= 8.3 dengan ekstensi `pdo`, `mbstring`, `openssl`, `gd`, `zip`
- **Composer** >= 2.x
- **Node.js** >= 22.x atau 24.x & **npm** >= 10.x

---

### 2. Konfigurasi & Menjalankan Backend (`bakayuh-backend`)

1. Buka terminal dan masuk ke folder `bakayuh-backend`:
   ```bash
   cd bakayuh-backend
   ```

2. Pasang dependensi PHP:
   ```bash
   composer install
   ```

3. Buat file konfigurasi `.env`:
   ```bash
   cp .env.example .env
   ```

4. Generate Application Key:
   ```bash
   php artisan key:generate
   ```

5. Jalankan migrasi database beserta data awal (seeders):
   ```bash
   php artisan migrate --seed
   ```
   *(Secara bawaan menggunakan database SQLite lokal. Jika menggunakan MySQL, sesuaikan nilai `DB_*` di dalam file `.env` terlebih dahulu).*

6. Generate dokumentasi Swagger (opsional jika ada pembaruan route API):
   ```bash
   php artisan l5-swagger:generate
   ```

7. Jalankan server backend:
   ```bash
   php artisan serve
   ```
   Backend API akan berjalan di: **`http://127.0.0.1:8000`**  
   Dokumentasi Swagger API dapat diakses di: **`http://127.0.0.1:8000/api/documentation`**

---

### 3. Konfigurasi & Menjalankan Frontend (`bakayuh-frontend`)

1. Buka terminal baru dan masuk ke folder `bakayuh-frontend`:
   ```bash
   cd bakayuh-frontend
   ```

2. Pasang dependensi Node.js:
   ```bash
   npm install
   ```

3. Jalankan server development:
   ```bash
   npm run dev
   ```
   Frontend akan berjalan di: **`http://localhost:5173`**

4. Untuk membangun aset produksi (*production build*):
   ```bash
   npm run build
   ```

---

## 👤 Akun Bawaan (Default Credentials)

Setelah menjalankan `php artisan migrate --seed`, akun uji coba berikut siap digunakan:

| Peran (Role) | Email | Password | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@kemenkum.go.id` | `password123` | Akses penuh manajemen user, satker, dan konfigurasi sistem. |
| **Admin Kanwil** | `verifikator@kemenkum.go.id` | `password123` | Verifikasi daduk RB, verifikasi renaksi, evaluasi SAKIP, dan ekspor data. |
| **Operator Satker** | `operator.lpbjm@kemenkum.go.id` | `password123` | Input realisasi IKU satker, unggah daduk LKE/RKT, respons revisi. *(Lapas Banjarmasin)* |
| **Viewer / Pimpinan** | `pimpinan@kemenkum.go.id` | `password123` | Dashboard eksekutif monitoring kinerja (*read-only*). |
| **Publik** | *(Tanpa Login)* | *(Tanpa Password)* | Dapat langsung mengakses menu Portal Publik di antarmuka. |

---

## 📚 Dokumen Spesifikasi & Rujukan

Untuk mempelajari arsitektur modul dan relasi database secara mendalam, silakan baca dokumentasi pendukung berikut:
- 📖 [PRD-BAKAYUH.md](PRD-BAKAYUH.md) — *Product Requirements Document v0.3 (Spesifikasi Bisnis & Fungsional Lengkap)*
- 🗄️ [BAKAYUH-ERD.md](BAKAYUH-ERD.md) — *Entity Relationship Diagram & Detail Struktur Tabel Database*

---

## 📄 Lisensi

Dikembangkan untuk kebutuhan internal **Kantor Wilayah Kementerian Hukum Kalimantan Selatan**.  
Hak Cipta © 2026 Tim Pengembang BAKAYUH.
