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
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'
import gedungKanwilUrl from '@/assets/gedung-kanwil.jpg'

const props = defineProps<{
  defaultTab?: string
}>()

const route = useRoute()
const router = useRouter()

// ==========================================
// Tab State (No Scroll Jump)
// ==========================================
type ActiveTab = 'beranda' | 'iku' | 'matriks' | 'renaksi' | 'sakip' | 'pohon'

function normalizeTab(rawTab?: string): ActiveTab {
  if (!rawTab || rawTab === 'beranda') return 'beranda'
  if (rawTab === 'pohon') return 'pohon'
  if (rawTab === 'renaksi') return 'renaksi'
  if (rawTab === 'capaian_pk' || rawTab === 'iku') return 'iku'
  if (rawTab === 'matriks') return 'matriks'
  if (rawTab === 'ikpa' || rawTab === 'sakip') return 'sakip'
  return 'beranda'
}

const initialTab = computed<ActiveTab>(() => {
  const qTab = (route.query.tab as string) || props.defaultTab
  return normalizeTab(qTab)
})

const activeTab = ref<ActiveTab>(initialTab.value)

watch(
  () => route.query.tab,
  (newTab) => {
    activeTab.value = normalizeTab(newTab as string)
  }
)

function setTab(tab: ActiveTab) {
  activeTab.value = tab
  if (tab === 'beranda') {
    router.replace({ path: '/' })
  } else {
    router.replace({
      path: '/',
      query: {
        ...route.query,
        tab: tab === 'iku' ? 'capaian_pk' : (tab === 'sakip' ? 'ikpa' : tab),
      },
    })
  }
}

function selectModule(tab: ActiveTab) {
  setTab(tab)
  setTimeout(() => {
    const el = document.getElementById('modul-detail-panel')
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }, 60)
}

function scrollToModulKinerja() {
  const el = document.getElementById('modul-kinerja')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
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
    <div class="rounded-3xl p-6 sm:p-8 lg:p-10 bg-[#0B2A6F] text-white shadow-xl relative overflow-hidden">
      <!-- Background subtle pattern -->
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none" />

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
        <!-- Left Column: Content -->
        <div class="lg:col-span-7 flex flex-col items-start">
          <!-- Pill Badge -->
          <span class="inline-block px-3.5 py-1 rounded-lg bg-white text-[#0B2A6F] font-black text-xs uppercase tracking-wider mb-4 shadow-sm">
            BAKAYUH
          </span>

          <!-- Title -->
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white leading-tight">
            Sistem Akuntabilitas Kinerja<br class="hidden sm:inline" />
            Kantor Wilayah Kementerian<br class="hidden sm:inline" />
            Hukum Kalimantan Selatan
          </h1>

          <!-- Subtext -->
          <p class="text-xs sm:text-sm text-white/90 mt-4 leading-relaxed max-w-xl">
            Aplikasi untuk memudahkan pemantauan dan pengendalian kinerja dalam rangka meningkatkan akuntabilitas dan kinerja unit kerja, mulai dari pohon kinerja, rencana aksi, capaian perjanjian kinerja, hingga pelaksanaan anggaran.
          </p>

          <!-- Button -->
          <button
            type="button"
            @click="scrollToModulKinerja"
            class="mt-6 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-[#F9C823] hover:bg-amber-400 text-slate-950 transition-all shadow-md inline-flex items-center gap-2 group cursor-pointer"
          >
            <span>Eksplorasi Modul Kinerja</span>
            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
          </button>

          <!-- Global Year Selector in Hero -->
          <div class="mt-6 flex flex-wrap items-center gap-2.5 pt-4 border-t border-white/10 w-full">
            <span class="text-xs text-white/70 font-medium">Tahun Anggaran:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
              <button
                v-for="t in tahuns"
                :key="t.id"
                type="button"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all border"
                :class="selectedTahunId === t.id
                  ? 'bg-[#F9C823] text-slate-950 border-[#F9C823] shadow-sm'
                  : 'bg-white/10 text-white border-white/20 hover:bg-white/20'"
                @click="selectedTahunId = t.id"
              >
                {{ t.tahun }}
                <span v-if="t.is_aktif" class="text-[10px] ml-1 opacity-80">(Aktif)</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Building Photo -->
        <div class="lg:col-span-5 flex justify-center lg:justify-end">
          <div class="w-full max-w-md lg:max-w-none rounded-2xl lg:rounded-3xl overflow-hidden shadow-2xl border border-white/15 aspect-[16/10] bg-slate-900 group">
            <img
              :src="gedungKanwilUrl"
              alt="Gedung Kantor Wilayah Kementerian Hukum Kalimantan Selatan"
              class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out"
              loading="eager"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: MODUL KINERJA (4 Clickable Cards - Strictly NO Emoji, NO Icon) -->
    <div id="modul-kinerja" class="space-y-4 pt-4">
      <div class="border-b border-slate-200 pb-3">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Modul Kinerja
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Pilih modul untuk melihat informasi kinerja terkini. Data diperbarui otomatis.
        </p>
      </div>

      <!-- 4 Clickable Cards Grid (No Emoji, No Icon) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. Pohon Kinerja -->
        <div
          role="button"
          tabindex="0"
          @click="selectModule('pohon')"
          @keydown.enter="selectModule('pohon')"
          class="bg-white rounded-2xl p-6 border transition-all duration-200 cursor-pointer flex flex-col justify-between group hover:-translate-y-0.5 select-none"
          :class="activeTab === 'pohon'
            ? 'border-kemenkum-navy ring-2 ring-kemenkum-navy/20 shadow-md bg-gradient-to-b from-blue-50/40 to-white'
            : 'border-slate-200 hover:border-slate-300 hover:shadow-card-hover'"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-blue-100 text-blue-800">
                01 &bull; Perencanaan
              </span>
              <span v-if="activeTab === 'pohon'" class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                Aktif
              </span>
            </div>
            <h3 class="text-base font-black text-slate-900 group-hover:text-blue-700 transition-colors">
              Pohon Kinerja
            </h3>
            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
              Keterkaitan kinerja dari Kementerian, sasaran strategis, Eselon I, hingga Kantor Wilayah.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-dashed border-slate-200 flex items-center justify-between text-xs font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
            <span>Lihat selengkapnya</span>
            <span>&rarr;</span>
          </div>
        </div>

        <!-- 2. Renaksi -->
        <div
          role="button"
          tabindex="0"
          @click="selectModule('renaksi')"
          @keydown.enter="selectModule('renaksi')"
          class="bg-white rounded-2xl p-6 border transition-all duration-200 cursor-pointer flex flex-col justify-between group hover:-translate-y-0.5 select-none"
          :class="activeTab === 'renaksi'
            ? 'border-kemenkum-navy ring-2 ring-kemenkum-navy/20 shadow-md bg-gradient-to-b from-emerald-50/40 to-white'
            : 'border-slate-200 hover:border-slate-300 hover:shadow-card-hover'"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800">
                02 &bull; Pelaksanaan
              </span>
              <span v-if="activeTab === 'renaksi'" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                Aktif
              </span>
            </div>
            <h3 class="text-base font-black text-slate-900 group-hover:text-emerald-700 transition-colors">
              Renaksi
            </h3>
            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
              Progres rencana aksi dan pengumpulan data dukung tiap unit per triwulan.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-dashed border-slate-200 flex items-center justify-between text-xs font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
            <span>Lihat selengkapnya</span>
            <span>&rarr;</span>
          </div>
        </div>

        <!-- 3. Capaian PK -->
        <div
          role="button"
          tabindex="0"
          @click="selectModule('iku')"
          @keydown.enter="selectModule('iku')"
          class="bg-white rounded-2xl p-6 border transition-all duration-200 cursor-pointer flex flex-col justify-between group hover:-translate-y-0.5 select-none"
          :class="activeTab === 'iku' || activeTab === 'matriks'
            ? 'border-kemenkum-navy ring-2 ring-kemenkum-navy/20 shadow-md bg-gradient-to-b from-indigo-50/40 to-white'
            : 'border-slate-200 hover:border-slate-300 hover:shadow-card-hover'"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-indigo-100 text-indigo-800">
                03 &bull; Pengukuran
              </span>
              <span v-if="activeTab === 'iku' || activeTab === 'matriks'" class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                Aktif
              </span>
            </div>
            <h3 class="text-base font-black text-slate-900 group-hover:text-indigo-700 transition-colors">
              Capaian PK
            </h3>
            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
              Capaian kinerja atas Perjanjian Kinerja per bulan, lengkap dengan rekap triwulan dan semester.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-dashed border-slate-200 flex items-center justify-between text-xs font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
            <span>Lihat selengkapnya</span>
            <span>&rarr;</span>
          </div>
        </div>

        <!-- 4. IKPA -->
        <div
          role="button"
          tabindex="0"
          @click="selectModule('sakip')"
          @keydown.enter="selectModule('sakip')"
          class="bg-white rounded-2xl p-6 border transition-all duration-200 cursor-pointer flex flex-col justify-between group hover:-translate-y-0.5 select-none"
          :class="activeTab === 'sakip'
            ? 'border-kemenkum-navy ring-2 ring-kemenkum-navy/20 shadow-md bg-gradient-to-b from-amber-50/40 to-white'
            : 'border-slate-200 hover:border-slate-300 hover:shadow-card-hover'"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-800">
                04 &bull; Anggaran
              </span>
              <span v-if="activeTab === 'sakip'" class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                Aktif
              </span>
            </div>
            <h3 class="text-base font-black text-slate-900 group-hover:text-amber-700 transition-colors">
              IKPA
            </h3>
            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">
              Indikator Kinerja Pelaksanaan Anggaran per satuan kerja per bulan.
            </p>
          </div>
          <div class="pt-4 mt-4 border-t border-dashed border-slate-200 flex items-center justify-between text-xs font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
            <span>Lihat selengkapnya</span>
            <span>&rarr;</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ACTIVE MODULE DETAIL WRAPPER (Displayed when a module tab is selected) -->
    <div v-if="activeTab !== 'beranda'" id="modul-detail-panel" class="space-y-4 pt-2">
      <!-- In Capaian PK, provide quick sub-toggle between Target & Matriks -->
      <div v-if="activeTab === 'iku' || activeTab === 'matriks'" class="flex items-center justify-between border-b border-slate-200 pb-3 flex-wrap gap-2">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-indigo-600" />
          <h3 class="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wider">
            Capaian Perjanjian Kinerja
          </h3>
        </div>
        <div class="flex items-center gap-1.5">
          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
            :class="activeTab === 'iku'
              ? 'bg-kemenkum-navy text-white shadow-sm'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('iku')"
          >
            Target &amp; Realisasi IKU
          </button>
          <button
            type="button"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
            :class="activeTab === 'matriks'
              ? 'bg-kemenkum-navy text-white shadow-sm'
              : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
            @click="setTab('matriks')"
          >
            Matriks Capaian IKU
          </button>
        </div>
      </div>

      <!-- TAB CONTENT: 0. POHON KINERJA (Logic Model) -->
      <div v-if="activeTab === 'pohon'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200 gap-3">
            <div>
              <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Modul Perencanaan Kinerja</span>
              <h3 class="text-xl font-black text-slate-900 mt-1">Arsitektur Pohon Kinerja (Logic Model)</h3>
              <p class="text-xs text-slate-500 mt-0.5">Penyelarasan mandat perundang-undangan dan sasaran strategis Kanwil Kementerian Hukum Kalimantan Selatan</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold self-start sm:self-auto">
              <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse" />
              17 Satker Terkoneksi
            </span>
          </div>

          <!-- 3-Tier Logic Model Breakdown -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Level 1: Ultimate Outcome -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-blue-100 text-blue-800">Tingkat 1 &bull; Impact</span>
                <span class="text-xs font-bold text-slate-400">Outcome Utama</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Kepastian Hukum &amp; Pelayanan Publik Berkualitas</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Terwujudnya supremasi hukum yang berkeadilan, penegakan HAM, kepastian hukum keimigrasian &amp; pemasyarakatan yang humanis di wilayah Kalimantan Selatan.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Indeks Kepuasan Masyarakat (IKM), Indeks Reformasi Hukum (IRH).
              </div>
            </div>

            <!-- Level 2: Intermediate Outcome -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-amber-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-amber-100 text-amber-800">Tingkat 2 &bull; Intermediate</span>
                <span class="text-xs font-bold text-slate-400">Sasaran Program</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Akuntabilitas &amp; Reformasi Birokrasi Satker Binaan</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Optimalisasi tata kelola birokrasi berintegritas tinggi bebas korupsi (WBK/WBBM) dan peningkatan maturitas SPIP di seluruh 17 Unit Pelaksana Teknis se-Kalsel.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Nilai Evaluasi SAKIP &ge; BB, Predikat WBK/WBBM Satker.
              </div>
            </div>

            <!-- Level 3: Output & Activities -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-emerald-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Tingkat 3 &bull; Output</span>
                <span class="text-xs font-bold text-slate-400">Rencana Aksi</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Pelaksanaan Rencana Aksi Triwulanan Terukur</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Realisasi rencana aksi kinerja secara bertahap mulai dari TW I hingga TW IV dengan verifikasi kelayakan bukti dukung 100% tepat waktu.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Kepatuhan Upload Renaksi &ge; 95%, Capaian Output Terverifikasi.
              </div>
            </div>
          </div>

          <!-- Cascading Action Banner -->
          <div class="p-5 bg-gradient-to-r from-kemenkum-navy to-slate-900 text-white rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1">
              <h4 class="text-sm font-bold text-kemenkum-gold">Penyelarasan Cascading Target Perjanjian Kinerja</h4>
              <p class="text-xs text-slate-300">
                Setiap sasaran strategis Kantor Wilayah diturunkan menjadi target Indikator Kinerja Utama (IKU) dan Perjanjian Kinerja (PK) tahunan pada seluruh satuan kerja binaan.
              </p>
            </div>
            <button
              type="button"
              @click="setTab('iku')"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-[#F9C823] text-slate-950 hover:bg-amber-400 transition-colors shadow flex-shrink-0"
            >
              Lihat Capaian PK &rarr;
            </button>
          </div>
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