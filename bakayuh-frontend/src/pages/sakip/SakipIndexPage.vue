<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { PerbandinganSakipRow, TahunAnggaran, SatuanKerja, StatusColor } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const router = useRouter()
const { isSuperAdmin, isAdminKanwil } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const rows = ref<PerbandinganSakipRow[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const satkers = ref<SatuanKerja[]>([])
const selectedTahunId = ref<number | null>(null)
const searchQuery = ref('')

// Modal Evaluation State
const evalModalOpen = ref(false)
const modalSubmitting = ref(false)
const editingEvaluasiId = ref<number | null>(null)

const evalForm = reactive({
  tahun_anggaran_id: 0,
  satker_id: 0,
  nilai_perencanaan: 80,
  nilai_pengukuran: 80,
  nilai_pelaporan: 80,
  nilai_evaluasi: 80,
  catatan: '',
})

// Live Preview of Weighted Score and Predicate
const liveTotal = computed(() => {
  const p = Number(evalForm.nilai_perencanaan) || 0
  const peng = Number(evalForm.nilai_pengukuran) || 0
  const pel = Number(evalForm.nilai_pelaporan) || 0
  const e = Number(evalForm.nilai_evaluasi) || 0

  const total = (p * 0.30) + (peng * 0.30) + (pel * 0.15) + (e * 0.25)
  return Number(total.toFixed(2))
})

const livePredikat = computed<{ predikat: string; label: string; color: StatusColor }>(() => {
  const n = liveTotal.value
  if (n > 90) return { predikat: 'AA', label: 'Sangat Memuaskan', color: 'green' }
  if (n >= 80) return { predikat: 'A', label: 'Memuaskan', color: 'green' }
  if (n >= 70) return { predikat: 'BB', label: 'Sangat Baik', color: 'blue' }
  if (n >= 60) return { predikat: 'B', label: 'Baik', color: 'blue' }
  if (n >= 50) return { predikat: 'CC', label: 'Cukup', color: 'yellow' }
  if (n >= 30) return { predikat: 'C', label: 'Kurang', color: 'red' }
  return { predikat: 'D', label: 'Sangat Kurang', color: 'red' }
})

// Predikat color styling helper
function getPredikatBadge(predikat: string | null): { label: string; color: StatusColor } {
  if (!predikat) return { label: 'Belum Dinilai', color: 'gray' }
  switch (predikat) {
    case 'AA':
    case 'A':
      return { label: predikat, color: 'green' }
    case 'BB':
    case 'B':
      return { label: predikat, color: 'blue' }
    case 'CC':
      return { label: predikat, color: 'yellow' }
    case 'C':
    case 'D':
      return { label: predikat, color: 'red' }
    default:
      return { label: predikat, color: 'gray' }
  }
}

// Summary Statistics
const stats = computed(() => {
  const evaluated = rows.value.filter((r) => r.nilai_total !== null)
  if (evaluated.length === 0) {
    return {
      evaluatedCount: 0,
      totalCount: rows.value.length,
      average: 0,
      highest: null,
      lowest: null,
    }
  }

  const sum = evaluated.reduce((acc, cur) => acc + (cur.nilai_total || 0), 0)
  const avg = Number((sum / evaluated.length).toFixed(2))

  const sorted = [...evaluated].sort((a, b) => (b.nilai_total || 0) - (a.nilai_total || 0))
  const highest = sorted[0]
  const lowest = sorted[sorted.length - 1]

  return {
    evaluatedCount: evaluated.length,
    totalCount: rows.value.length,
    average: avg,
    highest,
    lowest,
  }
})

// Search filter
const filteredRows = computed(() => {
  if (!searchQuery.value.trim()) return rows.value
  const q = searchQuery.value.toLowerCase()
  return rows.value.filter(
    (r) => r.nama.toLowerCase().includes(q) || r.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedRows,
} = usePagination(filteredRows, { defaultPageSize: 10 })

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
  } catch (err) {
    console.error(err)
  }
}

async function fetchPerbandingan() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value

    const res = await apiClient.get<{ data: PerbandinganSakipRow[] }>('/evaluasi-sakip/perbandingan', { params })
    rows.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat evaluasi SAKIP', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openCreateModal(row?: PerbandinganSakipRow) {
  evalForm.tahun_anggaran_id = selectedTahunId.value ?? (tahuns.value[0]?.id || 0)
  if (row) {
    editingEvaluasiId.value = row.evaluasi_id
    evalForm.satker_id = row.satker_id
    evalForm.nilai_perencanaan = row.nilai_perencanaan ?? 80
    evalForm.nilai_pengukuran = row.nilai_pengukuran ?? 80
    evalForm.nilai_pelaporan = row.nilai_pelaporan ?? 80
    evalForm.nilai_evaluasi = row.nilai_evaluasi ?? 80
    evalForm.catatan = ''
  } else {
    editingEvaluasiId.value = null
    evalForm.satker_id = satkers.value[0]?.id || 0
    evalForm.nilai_perencanaan = 80
    evalForm.nilai_pengukuran = 80
    evalForm.nilai_pelaporan = 80
    evalForm.nilai_evaluasi = 80
    evalForm.catatan = ''
  }
  evalModalOpen.value = true
}

async function handleSaveEvaluasi() {
  modalSubmitting.value = true
  try {
    if (editingEvaluasiId.value) {
      await apiClient.put(`/evaluasi-sakip/${editingEvaluasiId.value}`, {
        nilai_perencanaan: evalForm.nilai_perencanaan,
        nilai_pengukuran: evalForm.nilai_pengukuran,
        nilai_pelaporan: evalForm.nilai_pelaporan,
        nilai_evaluasi: evalForm.nilai_evaluasi,
        catatan: evalForm.catatan,
      })
      toastSuccess('Berhasil', 'Evaluasi SAKIP berhasil diperbarui.')
    } else {
      await apiClient.post('/evaluasi-sakip', evalForm)
      toastSuccess('Berhasil', 'Evaluasi SAKIP berhasil disimpan.')
    }
    evalModalOpen.value = false
    await fetchPerbandingan()
  } catch (err: any) {
    toastError('Gagal menyimpan evaluasi', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

onMounted(async () => {
  await loadInitialData()
  await fetchPerbandingan()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Evaluasi SAKIP (4 Komponen)</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Sistem Akuntabilitas Kinerja Instansi Pemerintah (Perencanaan 30%, Pengukuran 30%, Pelaporan 15%, Evaluasi 25%)
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="isSuperAdmin || isAdminKanwil"
          class="btn-primary inline-flex items-center gap-2 text-xs"
          @click="openCreateModal()"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Input Nilai SAKIP
        </button>
      </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="card p-4 bg-white border border-slate-200">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Se-Kalsel</p>
        <p class="text-2xl font-black text-kemenkum-navy mt-1">
          {{ stats.average }} <span class="text-xs font-normal text-slate-400">/ 100</span>
        </p>
        <p class="text-[11px] text-slate-400 mt-1">
          {{ stats.evaluatedCount }} dari {{ stats.totalCount }} satker dinilai
        </p>
      </div>

      <div class="card p-4 bg-emerald-50/50 border border-emerald-100">
        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Nilai Tertinggi</p>
        <div v-if="stats.highest" class="mt-1">
          <p class="text-2xl font-black text-emerald-700">{{ stats.highest.nilai_total }}</p>
          <p class="text-xs font-bold text-slate-800 truncate" :title="stats.highest.nama">{{ stats.highest.nama }}</p>
        </div>
        <p v-else class="text-sm font-semibold text-slate-400 mt-2">Belum ada data</p>
      </div>

      <div class="card p-4 bg-rose-50/50 border border-rose-100">
        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Nilai Terendah</p>
        <div v-if="stats.lowest" class="mt-1">
          <p class="text-2xl font-black text-rose-700">{{ stats.lowest.nilai_total }}</p>
          <p class="text-xs font-bold text-slate-800 truncate" :title="stats.lowest.nama">{{ stats.lowest.nama }}</p>
        </div>
        <p v-else class="text-sm font-semibold text-slate-400 mt-2">Belum ada data</p>
      </div>

      <div class="card p-4 bg-white border border-slate-200 flex flex-col justify-between">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bobot Komponen SAKIP</p>
        <div class="grid grid-cols-4 gap-1 text-center mt-2">
          <div class="p-1 rounded bg-slate-50">
            <span class="block text-[10px] text-slate-400">Ren</span>
            <span class="text-xs font-black text-slate-700">30%</span>
          </div>
          <div class="p-1 rounded bg-slate-50">
            <span class="block text-[10px] text-slate-400">Ukur</span>
            <span class="text-xs font-black text-slate-700">30%</span>
          </div>
          <div class="p-1 rounded bg-slate-50">
            <span class="block text-[10px] text-slate-400">Lapor</span>
            <span class="text-xs font-black text-slate-700">15%</span>
          </div>
          <div class="p-1 rounded bg-slate-50">
            <span class="block text-[10px] text-slate-400">Eval</span>
            <span class="text-xs font-black text-slate-700">25%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-4 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-48">
          <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Tahun Anggaran</label>
          <select
            v-model="selectedTahunId"
            class="input-select text-xs py-1.5"
            @change="fetchPerbandingan"
          >
            <option v-for="t in tahuns" :key="t.id" :value="t.id">
              {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
            </option>
          </select>
        </div>
      </div>

      <div class="w-full sm:w-72">
        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-0.5">Cari Satuan Kerja</label>
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Ketik nama atau kode..."
            class="input-text text-xs py-1.5 pl-8"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Comparison Table -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="6" :cols="8" />

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="w-72">Satuan Kerja</th>
              <th class="text-center w-28">Perencanaan<br><span class="text-[10px] font-normal text-slate-400">(Bobot 30%)</span></th>
              <th class="text-center w-28">Pengukuran<br><span class="text-[10px] font-normal text-slate-400">(Bobot 30%)</span></th>
              <th class="text-center w-28">Pelaporan<br><span class="text-[10px] font-normal text-slate-400">(Bobot 15%)</span></th>
              <th class="text-center w-28">Evaluasi<br><span class="text-[10px] font-normal text-slate-400">(Bobot 25%)</span></th>
              <th class="text-center w-32 bg-slate-50 font-black">Nilai Akhir<br><span class="text-[10px] font-normal text-slate-400">(Total 100%)</span></th>
              <th class="text-center w-28">Predikat</th>
              <th class="text-center w-32">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, idx) in paginatedRows"
              :key="row.satker_id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="text-center text-xs font-semibold text-slate-400">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
              <td class="text-xs">
                <span class="font-bold text-slate-800">{{ row.nama }}</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="text-[10px] text-slate-400 font-mono">{{ row.kode }}</span>
                  <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 uppercase font-semibold">
                    {{ row.tipe }}
                  </span>
                </div>
              </td>

              <!-- Perencanaan 30% -->
              <td class="text-center text-xs">
                <span v-if="row.nilai_perencanaan !== null" class="font-semibold text-slate-700">
                  {{ row.nilai_perencanaan }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>

              <!-- Pengukuran 30% -->
              <td class="text-center text-xs">
                <span v-if="row.nilai_pengukuran !== null" class="font-semibold text-slate-700">
                  {{ row.nilai_pengukuran }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>

              <!-- Pelaporan 15% -->
              <td class="text-center text-xs">
                <span v-if="row.nilai_pelaporan !== null" class="font-semibold text-slate-700">
                  {{ row.nilai_pelaporan }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>

              <!-- Evaluasi 25% -->
              <td class="text-center text-xs">
                <span v-if="row.nilai_evaluasi !== null" class="font-semibold text-slate-700">
                  {{ row.nilai_evaluasi }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>

              <!-- Nilai Akhir Total -->
              <td class="text-center text-xs bg-slate-50/60 font-black">
                <span
                  v-if="row.nilai_total !== null"
                  class="text-sm font-black"
                  :class="{
                    'text-emerald-700': (row.nilai_total ?? 0) >= 80,
                    'text-blue-700': (row.nilai_total ?? 0) >= 60 && (row.nilai_total ?? 0) < 80,
                    'text-amber-600': (row.nilai_total ?? 0) >= 50 && (row.nilai_total ?? 0) < 60,
                    'text-rose-600': (row.nilai_total ?? 0) < 50,
                  }"
                >
                  {{ row.nilai_total }}
                </span>
                <span v-else class="text-slate-300 text-xs font-normal">Belum ada</span>
              </td>

              <!-- Predikat -->
              <td class="text-center">
                <StatusBadge
                  :label="getPredikatBadge(row.predikat).label"
                  :color="getPredikatBadge(row.predikat).color"
                />
              </td>

              <!-- Aksi -->
              <td class="text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <router-link
                    :to="`/sakip/${row.satker_id}`"
                    class="btn-secondary py-1 px-2.5 text-[11px] inline-flex items-center gap-1"
                    title="Rincian & Tren"
                  >
                    <span>Detail</span>
                  </router-link>

                  <button
                    v-if="isSuperAdmin || isAdminKanwil"
                    class="p-1 text-slate-400 hover:text-kemenkum-navy rounded transition-colors"
                    :title="row.evaluasi_id ? 'Ubah Nilai' : 'Input Nilai'"
                    @click="openCreateModal(row)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AppPagination
        v-if="filteredRows.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>

    <!-- Evaluation Modal -->
    <div
      v-if="evalModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-base font-bold text-slate-800">
              {{ editingEvaluasiId ? 'Ubah Evaluasi SAKIP' : 'Input Nilai Evaluasi SAKIP' }}
            </h2>
            <p class="text-xs text-slate-400">Penilaian 4 komponen akuntabilitas kinerja instansi pemerintah</p>
          </div>
          <button
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
            @click="evalModalOpen = false"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleSaveEvaluasi">
          <!-- Tahun & Satker Selection -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Anggaran</label>
              <select v-model="evalForm.tahun_anggaran_id" class="input-select text-xs py-2" required>
                <option v-for="t in tahuns" :key="t.id" :value="t.id">
                  {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Kerja</label>
              <select
                v-model="evalForm.satker_id"
                class="input-select text-xs py-2"
                :disabled="editingEvaluasiId !== null"
                required
              >
                <option v-for="s in satkers" :key="s.id" :value="s.id">
                  [{{ s.kode }}] {{ s.nama }}
                </option>
              </select>
            </div>
          </div>

          <!-- Component Scores Inputs (0 - 100) -->
          <div class="space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nilai Komponen (Skala 0 - 100)</p>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  1. Perencanaan (30%)
                </label>
                <input
                  v-model.number="evalForm.nilai_perencanaan"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  2. Pengukuran (30%)
                </label>
                <input
                  v-model.number="evalForm.nilai_pengukuran"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  3. Pelaporan (15%)
                </label>
                <input
                  v-model.number="evalForm.nilai_pelaporan"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                  4. Evaluasi Internal (25%)
                </label>
                <input
                  v-model.number="evalForm.nilai_evaluasi"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-text text-xs py-1.5"
                  required
                />
              </div>
            </div>
          </div>

          <!-- Live Score & Predicate Preview Card -->
          <div class="card p-4 bg-kemenkum-navy text-white flex items-center justify-between">
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-300">Estimasi Nilai Akhir</p>
              <div class="flex items-baseline gap-2 mt-0.5">
                <span class="text-2xl font-black">{{ liveTotal }}</span>
                <span class="text-xs text-slate-300">/ 100</span>
              </div>
            </div>
            <div class="text-right">
              <span class="text-[10px] uppercase font-bold tracking-wider text-slate-300 block mb-0.5">Predikat Capaian</span>
              <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-gold text-slate-950">
                {{ livePredikat.predikat }} ({{ livePredikat.label }})
              </span>
            </div>
          </div>

          <!-- Catatan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Rekomendasi Evaluator</label>
            <textarea
              v-model="evalForm.catatan"
              rows="3"
              placeholder="Catatan kelebihan dan saran perbaikan implementasi SAKIP satker..."
              class="input-text text-xs py-2 resize-none"
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              class="btn-secondary text-xs"
              @click="evalModalOpen = false"
            >
              Batal
            </button>
            <button
              type="submit"
              class="btn-primary text-xs"
              :disabled="modalSubmitting"
            >
              <span v-if="modalSubmitting">Menyimpan...</span>
              <span v-else>Simpan Evaluasi SAKIP</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>