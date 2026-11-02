import { ref, computed } from 'vue'

export interface DadukSubmission {
  id: string
  unitKode: string // 'Setjen' | 'AHU' | 'KI' | 'PP' | 'BPHN' | 'BSK'
  unitName: string
  jenisLaporan: string // 'Laporan Bulanan' | 'Laporan Triwulan' | 'Data Dukung Renaksi'
  periode: string // 'September 2026' | 'Triwulan III (TW 3)'
  periodeKey: string // 'sep', 'tw3', etc.
  periodeType: 'bulanan' | 'triwulan'
  tahun: number
  tanggalKirim: string
  namaFile: string
  ukuranFile: string
  fileDataUrl?: string
  status: 'Menunggu' | 'Perlu Perbaikan' | 'Diterima'
  catatanPengirim?: string
  catatanVerifikator?: string
  verifiedAt?: string
}

export interface LaporanUnitRow {
  unitKode: string
  unitName: string
  bulananStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  triwulanStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  kepatuhan: number
}

export interface DadukMonitoringRow {
  unit: string
  unitName: string
  total: number
  belum: number
  menunggu: number
  perbaikan: number
  diterima: number
  progres: number
}

const STORAGE_KEY_MATRIX = 'bakayuh_laporan_matrix_v1'
const STORAGE_KEY_SUBMISSIONS = 'bakayuh_daduk_submissions_v2'
const STORAGE_KEY_MONITORING = 'bakayuh_monitoring_daduk_v2'

const initialMonitoringRows: DadukMonitoringRow[] = [
  { unit: 'Setjen', unitName: 'Bagian Tata Usaha dan Umum', total: 4, belum: 1, menunggu: 1, perbaikan: 1, diterima: 1, progres: 25 },
  { unit: 'AHU', unitName: 'Administrasi Hukum Umum', total: 3, belum: 1, menunggu: 1, perbaikan: 0, diterima: 1, progres: 33 },
  { unit: 'KI', unitName: 'Kekayaan Intelektual', total: 2, belum: 0, menunggu: 1, perbaikan: 1, diterima: 0, progres: 0 },
  { unit: 'PP', unitName: 'Peraturan Perundang-Undangan', total: 2, belum: 1, menunggu: 0, perbaikan: 0, diterima: 1, progres: 50 },
  { unit: 'BPHN', unitName: 'Badan Pembinaan Hukum Nasional', total: 3, belum: 2, menunggu: 1, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'BSK', unitName: 'Badan Strategi Kebijakan', total: 2, belum: 1, menunggu: 0, perbaikan: 1, diterima: 0, progres: 0 },
]

const initialUnitsData: LaporanUnitRow[] = [
  {
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'menunggu', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'diterima',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 11,
  },
  {
    unitKode: 'PP',
    unitName: 'Peraturan Perundang-Undangan',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'diterima', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 25,
  },
  {
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'perbaikan', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'menunggu', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
]

const initialSubmissions: DadukSubmission[] = [
  // --- 4 DATA DUKUNG MENUNGGU VERIFIKASI ---
  {
    id: 'sub-daduk-setjen-1',
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '08/10/2026 14:10',
    namaFile: 'Data_Dukung_TW3_Setjen_Layanan_Umum.pdf',
    ukuranFile: '2.5 MB',
    status: 'Menunggu',
    catatanPengirim: 'Data dukung realisasi rencana aksi TW 3 bagian umum dan kepegawaian.',
    catatanVerifikator: '',
  },
  {
    id: 'sub-daduk-ahu-1',
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '09/10/2026 10:25',
    namaFile: 'Data_Dukung_AHU_TW3_2026.pdf',
    ukuranFile: '3.1 MB',
    status: 'Menunggu',
    catatanPengirim: 'Bukti dukung target rencana aksi kenotariatan & fidusia TW 3.',
    catatanVerifikator: '',
  },
  {
    id: 'sub-daduk-ki-1',
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '09/10/2026 13:40',
    namaFile: 'Data_Dukung_KI_TW3_Permohonan.pdf',
    ukuranFile: '1.9 MB',
    status: 'Menunggu',
    catatanPengirim: 'Rekapitulasi berkas verifikasi permohonan kekayaan intelektual.',
    catatanVerifikator: '',
  },
  {
    id: 'sub-daduk-bphn-1',
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '10/10/2026 09:15',
    namaFile: 'Data_Dukung_BPHN_TW3_Penyuluhan.pdf',
    ukuranFile: '2.2 MB',
    status: 'Menunggu',
    catatanPengirim: 'Dokumentasi kegiatan pembinaan Desa Sadar Hukum TW 3.',
    catatanVerifikator: '',
  },

  // --- 3 DATA DUKUNG PERLU PERBAIKAN ---
  {
    id: 'sub-daduk-setjen-rev',
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan II (TW 2)',
    periodeKey: 'tw2',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '05/07/2026 11:20',
    namaFile: 'Berkas_Kepegawaian_TW2_Setjen.pdf',
    ukuranFile: '3.4 MB',
    status: 'Perlu Perbaikan',
    catatanPengirim: 'Data dukung SKP dan kepegawaian TW 2.',
    catatanVerifikator: 'Surat tugas dan daftar hadir belum lengkap, mohon dilengkapi.',
    verifiedAt: '08/07/2026 15:30',
  },
  {
    id: 'sub-daduk-ki-rev',
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan II (TW 2)',
    periodeKey: 'tw2',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '07/07/2026 14:00',
    namaFile: 'Bukti_Layanan_Merek_TW2.pdf',
    ukuranFile: '2.8 MB',
    status: 'Perlu Perbaikan',
    catatanPengirim: 'Laporan layanan fasilitasi pendaftaran merek TW 2.',
    catatanVerifikator: 'Bukti tanda terima PNBP belum dilampirkan.',
    verifiedAt: '09/07/2026 10:15',
  },
  {
    id: 'sub-daduk-bsk-rev',
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '09/10/2026 16:30',
    namaFile: 'Analisis_Kebijakan_Hukum_BSK_TW3.pdf',
    ukuranFile: '4.5 MB',
    status: 'Perlu Perbaikan',
    catatanPengirim: 'Laporan analisis evaluasi kebijakan hukum TW 3.',
    catatanVerifikator: 'Rekomendasi belum disertai matriks tindak lanjut satuan kerja.',
    verifiedAt: '11/10/2026 13:45',
  },

  // --- 3 DATA DUKUNG SUDAH DITERIMA ---
  {
    id: 'sub-daduk-setjen-acc',
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan I (TW 1)',
    periodeKey: 'tw1',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '05/04/2026 09:30',
    namaFile: 'Matriks_Capaian_Setjen_TW1.pdf',
    ukuranFile: '1.7 MB',
    status: 'Diterima',
    catatanPengirim: 'Data dukung renaksi TW 1 bagian umum dan perencanaan.',
    catatanVerifikator: 'Data dukung lengkap dan sesuai format.',
    verifiedAt: '08/04/2026 11:20',
  },
  {
    id: 'sub-daduk-ahu-acc',
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan II (TW 2)',
    periodeKey: 'tw2',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '06/07/2026 10:00',
    namaFile: 'Laporan_Evaluasi_Kenotariatan_TW2.pdf',
    ukuranFile: '3.6 MB',
    status: 'Diterima',
    catatanPengirim: 'Data dukung evaluasi notaris dan MPDN TW 2.',
    catatanVerifikator: 'Berkas verifikasi terbukti valid dan akurat.',
    verifiedAt: '09/07/2026 14:10',
  },
  {
    id: 'sub-daduk-pp-acc',
    unitKode: 'PP',
    unitName: 'Peraturan Perundang-Undangan',
    jenisLaporan: 'Data Dukung Renaksi',
    periode: 'Triwulan II (TW 2)',
    periodeKey: 'tw2',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '07/07/2026 13:15',
    namaFile: 'Harmonisasi_Perda_PP_TW2.pdf',
    ukuranFile: '2.9 MB',
    status: 'Diterima',
    catatanPengirim: 'Data dukung pelaksanaan harmonisasi 15 Raperda.',
    catatanVerifikator: 'Matriks harmonisasi lengkap dengan berita acara.',
    verifiedAt: '10/07/2026 16:00',
  },
]

// Singleton reactive state shared across all components
const unitsDataState = ref<LaporanUnitRow[]>(loadUnitsFromStorage())
const submissionsState = ref<DadukSubmission[]>(loadSubmissionsFromStorage())
const monitoringRowsState = ref<DadukMonitoringRow[]>(loadMonitoringFromStorage())

function loadUnitsFromStorage(): LaporanUnitRow[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_MATRIX)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed
      }
    }
  } catch (e) {
    console.error('Failed to load matrix from localStorage:', e)
  }
  return JSON.parse(JSON.stringify(initialUnitsData))
}

function loadSubmissionsFromStorage(): DadukSubmission[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_SUBMISSIONS)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed
      }
    }
  } catch (e) {
    console.error('Failed to load submissions from localStorage:', e)
  }
  return JSON.parse(JSON.stringify(initialSubmissions))
}

function loadMonitoringFromStorage(): DadukMonitoringRow[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_MONITORING)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed
      }
    }
  } catch (e) {
    console.error('Failed to load monitoring rows from localStorage:', e)
  }
  return JSON.parse(JSON.stringify(initialMonitoringRows))
}

function saveMatrixToStorage() {
  try {
    localStorage.setItem(STORAGE_KEY_MATRIX, JSON.stringify(unitsDataState.value))
  } catch (e) {
    console.error('Failed to save matrix to localStorage:', e)
  }
}

function saveSubmissionsToStorage() {
  try {
    localStorage.setItem(STORAGE_KEY_SUBMISSIONS, JSON.stringify(submissionsState.value))
  } catch (e) {
    console.error('Failed to save submissions to localStorage:', e)
  }
}

function saveMonitoringToStorage() {
  try {
    localStorage.setItem(STORAGE_KEY_MONITORING, JSON.stringify(monitoringRowsState.value))
  } catch (e) {
    console.error('Failed to save monitoring rows to localStorage:', e)
  }
}

export function useDadukLaporanStore() {
  const unitsData = unitsDataState
  const submissions = submissionsState
  const monitoringRows = monitoringRowsState

  function getUnitRow(unitKode: string): LaporanUnitRow | undefined {
    const target = unitKode.toUpperCase()
    return unitsData.value.find((u) => u.unitKode.toUpperCase() === target)
  }

  function getUnitSubmission(unitKode: string, periodeKey: string): DadukSubmission | undefined {
    const target = unitKode.toUpperCase()
    return submissions.value.find(
      (s) => s.unitKode.toUpperCase() === target && s.periodeKey.toLowerCase() === periodeKey.toLowerCase(),
    )
  }

  function addOrUpdateSubmission(payload: {
    unitKode: string
    unitName: string
    jenisLaporan: string
    periode: string
    periodeKey: string
    periodeType: 'bulanan' | 'triwulan'
    tahun: number
    namaFile: string
    ukuranFile: string
    fileDataUrl?: string
    catatanPengirim?: string
  }): DadukSubmission {
    const now = new Date()
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

    const existingIndex = submissions.value.findIndex(
      (s) =>
        s.unitKode.toUpperCase() === payload.unitKode.toUpperCase() &&
        s.periodeKey.toLowerCase() === payload.periodeKey.toLowerCase() &&
        s.tahun === payload.tahun,
    )

    let createdOrUpdated: DadukSubmission

    if (existingIndex >= 0) {
      // Update existing
      const existing = submissions.value[existingIndex]!
      existing.namaFile = payload.namaFile
      existing.ukuranFile = payload.ukuranFile
      if (payload.fileDataUrl) existing.fileDataUrl = payload.fileDataUrl
      existing.tanggalKirim = formattedDate
      existing.status = 'Menunggu'
      existing.catatanPengirim = payload.catatanPengirim || ''
      existing.catatanVerifikator = ''
      createdOrUpdated = existing
    } else {
      // Create new
      createdOrUpdated = {
        id: `sub-${Date.now()}`,
        unitKode: payload.unitKode,
        unitName: payload.unitName,
        jenisLaporan: payload.jenisLaporan,
        periode: payload.periode,
        periodeKey: payload.periodeKey,
        periodeType: payload.periodeType,
        tahun: payload.tahun,
        tanggalKirim: formattedDate,
        namaFile: payload.namaFile,
        ukuranFile: payload.ukuranFile,
        fileDataUrl: payload.fileDataUrl,
        status: 'Menunggu',
        catatanPengirim: payload.catatanPengirim || '',
        catatanVerifikator: '',
      }
      submissions.value.unshift(createdOrUpdated)
    }

    // Update matrix status
    const unitRow = getUnitRow(payload.unitKode)
    if (unitRow) {
      if (payload.periodeType === 'bulanan') {
        unitRow.bulananStatus[payload.periodeKey] = 'menunggu'
      } else {
        unitRow.triwulanStatus[payload.periodeKey] = 'menunggu'
      }
    }

    // Also update monitoring rows if applicable
    const mRow = monitoringRowsState.value.find(
      (r) => r.unit.toUpperCase() === payload.unitKode.toUpperCase(),
    )
    if (mRow) {
      if (mRow.belum > 0) mRow.belum--
      mRow.menunggu++
      mRow.progres = mRow.total > 0 ? Math.round((mRow.diterima / mRow.total) * 100) : 0
      saveMonitoringToStorage()
    }

    saveSubmissionsToStorage()
    saveMatrixToStorage()

    return createdOrUpdated
  }

  function verifySubmission(
    submissionId: string,
    decision: 'Diterima' | 'Perlu Perbaikan',
    catatan: string,
  ): boolean {
    const sub = submissions.value.find((s) => s.id === submissionId)
    if (!sub) return false

    const oldStatus = sub.status
    const now = new Date()
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

    sub.status = decision
    sub.catatanVerifikator = catatan.trim()
    sub.verifiedAt = formattedDate

    // Update monitoring rows state
    const mRow = monitoringRowsState.value.find(
      (r) => r.unit.toUpperCase() === sub.unitKode.toUpperCase(),
    )
    if (mRow) {
      if (oldStatus === 'Menunggu') {
        mRow.menunggu = Math.max(0, mRow.menunggu - 1)
      } else if (oldStatus === 'Perlu Perbaikan') {
        mRow.perbaikan = Math.max(0, mRow.perbaikan - 1)
      } else if (oldStatus === 'Diterima') {
        mRow.diterima = Math.max(0, mRow.diterima - 1)
      }

      if (decision === 'Diterima') {
        mRow.diterima++
      } else if (decision === 'Perlu Perbaikan') {
        mRow.perbaikan++
      }

      mRow.progres = mRow.total > 0 ? Math.round((mRow.diterima / mRow.total) * 100) : 0
      saveMonitoringToStorage()
    }

    // Update matrix status
    const unitRow = getUnitRow(sub.unitKode)
    if (unitRow) {
      const statusValue = decision === 'Diterima' ? 'diterima' : 'perbaikan'
      if (sub.periodeType === 'bulanan') {
        unitRow.bulananStatus[sub.periodeKey] = statusValue
      } else {
        unitRow.triwulanStatus[sub.periodeKey] = statusValue
      }

      // Recompute kepatuhan
      const allVals = [
        ...Object.values(unitRow.bulananStatus),
        ...Object.values(unitRow.triwulanStatus),
      ]
      const diterimaCount = allVals.filter((v) => v === 'diterima').length
      const dueCount = allVals.filter((v) => v !== 'belum_waktunya').length
      unitRow.kepatuhan = dueCount > 0 ? Math.round((diterimaCount / dueCount) * 100) : 0
    }

    saveSubmissionsToStorage()
    saveMatrixToStorage()

    return true
  }

  function deleteSubmission(submissionId: string): boolean {
    const idx = submissions.value.findIndex((s) => s.id === submissionId)
    if (idx < 0) return false

    const sub = submissions.value[idx]!
    const unitRow = getUnitRow(sub.unitKode)
    if (unitRow) {
      if (sub.periodeType === 'bulanan') {
        unitRow.bulananStatus[sub.periodeKey] = 'terlambat'
      } else {
        unitRow.triwulanStatus[sub.periodeKey] = 'terlambat'
      }
    }

    submissions.value.splice(idx, 1)
    saveSubmissionsToStorage()
    saveMatrixToStorage()

    return true
  }

  const countMenunggu = computed(
    () => submissions.value.filter((s) => s.status === 'Menunggu').length,
  )
  const countPerluPerbaikan = computed(
    () => submissions.value.filter((s) => s.status === 'Perlu Perbaikan').length,
  )
  const countDiterima = computed(
    () => submissions.value.filter((s) => s.status === 'Diterima').length,
  )

  // Dynamic metrics from monitoring rows
  const totalDadukRenaksi = computed(() =>
    monitoringRowsState.value.reduce((acc, r) => acc + r.total, 0),
  )
  const totalDadukBelum = computed(() =>
    monitoringRowsState.value.reduce((acc, r) => acc + r.belum, 0),
  )
  const totalDadukMenunggu = computed(() =>
    monitoringRowsState.value.reduce((acc, r) => acc + r.menunggu, 0),
  )
  const totalDadukPerbaikan = computed(() =>
    monitoringRowsState.value.reduce((acc, r) => acc + r.perbaikan, 0),
  )
  const totalDadukDiterima = computed(() =>
    monitoringRowsState.value.reduce((acc, r) => acc + r.diterima, 0),
  )
  const persenDadukDiterima = computed(() =>
    totalDadukRenaksi.value > 0
      ? Math.round((totalDadukDiterima.value / totalDadukRenaksi.value) * 100)
      : 0,
  )

  // Dynamic count of reports waiting verification
  const laporanMenungguCount = computed(() => {
    let count = 0
    for (const row of unitsDataState.value) {
      for (const st of Object.values(row.bulananStatus || {})) {
        if (st === 'menunggu') count++
      }
      for (const st of Object.values(row.triwulanStatus || {})) {
        if (st === 'menunggu') count++
      }
    }
    return count
  })

  return {
    unitsData,
    submissions,
    monitoringRows,
    getUnitRow,
    getUnitSubmission,
    addOrUpdateSubmission,
    verifySubmission,
    deleteSubmission,
    countMenunggu,
    countPerluPerbaikan,
    countDiterima,
    totalDadukRenaksi,
    totalDadukBelum,
    totalDadukMenunggu,
    totalDadukPerbaikan,
    totalDadukDiterima,
    persenDadukDiterima,
    laporanMenungguCount,
    saveMatrixToStorage,
    saveSubmissionsToStorage,
    saveMonitoringToStorage,
  }
}
