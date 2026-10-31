<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import type { DashboardSummary, TahunAnggaran } from '@/types'

const { user, isSuperAdmin, isAdminKanwil, isOperatorSatker, userUnitCode } = useAuth()

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

// Dynamic Satker unit full name mapping
const unitFullNameMap: Record<string, string> = {
  AHU: 'Administrasi Hukum Umum',
  KI: 'Kekayaan Intelektual',
  PP: 'Peraturan Perundang-Undangan',
  BPHN: 'Badan Pembinaan Hukum Nasional',
  BSK: 'Badan Strategi Kebijakan',
  Setjen: 'Bagian Tata Usaha dan Umum',
}

const userUnitFullName = computed(() => {
  const code = userUnitCode.value
  if (code && unitFullNameMap[code]) {
    return unitFullNameMap[code]
  }
  return user.value?.name || 'Satuan Kerja'
})

// ---------------------------------------------------------------------------
// OPERATOR SATKER DATA & STATE
// ---------------------------------------------------------------------------
// Satker Triwulan Progress (TW 1 - TW 4)
const satkerTriwulanProgress = ref([
  { tw: 'TW 1', date: '-', percent: 0, statusLabel: 'belum ditetapkan', isGreen: false },
  { tw: 'TW 2', date: '-', percent: 0, statusLabel: 'belum ditetapkan', isGreen: false },
  { tw: 'TW 3', date: '-', percent: 0, statusLabel: 'belum ditetapkan', isGreen: false },
  { tw: 'TW 4', date: '31/12/2026', percent: 0, statusLabel: '61 hari lagi', isGreen: true },
])

// Satker Laporan yang perlu dikumpulkan (Bounded scrollable container)
const satkerLaporanList = ref([
  {
    status: 'Terlambat',
    title: 'Laporan Bulanan — Juni 2026',
    tenggat: 'tenggat 10/07/2026',
  },
  {
    status: 'Terlambat',
    title: 'Laporan Triwulan — TW II 2026',
    tenggat: 'tenggat 10/07/2026',
  },
  {
    status: 'Terlambat',
    title: 'Laporan Bulanan — Juli 2026',
    tenggat: 'tenggat 10/08/2026',
  },
  {
    status: 'Terlambat',
    title: 'Laporan Bulanan — Agustus 2026',
    tenggat: 'tenggat 10/09/2026',
  },
  {
    status: 'Terlambat',
    title: 'Laporan Bulanan — September 2026',
    tenggat: 'tenggat 10/10/2026',
  },
  {
    status: 'Terlambat',
    title: 'Laporan Triwulan — TW III 2026',
    tenggat: 'tenggat 10/10/2026',
  },
  {
    status: 'Segera',
    title: 'Laporan Bulanan — Oktober 2026',
    tenggat: 'tenggat 10/11/2026',
  },
])

// Satker Capaian PK IKKs per unit
const satkerIkksMap: Record<string, { id: string; indikator: string }[]> = {
  AHU: [
    {
      id: 'ahu-1',
      indikator: 'IKK 7110.1.1 · Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
    },
    {
      id: 'ahu-2',
      indikator:
        'IKK 7110.2.1 · Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku ...',
    },
  ],
  KI: [
    {
      id: 'ki-1',
      indikator:
        'IKK 7121.1.1 · Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan',
    },
    {
      id: 'ki-2',
      indikator: 'IKK 7121.2.1 · Persentase penyelesaian aduan pelanggaran kekayaan intelektual di wilayah',
    },
  ],
  PP: [
    {
      id: 'pp-1',
      indikator:
        'IKK 7130.1.1 · Persentase Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
    },
  ],
  BPHN: [
    {
      id: 'bphn-1',
      indikator: 'IKK 7136.1.1 · Persentase Desa/Kelurahan Sadar Hukum yang Dibina di Wilayah',
    },
  ],
  BSK: [
    {
      id: 'bsk-1',
      indikator: 'IKK 7143.1.1 · Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah',
    },
  ],
  Setjen: [
    {
      id: 'setjen-1',
      indikator: 'IKK 7099.1.1 · Nilai Kinerja Anggaran Satuan Kerja Eselon I di Wilayah',
    },
    {
      id: 'setjen-2',
      indikator: 'IKK 7099.2.1 · Indeks kepuasan terhadap layanan dukungan manajemen',
    },
  ],
}

const currentSatkerIkks = computed(() => {
  const code = userUnitCode.value || 'AHU'
  return satkerIkksMap[code] || satkerIkksMap['AHU']
})

const monthLetters = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D']

function getSatkerMonthStatusClass(monthIndex: number): string {
  // 0 to 8 = Jan s.d. Sep (bulan lalu) -> Peach / Rose
  // 9 to 11 = Okt s.d. Des -> Abu-abu (belum waktunya)
  if (monthIndex < 9) {
    return 'bg-[#fecaca] text-[#991b1b]'
  }
  return 'bg-slate-100 text-slate-400'
}

// ---------------------------------------------------------------------------
// ADMIN DATA & STATE
// ---------------------------------------------------------------------------
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

// Data Kelengkapan Capaian PK 2026 (Gambar 1)
const pkMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

interface PkUnitKelengkapan {
  name: string
  months: { text: string; status: 'lengkap' | 'sebagian' | 'belum' }[]
}

const kelengkapanPkData = ref<PkUnitKelengkapan[]>([
  {
    name: 'Setjen',
    months: [
      { text: '1/2', status: 'sebagian' },
      { text: '1/2', status: 'sebagian' },
      { text: '1/2', status: 'sebagian' },
      { text: '2/2', status: 'lengkap' },
      { text: '2/2', status: 'lengkap' },
      { text: '2/2', status: 'lengkap' },
      { text: '2/2', status: 'lengkap' },
      { text: '2/2', status: 'lengkap' },
      { text: '2/2', status: 'lengkap' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
    ],
  },
  {
    name: 'AHU',
    months: [
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
    ],
  },
  {
    name: 'KI',
    months: [
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
      { text: '0/3', status: 'belum' },
    ],
  },
  {
    name: 'PP',
    months: [
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
      { text: '0/2', status: 'belum' },
    ],
  },
  {
    name: 'BPHN',
    months: [
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
      { text: '0/4', status: 'belum' },
    ],
  },
  {
    name: 'BSK',
    months: [
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
      { text: '0/1', status: 'belum' },
    ],
  },
])

// Data IKPA September 2026 (Gambar 2)
const ikpaSeptemberUnits = [
  { name: 'Setjen', score: 95.85, scoreLabel: '95,85', color: 'bg-amber-500' },
  { name: 'Ditjen AHU', score: 98.99, scoreLabel: '98,99', color: 'bg-emerald-500' },
  { name: 'Ditjen KI', score: 96.98, scoreLabel: '96,98', color: 'bg-emerald-500' },
  { name: 'Ditjen PP', score: 100, scoreLabel: '100', color: 'bg-blue-600' },
  { name: 'BPHN', score: 91.58, scoreLabel: '91,58', color: 'bg-amber-500' },
  { name: 'BSK', score: 95.54, scoreLabel: '95,54', color: 'bg-amber-500' },
]

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
      <!-- Operator Satker Header Title -->
      <div v-if="isOperatorSatker">
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Dashboard {{ userUnitFullName }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Unit {{ userUnitCode || 'AHU' }} &bull; {{ currentDateFormatted }}
        </p>
      </div>

      <!-- Administrator Header Title -->
      <div v-else>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Dashboard Administrator
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Ringkasan seluruh unit &bull; {{ currentDateFormatted }}
        </p>
      </div>

      <!-- Filters & Refresh (Administrator only) -->
      <div v-if="!isOperatorSatker" class="flex items-center gap-3">
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

    <!-- ========================================================================= -->
    <!-- SECTION A: OPERATOR SATKER DASHBOARD (Gambar 1, 2, 3)                     -->
    <!-- ========================================================================= -->
    <template v-if="isOperatorSatker">
      <!-- 1. Top 5 Metrics Cards for Satker -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Card 1: Renaksi diterima TW 4 (Circular Gauge Donut) -->
        <RouterLink
          to="/sasaran-renaksi"
          class="bg-white rounded-2xl p-5 border-l-4 border-l-emerald-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
        >
          <div class="w-14 h-14 relative flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path
                class="text-slate-100"
                stroke-width="3.5"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                class="text-emerald-500"
                stroke-width="3.5"
                stroke-dasharray="0, 100"
                stroke-linecap="round"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <span class="absolute text-xs font-black text-slate-800">0%</span>
          </div>
          <div class="mt-3">
            <span class="text-xs sm:text-sm font-bold text-slate-800 block">Renaksi diterima TW 4</span>
            <span class="text-[11px] text-slate-400 font-normal block mt-0.5">0 dari 0</span>
          </div>
        </RouterLink>

        <!-- Card 2: Perlu diperbaiki -->
        <RouterLink
          to="/sasaran-renaksi"
          class="bg-white rounded-2xl p-5 border-l-4 border-l-rose-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
        >
          <span class="text-3xl sm:text-4xl font-black text-rose-600">0</span>
          <div class="mt-3">
            <span class="text-xs sm:text-sm font-bold text-slate-800 block">Perlu diperbaiki</span>
            <span class="text-[11px] text-slate-400 font-normal block mt-0.5">semua aman</span>
          </div>
        </RouterLink>

        <!-- Card 3: Menunggu verifikasi -->
        <RouterLink
          to="/sasaran-renaksi"
          class="bg-white rounded-2xl p-5 border-l-4 border-l-amber-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
        >
          <span class="text-3xl sm:text-4xl font-black text-amber-500">0</span>
          <div class="mt-3">
            <span class="text-xs sm:text-sm font-bold text-slate-800 block">Menunggu verifikasi</span>
            <span class="text-[11px] text-slate-400 font-normal block mt-0.5">daduk &amp; laporan</span>
          </div>
        </RouterLink>

        <!-- Card 4: Laporan perlu dikumpulkan -->
        <RouterLink
          to="/laporan"
          class="bg-white rounded-2xl p-5 border-l-4 border-l-rose-500 border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between group"
        >
          <span class="text-3xl sm:text-4xl font-black text-rose-600">7</span>
          <div class="mt-3">
            <span class="text-xs sm:text-sm font-bold text-slate-800 block">Laporan perlu dikumpulkan</span>
            <span class="text-[11px] text-slate-400 font-normal block mt-0.5">lewat tenggat / segera</span>
          </div>
        </RouterLink>

        <!-- Card 5: Tenggat TW 4 -->
        <RouterLink
          to="/sasaran-renaksi"
          class="bg-white rounded-2xl p-5 border-l-4 border-l-[#0B2A6F] border border-slate-200/90 shadow-2xs hover:shadow-sm transition-all flex flex-col justify-between col-span-2 sm:col-span-1 group"
        >
          <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">61</span>
          <div class="mt-3">
            <span class="text-xs sm:text-sm font-bold text-slate-800 block">Tenggat TW 4</span>
            <span class="text-[11px] text-slate-400 font-normal block mt-0.5">hari lagi (31/12/2026)</span>
          </div>
        </RouterLink>
      </div>

      <!-- 2. Middle Grid: Tenggat & Progres per Triwulan AND Perlu Diperbaiki -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Left Column: Tenggat & progres per triwulan (lg:col-span-7) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
                Tenggat &amp; progres per triwulan
              </h2>
              <RouterLink
                to="/sasaran-renaksi"
                class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1"
              >
                <span>Buka renaksi</span>
                <span>&rarr;</span>
              </RouterLink>
            </div>

            <!-- List of Quarters TW 1 to TW 4 -->
            <div class="space-y-4 pt-4">
              <div
                v-for="item in satkerTriwulanProgress"
                :key="item.tw"
                class="flex items-center justify-between gap-3 text-xs"
              >
                <div class="w-16 flex-shrink-0">
                  <span class="font-bold text-slate-900 block">{{ item.tw }}</span>
                  <span class="text-[11px]" :class="item.date !== '-' ? 'text-slate-600' : 'text-slate-400'">
                    {{ item.date }}
                  </span>
                </div>

                <div class="flex-1 h-3 rounded-full bg-slate-100 overflow-hidden mx-2">
                  <div
                    class="h-full rounded-full transition-all duration-300"
                    :class="item.percent > 0 ? 'bg-emerald-500' : 'bg-slate-200'"
                    :style="{ width: item.percent + '%' }"
                  />
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                  <span class="text-slate-400 font-mono">-</span>
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[11px]"
                    :class="item.isGreen
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50 font-semibold'
                      : 'bg-slate-100 text-slate-500 font-medium'"
                  >
                    {{ item.statusLabel }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Legend -->
          <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-5 mt-4 border-t border-slate-100">
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

        <!-- Right Column: Perlu diperbaiki (lg:col-span-5) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 flex flex-col">
          <div class="pb-3 border-b border-slate-100">
            <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
              Perlu diperbaiki
            </h2>
          </div>
          <div class="pt-4">
            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/60 flex items-center gap-2.5 text-xs text-emerald-800 font-medium">
              <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Tidak ada daduk yang perlu diperbaiki</span>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Laporan yang perlu dikumpulkan (Bounded container with smooth scrollbar) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
            Laporan yang perlu dikumpulkan
          </h2>
          <RouterLink
            to="/laporan"
            class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1"
          >
            <span>Buka laporan</span>
            <span>&rarr;</span>
          </RouterLink>
        </div>

        <!-- Scrollable Inner Container to Prevent Infinite Scroll -->
        <div class="max-h-[380px] overflow-y-auto pr-1.5 space-y-2.5 scrollbar-thin">
          <div
            v-for="rep in satkerLaporanList"
            :key="rep.title"
            class="border border-slate-200/90 rounded-xl p-3.5 bg-white hover:border-slate-300 transition-colors"
          >
            <div class="flex items-center gap-2">
              <span
                class="px-2 py-0.5 rounded text-[11px] font-bold"
                :class="rep.status === 'Terlambat'
                  ? 'bg-rose-50 text-rose-700 border border-rose-200/60'
                  : 'bg-blue-50 text-blue-700 border border-blue-200/60'"
              >
                {{ rep.status }}
              </span>
              <span class="text-xs text-slate-800 font-semibold">
                {{ rep.title }}
              </span>
            </div>
            <div
              class="mt-1 text-[11px] font-mono"
              :class="rep.status === 'Terlambat' ? 'text-rose-600' : 'text-blue-600'"
            >
              {{ rep.tenggat }}
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Kelengkapan Capaian PK 2026 (for Satker) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
          <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
            Kelengkapan Capaian PK 2026
          </h2>
          <RouterLink
            to="/capaian-pk"
            class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1"
          >
            <span>Isi capaian</span>
            <span>&rarr;</span>
          </RouterLink>
        </div>

        <!-- Donut gauge + IKK rows with month pills -->
        <div class="flex flex-col md:flex-row items-center gap-6 pt-1">
          <!-- Donut gauge on the left -->
          <div class="w-28 h-28 relative flex items-center justify-center flex-shrink-0">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
              <path
                class="text-blue-100/70"
                stroke-width="3.5"
                stroke="currentColor"
                fill="none"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
              <span class="text-lg font-black text-slate-800 leading-tight">0%</span>
              <span class="text-[10px] text-slate-400 font-medium leading-tight">bulan lalu</span>
            </div>
          </div>

          <!-- IKK rows on the right -->
          <div class="flex-1 w-full space-y-3.5">
            <div
              v-for="ikk in currentSatkerIkks"
              :key="ikk.id"
              class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100 last:border-b-0 last:pb-0"
            >
              <div class="text-xs font-semibold text-slate-800 truncate sm:max-w-md">
                {{ ikk.indikator }}
              </div>
              <div class="flex items-center gap-1.5 flex-shrink-0 self-start sm:self-auto">
                <span
                  v-for="(letter, mIdx) in monthLetters"
                  :key="mIdx"
                  class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-bold select-none"
                  :class="getSatkerMonthStatusClass(mIdx)"
                >
                  {{ letter }}
                </span>
                <span class="ml-2 text-xs font-semibold text-slate-500 font-mono w-8 text-right">
                  0/12
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 text-xs text-slate-600 pt-3 border-t border-slate-100">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#bbf7d0] inline-block" />
            <span>Terisi</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#fecaca] inline-block" />
            <span>Belum diisi (bulan lalu)</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-slate-200 inline-block" />
            <span>Belum waktunya</span>
          </div>
        </div>
      </div>
    </template>

    <!-- ========================================================================= -->
    <!-- SECTION B: ADMINISTRATOR DASHBOARD (Super Admin / Admin Kanwil)           -->
    <!-- ========================================================================= -->
    <template v-else>
      <!-- 1. Top Quick Module Shortcuts (Zero Emojis, Pure SVG Icons) -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <RouterLink
          v-for="item in quickModules"
          :key="item.title"
          :to="item.to"
          class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs hover:border-blue-400 hover:shadow-sm transition-all flex items-center gap-3.5 group select-none"
        >
          <div
            class="w-10 h-10 rounded-xl border flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105"
            :class="item.iconColor"
          >
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

      <!-- 2. 5 Executive Metric Summary Cards for Admin -->
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

      <!-- 3. Main Two-Column Layout for Admin -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        <!-- LEFT COLUMN: PERLU TINDAKAN -->
        <div
          class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 flex flex-col overflow-hidden lg:h-[420px]"
        >
          <!-- Header -->
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

            <!-- Section 2: Capaian PK Uncompleted Items -->
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

            <!-- Section 3: Laporan Terlambat -->
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

        <!-- RIGHT COLUMN: PROGRES RENAKSI PER UNIT -->
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

          <!-- Quarters Filter Pills & Deadline -->
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

          <!-- Units Progress Track Rows -->
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

          <!-- Legend -->
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

      <!-- 4. Kelengkapan Capaian PK 2026 Table (All units, Admin) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
            Kelengkapan Capaian PK 2026
          </h2>
          <RouterLink
            to="/capaian-pk"
            class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors"
          >
            Buka modul &rarr;
          </RouterLink>
        </div>

        <!-- Matrix Grid 12 Months -->
        <div class="overflow-x-auto">
          <div class="min-w-[720px] space-y-2">
            <!-- Month Header Row -->
            <div
              class="grid gap-2 text-center text-xs font-semibold text-slate-500 pb-1"
              style="grid-template-columns: 80px repeat(12, minmax(0, 1fr))"
            >
              <div class="text-left font-bold text-slate-700"></div>
              <div v-for="m in pkMonths" :key="m" class="text-slate-500 font-medium">
                {{ m }}
              </div>
            </div>

            <!-- Unit Rows -->
            <div
              v-for="unit in kelengkapanPkData"
              :key="unit.name"
              class="grid gap-2 items-center"
              style="grid-template-columns: 80px repeat(12, minmax(0, 1fr))"
            >
              <div class="text-xs font-bold text-slate-900 truncate pr-2">
                {{ unit.name }}
              </div>
              <div
                v-for="(m, idx) in unit.months"
                :key="idx"
                class="flex items-center justify-center"
              >
                <span
                  v-if="m.status === 'lengkap'"
                  class="w-full py-1 rounded-md bg-[#bbf7d0] text-[#15803d] font-bold text-[11px] sm:text-xs text-center block shadow-2xs select-none"
                >
                  {{ m.text }}
                </span>
                <span
                  v-else-if="m.status === 'sebagian'"
                  class="w-full py-1 rounded-md bg-[#fef08a] text-[#854d0e] font-bold text-[11px] sm:text-xs text-center block shadow-2xs select-none"
                >
                  {{ m.text }}
                </span>
                <span
                  v-else
                  class="w-full py-1 rounded-md bg-[#fecaca] text-[#991b1b] font-bold text-[11px] sm:text-xs text-center block shadow-2xs select-none"
                >
                  {{ m.text }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 text-xs text-slate-600 pt-2 border-t border-slate-100">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#bbf7d0] inline-block" />
            <span>Lengkap</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#fef08a] inline-block" />
            <span>Sebagian</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#fecaca] inline-block" />
            <span>Belum diisi</span>
          </div>
        </div>
      </div>

      <!-- 5. IKPA September 2026 (Admin Only) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
            IKPA September 2026
          </h2>
          <RouterLink
            to="/ikpa"
            class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors"
          >
            Buka modul &rarr;
          </RouterLink>
        </div>

        <!-- 3-Column Visual Layout (Speedometer Gauge, Progress Bars, Trend Plot) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center pt-2">
          <!-- 1. Left: Speedometer Semi-circle Gauge (md:col-span-3) -->
          <div class="md:col-span-3 flex flex-col items-center justify-center">
            <svg viewBox="0 0 200 120" class="w-48 sm:w-52 h-auto overflow-visible">
              <!-- Background Arc -->
              <path
                d="M 30 100 A 70 70 0 0 1 170 100"
                fill="none"
                stroke="#f1f5f9"
                stroke-width="16"
                stroke-linecap="round"
              />
              <!-- Progress Arc (Green 96.49%) -->
              <path
                d="M 30 100 A 70 70 0 0 1 170 100"
                fill="none"
                stroke="#059669"
                stroke-width="16"
                stroke-linecap="round"
                stroke-dasharray="219.9"
                stroke-dashoffset="7.7"
              />
              <!-- Indicator End Dot -->
              <circle
                cx="168"
                cy="91"
                r="7"
                fill="white"
                stroke="#0B2A6F"
                stroke-width="3"
              />
              <!-- Center Text: 96,49 -->
              <text
                x="100"
                y="82"
                text-anchor="middle"
                class="fill-[#0B2A6F] font-black"
                font-size="28"
                font-weight="900"
              >
                96,49
              </text>
              <!-- Subtitle: rata-rata Nilai Akhir -->
              <text
                x="100"
                y="102"
                text-anchor="middle"
                class="fill-slate-500 font-medium"
                font-size="9.5"
              >
                rata-rata Nilai Akhir
              </text>
            </svg>
          </div>

          <!-- 2. Middle: Unit Progress Bars (md:col-span-5) -->
          <div class="md:col-span-5 space-y-2.5">
            <div
              v-for="unit in ikpaSeptemberUnits"
              :key="unit.name"
              class="flex items-center gap-3"
            >
              <span class="w-20 text-xs font-bold text-slate-800 flex-shrink-0">
                {{ unit.name }}
              </span>
              <div class="flex-1 h-3 rounded-full bg-slate-100 overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="unit.color"
                  :style="{ width: unit.score + '%' }"
                />
              </div>
              <span class="w-10 text-right text-xs font-mono font-semibold text-slate-600 flex-shrink-0">
                {{ unit.scoreLabel }}
              </span>
            </div>
          </div>

          <!-- 3. Right: Single-point Trend Chart with target 95 (md:col-span-4) -->
          <div class="md:col-span-4 flex items-center justify-center">
            <svg viewBox="0 0 200 130" class="w-full max-w-[240px] h-auto overflow-visible">
              <text x="5" y="16" class="fill-slate-400 font-mono" font-size="9">97</text>
              <line x1="22" y1="13" x2="195" y2="13" stroke="#f8fafc" stroke-width="1" />

              <text x="5" y="46" class="fill-slate-400 font-mono" font-size="9">97</text>
              <line x1="22" y1="43" x2="195" y2="43" stroke="#f8fafc" stroke-width="1" />

              <text x="5" y="76" class="fill-slate-400 font-mono" font-size="9">96</text>
              <line x1="22" y1="73" x2="195" y2="73" stroke="#f8fafc" stroke-width="1" />

              <text x="5" y="106" class="fill-slate-400 font-mono" font-size="9">95</text>
              <line x1="22" y1="103" x2="195" y2="103" stroke="#f8fafc" stroke-width="1" />

              <!-- Dashed target 95 line -->
              <line x1="22" y1="70" x2="195" y2="70" stroke="#10b981" stroke-width="1.2" stroke-dasharray="3,3" />
              <text x="195" y="66" text-anchor="end" class="fill-emerald-600 font-medium" font-size="8.5">
                target 95
              </text>

              <!-- Data point Sep 26 (96,49) -->
              <text x="110" y="50" text-anchor="middle" class="fill-[#0B2A6F] font-bold" font-size="10" font-weight="700">
                96,49
              </text>
              <circle cx="110" cy="58" r="4.5" fill="white" stroke="#2563eb" stroke-width="2.5" />

              <!-- X-axis label -->
              <text x="110" y="122" text-anchor="middle" class="fill-slate-500 font-medium" font-size="9.5">
                Sep 26
              </text>
            </svg>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
