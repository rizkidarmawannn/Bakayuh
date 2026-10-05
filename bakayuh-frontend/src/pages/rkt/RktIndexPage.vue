<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type {
  RbArea,
  RbIndikator,
  RbTargetPeriode,
  RbProgressSummary,
  SatuanKerja,
  TahunAnggaran,
  PeriodeRb,
  KategoriRb,
  StatusColor,
} from '@/types'
import SkeletonCard from '@/components/common/SkeletonCard.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const props = defineProps<{
  defaultCategory?: KategoriRb
}>()

const router = useRouter()
const route = useRoute()
const { isSuperAdmin, isAdminKanwil, isOperatorSatker, user } = useAuth()
const { error: toastError, success: toastSuccess } = useToast()

const loading = ref(true)
const areas = ref<RbArea[]>([])
const targets = ref<RbTargetPeriode[]>([])
const satkers = ref<SatuanKerja[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const progress = ref<RbProgressSummary | null>(null)

const selectedTahunId = ref<number | null>(null)
const selectedSatkerId = ref<number | null>(null)
const selectedPeriode = ref<PeriodeRb>('B03')
const activeCategoryTab = ref<KategoriRb>(props.defaultCategory || 'rkt_general')
const activeAreaId = ref<number | null>(null)
const expandedIndikatorId = ref<number | null>(null)

const targetMap = computed(() => {
  const map = new Map<number, RbTargetPeriode>()
  targets.value.forEach((t) => {
    map.set(t.rb_sub_indikator_id, t)
  })
  return map
})

const currentArea = computed(() => {
  return areas.value.find((a) => a.id === activeAreaId.value) || areas.value[0] || null
})

const categoryTitle = computed(() => {
  switch (activeCategoryTab.value) {
    case 'rkt_tematik':
      return 'RKT RB TEMATIK'
    case 'rkt_meso':
      return 'RKT RB MESO'
    default:
      return 'RKT RB GENERAL'
  }
})

function getStatusBadgeConfig(status?: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'tercapai':
      return { label: 'Tercapai', color: 'blue' }
    case 'lengkap':
      return { label: 'Lengkap (Terkunci)', color: 'green' }
    case 'belum_verif':
      return { label: 'Menunggu Verif', color: 'yellow' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'red' }
    default:
      return { label: 'Belum Upload', color: 'gray' }
  }
}

function getIndikatorDadukSummary(ind: RbIndikator) {
  const totalPoints = ind.sub_indikator?.length || 0
  if (totalPoints === 0) return { total: 0, fulfilled: 0, percent: 0 }
  let fulfilled = 0
  ind.sub_indikator?.forEach((s) => {
    const t = targetMap.value.get(s.id)
    if (t && (t.status_verifikasi === 'lengkap' || t.status_verifikasi === 'tercapai')) {
      fulfilled++
    }
  })
  const percent = totalPoints > 0 ? Math.round((fulfilled / totalPoints) * 100) : 0
  return { total: totalPoints, fulfilled, percent }
}

async function loadInitialData() {
  try {
    const [resTahun, resSatker] = await Promise.all([
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran'),
      apiClient.get<{ data: SatuanKerja[] }>('/satker'),
    ])
    tahuns.value = resTahun.data.data
    satkers.value = resSatker.data.data

    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) selectedTahunId.value = aktif.id

    if (isOperatorSatker.value && user.value?.satker_id) {
      selectedSatkerId.value = user.value.satker_id
    } else if (satkers.value.length > 0 && satkers.value[0]) {
      selectedSatkerId.value = satkers.value[0].id
    }
  } catch (err) {
    console.error(err)
  }
}

async function fetchRktData() {
  if (!selectedSatkerId.value) return
  loading.value = true

  try {
    const params = {
      satker_id: selectedSatkerId.value,
      tahun_anggaran_id: selectedTahunId.value,
      periode: selectedPeriode.value,
      kategori: activeCategoryTab.value,
    }

    const [resAreas, resTargets, resProgress] = await Promise.all([
      apiClient.get<{ data: RbArea[] }>('/rb-area', { params: { kategori: activeCategoryTab.value } }),
      apiClient.get<{ data: RbTargetPeriode[] }>('/rb-target-periode', { params }),
      apiClient.get<{ data: RbProgressSummary }>('/rb-area/progress', { params }),
    ])

    areas.value = resAreas.data.data
    targets.value = resTargets.data.data
    progress.value = resProgress.data.data

    if (areas.value.length > 0 && areas.value[0]) {
      activeAreaId.value = areas.value[0].id
    } else {
      activeAreaId.value = null
    }
  } catch (err: any) {
    toastError('Gagal memuat data RKT RB', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function handleRefresh() {
  fetchRktData()
  toastSuccess('Diperbarui', 'Data RKT berhasil disinkronkan.')
}

function toggleIndikator(indId: number) {
  expandedIndikatorId.value = expandedIndikatorId.value === indId ? null : indId
}

function openWorkspace(subId: number) {
  router.push({
    path: `/lke/${subId}`,
    query: {
      satker_id: selectedSatkerId.value,
      tahun_id: selectedTahunId.value,
      periode: selectedPeriode.value,
    },
  })
}

// Watch category tab changes
watch(
  () => props.defaultCategory,
  (newVal) => {
    if (newVal) {
      activeCategoryTab.value = newVal
      fetchRktData()
    }
  }
)

watch(
  () => route.path,
  (newPath) => {
    if (newPath.includes('tematik')) {
      activeCategoryTab.value = 'rkt_tematik'
    } else if (newPath.includes('meso')) {
      activeCategoryTab.value = 'rkt_meso'
    } else if (newPath.includes('general')) {
      activeCategoryTab.value = 'rkt_general'
    }
    fetchRktData()
  }
)

watch(activeCategoryTab, () => {
  fetchRktData()
})

onMounted(async () => {
  if (route.path.includes('tematik')) {
    activeCategoryTab.value = 'rkt_tematik'
  } else if (route.path.includes('meso')) {
    activeCategoryTab.value = 'rkt_meso'
  } else if (route.path.includes('general')) {
    activeCategoryTab.value = 'rkt_general'
  }

  await loadInitialData()
  await fetchRktData()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gold text-slate-900">
            Roadmap RB Nasional
          </span>
          <span class="text-xs text-slate-400 font-bold">Instruksi Presiden &amp; PermenPAN-RB</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
          Rencana Kerja Tahunan Reformasi Birokrasi (RKT RB)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Monitoring aksi dan data dukung RKT RB General, Tematik (Dampak Nyata), dan Meso Kemenkumham Kalsel
        </p>
      </div>
    </div>

    <!-- Category Tabs Selector -->
    <div class="flex items-center gap-2 border-b border-slate-200 pt-2">
      <router-link
        to="/rkt/general"
        class="px-5 py-2.5 text-xs font-black transition-all border-b-2 -mb-px flex items-center gap-2"
        :class="activeCategoryTab === 'rkt_general'
          ? 'border-kemenkum-navy text-kemenkum-navy'
          : 'border-transparent text-slate-500 hover:text-slate-700'"
      >
        <span>1. RKT RB General</span>
      </router-link>

      <router-link
        to="/rkt/tematik"
        class="px-5 py-2.5 text-xs font-black transition-all border-b-2 -mb-px flex items-center gap-2"
        :class="activeCategoryTab === 'rkt_tematik'
          ? 'border-kemenkum-navy text-kemenkum-navy'
          : 'border-transparent text-slate-500 hover:text-slate-700'"
      >
        <span>2. RKT RB Tematik (Prioritas)</span>
      </router-link>

      <router-link
        to="/rkt/meso"
        class="px-5 py-2.5 text-xs font-black transition-all border-b-2 -mb-px flex items-center gap-2"
        :class="activeCategoryTab === 'rkt_meso'
          ? 'border-kemenkum-navy text-kemenkum-navy'
          : 'border-transparent text-slate-500 hover:text-slate-700'"
      >
        <span>3. RKT RB Meso (Lintas Sektor)</span>
      </router-link>
    </div>

    <!-- Filter Bar: Satuan Kerja berdampingan dengan Tahun Anggaran, Periode Daduk terpisah lega -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm space-y-4">
      <!-- Baris 1: Tahun Anggaran & Satuan Kerja (Berdampingan Rapi) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Tahun Anggaran -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Anggaran</label>
          <select
            v-model="selectedTahunId"
            class="input-select text-xs py-2.5 w-full bg-slate-50 border-slate-200 rounded-lg"
            @change="fetchRktData"
          >
            <option v-for="t in tahuns" :key="t.id" :value="t.id">
              {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
            </option>
          </select>
        </div>

        <!-- Satuan Kerja -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Satuan Kerja</label>
          <select
            v-if="!isOperatorSatker"
            v-model="selectedSatkerId"
            class="input-select text-xs py-2.5 w-full bg-slate-50 border-slate-200 rounded-lg"
            @change="fetchRktData"
          >
            <option v-for="s in satkers" :key="s.id" :value="s.id">
              [{{ s.kode }}] {{ s.nama }}
            </option>
          </select>
          <input
            v-else
            type="text"
            disabled
            :value="user?.satker?.nama || ''"
            class="input-text text-xs py-2.5 w-full bg-slate-50 cursor-not-allowed"
          />
        </div>
      </div>

      <!-- Baris 2: Periode Pemenuhan Daduk yang Lega -->
      <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <span class="text-xs font-bold text-slate-700 block">Periode Pemenuhan Daduk</span>
          <span class="text-[11px] text-slate-400">Pilih triwulan pemenuhan berkas verifikasi</span>
        </div>
        <div class="grid grid-cols-4 gap-2 w-full sm:w-auto">
          <button
            v-for="p in (['B03', 'B06', 'B09', 'B12'] as PeriodeRb[])"
            :key="p"
            type="button"
            class="px-5 py-2 text-xs font-black rounded-lg transition-all border text-center"
            :class="selectedPeriode === p
              ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-sm'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
            @click="selectedPeriode = p; fetchRktData()"
          >
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Progress Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Progres Pemenuhan {{ categoryTitle }} Periode {{ selectedPeriode }}</span>
          <p class="text-lg font-black text-slate-800 mt-0.5">
            {{ progress?.percentage ?? 0 }}% Terverifikasi Lengkap
          </p>
        </div>
        <div class="flex items-center gap-3 text-xs">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block" />
            <span class="text-slate-600 font-semibold">{{ progress?.lengkap_count ?? 0 }} Lengkap</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block" />
            <span class="text-slate-600 font-semibold">{{ progress?.belum_verif_count ?? 0 }} Menunggu</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-rose-500 inline-block" />
            <span class="text-slate-600 font-semibold">{{ progress?.perlu_perbaikan_count ?? 0 }} Perbaikan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-slate-300 inline-block" />
            <span class="text-slate-600 font-semibold">{{ progress?.belum_upload_count ?? 0 }} Belum</span>
          </div>
        </div>
      </div>

      <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
        <div
          class="h-2.5 rounded-full bg-emerald-500 transition-all duration-700"
          :style="{ width: `${progress?.percentage ?? 0}%` }"
        />
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-4">
      <SkeletonCard v-for="n in 3" :key="n" />
    </div>

    <!-- Main Flat UI (Persis Contoh Halaman 3 PDF) -->
    <div v-else class="space-y-4">
      <!-- Area Selector Pill Bar -->
      <div v-if="areas.length > 1" class="flex items-center gap-2.5 overflow-x-auto pb-2 pt-1 scrollbar-thin">
        <button
          v-for="area in areas"
          :key="area.id"
          type="button"
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2.5 border shadow-xs select-none focus:outline-none"
          :class="activeAreaId === area.id
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy shadow-sm ring-1 ring-kemenkum-navy'
            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:border-slate-300 hover:text-kemenkum-navy'"
          @click="activeAreaId = area.id"
        >
          <span
            class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider shrink-0 transition-colors"
            :class="activeAreaId === area.id
              ? 'bg-kemenkum-gold text-kemenkum-navy-dark shadow-xs'
              : 'bg-slate-100 text-slate-600 border border-slate-200'"
          >
            {{ area.kode }}
          </span>
          <span
            class="font-bold tracking-tight"
            :class="activeAreaId === area.id ? 'text-white' : 'text-slate-700'"
          >
            {{ area.nama_area }}
          </span>
        </button>
      </div>

      <div class="card p-6 bg-white border border-slate-200 shadow-sm space-y-5">
        <!-- Top Title & Refresh Button -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">{{ categoryTitle }}</span>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-semibold">
              <span>Area</span>
              <span>/</span>
              <span class="text-kemenkum-navy font-bold">Indikator</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight mt-1 uppercase">
              {{ currentArea?.nama_area || 'Area RKT' }}
            </h2>
          </div>

          <button
            type="button"
            class="px-3.5 py-2 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-kemenkum-navy transition-all flex items-center gap-1.5"
            @click="handleRefresh"
          >
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
          </button>
        </div>

        <!-- Flat Table (Scroll Alami ke Bawah) -->
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-600 font-bold border-y border-slate-200">
              <tr>
                <th class="py-3 px-4 w-1/2">Indikator</th>
                <th class="py-3 px-4 w-1/3">Pemenuhan Daduk</th>
                <th class="py-3 px-4 text-center w-1/6">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <template v-for="ind in (currentArea?.indikator || [])" :key="ind.id">
                <tr class="hover:bg-slate-50/70 transition-colors">
                  <!-- Col 1: Indikator Info -->
                  <td class="py-4 px-4 align-top">
                    <div class="space-y-1">
                      <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-black bg-slate-100 text-slate-700">
                          {{ ind.kode }}
                        </span>
                        <span class="text-sm font-bold text-slate-900 leading-snug">
                          {{ ind.nama_indikator }}
                        </span>
                      </div>
                      <p v-if="ind.keterangan_juknis" class="text-[11px] text-slate-500 leading-relaxed pl-8">
                        {{ ind.keterangan_juknis }}
                      </p>
                    </div>
                  </td>

                  <!-- Col 2: Pemenuhan Daduk Progress & Period Chips -->
                  <td class="py-4 px-4 align-top">
                    <div class="space-y-2">
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-800">
                          {{ getIndikatorDadukSummary(ind).fulfilled }}/{{ getIndikatorDadukSummary(ind).total }}
                          ({{ getIndikatorDadukSummary(ind).percent }}%)
                        </span>
                      </div>

                      <!-- Mini Progress Bar -->
                      <div class="w-36 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="h-1.5 rounded-full bg-emerald-500"
                          :style="{ width: `${getIndikatorDadukSummary(ind).percent}%` }"
                        />
                      </div>

                      <!-- Period Badge Chips (B03, B06, B09, B12) -->
                      <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                        <span
                          v-for="p in (['B03', 'B06', 'B09', 'B12'] as PeriodeRb[])"
                          :key="p"
                          class="px-2 py-0.5 rounded text-[10px] font-mono font-bold tracking-tight"
                          :class="selectedPeriode === p
                            ? 'bg-slate-900 text-white'
                            : 'bg-slate-100 text-slate-600'"
                        >
                          {{ p }} {{ getIndikatorDadukSummary(ind).fulfilled }}/{{ getIndikatorDadukSummary(ind).total }}
                        </span>
                      </div>
                    </div>
                  </td>

                  <!-- Col 3: Golden Pill Masuk Button -->
                  <td class="py-4 px-4 align-top text-center">
                    <button
                      type="button"
                      class="px-5 py-2 rounded-full font-black text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 mx-auto"
                      :class="expandedIndikatorId === ind.id
                        ? 'bg-slate-800 text-white hover:bg-slate-700'
                        : 'bg-gold hover:bg-gold-light text-slate-950'"
                      @click="toggleIndikator(ind.id)"
                    >
                      <span>{{ expandedIndikatorId === ind.id ? 'Tutup' : 'Masuk' }}</span>
                      <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': expandedIndikatorId === ind.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  </td>
                </tr>

                <!-- Expanded Row: Sub-Indikator Poin Checklist & Workspace Daduk -->
                <tr v-if="expandedIndikatorId === ind.id" class="bg-slate-50/80">
                  <td colspan="3" class="p-4 sm:p-5 border-y border-slate-200">
                    <div class="space-y-3">
                      <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                          Daftar Poin Penilaian &bull; {{ ind.nama_indikator }}
                        </span>
                        <span class="text-[11px] font-bold text-kemenkum-navy">Periode {{ selectedPeriode }}</span>
                      </div>

                      <div class="divide-y divide-slate-200/60 bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div
                          v-for="sub in ind.sub_indikator"
                          :key="sub.id"
                          class="p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-slate-50"
                        >
                          <div class="space-y-1 flex-1">
                            <div class="flex items-start gap-2">
                              <span class="w-5 h-5 rounded bg-slate-100 text-slate-700 text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">
                                {{ sub.nomor_poin }}
                              </span>
                              <p class="text-xs font-bold text-slate-800">
                                {{ sub.judul_poin }}
                              </p>
                            </div>
                            <div v-if="sub.checklist_daduk" class="text-[11px] text-slate-500 pl-7">
                              <span class="font-semibold text-slate-600">Bukti Dukung:</span> {{ sub.checklist_daduk }}
                            </div>
                          </div>

                          <div class="flex items-center justify-end gap-3 flex-shrink-0 pl-7 md:pl-0">
                            <StatusBadge
                              :label="getStatusBadgeConfig(targetMap.get(sub.id)?.status_verifikasi).label"
                              :color="getStatusBadgeConfig(targetMap.get(sub.id)?.status_verifikasi).color"
                            />

                            <button
                              type="button"
                              class="btn-primary py-1.5 px-3 text-xs inline-flex items-center gap-1.5 bg-kemenkum-navy text-white hover:bg-kemenkum-navy-dark"
                              @click="openWorkspace(sub.id)"
                            >
                              <span>Workspace Daduk</span>
                              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-if="!(currentArea?.indikator?.length)">
                <td colspan="3" class="py-8 text-center text-slate-400">
                  Area perubahan tidak ditemukan atau belum ada indikator.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>