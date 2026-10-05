export interface ApiResponse<T> {
  data: T
  message?: string
}

export type UserRole = 'super_admin' | 'admin_kanwil' | 'operator_satker' | 'viewer'
export type TipeSatker = 'kanwil' | 'upt' | 'satker'
export type PolaritasIku = 'positif' | 'negatif'
export type LevelIku = 'strategis' | 'program' | 'kegiatan'
export type Triwulan = 'TW1' | 'TW2' | 'TW3' | 'TW4'
export type StatusRenaksi = 'belum_lapor' | 'menunggu_verifikasi' | 'terverifikasi' | 'perlu_perbaikan'
export type PredikatSakip = 'AA' | 'A' | 'BB' | 'B' | 'CC' | 'C' | 'D'
export type KategoriRb = 'lke_wbk_wbbm' | 'rkt_general' | 'rkt_tematik' | 'rkt_meso'
export type KomponenRb = 'pengungkit' | 'hasil' | 'none'
export type AspekRb = 'reform' | 'pemenuhan' | 'hasil' | 'none'
export type PeriodeRb = 'B03' | 'B06' | 'B09' | 'B12'
export type StatusVerifikasiRb = 'belum_upload' | 'belum_verif' | 'lengkap' | 'perlu_perbaikan' | 'tercapai'
export type StatusColor = 'green' | 'yellow' | 'red' | 'blue' | 'gray'

export interface SatuanKerja {
  id: number
  kode: string
  nama: string
  tipe: TipeSatker
  is_active: boolean
  created_at?: string
  updated_at?: string
}

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  role_label: string
  satker_id: number | null
  satker?: SatuanKerja
  is_active: boolean
}

export interface TahunAnggaran {
  id: number
  tahun: number
  is_aktif: boolean
  created_at?: string
  updated_at?: string
}

export interface IndikatorKinerja {
  id: number
  kode: string
  nama: string
  satuan: string
  polaritas: PolaritasIku
  level: LevelIku
}

export interface TargetIku {
  id: number
  tahun_anggaran_id: number
  satker_id: number
  indikator_id: number
  nilai_target: number
  satker?: SatuanKerja
  indikator?: IndikatorKinerja
  realisasi?: RealisasiIku
}

export interface RealisasiIku {
  id: number
  target_iku_id: number
  nilai_realisasi: number
  persentase_capaian: number
  keterangan: string | null
  target_iku?: TargetIku
}

export interface MatriksRow {
  satker: {
    id: number
    kode: string
    nama: string
    tipe: string
  }
  indikators: {
    indikator_id: number
    kode: string
    nama: string
    satuan: string
    target_iku_id: number | null
    target: number | null
    realisasi: number | null
    persentase: number | null
    status_color: StatusColor
  }[]
}

// ==========================================
// RENAKSI & BUKTI DUKUNG
// ==========================================
export interface BuktiDukungRenaksi {
  id: number
  realisasi_renaksi_id: number
  nama_file: string
  path_file: string
  ukuran_file: number
  mime_type: string
  created_at: string
}

export interface RealisasiRenaksi {
  id: number
  rencana_aksi_id: number
  deskripsi_realisasi: string | null
  persentase_selesai: number
  status: StatusRenaksi
  catatan_verifikasi: string | null
  verified_by: number | null
  verified_at: string | null
  submitted_at: string | null
  rencana_aksi?: RencanaAksi
  bukti_dukung?: BuktiDukungRenaksi[]
  verifikator?: User
  creator?: User
}

export interface RencanaAksi {
  id: number
  tahun_anggaran_id: number
  satker_id: number
  indikator_id: number
  nama_aksi: string
  triwulan: Triwulan
  target_output: string | null
  satker?: SatuanKerja
  indikator?: IndikatorKinerja
  realisasi?: RealisasiRenaksi
  tahun_anggaran?: TahunAnggaran
}

// ==========================================
// EVALUASI SAKIP
// ==========================================
export interface EvaluasiSakip {
  id: number
  tahun_anggaran_id: number
  satker_id: number
  nilai_perencanaan: number
  nilai_pengukuran: number
  nilai_pelaporan: number
  nilai_evaluasi: number
  nilai_total: number
  predikat: PredikatSakip
  catatan: string | null
  satker?: SatuanKerja
  tahun_anggaran?: TahunAnggaran
  creator?: User
}

export interface PerbandinganSakipRow {
  satker_id: number
  kode: string
  nama: string
  tipe: string
  evaluasi_id: number | null
  nilai_perencanaan: number | null
  nilai_pengukuran: number | null
  nilai_pelaporan: number | null
  nilai_evaluasi: number | null
  nilai_total: number | null
  predikat: PredikatSakip | null
  predikat_label: string | null
}

export interface DashboardSummary {
  total_satker_aktif: number
  rata_rata_capaian_iku: number
  renaksi_total: number
  renaksi_selesai: number
  sakip_tertinggi: {
    nilai: number
    satker: string
    predikat: string
  } | null
  sakip_terendah: {
    nilai: number
    satker: string
    predikat: string
  } | null
  lke_progress: {
    fulfilled: number
    total: number
    percentage: number
  }
  rkt_progress: {
    fulfilled: number
    total: number
    percentage: number
  }
}

export interface LoginCredentials {
  email: string
  password: string
}

// ==========================================
// E-RB & LKE ZI 2026
// ==========================================
export interface RbDokumenDaduk {
  id: number
  rb_target_periode_id: number
  nama_file: string
  path_file: string
  ukuran_file: number
  mime_type: string
  created_at: string
  uploaded_by?: number
  uploader?: User
  uploaded_by_user?: User
}

export interface RbCatatanVerifikasi {
  id: number
  rb_target_periode_id: number
  sender_id: number
  pesan: string
  created_at: string
  sender?: User
}

export interface CountdownInfo {
  days: number
  hours: number
  minutes: number
  is_past: boolean
}

export interface RbTargetPeriode {
  id: number
  rb_sub_indikator_id: number
  tahun_anggaran_id: number
  satker_id: number
  periode: PeriodeRb
  batas_waktu_upload: string | null
  status_verifikasi: StatusVerifikasiRb
  penjelasan_zi: string | null
  verified_by: number | null
  verified_at: string | null
  countdown?: CountdownInfo | null
  sub_indikator?: RbSubIndikator
  satker?: SatuanKerja
  tahun_anggaran?: TahunAnggaran
  verifikator?: User
  dokumen?: RbDokumenDaduk[]
  catatan?: RbCatatanVerifikasi[]
}

export interface RbSubIndikator {
  id: number
  rb_indikator_id: number
  nomor_poin: number
  judul_poin: string
  checklist_daduk: string | null
  catatan_tpi: string | null
  indikator?: RbIndikator
  target_periode?: RbTargetPeriode[]
}

export interface RbIndikator {
  id: number
  rb_area_id: number
  aspek: AspekRb
  kode: string
  nama_indikator: string
  keterangan_juknis: string | null
  urutan: number
  sub_indikator?: RbSubIndikator[]
  area?: RbArea
}

export interface RbArea {
  id: number
  kategori: KategoriRb
  komponen: KomponenRb
  kode: string
  nama_area: string
  urutan: number
  indikator?: RbIndikator[]
}

export interface RbProgressSummary {
  total_targets: number
  lengkap_count: number
  belum_verif_count: number
  perlu_perbaikan_count: number
  belum_upload_count: number
  percentage: number
}