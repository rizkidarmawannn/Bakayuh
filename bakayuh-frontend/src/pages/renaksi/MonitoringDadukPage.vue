<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Filter Triwulan
const dadukFilterTw = ref('Semua triwulan')
const twOptions = [
  'Semua triwulan',
  'Triwulan I',
  'Triwulan II',
  'Triwulan III',
  'Triwulan IV',
]

// Filter Kartu Metrik
export type MetricFilterType = 'all' | 'diterima' | 'menunggu' | 'perbaikan' | 'belum'
const activeMetricFilter = ref<MetricFilterType>('all')

// Tabel Data Dukung Per Unit
interface DadukRow {
  unit: string
  total: number
  belum: number
  menunggu: number
  perbaikan: number
  diterima: number
  progres: number
}

const dadukTableRows = ref<DadukRow[]>([
  { unit: 'Setjen', total: 4, belum: 1, menunggu: 1, perbaikan: 1, diterima: 1, progres: 25 },
  { unit: 'AHU', total: 3, belum: 1, menunggu: 1, perbaikan: 0, diterima: 1, progres: 33 },
  { unit: 'KI', total: 2, belum: 0, menunggu: 1, perbaikan: 1, diterima: 0, progres: 0 },
  { unit: 'PP', total: 2, belum: 1, menunggu: 0, perbaikan: 0, diterima: 1, progres: 50 },
  { unit: 'BPHN', total: 3, belum: 2, menunggu: 1, perbaikan: 0, diterima: 0, progres: 0 },
  { unit: 'BSK', total: 2, belum: 1, menunggu: 0, perbaikan: 1, diterima: 0, progres: 0 },
])

// Kalkulasi Dinamis Metrik
const totalRenaksi = computed(() => dadukTableRows.value.reduce((acc, r) => acc + r.total, 0))
const totalBelum = computed(() => dadukTableRows.value.reduce((acc, r) => acc + r.belum, 0))
const totalMenunggu = computed(() => dadukTableRows.value.reduce((acc, r) => acc + r.menunggu, 0))
const totalPerbaikan = computed(() => dadukTableRows.value.reduce((acc, r) => acc + r.perbaikan, 0))
const totalDiterima = computed(() => dadukTableRows.value.reduce((acc, r) => acc + r.diterima, 0))
const persenDiterima = computed(() =>
  totalRenaksi.value > 0 ? Math.round((totalDiterima.value / totalRenaksi.value) * 100) : 0,
)

// Filter Baris Tabel berdasarkan Metric Filter
const filteredRows = computed(() => {
  if (activeMetricFilter.value === 'all') return dadukTableRows.value
  if (activeMetricFilter.value === 'menunggu') return dadukTableRows.value.filter((r) => r.menunggu > 0)
  if (activeMetricFilter.value === 'diterima') return dadukTableRows.value.filter((r) => r.diterima > 0)
  if (activeMetricFilter.value === 'perbaikan') return dadukTableRows.value.filter((r) => r.perbaikan > 0)
  if (activeMetricFilter.value === 'belum') return dadukTableRows.value.filter((r) => r.belum > 0)
  return dadukTableRows.value
})

function toggleMetricFilter(filter: MetricFilterType) {
  if (activeMetricFilter.value === filter) {
    activeMetricFilter.value = 'all'
  } else {
    activeMetricFilter.value = filter
  }
}

function resetFilter() {
  activeMetricFilter.value = 'all'
}

function goToVerifikasi() {
  router.push({ path: '/verifikasi', query: { status: 'Menunggu' } })
}

function handleRowClick(unitName: string) {
  router.push({ path: '/sasaran-renaksi', query: { unit: unitName } })
}

// ========================================================
// POP-UP ATUR TENGGAT PENGUMPULAN
// ========================================================
const showTenggatModal = ref(false)
const tenggatData = ref({
  tw1: '',
  tw2: '',
  tw3: '',
  tw4: '31/12/2026',
})

const STORAGE_KEY_TENGGAT = 'bakayuh_tenggat_daduk'

onMounted(() => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_TENGGAT)
    if (raw) {
      const parsed = JSON.parse(raw)
      tenggatData.value = {
        tw1: parsed.tw1 || '',
        tw2: parsed.tw2 || '',
        tw3: parsed.tw3 || '',
        tw4: parsed.tw4 || '31/12/2026',
      }
    }
  } catch {
    // fallback default
  }
})

function openTenggatModal() {
  showTenggatModal.value = true
}

function closeTenggatModal() {
  showTenggatModal.value = false
}

function saveTenggat() {
  localStorage.setItem(STORAGE_KEY_TENGGAT, JSON.stringify(tenggatData.value))
  triggerToast('Tenggat pengumpulan berhasil disimpan!')
  closeTenggatModal()
}

// Konversi tanggal input bantu (format YYYY-MM-DD <-> DD/MM/YYYY)
function onNativeDateChange(e: Event, key: 'tw1' | 'tw2' | 'tw3' | 'tw4') {
  const target = e.target as HTMLInputElement
  if (!target.value) return
  const parts = target.value.split('-')
  if (parts.length === 3) {
    tenggatData.value[key] = `${parts[2]}/${parts[1]}/${parts[0]}`
  }
}

function getIsoFromFormatted(formatted: string): string {
  if (!formatted) return ''
  const parts = formatted.split('/')
  if (parts.length === 3) {
    const [d, m, y] = parts
    return `${y}-${m?.padStart(2, '0')}-${d?.padStart(2, '0')}`
  }
  return ''
}

// ========================================================
// EKSPOR REKAP (CSV)
// ========================================================
function exportDadukCsv() {
  const headers = [
    'No',
    'Unit',
    'Total Renaksi',
    'Belum Ada Daduk',
    'Menunggu Verifikasi',
    'Perlu Perbaikan',
    'Sudah Diterima',
    'Progres (%)',
  ]

  const rows = filteredRows.value.map((r, i) => [
    i + 1,
    `"${r.unit}"`,
    r.total,
    r.belum,
    r.menunggu,
    r.perbaikan,
    r.diterima,
    `"${r.progres}%"`,
  ])

  // Summary row
  rows.push([
    '',
    '"TOTAL"',
    totalRenaksi.value,
    totalBelum.value,
    totalMenunggu.value,
    totalPerbaikan.value,
    totalDiterima.value,
    `"${persenDiterima.value}%"`,
  ])

  const csvContent = '\uFEFF' + [headers.join(','), ...rows.map((r) => r.join(','))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  const twLabel = dadukFilterTw.value.replace(/\s+/g, '_')
  link.setAttribute('download', `Rekap_Monitoring_Daduk_${twLabel}_2026.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)

  triggerToast('Rekap Monitoring Daduk berhasil diekspor ke CSV!')
}

// ========================================================
// TOAST FEEDBACK
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
    <!-- Toast Feedback Notification -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Header with Filters & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Dashboard Monitoring Daduk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Status rekapitulasi data dukung seluruh unit kerja per triwulan
          <span v-if="tenggatData.tw4" class="text-slate-400 font-normal">
            &bull; Tenggat TW IV: {{ tenggatData.tw4 }}
          </span>
        </p>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
        <!-- Filter Triwulan -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="dadukFilterTw"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="tw in twOptions" :key="tw" :value="tw">{{ tw }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Tombol Atur Tenggat -->
        <button
          type="button"
          @click="openTenggatModal"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs active:scale-95 transition-all"
        >
          Atur Tenggat
        </button>

        <!-- Tombol Ekspor Rekap (CSV) -->
        <button
          type="button"
          @click="exportDadukCsv"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs active:scale-95 transition-all"
        >
          Ekspor Rekap (CSV)
        </button>
      </div>
    </div>

    <!-- Monitoring Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <!-- 5 Metric Boxes in a single row - Interaktif & Bisa Dipencet sebagai Filter -->
      <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-y sm:divide-y-0 divide-slate-100 border-b border-slate-100 bg-slate-50/50">
        <!-- Card 1: Total renaksi -->
        <button
          type="button"
          @click="toggleMetricFilter('all')"
          class="p-4 sm:p-5 text-center cursor-pointer transition-all select-none group focus:outline-none"
          :class="activeMetricFilter === 'all' ? 'bg-blue-50/80 ring-2 ring-inset ring-[#0B2A6F]' : 'hover:bg-slate-100/70'"
        >
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] group-hover:scale-105 inline-block transition-transform">
            {{ totalRenaksi }}
          </span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Total renaksi</span>
        </button>

        <!-- Card 2: Sudah diterima -->
        <button
          type="button"
          @click="toggleMetricFilter('diterima')"
          class="p-4 sm:p-5 text-center cursor-pointer transition-all select-none group focus:outline-none"
          :class="activeMetricFilter === 'diterima' ? 'bg-emerald-50/80 ring-2 ring-inset ring-emerald-500' : 'hover:bg-slate-100/70'"
        >
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] group-hover:scale-105 inline-block transition-transform">
            {{ persenDiterima }}%
          </span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Sudah diterima</span>
        </button>

        <!-- Card 3: Menunggu verifikasi > -->
        <div
          class="p-4 sm:p-5 text-center cursor-pointer transition-all select-none group relative focus:outline-none"
          :class="activeMetricFilter === 'menunggu' ? 'bg-amber-50/80 ring-2 ring-inset ring-amber-500' : 'hover:bg-slate-100/70'"
          role="button"
          tabindex="0"
          @click="toggleMetricFilter('menunggu')"
          @keydown.enter="toggleMetricFilter('menunggu')"
        >
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] group-hover:scale-105 inline-block transition-transform">
            {{ totalMenunggu }}
          </span>
          <div class="flex items-center justify-center gap-1 mt-1">
            <span class="text-[11px] font-medium text-slate-600 group-hover:text-amber-700">
              Menunggu verifikasi &gt;
            </span>
          </div>
        </div>

        <!-- Card 4: Perlu perbaikan -->
        <button
          type="button"
          @click="toggleMetricFilter('perbaikan')"
          class="p-4 sm:p-5 text-center cursor-pointer transition-all select-none group focus:outline-none"
          :class="activeMetricFilter === 'perbaikan' ? 'bg-rose-50/80 ring-2 ring-inset ring-rose-500' : 'hover:bg-slate-100/70'"
        >
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] group-hover:scale-105 inline-block transition-transform">
            {{ totalPerbaikan }}
          </span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Perlu perbaikan</span>
        </button>

        <!-- Card 5: Belum ada daduk -->
        <button
          type="button"
          @click="toggleMetricFilter('belum')"
          class="p-4 sm:p-5 text-center col-span-2 sm:col-span-1 cursor-pointer transition-all select-none group focus:outline-none"
          :class="activeMetricFilter === 'belum' ? 'bg-slate-200/70 ring-2 ring-inset ring-slate-400' : 'hover:bg-slate-100/70'"
        >
          <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] group-hover:scale-105 inline-block transition-transform">
            {{ totalBelum }}
          </span>
          <span class="text-[11px] font-medium text-slate-500 block mt-1">Belum ada daduk</span>
        </button>
      </div>

      <!-- Active Filter Banner & Shortcut Link -->
      <div
        v-if="activeMetricFilter !== 'all'"
        class="px-5 py-2.5 bg-blue-50/70 border-b border-blue-100 flex flex-wrap items-center justify-between gap-3 text-xs"
      >
        <div class="flex items-center gap-2 text-slate-700 font-medium">
          <span class="w-2 h-2 rounded-full bg-[#1a73e8]" />
          <span>
            Filter aktif:
            <strong class="text-[#0B2A6F] capitalize">
              {{
                activeMetricFilter === 'menunggu'
                  ? 'Menunggu Verifikasi'
                  : activeMetricFilter === 'diterima'
                    ? 'Sudah Diterima'
                    : activeMetricFilter === 'perbaikan'
                      ? 'Perlu Perbaikan'
                      : 'Belum Ada Daduk'
              }}
            </strong>
            ({{ filteredRows.length }} unit)
          </span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Tombol pintasan langsung ke halaman Verifikasi Daduk jika filter menunggu aktif -->
          <button
            v-if="activeMetricFilter === 'menunggu'"
            type="button"
            @click="goToVerifikasi"
            class="px-3 py-1 rounded-md bg-[#1a73e8] text-white hover:bg-[#1557b0] font-semibold text-xs transition-colors shadow-2xs"
          >
            Buka Halaman Verifikasi Daduk &rarr;
          </button>
          <button
            type="button"
            @click="resetFilter"
            class="px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"
          >
            Reset Filter
          </button>
        </div>
      </div>

      <!-- Flat Table -->
      <div class="table-container overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3 px-4 min-w-[160px] border-r border-white/10">UNIT</th>
              <th class="py-3 px-3 text-center w-20 border-r border-white/10">TOTAL</th>
              <th class="py-3 px-3 text-center w-20 border-r border-white/10">BELUM</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">MENUNGGU</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">PERBAIKAN</th>
              <th class="py-3 px-3 text-center w-24 border-r border-white/10">DITERIMA</th>
              <th class="py-3 px-4 min-w-[160px]">PROGRES</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in filteredRows"
              :key="row.unit"
              @click="handleRowClick(row.unit)"
              class="hover:bg-blue-50/50 cursor-pointer transition-colors group"
              title="Klik untuk membuka sasaran dan indikator unit ini"
            >
              <td class="py-3.5 px-4 font-bold text-slate-900 text-xs sm:text-sm group-hover:text-[#0B2A6F] flex items-center gap-1.5">
                <span>{{ row.unit }}</span>
                <svg class="w-3 h-3 text-slate-400 group-hover:text-[#0B2A6F] transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
              </td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.total }}</td>
              <td class="py-3.5 px-3 text-center font-mono text-slate-700">{{ row.belum }}</td>
              <td
                class="py-3.5 px-3 text-center font-mono font-bold"
                :class="row.menunggu > 0 ? 'text-amber-600 bg-amber-50/50' : 'text-slate-700'"
              >
                {{ row.menunggu }}
              </td>
              <td
                class="py-3.5 px-3 text-center font-mono font-bold"
                :class="row.perbaikan > 0 ? 'text-rose-600 bg-rose-50/50' : 'text-slate-700'"
              >
                {{ row.perbaikan }}
              </td>
              <td
                class="py-3.5 px-3 text-center font-mono font-bold"
                :class="row.diterima > 0 ? 'text-emerald-600 bg-emerald-50/50' : 'text-slate-700'"
              >
                {{ row.diterima }}
              </td>
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :class="row.progres > 0 ? 'bg-emerald-500' : 'bg-slate-200'"
                      :style="{ width: row.progres + '%' }"
                    />
                  </div>
                  <span class="text-[11px] font-mono text-slate-500 w-9 text-right font-semibold">
                    {{ row.progres }}%
                  </span>
                </div>
              </td>
            </tr>

            <!-- Jika tidak ada data yang cocok dengan filter -->
            <tr v-if="filteredRows.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                Tidak ada unit kerja dengan kriteria status ini.
                <button type="button" @click="resetFilter" class="text-[#0B2A6F] font-bold underline ml-1">
                  Tampilkan semua unit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-3.5 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
        <span>Klik baris unit untuk melihat sasaran dan indikatornya.</span>
        <button
          type="button"
          @click="goToVerifikasi"
          class="text-[#1a73e8] hover:underline font-semibold text-xs inline-flex items-center gap-1"
        >
          <span>Ke Halaman Verifikasi Daduk</span>
          <span>&rarr;</span>
        </button>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL ATUR TENGGAT PENGUMPULAN (Sesuai Referensi Gambar) -->
    <!-- ======================================================== -->
    <div
      v-if="showTenggatModal"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div
        class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5 animate-scale-up"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between">
          <h2 class="text-base sm:text-lg font-bold text-slate-900">
            Atur Tenggat Pengumpulan
          </h2>
          <button
            type="button"
            @click="closeTenggatModal"
            class="text-slate-400 hover:text-slate-600 transition-colors p-1"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Helper Text Subtitle -->
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
          Berlaku untuk semua unit dan semua renaksi pada triwulan tersebut. Kosongkan jika belum ditentukan.
        </p>

        <!-- Form 4 Triwulan Dates -->
        <div class="space-y-4">
          <!-- Tenggat TW I -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-800">
              Tenggat TW I
            </label>
            <div class="relative">
              <input
                v-model="tenggatData.tw1"
                type="text"
                placeholder="dd/mm/yyyy"
                class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 pr-10 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <input
                type="date"
                class="sr-only"
                :value="getIsoFromFormatted(tenggatData.tw1)"
                @change="(e) => onNativeDateChange(e, 'tw1')"
              />
            </div>
          </div>

          <!-- Tenggat TW II -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-800">
              Tenggat TW II
            </label>
            <div class="relative">
              <input
                v-model="tenggatData.tw2"
                type="text"
                placeholder="dd/mm/yyyy"
                class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 pr-10 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Tenggat TW III -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-800">
              Tenggat TW III
            </label>
            <div class="relative">
              <input
                v-model="tenggatData.tw3"
                type="text"
                placeholder="dd/mm/yyyy"
                class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 pr-10 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Tenggat TW IV -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-800">
              Tenggat TW IV
            </label>
            <div class="relative">
              <input
                v-model="tenggatData.tw4"
                type="text"
                placeholder="dd/mm/yyyy"
                class="w-full bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 pr-10 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              />
              <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer Actions (Right-aligned Batal and Simpan) -->
        <div class="flex items-center justify-end gap-2.5 pt-3">
          <button
            type="button"
            @click="closeTenggatModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-2xs"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveTenggat"
            class="px-5 py-2 text-xs font-semibold rounded-lg bg-[#1a73e8] hover:bg-[#1557b0] text-white transition-colors shadow-xs"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
