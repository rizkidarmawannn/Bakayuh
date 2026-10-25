<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import type { DashboardSummary, TahunAnggaran } from '@/types'

const { user, isSuperAdmin, isAdminKanwil } = useAuth()

const loading = ref(true)
const summary = ref<DashboardSummary | null>(null)
const tahuns = ref<TahunAnggaran[]>([])
const selectedTahunId = ref<number | null>(null)
const error = ref<string | null>(null)

// Current date formatted in Indonesian locale
const currentDateFormatted = computed(() => {
  const now = new Date()
  const options: Intl.DateTimeFormatOptions = {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }
  return now.toLocaleDateString('id-ID', options)
})

// Top Quick Modules Shortcuts (Zero emojis - Pure clean SVG icons)
const quickModules = [
  {
    title: 'Renaksi',
    desc: 'Rencana aksi & data dukung',
    to: '/sasaran-renaksi',
    iconColor: 'bg-amber-50 text-amber-600 border-amber-200',
    icon: 'clipboard',
  },
  {
    title: 'Capaian PK',
    desc: 'Capaian kinerja per bulan',
    to: '/capaian-pk',
    iconColor: 'bg-blue-50 text-blue-600 border-blue-200',
    icon: 'chart',
  },
  {
    title: 'Laporan',
    desc: 'Laporan bulanan & triwulan',
    to: '/laporan',
    iconColor: 'bg-indigo-50 text-indigo-600 border-indigo-200',
    icon: 'file',
  },
  {
    title: 'Pohon Kinerja',
    desc: 'Keterkaitan kinerja',
    to: '/pohon-kinerja',
    iconColor: 'bg-emerald-50 text-emerald-600 border-emerald-200',
    icon: 'tree',
  },
  {
    title: 'IKPA',
    desc: 'Pelaksanaan anggaran',
    to: '/ikpa',
    iconColor: 'bg-amber-50 text-amber-700 border-amber-200',
    icon: 'cash',
  },
]

// Renaksi Triwulan state for right panel
const selectedRenaksiTw = ref<'TW 1' | 'TW 2' | 'TW 3' | 'TW 4'>('TW 4')

// Unit Renaksi Progress data (Right column)
const renaksiUnitsProgress = ref([
  { kode: 'Setjen', name: 'Bagian Tata Usaha dan Umum', percent: 0, label: '-' },
  { kode: 'AHU', name: 'Administrasi Hukum Umum', percent: 0, label: '-' },
  { kode: 'KI', name: 'Kekayaan Intelektual', percent: 0, label: '-' },
  { kode: 'PP', name: 'Peraturan Perundang-Undangan', percent: 0, label: '-' },
  { kode: 'BPHN', name: 'Badan Pembinaan Hukum Nasional', percent: 0, label: '-' },
  { kode: 'BSK', name: 'Badan Strategi Kebijakan', percent: 0, label: '-' },
])

// Capaian PK Action items (Left column)
const capaianPkPendingList = ref([
  { kode: 'AHU', name: 'Administrasi Hukum Umum', uncompletedCount: 2, badgeColor: 'bg-blue-50 text-blue-700 border-blue-200' },
  { kode: 'KI', name: 'Kekayaan Intelektual', uncompletedCount: 3, badgeColor: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
  { kode: 'PP', name: 'Peraturan Perundang-Undangan', uncompletedCount: 2, badgeColor: 'bg-sky-50 text-sky-700 border-sky-200' },
  { kode: 'BPHN', name: 'Badan Pembinaan Hukum Nasional', uncompletedCount: 4, badgeColor: 'bg-purple-50 text-purple-700 border-purple-200' },
  { kode: 'BSK', name: 'Badan Strategi Kebijakan', uncompletedCount: 1, badgeColor: 'bg-teal-50 text-teal-700 border-teal-200' },
])

// Laporan Terlambat Action items (Left column)
const laporanTerlambatList = ref([
  { kode: 'Setjen', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
  { kode: 'AHU', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
  { kode: 'KI', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
  { kode: 'PP', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
  { kode: 'BPHN', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
  { kode: 'BSK', title: 'Laporan Bulanan Juni 2026', tenggat: '10/07/2026' },
])

async function loadData() {
  loading.value = true
  error.value = null
  try {
    const resTahun = await apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran')
    tahuns.value = resTahun.data.data
    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif && !selectedTahunId.value) selectedTahunId.value = aktif.id

    const params = selectedTahunId.value ? { tahun_anggaran_id: selectedTahunId.value } : {}

    const resSummary = await apiClient.get<{ data: DashboardSummary }>('/dashboard/summary', { params })
    summary.value = resSummary.data.data
  } catch (err: any) {
    console.warn('Dashboard summary notice:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header Title & Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Dashboard Administrator
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Ringkasan seluruh unit &bull; {{ currentDateFormatted }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Tahun Selector -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedTahunId"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 w-44 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
            @change="loadData"
          >
            <option v-for="t in tahuns" :key="t.id" :value="t.id">
              Tahun {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
            </option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <button
          type="button"
          class="btn-secondary text-xs py-2 px-3 inline-flex items-center gap-1.5 cursor-pointer"
          :disabled="loading"
          @click="loadData"
        >
          <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Segarkan</span>
        </button>
      </div>
    </div>

    <!-- Error State if any -->
    <div v-if="error" class="p-4 rounded-xl border-l-4 border-red-500 bg-red-50 text-red-800 text-xs">
      {{ error }}
    </div>

    <!-- 1. Top Quick Module Shortcuts (Zero Emojis, Pure SVG Icons) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
      <RouterLink
        v-for="item in quickModules"
        :key="item.title"
        :to="item.to"
        class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-sm transition-all flex items-center gap-3.5 group select-none"
      >
        <div class="w-10 h-10 rounded-xl border flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105" :class="item.iconColor">
          <!-- Clipboard / Renaksi -->
          <svg v-if="item.icon === 'clipboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          <!-- Chart / Capaian PK -->
          <svg v-else-if="item.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <!-- Document / Laporan -->
          <svg v-else-if="item.icon === 'file'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <!-- Tree / Pohon Kinerja -->
          <svg v-else-if="item.icon === 'tree'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a1.5 1.5 0 003 0m0-2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3" />
          </svg>
          <!-- Cash / IKPA -->
          <svg v-else-if="item.icon === 'cash'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="flex flex-col min-w-0">
          <span class="font-bold text-xs sm:text-sm text-slate-800 leading-snug group-hover:text-blue-600 transition-colors truncate">
            {{ item.title }}
          </span>
          <span class="text-[11px] text-slate-400 mt-0.5 truncate">
            {{ item.desc }}
          </span>
        </div>
      </RouterLink>
    </div>

    <!-- 2. 5 Executive Metric Summary Cards (Single-level cards, NO nested cards!) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
      <!-- Card 1: Menunggu verifikasi -->
      <RouterLink
        to="/verifikasi"
        class="bg-white rounded-2xl p-5 border-l-4 border-l-amber-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
      >
        <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
        <div class="mt-3">
          <span class="text-xs sm:text-sm font-bold text-slate-800 block">Menunggu verifikasi</span>
          <span class="text-[11px] text-amber-600 font-medium inline-flex items-center gap-1 mt-0.5 group-hover:underline">
            klik untuk memeriksa &rarr;
          </span>
        </div>
      </RouterLink>

      <!-- Card 2: Renaksi lewat tenggat -->
      <RouterLink
        to="/sasaran-renaksi"
        class="bg-white rounded-2xl p-5 border-l-4 border-l-rose-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
      >
        <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
        <div class="mt-3">
          <span class="text-xs sm:text-sm font-bold text-slate-800 block">Renaksi lewat tenggat</span>
          <span class="text-[11px] text-slate-400 font-normal block mt-0.5">belum ada daduk / perlu perbaikan</span>
        </div>
      </RouterLink>

      <!-- Card 3: Laporan menunggu verifikasi -->
      <RouterLink
        to="/laporan"
        class="bg-white rounded-2xl p-5 border-l-4 border-l-amber-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
      >
        <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
        <div class="mt-3">
          <span class="text-xs sm:text-sm font-bold text-slate-800 block">Laporan menunggu verifikasi</span>
          <span class="text-[11px] text-slate-400 font-normal block mt-0.5">36 laporan terlambat</span>
        </div>
      </RouterLink>

      <!-- Card 4: Renaksi diterima TW 4 -->
      <RouterLink
        to="/monitoring-daduk"
        class="bg-white rounded-2xl p-5 border-l-4 border-l-emerald-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
      >
        <div class="flex items-center justify-between">
          <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0%</span>
          <!-- Mini Donut SVG -->
          <div class="w-10 h-10 flex-shrink-0">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path class="text-slate-100" stroke-width="4" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
              <path class="text-emerald-500" stroke-width="4" stroke-dasharray="0, 100" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
          </div>
        </div>
        <div class="mt-3">
          <span class="text-xs sm:text-sm font-bold text-slate-800 block">Renaksi diterima TW 4</span>
          <span class="text-[11px] text-slate-400 font-normal block mt-0.5">0 dari 0 selesai</span>
        </div>
      </RouterLink>

      <!-- Card 5: IKPA September 2026 -->
      <RouterLink
        to="/ikpa"
        class="bg-white rounded-2xl p-5 border-l-4 border-l-[#0B2A6F] border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between col-span-2 sm:col-span-1 group"
      >
        <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">96,49</span>
        <div class="mt-3">
          <span class="text-xs sm:text-sm font-bold text-slate-800 block">IKPA September 2026</span>
          <span class="text-[11px] text-slate-400 font-normal block mt-0.5">rata-rata Nilai Akhir</span>
        </div>
      </RouterLink>
    </div>

    <!-- 3. Main Two-Column Layout (NO NESTED CARDS!) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
      <!-- LEFT COLUMN: PERLU TINDAKAN (Matched height with right card & scrollable inner container) -->
      <div
        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 flex flex-col overflow-hidden lg:h-[420px]"
      >
        <!-- Header (Fixed at top) -->
        <div class="flex-shrink-0 pb-3 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
              Perlu Tindakan
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Pengisian realisasi dan kepatuhan laporan unit kerja yang membutuhkan perhatian
            </p>
          </div>
        </div>

        <!-- Scrollable Inner Container -->
        <div class="flex-1 overflow-y-auto pr-1.5 pt-3 space-y-4 scrollbar-thin">
          <!-- Section 1: Alert Bars (Menunggu & Lewat Tenggat) -->
          <div class="space-y-2.5">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
              Menunggu Verifikasi
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/60 flex items-center gap-2.5 text-xs text-emerald-800 font-semibold">
              <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Tidak ada data dukung yang menunggu verifikasi</span>
            </div>

            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 pt-2">
              Lewat Tenggat
            </div>
            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/60 flex items-center gap-2.5 text-xs text-emerald-800 font-semibold">
              <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Tidak ada rencana aksi yang lewat tenggat</span>
            </div>
          </div>

          <!-- Section 2: Capaian PK Uncompleted Items (Flat divided rows, NO nested cards) -->
          <div class="space-y-2 pt-2 border-t border-slate-100">
            <div class="flex items-center gap-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Capaian PK</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                5 unit
              </span>
            </div>

            <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-xl overflow-hidden">
              <div
                v-for="item in capaianPkPendingList"
                :key="item.kode"
                class="p-3.5 flex items-center justify-between hover:bg-slate-50/70 transition-colors gap-3"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border flex-shrink-0" :class="item.badgeColor">
                    {{ item.kode }}
                  </span>
                  <span class="text-xs text-slate-700 truncate">
                    {{ item.name }}: <strong class="text-slate-900">{{ item.uncompletedCount }}</strong> indikator belum diisi untuk September
                  </span>
                </div>
                <RouterLink
                  to="/capaian-pk"
                  class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap transition-colors flex-shrink-0"
                >
                  Isi &rarr;
                </RouterLink>
              </div>
            </div>
          </div>

          <!-- Section 3: Laporan Terlambat (Flat divided rows, NO nested cards) -->
          <div class="space-y-2 pt-2 border-t border-slate-100">
            <div class="flex items-center gap-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Laporan Terlambat</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800">
                36
              </span>
            </div>

            <div class="divide-y divide-slate-100 border border-slate-200/80 rounded-xl overflow-hidden">
              <div
                v-for="rep in laporanTerlambatList"
                :key="rep.kode"
                class="p-3 flex items-center justify-between hover:bg-slate-50/70 transition-colors gap-3"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 flex-shrink-0">
                    {{ rep.kode }}
                  </span>
                  <span class="text-xs text-slate-700 font-medium truncate">
                    {{ rep.title }}
                  </span>
                </div>
                <span class="text-[11px] text-rose-600 font-mono whitespace-nowrap flex-shrink-0">
                  tenggat {{ rep.tenggat }}
                </span>
              </div>
            </div>

            <div class="pt-1 text-right">
              <RouterLink
                to="/laporan"
                class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors"
              >
                Lihat seluruh 36 laporan terlambat &rarr;
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: PROGRES RENAKSI PER UNIT (Matched height with left card) -->
      <div
        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 flex flex-col justify-between lg:h-[420px]"
      >
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
              Progres renaksi per unit
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Capaian upload data dukung per triwulan
            </p>
          </div>
          <RouterLink
            to="/monitoring-daduk"
            class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors"
          >
            Buka modul &rarr;
          </RouterLink>
        </div>

        <!-- Quarters Filter Pills & Deadline in single balanced row -->
        <div class="flex items-center justify-between flex-wrap gap-2 pt-1">
          <div class="flex items-center gap-1.5">
            <button
              v-for="tw in (['TW 1', 'TW 2', 'TW 3', 'TW 4'] as const)"
              :key="tw"
              type="button"
              class="px-3.5 py-1 rounded-full text-xs font-bold transition-all cursor-pointer border"
              :class="selectedRenaksiTw === tw
                ? 'bg-[#1D4ED8] text-white border-[#1D4ED8] shadow-xs'
                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
              @click="selectedRenaksiTw = tw"
            >
              {{ tw }}
            </button>
          </div>
          <span class="text-xs text-slate-500 font-medium">
            Tenggat {{ selectedRenaksiTw }}: 31/12/2026
          </span>
        </div>

        <!-- 6 Units Progress Track Rows (Compact and clean) -->
        <div class="space-y-3 pt-1">
          <div
            v-for="u in renaksiUnitsProgress"
            :key="u.kode"
            class="flex items-center gap-3.5"
          >
            <span class="w-16 text-xs font-bold text-slate-800 flex-shrink-0">
              {{ u.kode }}
            </span>
            <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="u.percent > 0 ? 'bg-emerald-500' : 'bg-slate-200'"
                :style="{ width: u.percent + '%' }"
              />
            </div>
            <span class="w-6 text-right text-xs text-slate-400 font-mono flex-shrink-0">
              {{ u.label }}
            </span>
          </div>
        </div>

        <!-- Legend (Bottom) -->
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-3 border-t border-slate-100">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600" />
            <span>Diterima</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500" />
            <span>Menunggu</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-600" />
            <span>Perbaikan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-slate-300" />
            <span>Belum</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
