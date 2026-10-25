<script setup lang="ts">
import { ref } from 'vue'

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
  { key: 'jan', label: 'JAN', date: '10/02' },
  { key: 'feb', label: 'FEB', date: '10/03' },
  { key: 'mar', label: 'MAR', date: '10/04' },
  { key: 'apr', label: 'APR', date: '10/05' },
  { key: 'mei', label: 'MEI', date: '10/06' },
  { key: 'jun', label: 'JUN', date: '10/07' },
  { key: 'jul', label: 'JUL', date: '10/08' },
  { key: 'agu', label: 'AGU', date: '10/09' },
  { key: 'sep', label: 'SEP', date: '10/10' },
  { key: 'okt', label: 'OKT', date: '10/11' },
  { key: 'nov', label: 'NOV', date: '10/12' },
  { key: 'des', label: 'DES', date: '10/01' },
]

const laporanTriwulanCols = [
  { key: 'tw1', label: 'TW I', date: '10/04' },
  { key: 'tw2', label: 'TW II', date: '10/07' },
  { key: 'tw3', label: 'TW III', date: '10/10' },
  { key: 'tw4', label: 'TW IV', date: '10/01' },
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
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header with Title & Year Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Progres Laporan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Kepatuhan pengumpulan laporan kinerja per unit kerja, tahun {{ laporanSelectedYear }}
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

    <!-- Summary Metric Horizontal Card -->
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
                stroke-dasharray="0, 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
              <span class="text-xs sm:text-sm font-black text-[#0B2A6F]">0%</span>
              <span class="text-[9px] text-slate-400 font-medium leading-none mt-0.5">diterima</span>
            </div>
          </div>

          <!-- Metric Items -->
          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Diterima</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Menunggu verifikasi</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Perlu perbaikan</span>
          </div>

          <div class="flex flex-col">
            <span class="text-3xl sm:text-4xl font-black text-rose-600">
              {{ laporanPeriodMode === 'bulanan' ? 54 : 18 }}
            </span>
            <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Terlambat</span>
          </div>
        </div>
      </div>
      <!-- Note under the card -->
      <p class="text-xs text-slate-500">
        Dihitung dari {{ laporanPeriodMode === 'bulanan' ? 54 : 18 }} laporan yang sudah jatuh tempo atau sudah dikirim.
      </p>
    </div>

    <!-- Table View -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
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

    <!-- Legend (Bottom) -->
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
        <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-blue-100 text-blue-700 text-[10px] font-bold">T</span>
        <span>Waktunya dikumpulkan</span>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="text-slate-400 font-bold text-sm leading-none">&ndash;</span>
        <span>Belum waktunya</span>
      </div>
    </div>

    <!-- Footnote -->
    <p class="text-xs text-slate-400">
      Halaman ini hanya menampilkan status pengumpulan. Isi laporan tidak dipublikasikan. Data diperbarui otomatis.
    </p>
  </div>
</template>
