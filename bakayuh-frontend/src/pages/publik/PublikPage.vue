<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import type {
  TargetIku,
  MatriksRow,
  RencanaAksi,
  PerbandinganSakipRow,
  SatuanKerja,
  TahunAnggaran,
  StatusColor,
} from '@/types'
import SkeletonCard from '@/components/common/SkeletonCard.vue'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const props = defineProps<{
  defaultTab?: string
}>()

const route = useRoute()
const router = useRouter()

// ==========================================
// Tab State (No Scroll Jump)
// ==========================================
type ActiveTab = 'iku' | 'matriks' | 'renaksi' | 'sakip'

const initialTab = computed<ActiveTab>(() => {
  const qTab = (route.query.tab as string) || props.defaultTab || 'iku'
  if (['iku', 'matriks', 'renaksi', 'sakip'].includes(qTab)) {
    return qTab as ActiveTab
  }
  return 'iku'
})

const activeTab = ref<ActiveTab>(initialTab.value)

watch(
  () => route.query.tab,
  (newTab) => {
    if (newTab && ['iku', 'matriks', 'renaksi', 'sakip'].includes(newTab as string)) {
      activeTab.value = newTab as ActiveTab
    } else if (!newTab) {
      activeTab.value = (props.defaultTab as ActiveTab) || 'iku'
    }
  }
)

function setTab(tab: ActiveTab) {
  activeTab.value = tab
  router.replace({
    query: {
      ...route.query,
      tab: tab === 'iku' ? undefined : tab,
    },
  })
}

// ==========================================
// Common Metadata (Satker & Tahun)
// ==========================================
interface PublikSummary {
  tahun: number
  total_satker: number
  rata_rata_capaian_iku: number
  nilai_sakip_rerata: number
  status_zi: string
}

const summaryLoading = ref(true)
const summary = ref<PublikSummary | null>(null)
const satkers = ref<SatuanKerja[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const selectedTahunId = ref<number | null>(null)

async function fetchMetadata() {
  summaryLoading.value = true
  try {
    const [resSummary, resSatker, resTahun] = await Promise.all([
      apiClient.get<{ data: PublikSummary }>('/publik/summary'),
      apiClient.get<{ data: SatuanKerja[] }>('/satker').catch(() => ({ data: { data: [] } })),
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran').catch(() => ({ data: { data: [] } })),
    ])

    summary.value = resSummary.data.data
    satkers.value = resSatker.data.data
    tahuns.value = resTahun.data.data

    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) {
      selectedTahunId.value = aktif.id
    } else if (tahuns.value.length > 0 && tahuns.value[0]) {
      selectedTahunId.value = tahuns.value[0].id
    }
  } catch (err) {
    console.error('Failed to load portal metadata:', err)
  } finally {
    summaryLoading.value = false
  }
}

// ==========================================
// 1. Tab: Target & Realisasi IKU
// ==========================================
const ikuLoading = ref(false)
const ikuTargets = ref<TargetIku[]>([])
const ikuSatkerFilter = ref<number | null>(null)
const ikuSearchQuery = ref('')

const filteredIkuTargets = computed(() => {
  let list = ikuTargets.value
  if (ikuSatkerFilter.value) {
    list = list.filter((t) => t.satker_id === ikuSatkerFilter.value)
  }
  if (ikuSearchQuery.value.trim()) {
    const q = ikuSearchQuery.value.toLowerCase()
    list = list.filter(
      (t) =>
        (t.indikator?.nama?.toLowerCase().includes(q) ?? false) ||
        (t.indikator?.kode?.toLowerCase().includes(q) ?? false) ||
        (t.satker?.nama?.toLowerCase().includes(q) ?? false)
    )
  }
  return list
})

const {
  currentPage: ikuCurrentPage,
  pageSize: ikuPageSize,
  totalItems: ikuTotalItems,
  paginatedItems: paginatedIkuTargets,
} = usePagination(filteredIkuTargets, { defaultPageSize: 10 })

async function fetchIkuData() {
  ikuLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (ikuSatkerFilter.value) params.satker_id = ikuSatkerFilter.value

    const res = await apiClient.get<{ data: TargetIku[] }>('/target-iku', { params })
    ikuTargets.value = res.data.data
  } catch (err) {
    console.error('Failed to load IKU data:', err)
  } finally {
    ikuLoading.value = false
  }
}

// ==========================================
// 2. Tab: Matriks Capaian IKU
// ==========================================
const matriksLoading = ref(false)
const matriksRows = ref<MatriksRow[]>([])
const matriksSearch = ref('')

const filteredMatriksRows = computed(() => {
  if (!matriksSearch.value.trim()) return matriksRows.value
  const q = matriksSearch.value.toLowerCase()
  return matriksRows.value.filter(
    (row) =>
      row.satker.nama.toLowerCase().includes(q) ||
      row.satker.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage: matriksCurrentPage,
  pageSize: matriksPageSize,
  totalItems: matriksTotalItems,
  paginatedItems: paginatedMatriksRows,
} = usePagination(filteredMatriksRows, { defaultPageSize: 10 })

async function fetchMatriksData() {
  matriksLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    const res = await apiClient.get<{ data: MatriksRow[] }>('/target-iku/matriks', { params })
    matriksRows.value = res.data.data
  } catch (err) {
    console.error('Failed to load matrix data:', err)
  } finally {
    matriksLoading.value = false
  }
}

// ==========================================
// 3. Tab: Rencana Aksi (Renaksi)
// ==========================================
const renaksiLoading = ref(false)
const renaksiList = ref<RencanaAksi[]>([])
const renaksiSatkerFilter = ref<number | null>(null)
const renaksiTriwulanFilter = ref<string>('ALL')
const renaksiSearch = ref('')

const filteredRenaksiList = computed(() => {
  let list = renaksiList.value
  if (renaksiSatkerFilter.value) {
    list = list.filter((r) => r.satker_id === renaksiSatkerFilter.value)
  }
  if (renaksiTriwulanFilter.value !== 'ALL') {
    list = list.filter((r) => r.triwulan === renaksiTriwulanFilter.value)
  }
  if (renaksiSearch.value.trim()) {
    const q = renaksiSearch.value.toLowerCase()
    list = list.filter(
      (r) =>
        r.nama_aksi.toLowerCase().includes(q) ||
        (r.satker?.nama?.toLowerCase().includes(q) ?? false) ||
        (r.indikator?.nama?.toLowerCase().includes(q) ?? false)
    )
  }
  return list
})

const {
  currentPage: renaksiCurrentPage,
  pageSize: renaksiPageSize,
  totalItems: renaksiTotalItems,
  paginatedItems: paginatedRenaksiList,
} = usePagination(filteredRenaksiList, { defaultPageSize: 10 })

async function fetchRenaksiData() {
  renaksiLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (renaksiSatkerFilter.value) params.satker_id = renaksiSatkerFilter.value
    if (renaksiTriwulanFilter.value !== 'ALL') params.triwulan = renaksiTriwulanFilter.value

    const res = await apiClient.get<{ data: RencanaAksi[] }>('/rencana-aksi', { params })
    renaksiList.value = res.data.data
  } catch (err) {
    console.error('Failed to load renaksi data:', err)
  } finally {
    renaksiLoading.value = false
  }
}

function getRenaksiStatusBadge(status?: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'terverifikasi':
      return { label: 'Terverifikasi', color: 'green' }
    case 'menunggu_verifikasi':
      return { label: 'Menunggu Verifikasi', color: 'blue' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'yellow' }
    default:
      return { label: 'Belum Lapor', color: 'gray' }
  }
}

// ==========================================
// 4. Tab: Evaluasi SAKIP
// ==========================================
const sakipLoading = ref(false)
const sakipRows = ref<PerbandinganSakipRow[]>([])
const sakipSearch = ref('')

const filteredSakipRows = computed(() => {
  if (!sakipSearch.value.trim()) return sakipRows.value
  const q = sakipSearch.value.toLowerCase()
  return sakipRows.value.filter(
    (row) =>
      row.nama.toLowerCase().includes(q) ||
      row.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage: sakipCurrentPage,
  pageSize: sakipPageSize,
  totalItems: sakipTotalItems,
  paginatedItems: paginatedSakipRows,
} = usePagination(filteredSakipRows, { defaultPageSize: 10 })

async function fetchSakipData() {
  sakipLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value

    const res = await apiClient.get<{ data: PerbandinganSakipRow[] }>('/evaluasi-sakip/perbandingan', { params })
    sakipRows.value = res.data.data
  } catch (err) {
    console.error('Failed to load SAKIP data:', err)
  } finally {
    sakipLoading.value = false
  }
}

function getPredikatBadge(predikat?: string | null): { label: string; color: StatusColor } {
  if (!predikat) return { label: 'Belum Dievaluasi', color: 'gray' }
  switch (predikat) {
    case 'AA':
      return { label: 'AA - Sangat Memuaskan', color: 'green' }
    case 'A':
      return { label: 'A - Memuaskan', color: 'green' }
    case 'BB':
      return { label: 'BB - Sangat Baik', color: 'blue' }
    case 'B':
      return { label: 'B - Baik', color: 'blue' }
    case 'CC':
      return { label: 'CC - Cukup', color: 'yellow' }
    case 'C':
      return { label: 'C - Kurang', color: 'red' }
    default:
      return { label: `${predikat} - Sangat Kurang`, color: 'red' }
  }
}

// Watchers for tab switching
watch(
  () => activeTab.value,
  (tab) => {
    if (tab === 'iku' && ikuTargets.value.length === 0) fetchIkuData()
    if (tab === 'matriks' && matriksRows.value.length === 0) fetchMatriksData()
    if (tab === 'renaksi' && renaksiList.value.length === 0) fetchRenaksiData()
    if (tab === 'sakip' && sakipRows.value.length === 0) fetchSakipData()
  },
  { immediate: true }
)

// Re-fetch on Year change
watch(
  () => selectedTahunId.value,
  () => {
    if (activeTab.value === 'iku') fetchIkuData()
    else if (activeTab.value === 'matriks') fetchMatriksData()
    else if (activeTab.value === 'renaksi') fetchRenaksiData()
    else if (activeTab.value === 'sakip') fetchSakipData()
  }
)

onMounted(async () => {
  await fetchMetadata()
  if (activeTab.value === 'iku') fetchIkuData()
  else if (activeTab.value === 'matriks') fetchMatriksData()
  else if (activeTab.value === 'renaksi') fetchRenaksiData()
  else if (activeTab.value === 'sakip') fetchSakipData()
})
</script>

<template>
  <div class="space-y-8 animate-fade-in">
    <!-- Hero Banner (Government & Public Transparency Header) -->
    <div class="rounded-2xl p-8 sm:p-10 bg-gradient-to-r from-kemenkum-navy via-[#0C2B64] to-[#12397F] text-white shadow-xl relative overflow-hidden">
      <!-- Background subtle pattern -->
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none" />

      <div class="relative z-10 max-w-3xl">
        <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white leading-tight">
          Akuntabilitas Kinerja &amp; Reformasi Birokrasi
        </h1>
        <p class="text-sm sm:text-base text-kemenkum-silver/90 mt-2.5 leading-relaxed">
          Portal transparansi resmi Kantor Wilayah Kementerian Hukum Kalimantan Selatan untuk menyajikan capaian indikator kinerja utama (IKU), rencana aksi triwulanan (Renaksi), dan evaluasi SAKIP seluruh satuan kerja binaan secara terbuka tanpa syarat login.
        </p>

        <!-- Global Year Selector in Hero -->
        <div class="mt-6 flex flex-wrap items-center gap-3 pt-4 border-t border-white/10">
          <span class="text-xs text-kemenkum-silver font-medium">Tahun Anggaran Terpilih:</span>
          <div class="flex items-center gap-1.5">
            <button
              v-for="t in tahuns"
              :key="t.id"
              type="button"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all border"
              :class="selectedTahunId === t.id
                ? 'bg-kemenkum-gold text-slate-950 border-kemenkum-gold shadow-sm'
                : 'bg-white/10 text-white border-white/20 hover:bg-white/20'"
              @click="selectedTahunId = t.id"
            >
              {{ t.tahun }}
              <span v-if="t.is_aktif" class="text-[10px] ml-1 opacity-80">(Aktif)</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Public Highlights Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <template v-if="summaryLoading">
        <SkeletonCard v-for="i in 4" :key="i" :lines="2" />
      </template>

      <template v-else-if="summary">
        <!-- 1. Total Satker -->
        <div class="card p-6 border-t-4 border-kemenkum-navy hover:shadow-md transition-shadow">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Satuan Kerja Binaan</p>
          <p class="text-3xl font-black text-kemenkum-navy mt-2">{{ summary.total_satker }}</p>
          <p class="text-xs text-slate-500 mt-2">Lapas, Rutan, Kanim, Bapas, Rupbasan se-Kalsel</p>
        </div>

        <!-- 2. Rata-rata IKU -->
        <div class="card p-6 border-t-4 border-green-600 hover:shadow-md transition-shadow">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rata-rata Capaian IKU</p>
          <p class="text-3xl font-black text-green-600 mt-2">{{ summary.rata_rata_capaian_iku.toFixed(1) }}%</p>
          <p class="text-xs text-slate-500 mt-2">Kinerja organisasi tahun anggaran {{ summary.tahun }}</p>
        </div>

        <!-- 3. Nilai SAKIP Rerata -->
        <div class="card p-6 border-t-4 border-blue-600 hover:shadow-md transition-shadow">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nilai SAKIP Rerata</p>
          <p class="text-3xl font-black text-blue-600 mt-2">{{ summary.nilai_sakip_rerata ? summary.nilai_sakip_rerata.toFixed(2) : 'Belum Ada' }}</p>
          <p class="text-xs text-slate-500 mt-2">Akuntabilitas kinerja instansi</p>
        </div>

        <!-- 4. Komitmen ZI -->
        <div class="card p-6 border-t-4 border-kemenkum-gold hover:shadow-md transition-shadow">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pembangunan ZI</p>
          <p class="text-base font-black text-kemenkum-gold mt-2">{{ summary.status_zi }}</p>
          <p class="text-xs text-slate-500 mt-2">Wilayah Bebas dari Korupsi (WBK)</p>
        </div>
      </template>
    </div>

    <!-- E-PERFORMANCE MODULE SELECTOR TABS -->
    <div class="space-y-4">
      <div class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-2">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-blue-600" />
          <h2 class="text-lg font-black text-slate-900 uppercase tracking-wider">
            Modul E-Performance Terpadu
          </h2>
        </div>

        <!-- Tab Pills (4 E-Performance Modules Only) -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
            :class="activeTab === 'iku'
              ? 'bg-kemenkum-navy text-white shadow-sm ring-1 ring-kemenkum-navy'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('iku')"
          >
            <svg class="w-3.5 h-3.5 text-kemenkum-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            Target &amp; Realisasi IKU
          </button>

          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
            :class="activeTab === 'matriks'
              ? 'bg-kemenkum-navy text-white shadow-sm ring-1 ring-kemenkum-navy'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('matriks')"
          >
            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Matriks Capaian IKU
          </button>

          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
            :class="activeTab === 'renaksi'
              ? 'bg-kemenkum-navy text-white shadow-sm ring-1 ring-kemenkum-navy'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('renaksi')"
          >
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Rencana Aksi (Renaksi)
          </button>

          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
            :class="activeTab === 'sakip'
              ? 'bg-kemenkum-navy text-white shadow-sm ring-1 ring-kemenkum-navy'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('sakip')"
          >
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            Evaluasi SAKIP
          </button>
        </div>
      </div>

      <!-- TAB CONTENT: 1. TARGET & REALISASI IKU (Direct Data Panel) -->
      <div v-if="activeTab === 'iku'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
          <!-- Toolbar Header -->
          <div class="p-4 bg-slate-50/70 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
              <div>
                <label class="text-[11px] font-bold text-slate-500 block mb-1">Satuan Kerja:</label>
                <select
                  v-model="ikuSatkerFilter"
                  class="form-input text-xs py-1.5"
                  @change="fetchIkuData"
                >
                  <option :value="null">Semua Satuan Kerja</option>
                  <option v-for="s in satkers" :key="s.id" :value="s.id">{{ s.kode }} - {{ s.nama }}</option>
                </select>
              </div>

              <div>
                <label class="text-[11px] font-bold text-slate-500 block mb-1">Cari Indikator:</label>
                <input
                  v-model="ikuSearchQuery"
                  type="text"
                  placeholder="Cari kode atau nama..."
                  class="form-input text-xs py-1.5 w-48 sm:w-64"
                />
              </div>
            </div>

            <div class="text-xs text-slate-500 self-end md:self-center font-medium">
              Total Target: <span class="font-bold text-slate-800">{{ filteredIkuTargets.length }}</span> data
            </div>
          </div>

          <!-- Table View -->
          <div>
            <SkeletonTable v-if="ikuLoading" :rows="8" />

            <div v-else-if="filteredIkuTargets.length === 0" class="p-12 text-center text-slate-400">
              <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-sm font-semibold">Tidak ada data target IKU yang sesuai kriteria.</p>
            </div>

            <div v-else class="table-container">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="bg-slate-50/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-4 w-24">Kode</th>
                    <th class="py-3 px-4 min-w-[240px]">Indikator Kinerja Utama</th>
                    <th class="py-3 px-4 min-w-[180px]">Satuan Kerja</th>
                    <th class="py-3 px-4 text-right w-28">Target</th>
                    <th class="py-3 px-4 text-right w-28">Realisasi</th>
                    <th class="py-3 px-4 text-center w-36">Capaian (%)</th>
                    <th class="py-3 px-4 min-w-[160px]">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="t in paginatedIkuTargets" :key="t.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-4 font-mono font-bold text-kemenkum-navy text-xs align-middle">
                      {{ t.indikator?.kode }}
                    </td>
                    <td class="py-3 px-4 align-middle">
                      <p class="font-semibold text-slate-800 leading-snug">{{ t.indikator?.nama }}</p>
                      <span class="text-[10px] text-slate-400">Satuan: {{ t.indikator?.satuan }} &bull; Tingkat: {{ t.indikator?.level }}</span>
                    </td>
                    <td class="py-3 px-4 font-medium text-slate-700 align-middle">
                      {{ t.satker?.nama }}
                    </td>
                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-800 align-middle">
                      {{ Number(t.nilai_target).toLocaleString('id-ID') }}
                    </td>
                    <td class="py-3 px-4 text-right font-mono font-bold align-middle">
                      <span v-if="t.realisasi" class="text-slate-900">
                        {{ Number(t.realisasi.nilai_realisasi).toLocaleString('id-ID') }}
                      </span>
                      <span v-else class="text-slate-400 italic font-normal">Belum Lapor</span>
                    </td>
                    <td class="py-3 px-4 text-center align-middle">
                      <template v-if="t.realisasi">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold"
                          :class="Number(t.realisasi.persentase_capaian) >= 100
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : Number(t.realisasi.persentase_capaian) >= 80
                              ? 'bg-blue-50 text-blue-700 border border-blue-200'
                              : 'bg-amber-50 text-amber-700 border border-amber-200'">
                          {{ Number(t.realisasi.persentase_capaian).toFixed(1) }}%
                        </div>
                      </template>
                      <span v-else class="text-slate-300 font-bold">&mdash;</span>
                    </td>
                    <td class="py-3 px-4 text-slate-500 max-w-xs truncate align-middle">
                      {{ t.realisasi?.keterangan || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <AppPagination
                v-model:current-page="ikuCurrentPage"
                v-model:page-size="ikuPageSize"
                :total-items="ikuTotalItems"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: 2. MATRIKS CAPAIAN IKU (Unified Table Surface, No Nested Cards) -->
      <div v-else-if="activeTab === 'matriks'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
          <!-- Toolbar Header -->
          <div class="p-4 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <label class="text-[11px] font-bold text-slate-500 block mb-1">Cari Satuan Kerja:</label>
              <input
                v-model="matriksSearch"
                type="text"
                placeholder="Cari kode atau nama satker..."
                class="form-input text-xs py-1.5 w-64"
              />
            </div>
            <div class="text-xs text-slate-500 font-medium">
              Total Satker: <span class="font-bold text-slate-800">{{ filteredMatriksRows.length }}</span> unit
            </div>
          </div>

          <!-- Matrix Table -->
          <div>
            <SkeletonTable v-if="matriksLoading" :rows="8" />

            <div v-else-if="filteredMatriksRows.length === 0" class="p-12 text-center text-slate-400">
              <p class="text-sm font-semibold">Tidak ada matriks IKU untuk filter saat ini.</p>
            </div>

            <div v-else class="table-container overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="bg-slate-50/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-2 text-center w-12 border-r border-slate-200/80">No</th>
                    <th class="py-3 px-4 min-w-[220px] border-r border-slate-200/80">Satuan Kerja</th>
                    <th
                      v-for="ind in filteredMatriksRows[0]?.indikators || []"
                      :key="ind.indikator_id"
                      class="py-2.5 px-3 text-center min-w-[120px] border-r border-slate-200/80 last:border-r-0"
                    >
                      <span class="block font-mono font-bold text-slate-800">{{ ind.kode }}</span>
                      <span class="text-[10px] font-medium text-slate-500 truncate block max-w-[110px] mx-auto normal-case" :title="ind.nama">
                        {{ ind.nama }}
                      </span>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(row, idx) in paginatedMatriksRows" :key="row.satker.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-2 text-center font-bold text-slate-400 font-mono text-xs border-r border-slate-100 align-middle">
                      {{ (matriksCurrentPage - 1) * matriksPageSize + idx + 1 }}
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 align-middle">
                      <span class="font-bold text-slate-900 block leading-snug">{{ row.satker.nama }}</span>
                      <span class="text-[10px] font-mono text-slate-400">{{ row.satker.kode }}</span>
                    </td>
                    <td
                      v-for="ind in row.indikators"
                      :key="ind.indikator_id"
                      class="py-3 px-3 text-center border-r border-slate-100 last:border-r-0 align-middle"
                    >
                      <template v-if="ind.persentase !== null">
                        <div class="inline-flex flex-col items-center">
                          <span class="text-[11px] font-bold"
                            :class="ind.persentase >= 100
                              ? 'text-emerald-700'
                              : ind.persentase >= 80
                                ? 'text-blue-700'
                                : 'text-amber-700'">
                            {{ Number(ind.persentase).toFixed(1) }}%
                          </span>
                          <span class="text-[9px] text-slate-400 font-mono">
                            {{ ind.realisasi }}/{{ ind.target }}
                          </span>
                        </div>
                      </template>
                      <span v-else class="text-slate-300 font-bold">&mdash;</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <AppPagination
                v-model:current-page="matriksCurrentPage"
                v-model:page-size="matriksPageSize"
                :total-items="matriksTotalItems"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: 3. RENCANA AKSI (RENAKSI) (Unified Table Surface, No Nested Cards) -->
      <div v-else-if="activeTab === 'renaksi'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
          <!-- Toolbar Header -->
          <div class="p-4 bg-slate-50/70 border-b border-slate-200 space-y-3">
            <!-- Triwulan Pills -->
            <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3 overflow-x-auto">
              <span class="text-xs font-bold text-slate-500 whitespace-nowrap">Periode:</span>
              <button
                v-for="tw in ['ALL', 'TW1', 'TW2', 'TW3', 'TW4']"
                :key="tw"
                type="button"
                class="px-3 py-1 rounded-lg text-xs font-bold transition-all whitespace-nowrap"
                :class="renaksiTriwulanFilter === tw
                  ? 'bg-kemenkum-navy text-white shadow-xs'
                  : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                @click="renaksiTriwulanFilter = tw; fetchRenaksiData()"
              >
                {{ tw === 'ALL' ? 'Semua Triwulan' : tw === 'TW1' ? 'Triwulan I' : tw === 'TW2' ? 'Triwulan II' : tw === 'TW3' ? 'Triwulan III' : 'Triwulan IV' }}
              </button>
            </div>

            <!-- Secondary Filters -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <div class="flex flex-wrap items-center gap-3">
                <div>
                  <label class="text-[11px] font-bold text-slate-500 block mb-1">Satuan Kerja:</label>
                  <select
                    v-model="renaksiSatkerFilter"
                    class="form-input text-xs py-1.5"
                    @change="fetchRenaksiData"
                  >
                    <option :value="null">Semua Satuan Kerja</option>
                    <option v-for="s in satkers" :key="s.id" :value="s.id">{{ s.kode }} - {{ s.nama }}</option>
                  </select>
                </div>

                <div>
                  <label class="text-[11px] font-bold text-slate-500 block mb-1">Cari Renaksi:</label>
                  <input
                    v-model="renaksiSearch"
                    type="text"
                    placeholder="Cari nama aksi atau satker..."
                    class="form-input text-xs py-1.5 w-48 sm:w-64"
                  />
                </div>
              </div>

              <div class="text-xs text-slate-500 font-medium">
                Total: <span class="font-bold text-slate-800">{{ filteredRenaksiList.length }}</span> aksi
              </div>
            </div>
          </div>

          <!-- Renaksi Table -->
          <div>
            <SkeletonTable v-if="renaksiLoading" :rows="8" />

            <div v-else-if="filteredRenaksiList.length === 0" class="p-12 text-center text-slate-400">
              <p class="text-sm font-semibold">Tidak ada data Rencana Aksi yang sesuai.</p>
            </div>

            <div v-else class="table-container overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="bg-slate-50/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-3 text-center w-20 border-r border-slate-200/80">Periode</th>
                    <th class="py-3 px-4 min-w-[180px] border-r border-slate-200/80">Satuan Kerja</th>
                    <th class="py-3 px-4 min-w-[240px] border-r border-slate-200/80">Nama Rencana Aksi</th>
                    <th class="py-3 px-4 min-w-[160px] border-r border-slate-200/80">Target Output</th>
                    <th class="py-3 px-3 text-center w-24 border-r border-slate-200/80">Progres</th>
                    <th class="py-3 px-3 text-center w-36 border-r border-slate-200/80">Status</th>
                    <th class="py-3 px-3 text-center w-20">Daduk</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="item in paginatedRenaksiList" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-3 text-center border-r border-slate-100 align-middle">
                      <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ item.triwulan }}
                      </span>
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 align-middle">
                      <span class="font-bold text-slate-800 block leading-snug">{{ item.satker?.nama }}</span>
                      <span class="text-[10px] text-slate-400 font-mono">{{ item.indikator?.kode }}</span>
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 align-middle">
                      <p class="font-semibold text-slate-800 leading-snug">{{ item.nama_aksi }}</p>
                    </td>
                    <td class="py-3 px-4 text-slate-600 border-r border-slate-100 align-middle">
                      {{ item.target_output || '-' }}
                    </td>
                    <td class="py-3 px-3 text-center font-mono font-bold border-r border-slate-100 align-middle">
                      <span :class="Number(item.realisasi?.persentase_selesai || 0) >= 100 ? 'text-emerald-600' : 'text-slate-700'">
                        {{ item.realisasi?.persentase_selesai ?? 0 }}%
                      </span>
                    </td>
                    <td class="py-3 px-3 text-center border-r border-slate-100 align-middle">
                      <StatusBadge
                        :label="getRenaksiStatusBadge(item.realisasi?.status).label"
                        :color="getRenaksiStatusBadge(item.realisasi?.status).color"
                      />
                    </td>
                    <td class="py-3 px-3 text-center align-middle">
                      <span v-if="item.realisasi?.bukti_dukung && item.realisasi.bukti_dukung.length > 0"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600"
                        title="Data dukung terlampir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ item.realisasi.bukti_dukung.length }}
                      </span>
                      <span v-else class="text-slate-300 text-xs font-bold">&mdash;</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <AppPagination
                v-model:current-page="renaksiCurrentPage"
                v-model:page-size="renaksiPageSize"
                :total-items="renaksiTotalItems"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: 4. EVALUASI SAKIP (Unified Table Surface, No Nested Cards) -->
      <div v-else-if="activeTab === 'sakip'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
          <!-- Toolbar Header -->
          <div class="p-4 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <label class="text-[11px] font-bold text-slate-500 block mb-1">Cari Satuan Kerja:</label>
              <input
                v-model="sakipSearch"
                type="text"
                placeholder="Cari nama atau kode satker..."
                class="form-input text-xs py-1.5 w-64"
              />
            </div>
            <div class="text-xs text-slate-500 font-medium">
              Total Satker: <span class="font-bold text-slate-800">{{ filteredSakipRows.length }}</span> data
            </div>
          </div>

          <!-- SAKIP Table & Leaderboard -->
          <div>
            <SkeletonTable v-if="sakipLoading" :rows="8" />

            <div v-else-if="filteredSakipRows.length === 0" class="p-12 text-center text-slate-400">
              <p class="text-sm font-semibold">Tidak ada data SAKIP yang cocok.</p>
            </div>

            <div v-else class="table-container overflow-x-auto">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="bg-slate-50/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-3 text-center w-14 border-r border-slate-200/80">Rank</th>
                    <th class="py-3 px-4 border-r border-slate-200/80 min-w-[200px]">Satuan Kerja</th>
                    <th class="py-3 px-3 text-right border-r border-slate-200/80 w-32">Perencanaan (30%)</th>
                    <th class="py-3 px-3 text-right border-r border-slate-200/80 w-32">Pengukuran (30%)</th>
                    <th class="py-3 px-3 text-right border-r border-slate-200/80 w-28">Pelaporan (15%)</th>
                    <th class="py-3 px-3 text-right border-r border-slate-200/80 w-28">Evaluasi (25%)</th>
                    <th class="py-3 px-4 text-right border-r border-slate-200/80 font-black w-28">Nilai SAKIP</th>
                    <th class="py-3 px-4 text-center w-36">Predikat</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(row, idx) in paginatedSakipRows" :key="row.satker_id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-3 text-center font-bold text-slate-700 border-r border-slate-100 align-middle">
                      #{{ (sakipCurrentPage - 1) * sakipPageSize + idx + 1 }}
                    </td>
                    <td class="py-3 px-4 border-r border-slate-100 align-middle">
                      <span class="font-bold text-slate-900 block leading-snug">{{ row.nama }}</span>
                      <span class="text-[10px] font-mono text-slate-400">{{ row.kode }}</span>
                    </td>
                    <td class="py-3 px-3 text-right font-mono border-r border-slate-100 align-middle">{{ row.nilai_perencanaan ?? '-' }}</td>
                    <td class="py-3 px-3 text-right font-mono border-r border-slate-100 align-middle">{{ row.nilai_pengukuran ?? '-' }}</td>
                    <td class="py-3 px-3 text-right font-mono border-r border-slate-100 align-middle">{{ row.nilai_pelaporan ?? '-' }}</td>
                    <td class="py-3 px-3 text-right font-mono border-r border-slate-100 align-middle">{{ row.nilai_evaluasi ?? '-' }}</td>
                    <td class="py-3 px-4 text-right font-mono font-black text-sm text-kemenkum-navy border-r border-slate-100 align-middle">
                      {{ row.nilai_total ? Number(row.nilai_total).toFixed(2) : '-' }}
                    </td>
                    <td class="py-3 px-4 text-center align-middle">
                      <StatusBadge
                        :label="getPredikatBadge(row.predikat).label"
                        :color="getPredikatBadge(row.predikat).color"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <AppPagination
                v-model:current-page="sakipCurrentPage"
                v-model:page-size="sakipPageSize"
                :total-items="sakipTotalItems"
              />
            </div>
          </div>

          <!-- SAKIP Component Legend Footer -->
          <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 flex flex-wrap items-center justify-between gap-2">
            <span class="font-bold text-slate-700">Bobot Komponen SAKIP:</span>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px]">
              <span>Perencanaan: <strong>30%</strong></span>
              <span>Pengukuran: <strong>30%</strong></span>
              <span>Pelaporan: <strong>15%</strong></span>
              <span>Evaluasi: <strong>25%</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transparency Legal Statement (Borderless Clean Footer Text) -->
    <div class="pt-2 pb-6 text-center text-xs text-slate-400 leading-relaxed max-w-2xl mx-auto">
      Portal Transparansi Kinerja Kanwil Kementerian Hukum Kalimantan Selatan &bull; UU No. 14 Tahun 2008 &amp; PermenPAN-RB tentang SAKIP.
    </div>
  </div>
</template>