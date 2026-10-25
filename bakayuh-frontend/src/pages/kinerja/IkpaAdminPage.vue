<script setup lang="ts">
import { ref, computed } from 'vue'

const selectedYear = ref(2026)
const yearOptions = [2026, 2025, 2024]

const selectedMonth = ref('Oktober')
const monthOptions = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
]

interface SatkerConfig {
  id: string
  label: string
  name: string
  kode: string
}

interface IndicatorTarget {
  key: string
  label: string
  target: number
}

// 6 Satker configuration (matching Image 1 & 2)
const satkersConfig = ref<SatkerConfig[]>([
  { id: 'setjen', label: 'Setjen', name: 'Setjen', kode: '692016' },
  { id: 'ahu', label: 'AHU', name: 'Ditjen AHU', kode: '692056' },
  { id: 'ki', label: 'KI', name: 'Ditjen KI', kode: '693003' },
  { id: 'pp', label: 'PP', name: 'Ditjen PP', kode: '693036' },
  { id: 'bphn', label: 'BPHN', name: 'BPHN', kode: '693104' },
  { id: 'bsk', label: 'BSK', name: 'BSK', kode: '693138' },
])

// Target per indicator (default 96 per Image 2)
const indicatorTargets = ref<IndicatorTarget[]>([
  { key: 'revisiDipa', label: 'Revisi DIPA', target: 96 },
  { key: 'deviasiHal3', label: 'Deviasi Halaman III DIPA', target: 96 },
  { key: 'penyerapanAnggaran', label: 'Penyerapan Anggaran', target: 96 },
  { key: 'belanjaKontraktual', label: 'Belanja Kontraktual', target: 96 },
  { key: 'penyelesaianTagihan', label: 'Penyelesaian Tagihan', target: 96 },
  { key: 'pengelolaanUpTup', label: 'Pengelolaan UP dan TUP', target: 96 },
  { key: 'capaianOutput', label: 'Capaian Output', target: 96 },
])

// IKPA Data records per Satker & Month
// Key: `${year}-${month}-${satkerId}`
interface IkpaRecord {
  revisiDipa: number | string
  deviasiHal3: number | string
  penyerapanAnggaran: number | string
  belanjaKontraktual: number | string
  penyelesaianTagihan: number | string
  pengelolaanUpTup: number | string
  capaianOutput: number | string
  nilaiAkhir?: number | string
}

const ikpaDatabase = ref<Record<string, IkpaRecord>>({
  // Initial demo data for September 2026 (sample filled month)
  '2026-September-setjen': { revisiDipa: 100, deviasiHal3: 93.27, penyerapanAnggaran: 84.3, belanjaKontraktual: 100, penyelesaianTagihan: 100, pengelolaanUpTup: 99.97, capaianOutput: 100, nilaiAkhir: 95.85 },
  '2026-September-ahu': { revisiDipa: 100, deviasiHal3: 100, penyerapanAnggaran: 97.12, belanjaKontraktual: 96.67, penyelesaianTagihan: 100, pengelolaanUpTup: '-', capaianOutput: 100, nilaiAkhir: 98.99 },
  '2026-September-ki': { revisiDipa: 75, deviasiHal3: 100, penyerapanAnggaran: 98.89, belanjaKontraktual: 100, penyelesaianTagihan: 100, pengelolaanUpTup: '-', capaianOutput: 100, nilaiAkhir: 96.98 },
  '2026-September-pp': { revisiDipa: 100, deviasiHal3: 100, penyerapanAnggaran: 100, belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: 100, capaianOutput: 100, nilaiAkhir: 100 },
  '2026-September-bphn': { revisiDipa: 100, deviasiHal3: 89.7, penyerapanAnggaran: 76.72, belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: 94.61, capaianOutput: 100, nilaiAkhir: 91.58 },
  '2026-September-bsk': { revisiDipa: 100, deviasiHal3: 100, penyerapanAnggaran: 86.23, belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: 91.86, capaianOutput: 100, nilaiAkhir: 95.54 },

  // Oktober 2026 starts blank '-' exactly as shown in User Image 1
  '2026-Oktober-setjen': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
  '2026-Oktober-ahu': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
  '2026-Oktober-ki': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
  '2026-Oktober-pp': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
  '2026-Oktober-bphn': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
  '2026-Oktober-bsk': { revisiDipa: '-', deviasiHal3: '-', penyerapanAnggaran: '-', belanjaKontraktual: '-', penyelesaianTagihan: '-', pengelolaanUpTup: '-', capaianOutput: '-', nilaiAkhir: '-' },
})

function getRecordKey(satkerId: string): string {
  return `${selectedYear.value}-${selectedMonth.value}-${satkerId}`
}

function getSatkerRecord(satkerId: string): IkpaRecord {
  const key = getRecordKey(satkerId)
  if (!ikpaDatabase.value[key]) {
    ikpaDatabase.value[key] = {
      revisiDipa: '-',
      deviasiHal3: '-',
      penyerapanAnggaran: '-',
      belanjaKontraktual: '-',
      penyelesaianTagihan: '-',
      pengelolaanUpTup: '-',
      capaianOutput: '-',
      nilaiAkhir: '-',
    }
  }
  return ikpaDatabase.value[key]
}

// Compute table rows based on current satkersConfig & active month/year
const currentTableRows = computed(() => {
  return satkersConfig.value.map((s, idx) => {
    const rec = getSatkerRecord(s.id)

    // Calculate Nilai Akhir automatically if not explicitly given or compute on the fly
    const numericVals: number[] = []
    const keys: (keyof IkpaRecord)[] = [
      'revisiDipa',
      'deviasiHal3',
      'penyerapanAnggaran',
      'belanjaKontraktual',
      'penyelesaianTagihan',
      'pengelolaanUpTup',
      'capaianOutput',
    ]

    keys.forEach((k) => {
      const v = rec[k]
      if (typeof v === 'number') {
        numericVals.push(v)
      } else if (typeof v === 'string' && v !== '-' && v.trim() !== '') {
        const num = parseFloat(v.replace(',', '.'))
        if (!isNaN(num)) numericVals.push(num)
      }
    })

    let calculatedNilaiAkhir: number | string = '-'
    if (numericVals.length > 0) {
      const avg = numericVals.reduce((a, b) => a + b, 0) / numericVals.length
      calculatedNilaiAkhir = parseFloat(avg.toFixed(2))
    }

    const nilaiAkhir = rec.nilaiAkhir !== undefined && rec.nilaiAkhir !== '-' && rec.nilaiAkhir !== ''
      ? rec.nilaiAkhir
      : calculatedNilaiAkhir

    return {
      no: idx + 1,
      id: s.id,
      satker: s.name,
      kode: s.kode,
      revisiDipa: rec.revisiDipa,
      deviasiHal3: rec.deviasiHal3,
      penyerapanAnggaran: rec.penyerapanAnggaran,
      belanjaKontraktual: rec.belanjaKontraktual,
      penyelesaianTagihan: rec.penyelesaianTagihan,
      pengelolaanUpTup: rec.pengelolaanUpTup,
      capaianOutput: rec.capaianOutput,
      nilaiAkhir,
    }
  })
})

interface IkpaSummaryRow {
  label: string
  revisiDipa: string
  deviasiHal3: string
  penyerapanAnggaran: string
  belanjaKontraktual: string
  penyelesaianTagihan: string
  pengelolaanUpTup: string
  capaianOutput: string
  nilaiAkhir: string
}

// Calculate Bottom Row: "Nilai Rata Kantor Wilayah"
const summaryAverageRow = computed<IkpaSummaryRow>(() => {
  const keys: (keyof IkpaRecord)[] = [
    'revisiDipa',
    'deviasiHal3',
    'penyerapanAnggaran',
    'belanjaKontraktual',
    'penyelesaianTagihan',
    'pengelolaanUpTup',
    'capaianOutput',
  ]

  const result: IkpaSummaryRow = {
    label: 'Nilai Rata Kantor Wilayah',
    revisiDipa: '-',
    deviasiHal3: '-',
    penyerapanAnggaran: '-',
    belanjaKontraktual: '-',
    penyelesaianTagihan: '-',
    pengelolaanUpTup: '-',
    capaianOutput: '-',
    nilaiAkhir: '-',
  }

  keys.forEach((key) => {
    const values: number[] = []
    currentTableRows.value.forEach((r) => {
      const v = r[key as keyof typeof r]
      if (typeof v === 'number') {
        values.push(v)
      } else if (typeof v === 'string' && v !== '-' && v.trim() !== '') {
        const num = parseFloat(v.replace(',', '.'))
        if (!isNaN(num)) values.push(num)
      }
    })

    if (values.length > 0) {
      const avg = values.reduce((a, b) => a + b, 0) / values.length
      result[key as keyof IkpaSummaryRow] = avg.toFixed(2).replace('.', ',')
    } else {
      result[key as keyof IkpaSummaryRow] = '-'
    }
  })

  // Nilai Akhir average
  const finalVals: number[] = []
  currentTableRows.value.forEach((r) => {
    const v = r.nilaiAkhir
    if (typeof v === 'number') {
      finalVals.push(v)
    } else if (typeof v === 'string' && v !== '-' && v.trim() !== '') {
      const num = parseFloat(v.replace(',', '.'))
      if (!isNaN(num)) finalVals.push(num)
    }
  })

  if (finalVals.length > 0) {
    const avg = finalVals.reduce((a, b) => a + b, 0) / finalVals.length
    result.nilaiAkhir = avg.toFixed(2).replace('.', ',')
  } else {
    result.nilaiAkhir = '-'
  }

  return result
})

// Cell color formatting based on Image 1 & 2 rules:
// - Sempurna: num >= 100 -> bg-[#DCE8F7]
// - Memenuhi Target: num >= target (96) and num < 100 -> bg-[#98D65F]
// - Belum Memenuhi Target: num < target -> bg-[#FEF08A]
// - Empty / '-': bg-white text-slate-400
function getCellClass(val: number | string, indicatorKey?: string): string {
  if (val === '-' || val === null || val === undefined || val === '') {
    return 'bg-white text-slate-400 font-normal'
  }
  const num = typeof val === 'number' ? val : parseFloat(val.toString().replace(',', '.'))
  if (isNaN(num)) return 'bg-white text-slate-400 font-normal'

  if (num >= 100) {
    return 'bg-[#DCE8F7] text-slate-900 font-medium'
  }

  // Get configured target for this indicator (default 96)
  let target = 96
  if (indicatorKey) {
    const targetObj = indicatorTargets.value.find((t) => t.key === indicatorKey)
    if (targetObj) target = targetObj.target
  }

  if (num >= target) {
    return 'bg-[#98D65F] text-slate-900 font-medium'
  }

  return 'bg-[#FEF08A] text-slate-900 font-medium'
}

function formatValue(val: number | string): string {
  if (typeof val === 'number') {
    return val.toString().replace('.', ',')
  }
  return val || '-'
}

// ==========================================
// MODAL 1: Atur Satker & Target IKPA (Image 2)
// ==========================================
const showConfigModal = ref(false)
const bulkTargetInput = ref<number | ''>(96)

// Temporary editing state for config modal
const tempSatkers = ref<SatkerConfig[]>([])
const tempTargets = ref<IndicatorTarget[]>([])

function openConfigModal() {
  tempSatkers.value = JSON.parse(JSON.stringify(satkersConfig.value))
  tempTargets.value = JSON.parse(JSON.stringify(indicatorTargets.value))
  bulkTargetInput.value = 96
  showConfigModal.value = true
}

function closeConfigModal() {
  showConfigModal.value = false
}

function applyBulkTarget() {
  if (typeof bulkTargetInput.value === 'number') {
    const val = bulkTargetInput.value
    tempTargets.value.forEach((t) => {
      t.target = val
    })
  }
}

function saveConfigModal() {
  satkersConfig.value = JSON.parse(JSON.stringify(tempSatkers.value))
  indicatorTargets.value = JSON.parse(JSON.stringify(tempTargets.value))
  showConfigModal.value = false
}

// ==========================================
// MODAL 2: Isi Nilai IKPA (Per Satker Row)
// ==========================================
const showIsiModal = ref(false)
const activeEditingSatker = ref<SatkerConfig | null>(null)
const tempEditRecord = ref<IkpaRecord>({
  revisiDipa: '-',
  deviasiHal3: '-',
  penyerapanAnggaran: '-',
  belanjaKontraktual: '-',
  penyelesaianTagihan: '-',
  pengelolaanUpTup: '-',
  capaianOutput: '-',
  nilaiAkhir: '-',
})

function openIsiModal(satkerId: string) {
  const satker = satkersConfig.value.find((s) => s.id === satkerId)
  if (!satker) return
  activeEditingSatker.value = satker
  const current = getSatkerRecord(satker.id)
  tempEditRecord.value = { ...current }
  showIsiModal.value = true
}

function closeIsiModal() {
  showIsiModal.value = false
  activeEditingSatker.value = null
}

function saveIsiModal() {
  if (!activeEditingSatker.value) return
  const key = getRecordKey(activeEditingSatker.value.id)
  ikpaDatabase.value[key] = { ...tempEditRecord.value }
  showIsiModal.value = false
  activeEditingSatker.value = null
}

// ==========================================
// EKSPOR CSV
// ==========================================
function exportCsv() {
  const headers = [
    'No',
    'Nama Satker/DIPA',
    'Kode Satker',
    'Revisi DIPA',
    'Deviasi Halaman III DIPA',
    'Penyerapan Anggaran',
    'Belanja Kontraktual',
    'Penyelesaian Tagihan',
    'Pengelolaan UP dan TUP',
    'Capaian Output',
    `Nilai Akhir ${selectedMonth.value}`,
  ]

  const rows = currentTableRows.value.map((r) => [
    r.no,
    `"${r.satker}"`,
    `"${r.kode}"`,
    formatValue(r.revisiDipa),
    formatValue(r.deviasiHal3),
    formatValue(r.penyerapanAnggaran),
    formatValue(r.belanjaKontraktual),
    formatValue(r.penyelesaianTagihan),
    formatValue(r.pengelolaanUpTup),
    formatValue(r.capaianOutput),
    formatValue(r.nilaiAkhir),
  ])

  // Summary Row
  rows.push([
    '',
    `"${summaryAverageRow.value.label || 'Nilai Rata Kantor Wilayah'}"`,
    '',
    summaryAverageRow.value.revisiDipa,
    summaryAverageRow.value.deviasiHal3,
    summaryAverageRow.value.penyerapanAnggaran,
    summaryAverageRow.value.belanjaKontraktual,
    summaryAverageRow.value.penyelesaianTagihan,
    summaryAverageRow.value.pengelolaanUpTup,
    summaryAverageRow.value.capaianOutput,
    summaryAverageRow.value.nilaiAkhir,
  ])

  const csvContent = [headers.join(','), ...rows.map((r) => r.join(','))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `IKPA_${selectedMonth.value}_${selectedYear.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
</script>

<template>
  <div class="space-y-6 pb-16 animate-fade-in">
    <!-- Header with Title, Month/Year Filter, and Action Buttons (Matches Image 1) -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Indikator Kinerja Pelaksanaan Anggaran (IKPA)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Khusus admin. Rata-rata dihitung otomatis dari nilai yang terisi.
        </p>
      </div>

      <!-- Controls: Year, Month, Atur Satker & Target, Ekspor CSV -->
      <div class="flex items-center gap-2.5 flex-wrap self-start lg:self-auto">
        <!-- Year Selector with shifted chevron -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedYear"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Month Selector with shifted chevron -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedMonth"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="m in monthOptions" :key="m" :value="m">{{ m }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Button: Atur Satker & Target (Image 1) -->
        <button
          type="button"
          @click="openConfigModal"
          class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3.5 py-2 text-xs sm:text-sm font-semibold shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
        >
          Atur Satker &amp; Target
        </button>

        <!-- Button: Ekspor CSV (Image 1) -->
        <button
          type="button"
          @click="exportCsv"
          class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3.5 py-2 text-xs sm:text-sm font-semibold shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1.5"
        >
          Ekspor CSV
        </button>
      </div>
    </div>

    <!-- Official Multi-tier IKPA Matrix Table (Matches Image 1) -->
    <div class="bg-white rounded-none border border-slate-300 shadow-2xs overflow-hidden">
      <div class="table-container overflow-x-auto">
        <table class="w-full text-center border-collapse text-xs">
          <thead>
            <!-- Level 1 Header (Dark Navy #091F4A with Crisp White Text per User Design) -->
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider">
              <th rowspan="2" class="py-3 px-2 border border-white/20 w-12 align-middle">
                No
              </th>
              <th rowspan="2" class="py-3 px-4 border border-white/20 text-left min-w-[130px] align-middle">
                Nama Satker/DIPA
              </th>
              <th rowspan="2" class="py-3 px-3 border border-white/20 w-24 align-middle">
                Kode Satker
              </th>
              <th colspan="2" class="py-2.5 px-3 border border-white/20 align-middle">
                Kualitas Perencanaan Anggaran
              </th>
              <th colspan="4" class="py-2.5 px-3 border border-white/20 align-middle">
                Kualitas Pelaksanaan Anggaran
              </th>
              <th colspan="1" class="py-2.5 px-3 border border-white/20 align-middle">
                Kualitas Hasil Pelaksanaan Anggaran
              </th>
              <th rowspan="2" class="py-3 px-4 border border-white/20 min-w-[100px] align-middle">
                Nilai Akhir<br />{{ selectedMonth }}
              </th>
              <th rowspan="2" class="py-3 px-3 border border-white/20 w-16 align-middle">
                Isi
              </th>
            </tr>
            <!-- Level 2 Sub-headers (Dark Navy #0C2B64 with White Text) -->
            <tr class="bg-[#0C2B64] text-white text-[11px] font-bold uppercase tracking-wider">
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[75px] align-middle">
                Revisi DIPA
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                Deviasi Halaman III DIPA
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                Penyerapan Anggaran
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[85px] align-middle">
                Belanja Kontraktual
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[85px] align-middle">
                Penyelesaian Tagihan
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                Pengelolaan UP dan TUP
              </th>
              <th class="py-2.5 px-2.5 border border-white/20 min-w-[90px] align-middle">
                Capaian Output
              </th>
            </tr>
          </thead>
          <tbody>
            <!-- 6 Satker Rows (Matches Image 1) -->
            <tr
              v-for="row in currentTableRows"
              :key="row.no"
              class="transition-colors hover:brightness-95"
            >
              <td class="py-3 px-2 border border-slate-300 text-slate-700 font-mono text-xs bg-white">
                {{ row.no }}
              </td>
              <td class="py-3 px-4 border border-slate-300 text-left font-bold text-slate-800 text-xs bg-white">
                {{ row.satker }}
              </td>
              <td class="py-3 px-3 border border-slate-300 text-slate-600 font-mono text-xs bg-white">
                {{ row.kode }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.revisiDipa, 'revisiDipa')">
                {{ formatValue(row.revisiDipa) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.deviasiHal3, 'deviasiHal3')">
                {{ formatValue(row.deviasiHal3) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.penyerapanAnggaran, 'penyerapanAnggaran')">
                {{ formatValue(row.penyerapanAnggaran) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.belanjaKontraktual, 'belanjaKontraktual')">
                {{ formatValue(row.belanjaKontraktual) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.penyelesaianTagihan, 'penyelesaianTagihan')">
                {{ formatValue(row.penyelesaianTagihan) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.pengelolaanUpTup, 'pengelolaanUpTup')">
                {{ formatValue(row.pengelolaanUpTup) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getCellClass(row.capaianOutput, 'capaianOutput')">
                {{ formatValue(row.capaianOutput) }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono text-xs font-bold" :class="getCellClass(row.nilaiAkhir)">
                {{ formatValue(row.nilaiAkhir) }}
              </td>
              <!-- "Isi" Action Button (Image 1) -->
              <td class="py-2.5 px-2 border border-slate-300 bg-white text-center">
                <button
                  type="button"
                  @click="openIsiModal(row.id)"
                  class="bg-[#1D4ED8] hover:bg-blue-700 text-white font-bold text-xs px-3 py-1 rounded-md shadow-xs transition-colors cursor-pointer"
                >
                  Isi
                </button>
              </td>
            </tr>

            <!-- Summary Average Row (Matches Image 1) -->
            <tr class="bg-[#DCE8F3] text-slate-900 font-bold text-xs">
              <td colspan="3" class="py-3 px-4 border border-slate-300 text-center font-bold">
                Nilai Rata Kantor Wilayah
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.revisiDipa, 'revisiDipa')">
                {{ summaryAverageRow.revisiDipa }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.deviasiHal3, 'deviasiHal3')">
                {{ summaryAverageRow.deviasiHal3 }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.penyerapanAnggaran, 'penyerapanAnggaran')">
                {{ summaryAverageRow.penyerapanAnggaran }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.belanjaKontraktual, 'belanjaKontraktual')">
                {{ summaryAverageRow.belanjaKontraktual }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.penyelesaianTagihan, 'penyelesaianTagihan')">
                {{ summaryAverageRow.penyelesaianTagihan }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.pengelolaanUpTup, 'pengelolaanUpTup')">
                {{ summaryAverageRow.pengelolaanUpTup }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.capaianOutput, 'capaianOutput')">
                {{ summaryAverageRow.capaianOutput }}
              </td>
              <td class="py-3 px-2 border border-slate-300 font-mono font-bold" :class="getCellClass(summaryAverageRow.nilaiAkhir)">
                {{ summaryAverageRow.nilaiAkhir }}
              </td>
              <td class="py-3 px-2 border border-slate-300 bg-[#DCE8F3]">
                <!-- Blank under Isi column -->
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- IKPA Color Legend (Matches Image 1) -->
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-700 pt-1">
      <div class="flex items-center gap-2">
        <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#DCE8F7] inline-block" />
        <span>Sempurna (100)</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#98D65F] inline-block" />
        <span>Memenuhi Target (96 s.d. kurang dari 100)</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#FEF08A] inline-block" />
        <span>Belum Memenuhi Target (kurang dari 96)</span>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: ATUR SATKER & TARGET IKPA (Identical to Image 2) -->
    <!-- ======================================================== -->
    <div
      v-if="showConfigModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-2xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-900">
            Atur Satker &amp; Target IKPA
          </h3>
          <button
            type="button"
            @click="closeConfigModal"
            class="text-slate-400 hover:text-slate-600 transition-colors p-1"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="px-6 py-4 overflow-y-auto space-y-5 scrollbar-thin text-xs">
          <p class="text-slate-500">
            Nama dan kode satker tampil di tabel. Target dipakai untuk warna hijau/kuning.
          </p>

          <!-- 6 Satker Inputs (Name and Code per Image 2) -->
          <div class="space-y-3.5">
            <div
              v-for="satker in tempSatkers"
              :key="satker.id"
              class="space-y-1"
            >
              <label class="font-bold text-slate-800 block text-[11px]">
                {{ satker.label }}
              </label>
              <div class="grid grid-cols-3 gap-2.5">
                <input
                  v-model="satker.name"
                  type="text"
                  placeholder="Nama Satker"
                  class="col-span-2 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <input
                  v-model="satker.kode"
                  type="text"
                  placeholder="Kode Satker"
                  class="col-span-1 bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Section: Target per Indikator (Image 2) -->
          <div class="pt-4 border-t border-slate-100 space-y-3">
            <div>
              <h4 class="font-bold text-slate-900 text-xs">
                Target per indikator (nilai &ge; target = hijau, 100 = biru)
              </h4>
            </div>

            <!-- Bulk Setter (mis. 96 & Samakan semua indikator per Image 2) -->
            <div class="flex items-center gap-2">
              <input
                v-model.number="bulkTargetInput"
                type="number"
                placeholder="mis. 96"
                class="w-24 bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                type="button"
                @click="applyBulkTarget"
                class="border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-colors cursor-pointer"
              >
                Samakan semua indikator
              </button>
            </div>

            <!-- Individual Target Inputs (Image 2) -->
            <div class="space-y-3 pt-2">
              <div
                v-for="targetObj in tempTargets"
                :key="targetObj.key"
                class="space-y-1"
              >
                <label class="font-bold text-slate-700 block text-[11px]">
                  {{ targetObj.label }}
                </label>
                <input
                  v-model.number="targetObj.target"
                  type="number"
                  class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer (Image 2) -->
        <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-end gap-2.5 bg-slate-50/50">
          <button
            type="button"
            @click="closeConfigModal"
            class="border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 rounded-lg px-4 py-2 text-xs font-semibold transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveConfigModal"
            class="bg-[#1D4ED8] hover:bg-blue-700 text-white rounded-lg px-5 py-2 text-xs font-bold transition-colors cursor-pointer shadow-xs"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: ISI NILAI IKPA SATKER (Triggered by "Isi" button) -->
    <!-- ======================================================== -->
    <div
      v-if="showIsiModal && activeEditingSatker"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-2xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-slate-900">
              Isi Nilai IKPA - {{ activeEditingSatker.name }}
            </h3>
            <p class="text-[11px] text-slate-500">
              Periode {{ selectedMonth }} {{ selectedYear }} &bull; Kode Satker {{ activeEditingSatker.kode }}
            </p>
          </div>
          <button
            type="button"
            @click="closeIsiModal"
            class="text-slate-400 hover:text-slate-600 transition-colors p-1"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Body (Inputs for 7 Indicators + Nilai Akhir) -->
        <div class="px-6 py-4 overflow-y-auto space-y-3.5 scrollbar-thin text-xs">
          <p class="text-slate-500 text-[11px]">
            Masukkan angka capaian (0-100) atau tanda strip <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">-</code> jika indikator tidak berlaku untuk satker ini.
          </p>

          <div
            v-for="targetObj in indicatorTargets"
            :key="targetObj.key"
            class="space-y-1"
          >
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-800 text-[11px]">
                {{ targetObj.label }}
              </label>
              <span class="text-[10px] text-slate-400">Target: {{ targetObj.target }}</span>
            </div>
            <input
              v-model="tempEditRecord[targetObj.key as keyof IkpaRecord]"
              type="text"
              placeholder="0 s.d. 100 atau -"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
            />
          </div>

          <!-- Nilai Akhir Override (Optional) -->
          <div class="pt-2 border-t border-slate-100 space-y-1">
            <label class="font-bold text-slate-800 text-[11px] flex items-center justify-between">
              <span>Nilai Akhir (Opsional / Otomatis dihitung jika kosong)</span>
            </label>
            <input
              v-model="tempEditRecord.nilaiAkhir"
              type="text"
              placeholder="Otomatis atau isi nilai manual"
              class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
            />
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-end gap-2.5 bg-slate-50/50">
          <button
            type="button"
            @click="closeIsiModal"
            class="border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 rounded-lg px-4 py-2 text-xs font-semibold transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveIsiModal"
            class="bg-[#1D4ED8] hover:bg-blue-700 text-white rounded-lg px-5 py-2 text-xs font-bold transition-colors cursor-pointer shadow-xs"
          >
            Simpan Nilai
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
