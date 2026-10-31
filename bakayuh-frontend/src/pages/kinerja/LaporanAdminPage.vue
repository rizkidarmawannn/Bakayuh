<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

const { isOperatorSatker, userUnitCode } = useAuth()

type LaporanMode = 'bulanan' | 'triwulan'
const laporanPeriodMode = ref<LaporanMode>('bulanan')
const laporanSelectedYear = ref<number>(2026)
const laporanYearOptions = [2026, 2025, 2024]

interface LaporanUnitRow {
  unitKode: string
  unitName: string
  bulananStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  triwulanStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  kepatuhan: number
}

const laporanBulananCols = [
  { key: 'jan', label: 'JAN', labelLong: 'Januari', date: '10/02' },
  { key: 'feb', label: 'FEB', labelLong: 'Februari', date: '10/03' },
  { key: 'mar', label: 'MAR', labelLong: 'Maret', date: '10/04' },
  { key: 'apr', label: 'APR', labelLong: 'April', date: '10/05' },
  { key: 'mei', label: 'MEI', labelLong: 'Mei', date: '10/06' },
  { key: 'jun', label: 'JUN', labelLong: 'Juni', date: '10/07' },
  { key: 'jul', label: 'JUL', labelLong: 'Juli', date: '10/08' },
  { key: 'agu', label: 'AGU', labelLong: 'Agustus', date: '10/09' },
  { key: 'sep', label: 'SEP', labelLong: 'September', date: '10/10' },
  { key: 'okt', label: 'OKT', labelLong: 'Oktober', date: '10/11' },
  { key: 'nov', label: 'NOV', labelLong: 'November', date: '10/12' },
  { key: 'des', label: 'DES', labelLong: 'Desember', date: '10/01' },
]

const laporanTriwulanCols = [
  { key: 'tw1', label: 'TW I', labelLong: 'Triwulan I', date: '10/04' },
  { key: 'tw2', label: 'TW II', labelLong: 'Triwulan II', date: '10/07' },
  { key: 'tw3', label: 'TW III', labelLong: 'Triwulan III', date: '10/10' },
  { key: 'tw4', label: 'TW IV', labelLong: 'Triwulan IV', date: '10/01' },
]

const laporanUnitsData = ref<LaporanUnitRow[]>([
  {
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
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
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
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
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
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
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
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
])

const defaultFallbackRow: LaporanUnitRow = {
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
}

// Target unit for operator satker
const currentOperatorUnitRow = computed<LaporanUnitRow>(() => {
  const target = (userUnitCode.value || 'AHU').toUpperCase()
  const found = laporanUnitsData.value.find((u) => u.unitKode.toUpperCase() === target)
  return found ?? defaultFallbackRow
})

// Metrics computed dynamically based on role
const summaryMetrics = computed(() => {
  if (isOperatorSatker.value) {
    const row = currentOperatorUnitRow.value
    const statusMap = laporanPeriodMode.value === 'bulanan' ? row.bulananStatus : row.triwulanStatus
    const values = Object.values(statusMap)
    const diterima = values.filter((v) => v === 'diterima').length
    const menunggu = values.filter((v) => v === 'menunggu').length
    const perbaikan = values.filter((v) => v === 'perbaikan').length
    const terlambat = values.filter((v) => v === 'terlambat').length
    const totalDue = diterima + menunggu + perbaikan + terlambat
    const percent = totalDue > 0 ? Math.round((diterima / totalDue) * 100) : 0
    return {
      percent,
      diterima,
      menunggu,
      perbaikan,
      terlambat,
      totalDue: totalDue || (laporanPeriodMode.value === 'bulanan' ? 9 : 3),
    }
  }

  // Admin: across all units
  let totalDiterima = 0
  let totalMenunggu = 0
  let totalPerbaikan = 0
  let totalTerlambat = 0

  for (const u of laporanUnitsData.value) {
    const statusMap = laporanPeriodMode.value === 'bulanan' ? u.bulananStatus : u.triwulanStatus
    for (const v of Object.values(statusMap)) {
      if (v === 'diterima') totalDiterima++
      else if (v === 'menunggu') totalMenunggu++
      else if (v === 'perbaikan') totalPerbaikan++
      else if (v === 'terlambat') totalTerlambat++
    }
  }
  const totalDue = totalDiterima + totalMenunggu + totalPerbaikan + totalTerlambat
  const percent = totalDue > 0 ? Math.round((totalDiterima / totalDue) * 100) : 0
  return {
    percent,
    diterima: totalDiterima,
    menunggu: totalMenunggu,
    perbaikan: totalPerbaikan,
    terlambat: totalTerlambat,
    totalDue: totalDue || (laporanPeriodMode.value === 'bulanan' ? 54 : 18),
  }
})

// ========================================================
// MODAL UNGGAH LAPORAN (OPERATOR SATKER)
// ========================================================
const showUploadModal = ref(false)
const uploadTargetPeriod = ref('')
const uploadTargetKey = ref('')
const uploadFileName = ref('')
const uploadNotes = ref('')
const showToast = ref(false)
const toastMessage = ref('')

function openUploadModal(periodLabel: string, periodKey: string) {
  uploadTargetPeriod.value = `${periodLabel} ${laporanSelectedYear.value}`
  uploadTargetKey.value = periodKey
  uploadFileName.value = ''
  uploadNotes.value = ''
  showUploadModal.value = true
}

function closeUploadModal() {
  showUploadModal.value = false
}

function handleFileSelect(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    uploadFileName.value = input.files[0].name
  }
}

function submitLaporan() {
  if (isOperatorSatker.value && uploadTargetKey.value) {
    const row = currentOperatorUnitRow.value
    if (laporanPeriodMode.value === 'bulanan') {
      row.bulananStatus[uploadTargetKey.value] = 'menunggu'
    } else {
      row.triwulanStatus[uploadTargetKey.value] = 'menunggu'
    }
  }

  triggerToast('Laporan berhasil diunggah dan dikirim untuk verifikasi!')
  closeUploadModal()
}

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

    <!-- Header with Title & Year Filter (Matching Screenshot) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Laporan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Tenggat: tanggal 10 bulan berikutnya setelah periode berakhir
        </p>
      </div>
      <div class="self-start sm:self-auto">
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="laporanSelectedYear"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="y in laporanYearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Period Mode Switcher (Bulanan / Triwulan Pills) -->
    <div class="flex items-center gap-2 sm:gap-2.5">
      <button
        type="button"
        class="px-5 py-2 rounded-full text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer border"
        :class="laporanPeriodMode === 'bulanan'
          ? 'bg-[#1D4ED8] text-white border-[#1D4ED8] shadow-sm'
          : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
        @click="laporanPeriodMode = 'bulanan'"
      >
        Laporan Bulanan
      </button>
      <button
        type="button"
        class="px-5 py-2 rounded-full text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer border"
        :class="laporanPeriodMode === 'triwulan'
          ? 'bg-[#1D4ED8] text-white border-[#1D4ED8] shadow-sm'
          : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
        @click="laporanPeriodMode = 'triwulan'"
      >
        Laporan Triwulan
      </button>
    </div>

    <!-- Summary Metric Horizontal Card (Matches Image 2) -->
    <div class="space-y-2">
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-6 sm:p-7">
        <div class="flex flex-wrap items-center gap-8 lg:gap-14">
          <!-- Circular Progress Gauge on Left -->
          <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path
                class="text-slate-100"
                stroke-width="3.5"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="text-blue-600 transition-all duration-500"
                stroke-width="3.5"
                :stroke-dasharray="`${summaryMetrics.percent}, 100`"
                stroke-linecap="round"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
              <span class="text-xs sm:text-sm font-black text-[#0B2A6F]">{{ summaryMetrics.percent }}%</span>
              <span class="text-[9px] text-slate-400 font-medium leading-none mt-0.5">diterima</span>
            </div>
          </div>

          <!-- Metric Items -->
          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">{{ summaryMetrics.diterima }}</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Diterima</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">{{ summaryMetrics.menunggu }}</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Menunggu verifikasi</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">{{ summaryMetrics.perbaikan }}</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Perlu perbaikan</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-rose-600">
              {{ summaryMetrics.terlambat }}
            </span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Terlambat</span>
          </div>
        </div>
      </div>
      <!-- Note under the card -->
      <p class="text-xs text-slate-500">
        Dihitung dari {{ summaryMetrics.totalDue }} laporan yang sudah jatuh tempo atau sudah dikirim.
      </p>
    </div>

    <!-- Case A: Operator Satker Grid View (Matches Image 2) -->
    <div v-if="isOperatorSatker">
      <!-- Bulanan Grid (12 Months, 2x6 cards) -->
      <div v-if="laporanPeriodMode === 'bulanan'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-3.5">
        <div
          v-for="m in laporanBulananCols"
          :key="m.key"
          class="bg-white rounded-xl border border-slate-200/90 p-4 flex flex-col justify-between min-h-[145px] text-center space-y-3 shadow-2xs hover:border-slate-300 transition-colors"
        >
          <!-- Top Row: Month Name & Deadline -->
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-800 text-xs">{{ m.labelLong }}</span>
            <span class="text-[10px] text-slate-400 font-mono">tenggat {{ m.date }}</span>
          </div>

          <!-- Middle Badge -->
          <div class="flex items-center justify-center my-auto">
            <!-- Terlambat -->
            <div
              v-if="currentOperatorUnitRow.bulananStatus[m.key] === 'terlambat'"
              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50/50 text-rose-600 text-xs font-semibold"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Terlambat</span>
            </div>

            <!-- Menunggu Verifikasi -->
            <div
              v-else-if="currentOperatorUnitRow.bulananStatus[m.key] === 'menunggu'"
              class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-xs font-semibold"
            >
              <span class="w-2 h-2 rounded-full bg-amber-500"></span>
              <span>Menunggu</span>
            </div>

            <!-- Diterima -->
            <div
              v-else-if="currentOperatorUnitRow.bulananStatus[m.key] === 'diterima'"
              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 text-xs font-semibold"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span>Diterima</span>
            </div>

            <!-- Perlu Perbaikan -->
            <div
              v-else-if="currentOperatorUnitRow.bulananStatus[m.key] === 'perbaikan'"
              class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-orange-200 bg-orange-50 text-orange-700 text-xs font-semibold"
            >
              <span class="font-bold">!</span>
              <span>Perlu perbaikan</span>
            </div>

            <!-- Belum Waktunya -->
            <div
              v-else
              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 text-xs font-semibold"
            >
              <span>-</span>
              <span>Belum waktunya</span>
            </div>
          </div>

          <!-- Bottom Action Link -->
          <div>
            <button
              v-if="currentOperatorUnitRow.bulananStatus[m.key] === 'terlambat' || currentOperatorUnitRow.bulananStatus[m.key] === 'perbaikan'"
              type="button"
              @click="openUploadModal(m.labelLong, m.key)"
              class="text-xs text-blue-600 hover:text-blue-700 font-semibold cursor-pointer underline-offset-2 hover:underline"
            >
              Klik untuk mengunggah
            </button>
            <span
              v-else-if="currentOperatorUnitRow.bulananStatus[m.key] === 'menunggu'"
              class="text-[11px] text-slate-400"
            >
              Menunggu verifikasi
            </span>
            <span
              v-else-if="currentOperatorUnitRow.bulananStatus[m.key] === 'diterima'"
              class="text-[11px] text-emerald-600 font-semibold"
            >
              Sudah terverifikasi
            </span>
            <span v-else class="text-[11px] text-transparent select-none">
              -
            </span>
          </div>
        </div>
      </div>

      <!-- Triwulan Grid (4 Cards) -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="tw in laporanTriwulanCols"
          :key="tw.key"
          class="bg-white rounded-xl border border-slate-200/90 p-5 flex flex-col justify-between min-h-[155px] text-center space-y-3 shadow-2xs hover:border-slate-300 transition-colors"
        >
          <!-- Top Row: TW Name & Deadline -->
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-800 text-xs sm:text-sm">{{ tw.labelLong }}</span>
            <span class="text-[10px] text-slate-400 font-mono">tenggat {{ tw.date }}</span>
          </div>

          <!-- Middle Badge -->
          <div class="flex items-center justify-center my-auto">
            <div
              v-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'terlambat'"
              class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-rose-200 bg-rose-50/50 text-rose-600 text-xs font-semibold"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Terlambat</span>
            </div>
            <div
              v-else-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'menunggu'"
              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-xs font-semibold"
            >
              <span class="w-2 h-2 rounded-full bg-amber-500"></span>
              <span>Menunggu</span>
            </div>
            <div
              v-else-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'diterima'"
              class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 text-xs font-semibold"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span>Diterima</span>
            </div>
            <div
              v-else
              class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 text-xs font-semibold"
            >
              <span>-</span>
              <span>Belum waktunya</span>
            </div>
          </div>

          <!-- Bottom Action Link -->
          <div>
            <button
              v-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'terlambat' || currentOperatorUnitRow.triwulanStatus[tw.key] === 'perbaikan'"
              type="button"
              @click="openUploadModal(tw.labelLong, tw.key)"
              class="text-xs text-blue-600 hover:text-blue-700 font-semibold cursor-pointer underline-offset-2 hover:underline"
            >
              Klik untuk mengunggah
            </button>
            <span
              v-else-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'menunggu'"
              class="text-[11px] text-slate-400"
            >
              Menunggu verifikasi
            </span>
            <span
              v-else-if="currentOperatorUnitRow.triwulanStatus[tw.key] === 'diterima'"
              class="text-[11px] text-emerald-600 font-semibold"
            >
              Sudah terverifikasi
            </span>
            <span v-else class="text-[11px] text-transparent select-none">
              -
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Case B: Admin Table View (Multi-Unit Matriks) -->
    <div v-else class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="table-container overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3 px-4 min-w-[220px] border-r border-white/10">UNIT</th>
              <template v-if="laporanPeriodMode === 'bulanan'">
                <th
                  v-for="c in laporanBulananCols"
                  :key="c.key"
                  class="py-2.5 px-2 text-center min-w-[48px] border-r border-white/10"
                >
                  <span class="block font-bold text-white text-xs">{{ c.label }}</span>
                  <span class="block text-[10px] text-slate-300 font-mono font-normal">{{ c.date }}</span>
                </th>
              </template>
              <template v-else>
                <th
                  v-for="c in laporanTriwulanCols"
                  :key="c.key"
                  class="py-2.5 px-3 text-center min-w-[70px] border-r border-white/10"
                >
                  <span class="block font-bold text-white text-xs">{{ c.label }}</span>
                  <span class="block text-[10px] text-slate-300 font-mono font-normal">{{ c.date }}</span>
                </th>
              </template>
              <th class="py-3 px-4 min-w-[140px]">KEPATUHAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in laporanUnitsData"
              :key="row.unitKode"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="py-3 px-4 align-middle">
                <span class="font-bold text-slate-900 text-sm block leading-tight">{{ row.unitKode }}</span>
                <span class="text-[11px] text-slate-400 block mt-0.5">{{ row.unitName }}</span>
              </td>

              <!-- Bulanan Cells -->
              <template v-if="laporanPeriodMode === 'bulanan'">
                <td
                  v-for="c in laporanBulananCols"
                  :key="c.key"
                  class="py-3 px-2 text-center align-middle"
                >
                  <div
                    v-if="row.bulananStatus[c.key] === 'terlambat'"
                    class="w-6 h-6 rounded-md border border-rose-300 bg-rose-50/60 text-rose-500 flex items-center justify-center mx-auto text-xs font-bold shadow-2xs"
                    title="Terlambat"
                  >
                    &times;
                  </div>
                  <span
                    v-else-if="row.bulananStatus[c.key] === 'belum_waktunya'"
                    class="text-slate-300 font-bold text-sm block"
                    title="Belum waktunya"
                  >
                    &ndash;
                  </span>
                  <span v-else class="text-slate-300 font-bold">&ndash;</span>
                </td>
              </template>

              <!-- Triwulanan Cells -->
              <template v-else>
                <td
                  v-for="c in laporanTriwulanCols"
                  :key="c.key"
                  class="py-3 px-3 text-center align-middle"
                >
                  <div
                    v-if="row.triwulanStatus[c.key] === 'terlambat'"
                    class="w-6 h-6 rounded-md border border-rose-300 bg-rose-50/60 text-rose-500 flex items-center justify-center mx-auto text-xs font-bold shadow-2xs"
                    title="Terlambat"
                  >
                    &times;
                  </div>
                  <span
                    v-else-if="row.triwulanStatus[c.key] === 'belum_waktunya'"
                    class="text-slate-300 font-bold text-sm block"
                    title="Belum waktunya"
                  >
                    &ndash;
                  </span>
                  <span v-else class="text-slate-300 font-bold">&ndash;</span>
                </td>
              </template>

              <!-- Kepatuhan Bar Column -->
              <td class="py-3 px-4 align-middle">
                <div class="flex items-center gap-2.5">
                  <div class="w-20 sm:w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-emerald-500 rounded-full transition-all"
                      :style="{ width: row.kepatuhan + '%' }"
                    />
                  </div>
                  <span class="text-xs font-bold text-slate-700 font-mono">{{ row.kepatuhan }}%</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Legend (Bottom, matches Image 2) -->
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600 pt-1">
      <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold">&check;</span>
        <span>Diterima</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-amber-100 text-amber-700 text-xs font-bold">&bull;</span>
        <span>Menunggu verifikasi</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-orange-100 text-orange-700 text-[10px] font-bold">!</span>
        <span>Perlu perbaikan</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center justify-center w-4 h-4 rounded border border-rose-300 bg-rose-50 text-rose-600 text-[10px] font-bold">&times;</span>
        <span>Terlambat</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-blue-100 text-blue-700 text-[10px] font-bold">1</span>
        <span>Waktunya dikumpulkan</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="text-slate-400 font-bold text-sm leading-none">&ndash;</span>
        <span>Belum waktunya</span>
      </div>
    </div>

    <!-- Footnote -->
    <p class="text-xs text-slate-400">
      Halaman ini hanya menampilkan status pengumpulan. Data diperbarui otomatis secara real-time.
    </p>

    <!-- ======================================================== -->
    <!-- MODAL UNGGAH LAPORAN (OPERATOR SATKER) -->
    <!-- ======================================================== -->
    <div
      v-if="showUploadModal"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-scale-up">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-base font-bold text-slate-900">Unggah Laporan</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ uploadTargetPeriod }} &bull; Unit {{ userUnitCode || 'AHU' }}</p>
          </div>
          <button type="button" @click="closeUploadModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-4 text-xs">
          <!-- File Upload Zone -->
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Berkas Laporan (PDF, Word, atau Excel)</label>
            <label class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-xl p-5 flex flex-col items-center justify-center gap-2 cursor-pointer bg-slate-50/50 hover:bg-blue-50/20 transition-colors">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
              </svg>
              <span class="text-xs font-semibold text-slate-700">
                {{ uploadFileName || 'Klik untuk memilih berkas laporan...' }}
              </span>
              <span class="text-[11px] text-slate-400">Maksimum ukuran berkas 15 MB</span>
              <input type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx" @change="handleFileSelect" />
            </label>
          </div>

          <!-- Notes / Description -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan / Keterangan (Opsional)</label>
            <textarea
              v-model="uploadNotes"
              rows="3"
              placeholder="Tambahkan catatan untuk verifikator jika ada..."
              class="w-full bg-white border border-slate-200 rounded-lg p-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="closeUploadModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitLaporan"
            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#0B2A6F] hover:bg-[#1D4ED8] text-white shadow-2xs"
          >
            Kirim Laporan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
