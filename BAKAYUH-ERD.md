# Entity Relationship Diagram (ERD) & Skema Data BAKAYUH
## Sistem Terpadu Akuntabilitas Kinerja (E-Performance) & Reformasi Birokrasi (E-RB)
### Kantor Wilayah Kementerian Hukum Kalimantan Selatan

**Versi:** v0.3 — Integrasi Penuh E-Performance & E-RB | **Tanggal:** 5 Oktober 2026

---

## Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    %% ==========================================
    %% 1. SHARED & USER MANAGEMENT ENTITIES
    %% ==========================================
    users {
        bigint id PK
        string name
        string email
        string password
        enum role "super_admin|admin_kanwil|operator_satker|viewer"
        bigint satker_id FK
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    satuan_kerja {
        bigint id PK
        string kode
        string nama
        enum tipe "kanwil|upt|satker"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    tahun_anggaran {
        bigint id PK
        year tahun
        boolean is_aktif
        timestamp created_at
        timestamp updated_at
    }

    %% ==========================================
    %% 2. PILAR 1: E-PERFORMANCE (IKU, RENAKSI, SAKIP)
    %% ==========================================
    indikator_kinerja {
        bigint id PK
        string kode
        string nama
        string satuan
        enum polaritas "positif|negatif"
        enum level "strategis|program|kegiatan"
        timestamp created_at
        timestamp updated_at
    }

    target_iku {
        bigint id PK
        bigint tahun_anggaran_id FK
        bigint satker_id FK
        bigint indikator_id FK
        decimal nilai_target
        timestamp created_at
        timestamp updated_at
    }

    realisasi_iku {
        bigint id PK
        bigint target_iku_id FK
        decimal nilai_realisasi
        decimal persentase_capaian
        text keterangan
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    rencana_aksi {
        bigint id PK
        bigint tahun_anggaran_id FK
        bigint satker_id FK
        bigint indikator_id FK
        string nama_aksi
        enum triwulan "TW1|TW2|TW3|TW4"
        text target_output
        timestamp created_at
        timestamp updated_at
    }

    realisasi_renaksi {
        bigint id PK
        bigint rencana_aksi_id FK
        text deskripsi_realisasi
        int persentase_selesai
        enum status "belum_lapor|menunggu_verifikasi|terverifikasi|perlu_perbaikan"
        text catatan_verifikasi
        bigint verified_by FK
        timestamp verified_at
        timestamp submitted_at
        bigint created_by FK
        timestamp updated_at
    }

    bukti_dukung_renaksi {
        bigint id PK
        bigint realisasi_renaksi_id FK
        string nama_file
        string path_file
        int ukuran_file
        string mime_type
        bigint uploaded_by FK
        timestamp created_at
    }

    evaluasi_sakip {
        bigint id PK
        bigint tahun_anggaran_id FK
        bigint satker_id FK
        decimal nilai_perencanaan
        decimal nilai_pengukuran
        decimal nilai_pelaporan
        decimal nilai_evaluasi
        decimal nilai_total
        enum predikat "AA|A|BB|B|CC|C|D"
        text catatan
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    %% ==========================================
    %% 3. PILAR 2: E-RB & LKE ZI (WBK/WBBM)
    %% ==========================================
    rb_area {
        bigint id PK
        enum kategori "lke_wbk_wbbm|rkt_general|rkt_tematik|rkt_meso"
        enum komponen "pengungkit|hasil|none"
        string kode
        string nama_area
        int urutan
        timestamp created_at
        timestamp updated_at
    }

    rb_indikator {
        bigint id PK
        bigint rb_area_id FK
        enum aspek "reform|pemenuhan|hasil|none"
        string kode
        string nama_indikator
        text keterangan_juknis
        int urutan
        timestamp created_at
        timestamp updated_at
    }

    rb_sub_indikator {
        bigint id PK
        bigint rb_indikator_id FK
        int nomor_poin
        string judul_poin
        text checklist_daduk
        text catatan_tpi
        timestamp created_at
        timestamp updated_at
    }

    rb_target_periode {
        bigint id PK
        bigint rb_sub_indikator_id FK
        bigint tahun_anggaran_id FK
        bigint satker_id FK
        enum periode "B03|B06|B09|B12"
        datetime batas_waktu_upload
        enum status_verifikasi "belum_upload|belum_verif|lengkap|perlu_perbaikan|tercapai"
        text penjelasan_zi
        bigint verified_by FK
        timestamp verified_at
        timestamp created_at
        timestamp updated_at
    }

    rb_dokumen_daduk {
        bigint id PK
        bigint rb_target_periode_id FK
        string nama_file
        string path_file
        int ukuran_file
        string mime_type
        bigint uploaded_by FK
        timestamp created_at
    }

    rb_catatan_verifikasi {
        bigint id PK
        bigint rb_target_periode_id FK
        bigint sender_id FK
        text pesan
        timestamp created_at
    }

    %% ==========================================
    %% RELASI ENTITAS
    %% ==========================================
    users }o--|| satuan_kerja : "belongs to"

    %% Relasi Pilar 1: Kinerja
    target_iku }o--|| tahun_anggaran : "untuk tahun"
    target_iku }o--|| satuan_kerja : "milik satker"
    target_iku }o--|| indikator_kinerja : "mengacu indikator"
    realisasi_iku ||--|| target_iku : "realisasi dari"
    realisasi_iku }o--|| users : "diinput oleh"

    rencana_aksi }o--|| tahun_anggaran : "untuk tahun"
    rencana_aksi }o--|| satuan_kerja : "milik satker"
    rencana_aksi }o--|| indikator_kinerja : "terkait indikator"
    realisasi_renaksi }o--|| rencana_aksi : "realisasi dari"
    realisasi_renaksi }o--o| users : "diverifikasi oleh"
    realisasi_renaksi }o--|| users : "dibuat oleh"
    bukti_dukung_renaksi }o--|| realisasi_renaksi : "lampiran untuk"
    bukti_dukung_renaksi }o--|| users : "diunggah oleh"

    evaluasi_sakip }o--|| tahun_anggaran : "untuk tahun"
    evaluasi_sakip }o--|| satuan_kerja : "milik satker"
    evaluasi_sakip }o--|| users : "dibuat oleh"

    %% Relasi Pilar 2: RB & ZI
    rb_indikator }o--|| rb_area : "bagian dari area"
    rb_sub_indikator }o--|| rb_indikator : "bagian dari indikator"
    rb_target_periode }o--|| rb_sub_indikator : "target untuk poin"
    rb_target_periode }o--|| tahun_anggaran : "tahun anggaran"
    rb_target_periode }o--|| satuan_kerja : "milik satker"
    rb_target_periode }o--o| users : "diverifikasi oleh"

    rb_dokumen_daduk }o--|| rb_target_periode : "dokumen daduk"
    rb_dokumen_daduk }o--|| users : "diunggah oleh"

    rb_catatan_verifikasi }o--|| rb_target_periode : "catatan target"
    rb_catatan_verifikasi }o--|| users : "dikirim oleh"
```

---

## Ringkasan Struktur Tabel & Relasi

### 1. Entitas Bersama (Core & Shared)
* **`users`**: Akun pengguna sistem dengan peran `super_admin`, `admin_kanwil`, `operator_satker`, `viewer`.
* **`satuan_kerja`**: Master Satker (Kanwil, Lapas, Rutan, Bapas, Kanim, Rupbasan, BHP).
* **`tahun_anggaran`**: Periode tahun anggaran aktif.

### 2. Entitas Pilar E-Performance (Akuntabilitas Kinerja)
* **`indikator_kinerja`**: Master IKU (kode, nama, satuan, polaritas, level).
* **`target_iku`**: Penetapan target per satker per tahun anggaran.
* **`realisasi_iku`**: Realisasi capaian IKU oleh operator satker.
* **`rencana_aksi`**: Rencana aksi triwulanan (TW I–IV) terhubung IKU.
* **`realisasi_renaksi`**: Pelaporan realisasi, progres %, dan status persetujuan.
* **`bukti_dukung_renaksi`**: File lampiran pendukung laporan realisasi Renaksi.
* **`evaluasi_sakip`**: Nilai evaluasi 4 komponen SAKIP (Perencanaan, Pengukuran, Pelaporan, Evaluasi) dan predikat.

### 3. Entitas Pilar E-RB (Reformasi Birokrasi & ZI)
* **`rb_area`**: Master Area LKE WBK/WBBM (6 Area Pengungkit + 2 Area Hasil) & RKT RB (General, Tematik, Meso).
* **`rb_indikator`**: Indikator per area dengan filter aspek (`reform`, `pemenuhan`, `hasil`).
* **`rb_sub_indikator`**: Poin indikator rinci, checklist persyaratan berkas data dukung, petunjuk TPI.
* **`rb_target_periode`**: Pemetaan satker + poin indikator + target periodik (`B03`, `B06`, `B09`, `B12`) + batas waktu + status verifikasi + narasi penjelasan ZI.
* **`rb_dokumen_daduk`**: Berkas PDF data dukung yang diunggah satker (maks. 50MB, auto-lock jika lengkap).
* **`rb_catatan_verifikasi`**: Log percakapan/catatan klarifikasi dua arah antara verifikator dan operator.

---

## Matriks Hak Akses Peran (RBAC)

| Modul / Fitur | Super Admin | Admin Kanwil | Operator Satker | Viewer | Publik |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Manajemen Pengguna** | ✅ Full | ❌ | ❌ | ❌ | ❌ |
| **Master Data (Satker, Tahun, IKU, RB)** | ✅ Full | ✅ Full | ❌ | ✅ Read | ❌ |
| **IKU (Target & Realisasi)** | ✅ Full | ✅ Full | ✅ (Satker sendiri) | ✅ Read | ❌ |
| **Renaksi (Pelaporan TW I–IV)** | ✅ Full | ✅ Full | ✅ (Satker sendiri) | ✅ Read | ❌ |
| **Verifikasi Renaksi** | ✅ Full | ✅ Full | ❌ | ✅ Read | ❌ |
| **Evaluasi SAKIP (4 Komponen)** | ✅ Full | ✅ Full | ❌ | ✅ Read | ❌ |
| **LKE WBK/WBBM (Upload Daduk B03–B12)** | ✅ Full | ✅ Full | ✅ (Satker sendiri) | ✅ Read | ❌ |
| **RKT RB General/Tematik/Meso** | ✅ Full | ✅ Full | ✅ (Satker sendiri) | ✅ Read | ❌ |
| **Verifikasi Daduk & Kunci Berkas** | ✅ Full | ✅ Full | ❌ | ❌ | ❌ |
| **Thread Catatan Klarifikasi Daduk** | ✅ Full | ✅ Full | ✅ (Satker sendiri) | ❌ | ❌ |
| **Dashboard Terpadu (Kinerja + RB)** | ✅ Full | ✅ Full | ✅ Read | ✅ Read | ❌ |
| **Portal Transparansi Publik** | ✅ | ✅ | ✅ | ✅ | ✅ |
