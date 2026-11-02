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

const STORAGE_KEY_MATRIX = 'bakayuh_laporan_matrix_v1'
const STORAGE_KEY_SUBMISSIONS = 'bakayuh_daduk_submissions_v1'

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
  {
    id: 'sub-1',
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    periodeKey: 'sep',
    periodeType: 'bulanan',
    tahun: 2026,
    tanggalKirim: '09/10/2026 14:20',
    namaFile: 'Laporan_Bulanan_Setjen_Sep2026.pdf',
    ukuranFile: '2.4 MB',
    status: 'Menunggu',
    catatanPengirim: 'Laporan bulanan September bidang kepegawaian dan keuangan.',
    catatanVerifikator: '',
  },
  {
    id: 'sub-2',
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    jenisLaporan: 'Laporan Triwulan',
    periode: 'Triwulan III (TW 3)',
    periodeKey: 'tw3',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '08/10/2026 11:05',
    namaFile: 'Laporan_TW3_AHU_2026_Final.pdf',
    ukuranFile: '4.1 MB',
    status: 'Menunggu',
    catatanPengirim: 'Dokumen bukti dukung capaian kinerja TW 3 AHU.',
    catatanVerifikator: '',
  },
  {
    id: 'sub-3',
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'Agustus 2026',
    periodeKey: 'agu',
    periodeType: 'bulanan',
    tahun: 2026,
    tanggalKirim: '09/09/2026 16:45',
    namaFile: 'Laporan_Bulanan_KI_Agustus2026.pdf',
    ukuranFile: '1.8 MB',
    status: 'Diterima',
    catatanPengirim: 'Data dukung permohonan merk dan hak cipta Agustus.',
    catatanVerifikator: 'Data dukung dan laporan lengkap sesuai format standar.',
    verifiedAt: '10/09/2026 09:15',
  },
  {
    id: 'sub-4',
    unitKode: 'PP',
    unitName: 'Peraturan Perundang-Undangan',
    jenisLaporan: 'Laporan Triwulan',
    periode: 'Triwulan II (TW 2)',
    periodeKey: 'tw2',
    periodeType: 'triwulan',
    tahun: 2026,
    tanggalKirim: '09/07/2026 10:15',
    namaFile: 'Laporan_TW2_PP_Revisi.pdf',
    ukuranFile: '3.2 MB',
    status: 'Diterima',
    catatanPengirim: 'Revisi matriks capaian harmonisasi Raperda TW 2.',
    catatanVerifikator: 'Revisi matriks capaian telah diperbaiki dan diverifikasi.',
    verifiedAt: '11/07/2026 14:00',
  },
  {
    id: 'sub-5',
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    periodeKey: 'sep',
    periodeType: 'bulanan',
    tahun: 2026,
    tanggalKirim: '10/10/2026 09:30',
    namaFile: 'Laporan_BPHN_Sep_Draft.pdf',
    ukuranFile: '1.2 MB',
    status: 'Perlu Perbaikan',
    catatanPengirim: 'Laporan penyuluhan hukum Desa Sadar Hukum.',
    catatanVerifikator: 'Tanda tangan pimpinan belum terlampir, mohon perbaiki.',
    verifiedAt: '12/10/2026 11:20',
  },
  {
    id: 'sub-6',
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    periodeKey: 'sep',
    periodeType: 'bulanan',
    tahun: 2026,
    tanggalKirim: '09/10/2026 15:10',
    namaFile: 'Laporan_Kinerja_BSK_Sep2026.pdf',
    ukuranFile: '2.9 MB',
    status: 'Menunggu',
    catatanPengirim: 'Hasil analisis kebijakan dan survei integritas.',
    catatanVerifikator: '',
  },
]

// Singleton reactive state shared across all components
const unitsDataState = ref<LaporanUnitRow[]>(loadUnitsFromStorage())
const submissionsState = ref<DadukSubmission[]>(loadSubmissionsFromStorage())

function loadUnitsFromStorage(): LaporanUnitRow[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_MATRIX)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Reconcile: If AHU tw3 was legacy mock 'menunggu' but no real user upload, set to terlambat
        const ahu = parsed.find((u: LaporanUnitRow) => u.unitKode === 'AHU')
        if (ahu && ahu.triwulanStatus?.tw3 === 'menunggu') {
          const rawSubs = localStorage.getItem(STORAGE_KEY_SUBMISSIONS)
          const subs = rawSubs ? JSON.parse(rawSubs) : []
          const hasUserUpload = subs.some(
            (s: DadukSubmission) => s.unitKode === 'AHU' && s.periodeKey === 'tw3' && s.id !== 'sub-2',
          )
          if (!hasUserUpload) {
            ahu.triwulanStatus.tw3 = 'terlambat'
          }
        }
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
        // Clean up legacy mock sub-2 for AHU so AHU starts with a clean slate
        return parsed.filter((s: DadukSubmission) => s.id !== 'sub-2')
      }
    }
  } catch (e) {
    console.error('Failed to load submissions from localStorage:', e)
  }
  return JSON.parse(JSON.stringify(initialSubmissions.filter((s) => s.id !== 'sub-2')))
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

export function useDadukLaporanStore() {
  const unitsData = unitsDataState
  const submissions = submissionsState

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

    const now = new Date()
    const formattedDate = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

    sub.status = decision
    sub.catatanVerifikator = catatan.trim()
    sub.verifiedAt = formattedDate

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

  return {
    unitsData,
    submissions,
    getUnitRow,
    getUnitSubmission,
    addOrUpdateSubmission,
    verifySubmission,
    deleteSubmission,
    countMenunggu,
    countPerluPerbaikan,
    countDiterima,
    saveMatrixToStorage,
    saveSubmissionsToStorage,
  }
}
