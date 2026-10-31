<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { isOperatorSatker, userUnitCode } = useAuth()

const selectedPkYear = ref(2026)
const pkYearOptions = [2026, 2025, 2024]
const selectedPkUnit = ref('ALL')
const pkUnitFilterOptions = [
  { id: 'ALL', label: 'Semua unit' },
  { id: 'setjen', label: 'Setjen' },
  { id: 'ahu', label: 'AHU' },
  { id: 'ki', label: 'KI' },
  { id: 'pp', label: 'PP' },
  { id: 'bphn', label: 'BPHN' },
  { id: 'bsk', label: 'BSK' },
]

export interface CapaianPkRow {
  id: string
  sasaran: string
  indikator: string
  target: string
  // TW 1
  jan: string
  feb: string
  mar: string
  rekapTw1: string
  rekapTw1Manual: boolean
  keteranganTw1: string
  // TW 2
  apr: string
  mei: string
  jun: string
  rekapTw2: string
  rekapTw2Manual: boolean
  sdSemester1: string
  keteranganTw2: string
  // TW 3
  jul: string
  agu: string
  sep: string
  rekapTw3: string
  rekapTw3Manual: boolean
  sdTw3: string
  keteranganTw3: string
  // TW 4
  okt: string
  nov: string
  des: string
  rekapTw4: string
  rekapTw4Manual: boolean
  sdTw4: string
  keteranganTw4: string
}

export interface CapaianPkGroup {
  unitId: string
  unitName: string
  rows: CapaianPkRow[]
}

const STORAGE_KEY_PK = 'bakayuh_capaian_pk_data_v3'

const defaultPkGroups: CapaianPkGroup[] = [
  {
    unitId: 'setjen',
    unitName: 'Setjen',
    rows: [
      {
        id: 'sj-1',
        sasaran: 'SK 7099.1 · Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        indikator: 'IKK 7099.1.1 · Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK',
        target: '100 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1:
          'Belum terdapat rekomendasi dari BPK, sehingga belum ada tindak lanjut yang dapat dilaksanakan...',
        apr: '100 %',
        mei: '100 %',
        jun: '100 %',
        rekapTw2: '100 %',
        rekapTw2Manual: false,
        sdSemester1: '100 %',
        keteranganTw2:
          'Semua tindak lanjut rekomendasi hasil pemeriksaan BPK sudah selesai ditindaklanjuti',
        jul: '100 %',
        agu: '100 %',
        sep: '100 %',
        rekapTw3: '100 %',
        rekapTw3Manual: false,
        sdTw3: '100 %',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
      {
        id: 'sj-2',
        sasaran:
          'SK 7099.2 · Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        indikator: 'IKK 7099.2.1 · Indeks kepuasan terhadap layanan dukungan manajemen',
        target: '3,90 Indeks',
        jan: '3,94',
        feb: '3,94',
        mar: '3,94',
        rekapTw1: '3,94',
        rekapTw1Manual: false,
        keteranganTw1:
          'Berdasarkan hasil pengukuran Indeks Layanan Kesekretariatan TW I TAhun 2026 dari BSK',
        apr: '3,96',
        mei: '3,96',
        jun: '3,96',
        rekapTw2: '3,96',
        rekapTw2Manual: false,
        sdSemester1: '3,95',
        keteranganTw2:
          'Berdasarkan hasil pengukuran Indeks Layanan Kesekretariatan TW II TAhun 2026 dari BSK',
        jul: '3,97',
        agu: '3,97',
        sep: '3,97',
        rekapTw3: '3,97',
        rekapTw3Manual: false,
        sdTw3: '3,96',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
  {
    unitId: 'ahu',
    unitName: 'AHU',
    rows: [
      {
        id: 'ahu-1',
        sasaran:
          'SK 7110.1 · Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        indikator: 'IKK 7110.1.1 · Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        target: '3,22 Indeks',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
      {
        id: 'ahu-2',
        sasaran:
          'SK 7110.2 · Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
        indikator:
          'IKK 7110.2.1 · Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
        target: '98,2 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
  {
    unitId: 'ki',
    unitName: 'KI',
    rows: [
      {
        id: 'ki-1',
        sasaran:
          'SK 7121.1 · Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
        indikator:
          'IKK 7121.1.1 · Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan',
        target: '95 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
      {
        id: 'ki-2',
        sasaran:
          'SK 7121.2 · Terlaksananya Pelindungan Kekayaan Intelektual Melalui Penegakan Hukum Kekayaan Intelektual di Wilayah',
        indikator:
          'IKK 7121.2.1 · Persentase penyelesaian aduan pelanggaran kekayaan intelektual di wilayah',
        target: '100 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
  {
    unitId: 'pp',
    unitName: 'PP',
    rows: [
      {
        id: 'pp-1',
        sasaran:
          'SK 7130.1 · Meningkatnya Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
        indikator:
          'IKK 7130.1.1 · Persentase Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
        target: '99 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
  {
    unitId: 'bphn',
    unitName: 'BPHN',
    rows: [
      {
        id: 'bphn-1',
        sasaran: 'SK 7136.1 · Meningkatnya Kualitas Pembinaan Hukum Nasional di Wilayah',
        indikator:
          'IKK 7136.1.1 · Persentase Desa/Kelurahan Sadar Hukum yang Dibina di Wilayah',
        target: '85 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
  {
    unitId: 'bsk',
    unitName: 'BSK',
    rows: [
      {
        id: 'bsk-1',
        sasaran:
          'SK 7143.1 · Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        indikator:
          'IKK 7143.1.1 · Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        target: '95,5 %',
        jan: '',
        feb: '',
        mar: '',
        rekapTw1: '',
        rekapTw1Manual: false,
        keteranganTw1: '',
        apr: '',
        mei: '',
        jun: '',
        rekapTw2: '',
        rekapTw2Manual: false,
        sdSemester1: '',
        keteranganTw2: '',
        jul: '',
        agu: '',
        sep: '',
        rekapTw3: '',
        rekapTw3Manual: false,
        sdTw3: '',
        keteranganTw3: '',
        okt: '',
        nov: '',
        des: '',
        rekapTw4: '',
        rekapTw4Manual: false,
        sdTw4: '',
        keteranganTw4: '',
      },
    ],
  },
]

const pkGroups = ref<CapaianPkGroup[]>(defaultPkGroups)

onMounted(() => {
  if (isOperatorSatker.value) {
    selectedPkUnit.value = (userUnitCode.value || 'AHU').toLowerCase()
  }

  try {
    const raw = localStorage.getItem(STORAGE_KEY_PK)
    if (raw) {
      pkGroups.value = JSON.parse(raw)
    }
  } catch {
    // fallback default
  }
  // Recalculate cumulative fields across all groups
  for (const grp of pkGroups.value) {
    for (const r of grp.rows) {
      updateRowCalculations(r)
    }
  }
})

function saveStorage() {
  localStorage.setItem(STORAGE_KEY_PK, JSON.stringify(pkGroups.value))
}

const filteredPkGroups = computed(() => {
  if (isOperatorSatker.value) {
    const target = (userUnitCode.value || 'AHU').toLowerCase()
    return pkGroups.value.filter((g) => g.unitId.toLowerCase() === target)
  }
  if (selectedPkUnit.value === 'ALL') return pkGroups.value
  return pkGroups.value.filter((g) => g.unitId === selectedPkUnit.value)
})

// ========================================================
// KALKULASI OTOMATIS REKAP TRIWULAN & KUMULATIF
// ========================================================
function computeTriwulanAuto(val1: string, val2: string, val3: string): string {
  const vals = [val1, val2, val3].filter((v) => v && v.trim() && v.trim() !== '-' && v.trim() !== '+')
  if (vals.length === 0) return ''

  const isPercent = vals.some((v) => v.includes('%'))
  const nums = vals
    .map((v) => {
      const cleaned = v.replace('%', '').replace(',', '.').trim()
      const n = parseFloat(cleaned)
      return isNaN(n) ? null : n
    })
    .filter((n): n is number => n !== null)

  if (nums.length === 0) {
    return vals[vals.length - 1] || ''
  }

  const avg = nums.reduce((a, b) => a + b, 0) / nums.length
  const formatted = avg.toFixed(2).replace('.', ',').replace(/,00$/, '')
  return isPercent ? `${formatted} %` : formatted
}

function computeCumulativeAuto(...rekapVals: string[]): string {
  const vals = rekapVals.filter((v) => v && v.trim() && v.trim() !== '-' && v.trim() !== '+')
  if (vals.length === 0) return ''

  const isPercent = vals.some((v) => v.includes('%'))
  const nums = vals
    .map((v) => {
      const cleaned = v.replace('%', '').replace(',', '.').trim()
      const n = parseFloat(cleaned)
      return isNaN(n) ? null : n
    })
    .filter((n): n is number => n !== null)

  if (nums.length === 0) {
    return vals[vals.length - 1] || ''
  }

  const avg = nums.reduce((a, b) => a + b, 0) / nums.length
  const formatted = avg.toFixed(2).replace('.', ',').replace(/,00$/, '')
  return isPercent ? `${formatted} %` : formatted
}

function updateRowCalculations(row: CapaianPkRow) {
  if (!row.rekapTw1Manual) {
    row.rekapTw1 = computeTriwulanAuto(row.jan, row.feb, row.mar)
  }
  if (!row.rekapTw2Manual) {
    row.rekapTw2 = computeTriwulanAuto(row.apr, row.mei, row.jun)
  }
  row.sdSemester1 = computeCumulativeAuto(row.rekapTw1, row.rekapTw2)

  if (!row.rekapTw3Manual) {
    row.rekapTw3 = computeTriwulanAuto(row.jul, row.agu, row.sep)
  }
  row.sdTw3 = computeCumulativeAuto(row.rekapTw1, row.rekapTw2, row.rekapTw3)

  if (!row.rekapTw4Manual) {
    row.rekapTw4 = computeTriwulanAuto(row.okt, row.nov, row.des)
  }
  row.sdTw4 = computeCumulativeAuto(row.rekapTw1, row.rekapTw2, row.rekapTw3, row.rekapTw4)
}

// ========================================================
// SELEKSI SEL & MODAL EDIT NILAI
// ========================================================
interface ActiveCellInfo {
  unitId: string
  rowId: string
  field: keyof CapaianPkRow
  label: string
  currentValue: string
  isRekap: boolean
  isManual: boolean
  isKeterangan: boolean
  row: CapaianPkRow
}

const activeCell = ref<ActiveCellInfo | null>(null)
const showEditModal = ref(false)
const editValue = ref('')
const editManualOverride = ref(false)

function isCellSelected(unitId: string, rowId: string, field: string): boolean {
  return (
    activeCell.value?.unitId === unitId &&
    activeCell.value?.rowId === rowId &&
    activeCell.value?.field === field
  )
}

function selectAndEditCell(
  unitId: string,
  row: CapaianPkRow,
  field: keyof CapaianPkRow,
  label: string,
) {
  const isRekap = field.startsWith('rekap')
  const isKeterangan = field.startsWith('keterangan')
  const isManual = isRekap ? (row[`${field}Manual` as keyof CapaianPkRow] as boolean) : false

  activeCell.value = {
    unitId,
    rowId: row.id,
    field,
    label,
    currentValue: (row[field] as string) || '',
    isRekap,
    isManual,
    isKeterangan,
    row,
  }

  editValue.value = (row[field] as string) || ''
  editManualOverride.value = isManual
  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
}

function saveCellEdit() {
  if (!activeCell.value) return
  const row = activeCell.value.row
  const field = activeCell.value.field

  if (activeCell.value.isRekap) {
    const manualKey = `${String(field)}Manual` as keyof CapaianPkRow
    ;(row as unknown as Record<string, unknown>)[manualKey] = editManualOverride.value
    ;(row as unknown as Record<string, unknown>)[field] = editValue.value.trim()
  } else {
    ;(row as unknown as Record<string, unknown>)[field] = editValue.value.trim()
  }

  updateRowCalculations(row)
  saveStorage()
  triggerToast('Capaian berhasil diperbarui!')
  closeEditModal()
}

// ========================================================
// MODAL EDIT SASARAN / INDIKATOR
// ========================================================
const showRowEditModal = ref(false)
const editingRow = ref<CapaianPkRow | null>(null)
const rowEditForm = ref({
  sasaran: '',
  indikator: '',
  target: '',
})

function openRowEdit(row: CapaianPkRow) {
  editingRow.value = row
  rowEditForm.value = {
    sasaran: row.sasaran,
    indikator: row.indikator,
    target: row.target,
  }
  showRowEditModal.value = true
}

function closeRowEditModal() {
  showRowEditModal.value = false
  editingRow.value = null
}

function saveRowEdit() {
  if (!editingRow.value) return
  editingRow.value.sasaran = rowEditForm.value.sasaran.trim()
  editingRow.value.indikator = rowEditForm.value.indikator.trim()
  editingRow.value.target = rowEditForm.value.target.trim()
  saveStorage()
  triggerToast('Data Indikator dan Target berhasil disimpan!')
  closeRowEditModal()
}

// ========================================================
// EKSPOR CSV
// ========================================================
function exportCapaianCsv() {
  const headers = [
    'No',
    'Unit',
    'Sasaran Kinerja',
    'Indikator Kinerja Kegiatan',
    'Target',
    'Jan',
    'Feb',
    'Mar',
    'Rekap TW I',
    'Keterangan TW I',
    'Apr',
    'Mei',
    'Jun',
    'Rekap TW II',
    's.d. Semester I',
    'Keterangan TW II',
    'Jul',
    'Agu',
    'Sep',
    'Rekap TW III',
    's.d. TW III',
    'Keterangan TW III',
    'Okt',
    'Nov',
    'Des',
    'Rekap TW IV',
    's.d. TW IV',
    'Keterangan TW IV',
  ]

  const rows: (string | number)[][] = []
  let counter = 1

  for (const grp of filteredPkGroups.value) {
    for (const r of grp.rows) {
      rows.push([
        counter++,
        `"${grp.unitName}"`,
        `"${r.sasaran.replace(/"/g, '""')}"`,
        `"${r.indikator.replace(/"/g, '""')}"`,
        `"${r.target}"`,
        `"${r.jan || '-'}"`,
        `"${r.feb || '-'}"`,
        `"${r.mar || '-'}"`,
        `"${r.rekapTw1 || '-'}"`,
        `"${(r.keteranganTw1 || '-').replace(/"/g, '""')}"`,
        `"${r.apr || '-'}"`,
        `"${r.mei || '-'}"`,
        `"${r.jun || '-'}"`,
        `"${r.rekapTw2 || '-'}"`,
        `"${r.sdSemester1 || '-'}"`,
        `"${(r.keteranganTw2 || '-').replace(/"/g, '""')}"`,
        `"${r.jul || '-'}"`,
        `"${r.agu || '-'}"`,
        `"${r.sep || '-'}"`,
        `"${r.rekapTw3 || '-'}"`,
        `"${r.sdTw3 || '-'}"`,
        `"${(r.keteranganTw3 || '-').replace(/"/g, '""')}"`,
        `"${r.okt || '-'}"`,
        `"${r.nov || '-'}"`,
        `"${r.des || '-'}"`,
        `"${r.rekapTw4 || '-'}"`,
        `"${r.sdTw4 || '-'}"`,
        `"${(r.keteranganTw4 || '-').replace(/"/g, '""')}"`,
      ])
    }
  }

  const csvContent = '\uFEFF' + [headers.join(','), ...rows.map((r) => r.join(','))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  const unitLabel = selectedPkUnit.value === 'ALL' ? 'Semua_Unit' : selectedPkUnit.value
  link.setAttribute('download', `Capaian_PK_${selectedPkYear.value}_${unitLabel}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)

  triggerToast('Capaian PK berhasil diekspor ke CSV!')
}

// ========================================================
// TOAST NOTIFIKASI
// ========================================================
const toastMessage = ref('')
const showToast = ref(false)
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in relative">
    <!-- Toast Feedback -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-slate-800"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Breadcrumb Label -->
    <div class="text-xs font-semibold text-[#0B2A6F]">
      Capaian PK
    </div>

    <!-- Header & Top Dropdown Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Capaian Kinerja atas Perjanjian Kinerja
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Klik sel untuk mengisi. Teks miring = dihitung otomatis. Rekap bisa ditimpa manual.
        </p>
      </div>

      <!-- Controls: Year, Unit, Ekspor CSV -->
      <div class="flex items-center gap-2.5 self-start sm:self-auto flex-nowrap">
        <!-- Year Select -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedPkYear"
            class="appearance-none bg-white border border-slate-300 text-slate-700 hover:border-slate-400 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 cursor-pointer shadow-2xs"
          >
            <option v-for="y in pkYearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Unit Select (Admin Only per Image 3) -->
        <div v-if="!isOperatorSatker" class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedPkUnit"
            class="appearance-none bg-white border border-slate-300 text-slate-700 hover:border-slate-400 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 cursor-pointer shadow-2xs"
          >
            <option v-for="opt in pkUnitFilterOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-400">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Tombol Ekspor CSV -->
        <button
          type="button"
          @click="exportCapaianCsv"
          class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg px-3.5 py-2 cursor-pointer transition-colors shadow-2xs active:scale-95"
        >
          Ekspor CSV
        </button>
      </div>
    </div>

    <!-- Multi-column Matrix Table Surface -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto bg-white">
        <table class="w-full text-left border-collapse text-xs select-none bg-white">
          <thead>
            <tr class="text-[11px] font-bold tracking-wider">
              <!-- Regular Columns (Navy / Blue Theme) -->
              <th class="py-3 px-4 min-w-[220px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Sasaran Kinerja
              </th>
              <th class="py-3 px-4 min-w-[280px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Indikator Kinerja Kegiatan
              </th>
              <th class="py-3 px-3 text-center min-w-[90px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Target
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Jan
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Feb
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Mar
              </th>

              <!-- Rekap TW I & Keterangan (Orange Header) -->
              <th class="py-3 px-3 text-center min-w-[95px] bg-[#ea580c] text-white font-black border-r border-orange-600">
                Rekap TW I
              </th>
              <th class="py-3 px-4 min-w-[240px] bg-[#ea580c] text-white font-black border-r border-blue-600">
                Keterangan
              </th>

              <!-- TW II Columns -->
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Apr
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Mei
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Jun
              </th>
              <th class="py-3 px-3 text-center min-w-[95px] bg-[#ea580c] text-white font-black border-r border-orange-600">
                Rekap TW II
              </th>
              <!-- s.d. Semester I (Yellow Header) -->
              <th class="py-3 px-3 text-center min-w-[105px] bg-[#eab308] text-slate-900 font-black border-r border-yellow-600">
                s.d. Semester I
              </th>
              <th class="py-3 px-4 min-w-[240px] bg-[#ea580c] text-white font-black border-r border-blue-600">
                Keterangan
              </th>

              <!-- TW III Columns -->
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Jul
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Agu
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Sep
              </th>
              <th class="py-3 px-3 text-center min-w-[95px] bg-[#ea580c] text-white font-black border-r border-orange-600">
                Rekap TW III
              </th>
              <!-- s.d. TW III (Yellow Header) -->
              <th class="py-3 px-3 text-center min-w-[105px] bg-[#eab308] text-slate-900 font-black border-r border-yellow-600">
                s.d. TW III
              </th>
              <th class="py-3 px-4 min-w-[240px] bg-[#ea580c] text-white font-black border-r border-blue-600">
                Keterangan
              </th>

              <!-- TW IV Columns -->
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Okt
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Nov
              </th>
              <th class="py-3 px-2 text-center min-w-[55px] bg-[#1d4ed8] text-white border-r border-blue-600">
                Des
              </th>
              <th class="py-3 px-3 text-center min-w-[95px] bg-[#ea580c] text-white font-black border-r border-orange-600">
                Rekap TW IV
              </th>
              <!-- s.d. TW IV (Yellow Header) -->
              <th class="py-3 px-3 text-center min-w-[105px] bg-[#eab308] text-slate-900 font-black border-r border-yellow-600">
                s.d. TW IV
              </th>
              <th class="py-3 px-4 min-w-[240px] bg-[#ea580c] text-white font-black">
                Keterangan
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 bg-white">
            <template v-for="grp in filteredPkGroups" :key="grp.unitId">
              <!-- Dark Unit Group Divider Row (Admin Only per Image 3) -->
              <tr v-if="!isOperatorSatker" class="bg-[#091F4A] text-white font-bold">
                <td colspan="26" class="py-2.5 px-4 text-xs font-black tracking-wide bg-[#091F4A] text-white">
                  {{ grp.unitName }}
                </td>
              </tr>

              <!-- Indicators under this group -->
              <tr
                v-for="row in grp.rows"
                :key="row.id"
                class="bg-white hover:bg-slate-50/70 transition-colors"
              >
                <!-- Sasaran Kinerja -->
                <td class="py-3.5 px-4 font-semibold text-slate-800 text-xs border-r border-b border-slate-200 align-top leading-snug bg-white">
                  {{ row.sasaran }}
                </td>

                <!-- Indikator Kinerja Kegiatan with Edit Button (Edit button for Admin only per Image 3) -->
                <td class="py-3.5 px-4 text-slate-700 text-xs border-r border-b border-slate-200 align-top leading-snug space-y-2 bg-white">
                  <div>{{ row.indikator }}</div>
                  <div v-if="!isOperatorSatker">
                    <button
                      type="button"
                      @click.stop="openRowEdit(row)"
                      class="bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 text-[11px] font-semibold px-2.5 py-0.5 rounded border border-slate-300 transition-colors shadow-2xs cursor-pointer"
                    >
                      Edit
                    </button>
                  </div>
                </td>

                <!-- Target -->
                <td class="py-3.5 px-3 text-center font-bold text-slate-900 text-xs border-r border-b border-slate-200 align-top whitespace-nowrap bg-white">
                  {{ row.target }}
                </td>

                <!-- Jan -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'jan', 'Januari')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'jan') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.jan ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.jan || '+' }}
                </td>

                <!-- Feb -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'feb', 'Februari')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'feb') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.feb ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.feb || '+' }}
                </td>

                <!-- Mar -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'mar', 'Maret')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'mar') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.mar ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.mar || '+' }}
                </td>

                <!-- Rekap TW I (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'rekapTw1', 'Rekap TW I')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'rekapTw1') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.rekapTw1Manual ? 'font-bold text-orange-950 not-italic' : 'italic font-bold text-orange-900',
                    !row.rekapTw1 && 'text-orange-300 font-light'
                  ]"
                  :title="row.rekapTw1Manual ? 'Ditentukan manual' : 'Dihitung otomatis'"
                >
                  {{ row.rekapTw1 || '+' }}
                </td>

                <!-- Keterangan TW I (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'keteranganTw1', 'Keterangan TW I')"
                  class="py-3.5 px-3 text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors leading-snug"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'keteranganTw1') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.keteranganTw1 ? 'text-slate-700 font-normal' : 'text-orange-300 text-center font-light'
                  ]"
                >
                  {{ row.keteranganTw1 || '+' }}
                </td>

                <!-- Apr -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'apr', 'April')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'apr') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.apr ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.apr || '+' }}
                </td>

                <!-- Mei -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'mei', 'Mei')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'mei') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.mei ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.mei || '+' }}
                </td>

                <!-- Jun -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'jun', 'Juni')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'jun') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.jun ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.jun || '+' }}
                </td>

                <!-- Rekap TW II (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'rekapTw2', 'Rekap TW II')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'rekapTw2') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.rekapTw2Manual ? 'font-bold text-orange-950 not-italic' : 'italic font-bold text-orange-900',
                    !row.rekapTw2 && 'text-orange-300 font-light'
                  ]"
                >
                  {{ row.rekapTw2 || '+' }}
                </td>

                <!-- s.d. Semester I (Soft Yellow Background) -->
                <td
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fef9c3] border-r border-b border-yellow-200 font-bold text-yellow-950"
                >
                  {{ row.sdSemester1 || '+' }}
                </td>

                <!-- Keterangan TW II (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'keteranganTw2', 'Keterangan TW II')"
                  class="py-3.5 px-3 text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors leading-snug"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'keteranganTw2') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.keteranganTw2 ? 'text-slate-700 font-normal' : 'text-orange-300 text-center font-light'
                  ]"
                >
                  {{ row.keteranganTw2 || '+' }}
                </td>

                <!-- Jul -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'jul', 'Juli')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'jul') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.jul ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.jul || '+' }}
                </td>

                <!-- Agu -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'agu', 'Agustus')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'agu') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.agu ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.agu || '+' }}
                </td>

                <!-- Sep -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'sep', 'September')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'sep') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.sep ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.sep || '+' }}
                </td>

                <!-- Rekap TW III (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'rekapTw3', 'Rekap TW III')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'rekapTw3') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.rekapTw3Manual ? 'font-bold text-orange-950 not-italic' : 'italic font-bold text-orange-900',
                    !row.rekapTw3 && 'text-orange-300 font-light'
                  ]"
                >
                  {{ row.rekapTw3 || '+' }}
                </td>

                <!-- s.d. TW III (Soft Yellow Background) -->
                <td
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fef9c3] border-r border-b border-yellow-200 font-bold text-yellow-950"
                >
                  {{ row.sdTw3 || '+' }}
                </td>

                <!-- Keterangan TW III (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'keteranganTw3', 'Keterangan TW III')"
                  class="py-3.5 px-3 text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors leading-snug"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'keteranganTw3') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.keteranganTw3 ? 'text-slate-700 font-normal' : 'text-orange-300 text-center font-light'
                  ]"
                >
                  {{ row.keteranganTw3 || '+' }}
                </td>

                <!-- Okt -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'okt', 'Oktober')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'okt') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.okt ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.okt || '+' }}
                </td>

                <!-- Nov -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'nov', 'November')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'nov') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.nov ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.nov || '+' }}
                </td>

                <!-- Des -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'des', 'Desember')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top border-r border-b border-slate-200 cursor-pointer transition-colors bg-white"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'des') ? 'outline outline-2 outline-blue-600 bg-blue-50' : 'hover:bg-slate-50',
                    row.des ? 'text-slate-900 font-medium' : 'text-slate-400 font-light'
                  ]"
                >
                  {{ row.des || '+' }}
                </td>

                <!-- Rekap TW IV (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'rekapTw4', 'Rekap TW IV')"
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fff7ed] border-r border-b border-orange-200 cursor-pointer transition-colors"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'rekapTw4') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.rekapTw4Manual ? 'font-bold text-orange-950 not-italic' : 'italic font-bold text-orange-900',
                    !row.rekapTw4 && 'text-orange-300 font-light'
                  ]"
                >
                  {{ row.rekapTw4 || '+' }}
                </td>

                <!-- s.d. TW IV (Soft Yellow Background) -->
                <td
                  class="py-3.5 px-2 text-center font-mono text-xs align-top bg-[#fef9c3] border-r border-b border-yellow-200 font-bold text-yellow-950"
                >
                  {{ row.sdTw4 || '+' }}
                </td>

                <!-- Keterangan TW IV (Light Peach Background) -->
                <td
                  @click="selectAndEditCell(grp.unitId, row, 'keteranganTw4', 'Keterangan TW IV')"
                  class="py-3.5 px-3 text-xs align-top bg-[#fff7ed] border-b border-orange-200 cursor-pointer transition-colors leading-snug"
                  :class="[
                    isCellSelected(grp.unitId, row.id, 'keteranganTw4') ? 'outline outline-2 outline-orange-600 bg-orange-100' : 'hover:bg-[#ffedd5]',
                    row.keteranganTw4 ? 'text-slate-700 font-normal' : 'text-orange-300 text-center font-light'
                  ]"
                >
                  {{ row.keteranganTw4 || '+' }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL ISI / EDIT NILAI SEL -->
    <!-- ======================================================== -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div class="bg-white text-slate-900 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 animate-scale-up">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-base font-bold text-slate-900">
              Isi {{ activeCell?.label }}
            </h2>
            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">
              {{ activeCell?.row.indikator }}
            </p>
          </div>
          <button
            type="button"
            @click="closeEditModal"
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <!-- Textarea for Keterangan -->
          <div v-if="activeCell?.isKeterangan">
            <label class="block font-bold text-slate-700 mb-1">Uraian Keterangan</label>
            <textarea
              v-model="editValue"
              rows="4"
              placeholder="Masukkan penjelasan capaian..."
              class="w-full bg-white border border-slate-300 rounded-lg p-3 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <!-- Input for Rekap TW -->
          <div v-else-if="activeCell?.isRekap" class="space-y-3">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-700">Nilai Rekap</label>
              <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-orange-600 font-semibold">
                <input
                  v-model="editManualOverride"
                  type="checkbox"
                  class="rounded border-slate-300 text-orange-600 focus:ring-0"
                />
                <span>Timpa Manual</span>
              </label>
            </div>
            <input
              v-model="editValue"
              type="text"
              placeholder="Contoh: 3,94 atau 100 %"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              @keydown.enter="saveCellEdit"
            />
            <p v-if="!editManualOverride" class="text-[11px] text-slate-500 italic">
              * Rekap akan otomatis dihitung dari rata-rata bulan terkait jika opsi "Timpa Manual" tidak dicentang.
            </p>
          </div>

          <!-- Input for Monthly Value -->
          <div v-else>
            <label class="block font-bold text-slate-700 mb-1">Nilai Capaian</label>
            <input
              v-model="editValue"
              type="text"
              placeholder="Contoh: 3,94 atau 100 %"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              @keydown.enter="saveCellEdit"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="closeEditModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 bg-white hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveCellEdit"
            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#0B2A6F] hover:bg-[#1D4ED8] text-white shadow-2xs"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL EDIT SASARAN / INDIKATOR / TARGET -->
    <!-- ======================================================== -->
    <div
      v-if="showRowEditModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div class="bg-white text-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4 animate-scale-up">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="text-base font-bold text-slate-900">Edit Indikator &amp; Target</h2>
          <button
            type="button"
            @click="closeRowEditModal"
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Sasaran Kinerja</label>
            <input
              v-model="rowEditForm.sasaran"
              type="text"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Indikator Kinerja Kegiatan</label>
            <input
              v-model="rowEditForm.indikator"
              type="text"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Target PK</label>
            <input
              v-model="rowEditForm.target"
              type="text"
              placeholder="Contoh: 100 % atau 3,90 Indeks"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="closeRowEditModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 bg-white hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveRowEdit"
            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#0B2A6F] hover:bg-[#1D4ED8] text-white shadow-2xs"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
