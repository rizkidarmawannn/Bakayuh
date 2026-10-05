<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import type { DashboardSummary, TahunAnggaran } from '@/types'
import SkeletonCard from '@/components/common/SkeletonCard.vue'

interface ChartData {
  sakip_radar: {
    categories: string[]
    series: number[]
  }
  renaksi_quarterly: {
    triwulan: string
    total: number
    selesai: number
    belum: number
  }[]
  lke_distribution: {
    labels: string[]
    series: number[]
  }
  sakip_ranking: {
    satker: string
    nilai: number
    predikat: string
  }[]
}

const { user, isSuperAdmin, isAdminKanwil } = useAuth()

const loading = ref(true)
const summary = ref<DashboardSummary | null>(null)
const charts = ref<ChartData | null>(null)
const tahuns = ref<TahunAnggaran[]>([])
const selectedTahunId = ref<number | null>(null)
const error = ref<string | null>(null)

// ApexCharts Options
const radarChartOptions = computed(() => ({
  chart: {
    type: 'radar',
    toolbar: { show: false },
    fontFamily: 'inherit',
  },
  colors: ['#0C2B64'],
  fill: {
    opacity: 0.3,
  },
  markers: {
    size: 4,
    colors: ['#C8993D'],
    strokeColors: '#fff',
    strokeWidth: 2,
  },
  xaxis: {
    categories: charts.value?.sakip_radar.categories || [],
    labels: {
      style: {
        fontSize: '11px',
        fontWeight: 600,
        colors: ['#475569', '#475569', '#475569', '#475569'],
      },
    },
  },
  yaxis: {
    show: false,
    max: 100,
  },
}))

const radarChartSeries = computed(() => [
  {
    name: 'Rata-rata Skor (%)',
    data: charts.value?.sakip_radar.series || [],
  },
])

const donutChartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit',
  },
  labels: charts.value?.lke_distribution.labels || [],
  colors: ['#10B981', '#F59E0B', '#EF4444', '#94A3B8'],
  legend: {
    position: 'bottom',
    fontSize: '11px',
    markers: { radius: 12 },
  },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Target',
            fontSize: '11px',
            color: '#64748B',
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
}))

const donutChartSeries = computed(() => charts.value?.lke_distribution.series || [])

const barChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    stacked: true,
    toolbar: { show: false },
    fontFamily: 'inherit',
  },
  colors: ['#10B981', '#E2E8F0'],
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '40%',
      borderRadius: 6,
    },
  },
  xaxis: {
    categories: charts.value?.renaksi_quarterly.map((r) => r.triwulan) || ['TW1', 'TW2', 'TW3', 'TW4'],
    labels: {
      style: { fontSize: '11px', fontWeight: 600 },
    },
  },
  yaxis: {
    title: { text: 'Jumlah Rencana Aksi', style: { fontSize: '11px', color: '#64748B' } },
  },
  legend: { position: 'top', fontSize: '11px' },
  dataLabels: { enabled: false },
}))

const barChartSeries = computed(() => [
  {
    name: 'Terverifikasi Selesai',
    data: charts.value?.renaksi_quarterly.map((r) => r.selesai) || [],
  },
  {
    name: 'Belum Selesai',
    data: charts.value?.renaksi_quarterly.map((r) => r.belum) || [],
  },
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

    const [resSummary, resCharts] = await Promise.all([
      apiClient.get<{ data: DashboardSummary }>('/dashboard/summary', { params }),
      apiClient.get<{ data: ChartData }>('/dashboard/charts', { params }),
    ])

    summary.value = resSummary.data.data
    charts.value = resCharts.data.data
  } catch (err: any) {
    error.value = err.response?.data?.message ?? 'Gagal memuat ringkasan dashboard.'
  } finally {
    loading.value = false
  }
}

function getDownloadUrl(type: 'iku' | 'sakip'): string {
  const t = selectedTahunId.value ? `?tahun_anggaran_id=${selectedTahunId.value}` : ''
  return `http://127.0.0.1:8000/api/export/${type}/excel${t}`
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
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gold text-slate-950">
            BAKAYUH Analytics
          </span>
          <span class="text-xs text-slate-400 font-bold">Kanwil Kemenkumham Kalsel</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
          Dashboard Eksekutif Terpadu
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Pemantauan terpusat Indikator Kinerja Utama (IKU), SAKIP, Renaksi, dan LKE ZI 2026
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- Tahun Selector -->
        <select
          v-model="selectedTahunId"
          class="input-select text-xs py-2 w-44 bg-white shadow-sm"
          @change="loadData"
        >
          <option v-for="t in tahuns" :key="t.id" :value="t.id">
            Tahun {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
          </option>
        </select>

        <button
          type="button"
          class="btn-secondary text-xs py-2 px-3 inline-flex items-center gap-1.5"
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

    <!-- Error State -->
    <div v-if="error" class="card p-4 border-l-4 border-red-500 bg-red-50 text-red-800 text-xs">
      {{ error }}
    </div>

    <!-- 4 High Level Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <template v-if="loading">
        <SkeletonCard v-for="i in 4" :key="i" :lines="2" />
      </template>

      <template v-else-if="summary">
        <!-- Card 1: IKU Capaian -->
        <div class="card p-5 bg-white border border-slate-200 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata IKU Se-Kalsel</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <div class="mt-2">
            <p class="text-3xl font-black text-slate-800">{{ summary.rata_rata_capaian_iku }}%</p>
            <p class="text-xs text-slate-400 mt-1">Capaian kinerja sasaran strategis</p>
          </div>
        </div>

        <!-- Card 2: Renaksi -->
        <div class="card p-5 bg-white border border-slate-200 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rencana Aksi Selesai</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-2">
            <p class="text-3xl font-black text-slate-800">
              {{ summary.renaksi_selesai }} <span class="text-sm font-semibold text-slate-400">/ {{ summary.renaksi_total }}</span>
            </p>
            <p class="text-xs text-slate-400 mt-1">Telah diverifikasi sah tim Kanwil</p>
          </div>
        </div>

        <!-- Card 3: SAKIP Tertinggi -->
        <div class="card p-5 bg-white border border-slate-200 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">SAKIP Tertinggi</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
            </div>
          </div>
          <div class="mt-2">
            <div v-if="summary.sakip_tertinggi" class="flex items-baseline gap-2">
              <span class="text-3xl font-black text-slate-800">{{ summary.sakip_tertinggi.nilai }}</span>
              <span class="px-2 py-0.5 rounded text-[11px] font-black bg-emerald-100 text-emerald-800">
                Predikat {{ summary.sakip_tertinggi.predikat }}
              </span>
            </div>
            <p v-if="summary.sakip_tertinggi" class="text-xs font-bold text-slate-600 truncate mt-1" :title="summary.sakip_tertinggi.satker">
              {{ summary.sakip_tertinggi.satker }}
            </p>
            <p v-else class="text-xs text-slate-400 mt-1">Belum ada evaluasi</p>
          </div>
        </div>

        <!-- Card 4: LKE ZI Progress -->
        <div class="card p-5 bg-white border border-slate-200 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pemenuhan Data Dukung ZI</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
          </div>
          <div class="mt-2">
            <p class="text-3xl font-black text-slate-800">{{ summary.lke_progress.percentage }}%</p>
            <p class="text-xs text-slate-400 mt-1">
              {{ summary.lke_progress.fulfilled }} dari {{ summary.lke_progress.total }} target lengkap
            </p>
          </div>
        </div>
      </template>
    </div>

    <!-- Quick Export Bar -->
    <div class="card p-4 bg-gradient-to-r from-kemenkum-navy to-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gold">Ekspor Data & Laporan Resmi</p>
        <p class="text-xs text-slate-300 mt-0.5">Unduh data rekapitulasi kinerja satker se-Kalimantan Selatan dalam format Excel</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <a
          :href="getDownloadUrl('iku')"
          target="_blank"
          class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black tracking-wide inline-flex items-center gap-2 shadow-md border border-emerald-400/40 transition-all hover:scale-[1.02]"
        >
          <svg class="w-4 h-4 text-emerald-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Ekspor Matriks IKU</span>
        </a>

        <a
          :href="getDownloadUrl('sakip')"
          target="_blank"
          style="background-color: #F5BA31; color: #091F4A;"
          class="px-4 py-2.5 rounded-xl text-xs font-black tracking-wide inline-flex items-center gap-2 shadow-md border border-amber-300 hover:brightness-105 transition-all hover:scale-[1.02]"
        >
          <svg class="w-4 h-4 text-[#091F4A] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span style="color: #091F4A;" class="font-black">Ekspor Komparasi SAKIP</span>
        </a>
      </div>
    </div>

    <!-- Charts Row 1: Radar SAKIP & Donut LKE Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Chart 1: SAKIP Radar -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div>
          <h2 class="text-sm font-bold text-slate-800">Capaian 4 Komponen SAKIP Se-Kalsel</h2>
          <p class="text-xs text-slate-400">Rata-rata capaian instansi: Perencanaan, Pengukuran, Pelaporan, Evaluasi</p>
        </div>

        <div v-if="loading" class="h-64 flex items-center justify-center text-slate-400 text-xs">
          Memuat visualisasi...
        </div>
        <div v-else class="h-64">
          <apexchart
            type="radar"
            height="100%"
            :options="radarChartOptions"
            :series="radarChartSeries"
          />
        </div>
      </div>

      <!-- Chart 2: LKE Distribution Donut -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div>
          <h2 class="text-sm font-bold text-slate-800">Distribusi Status Data Dukung ZI 2026</h2>
          <p class="text-xs text-slate-400">Komposisi status verifikasi berkas bukti dukung 6 Area Pengungkit</p>
        </div>

        <div v-if="loading" class="h-64 flex items-center justify-center text-slate-400 text-xs">
          Memuat visualisasi...
        </div>
        <div v-else class="h-64">
          <apexchart
            type="donut"
            height="100%"
            :options="donutChartOptions"
            :series="donutChartSeries"
          />
        </div>
      </div>
    </div>

    <!-- Charts Row 2: Renaksi Quarterly Stacked Bar & Top SAKIP Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart 3: Renaksi Quarterly -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4 lg:col-span-2">
        <div>
          <h2 class="text-sm font-bold text-slate-800">Progres Rencana Aksi per Triwulan (TW I - IV)</h2>
          <p class="text-xs text-slate-400">Jumlah kegiatan rencana aksi yang telah selesai vs dalam pelaksanaan</p>
        </div>

        <div v-if="loading" class="h-64 flex items-center justify-center text-slate-400 text-xs">
          Memuat visualisasi...
        </div>
        <div v-else class="h-64">
          <apexchart
            type="bar"
            height="100%"
            :options="barChartOptions"
            :series="barChartSeries"
          />
        </div>
      </div>

      <!-- SAKIP Top Leaderboard -->
      <div class="card p-6 bg-white border border-slate-200 space-y-4">
        <div>
          <h2 class="text-sm font-bold text-slate-800">Peringkat SAKIP Satker</h2>
          <p class="text-xs text-slate-400">Satuan kerja dengan nilai tertinggi tahun berjalan</p>
        </div>

        <div v-if="loading" class="space-y-3">
          <SkeletonCard v-for="n in 5" :key="n" />
        </div>

        <div v-else-if="!charts?.sakip_ranking.length" class="text-center py-10 text-xs text-slate-400">
          Belum ada data evaluasi SAKIP.
        </div>

        <div v-else class="space-y-2.5">
          <div
            v-for="(item, idx) in charts.sakip_ranking"
            :key="idx"
            class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <span
                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0"
                :class="{
                  'bg-gold text-slate-950': idx === 0,
                  'bg-slate-200 text-slate-700': idx === 1,
                  'bg-amber-100 text-amber-800': idx === 2,
                  'bg-slate-100 text-slate-500': idx > 2,
                }"
              >
                {{ idx + 1 }}
              </span>
              <span class="text-xs font-bold text-slate-800 truncate" :title="item.satker">
                {{ item.satker }}
              </span>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <span class="text-xs font-black text-slate-900">{{ item.nilai }}</span>
              <span class="text-[10px] px-1.5 py-0.5 rounded font-black bg-blue-50 text-blue-700">
                {{ item.predikat }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>