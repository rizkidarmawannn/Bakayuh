<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useToast } from '@/composables/useToast'
import type { MatriksRow, TahunAnggaran } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const matriks = ref<MatriksRow[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const selectedTahunId = ref<number | null>(null)
const searchQuery = ref('')
const error = ref<string | null>(null)

const filteredMatriks = computed(() => {
  if (!searchQuery.value.trim()) return matriks.value
  const q = searchQuery.value.toLowerCase()
  return matriks.value.filter(
    (row) =>
      row.satker.nama.toLowerCase().includes(q) ||
      row.satker.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedMatriks,
} = usePagination(filteredMatriks, { defaultPageSize: 10 })

async function fetchTahuns() {
  try {
    const res = await apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran')
    tahuns.value = res.data.data
    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) selectedTahunId.value = aktif.id
  } catch (err) {
    console.error(err)
  }
}

async function fetchMatriks() {
  loading.value = true
  error.value = null
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value

    const res = await apiClient.get<{ data: MatriksRow[] }>('/target-iku/matriks', { params })
    matriks.value = res.data.data
  } catch (err: any) {
    error.value = err.response?.data?.message ?? 'Gagal memuat matriks capaian IKU.'
    toastError('Gagal memuat matriks', error.value || undefined)
  } finally {
    loading.value = false
  }
}

function handleRefresh() {
  fetchMatriks()
  toastSuccess('Diperbarui', 'Matriks capaian IKU berhasil disinkronkan.')
}

function getExportUrl(): string {
  const t = selectedTahunId.value ? `?tahun_anggaran_id=${selectedTahunId.value}` : ''
  return `http://127.0.0.1:8000/api/export/iku/excel${t}`
}

onMounted(async () => {
  await fetchTahuns()
  await fetchMatriks()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gold text-slate-950">
            E-Performance
          </span>
          <span class="text-xs text-slate-400 font-bold">Pengukuran Kinerja Instansi</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
          Matriks Capaian IKU
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Matriks komparasi realisasi Indikator Kinerja Utama seluruh Satuan Kerja se-Kalsel
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2.5">
        <a
          :href="getExportUrl()"
          target="_blank"
          download
          class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-2 shadow-xs"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Ekspor Matriks</span>
        </a>

        <button
          type="button"
          class="px-3.5 py-2 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-kemenkum-navy transition-all flex items-center gap-1.5 bg-white shadow-xs"
          :disabled="loading"
          @click="handleRefresh"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Segarkan</span>
        </button>
      </div>
    </div>

    <!-- Filter & Legend Bar -->
    <div class="card p-4 bg-white border border-slate-200 shadow-sm space-y-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Tahun Selector & Search -->
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-600 whitespace-nowrap">Tahun Anggaran:</label>
            <select
              v-model="selectedTahunId"
              class="input-select text-xs py-1.5 pl-3 pr-8 font-semibold border-slate-200 rounded-lg focus:ring-1 focus:ring-kemenkum-navy"
              @change="fetchMatriks"
            >
              <option v-for="t in tahuns" :key="t.id" :value="t.id">
                {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <div class="w-full sm:w-64 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama atau kode satker..."
              class="input-text text-xs py-1.5 pl-8 border-slate-200 rounded-lg"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Color Legend Chips -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Capaian:</span>
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500" />
            <span>&ge; 100% Tercapai</span>
          </div>
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-amber-500" />
            <span>80% - 99% Hampir</span>
          </div>
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">
            <span class="w-2 h-2 rounded-full bg-rose-500" />
            <span>&lt; 80% Belum</span>
          </div>
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 text-[11px] font-medium">
            <span class="w-2 h-2 rounded-full bg-slate-300" />
            <span>Belum Lapor</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="card p-4 border-l-4 border-red-500 bg-red-50 text-red-800 text-xs">
      {{ error }}
    </div>

    <!-- Loading Skeleton -->
    <SkeletonTable v-if="loading" :rows="8" :cols="6" />

    <!-- Matriks Data Table -->
    <div v-else class="card bg-white border border-slate-200 shadow-sm overflow-hidden">
      <div v-if="filteredMatriks.length === 0" class="p-12 text-center text-slate-400 text-xs">
        Tidak ada satuan kerja yang sesuai pencarian.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="table-custom w-full">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="min-w-[220px]">Satuan Kerja</th>
              <th
                v-for="ind in (matriks[0]?.indikators ?? [])"
                :key="ind.indikator_id"
                class="min-w-[140px] text-center"
              >
                <div class="font-extrabold text-slate-800 text-xs uppercase">{{ ind.kode }}</div>
                <div class="text-[10px] text-slate-400 font-semibold truncate max-w-[150px] mx-auto mt-0.5" :title="ind.nama">
                  {{ ind.nama }}
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, idx) in paginatedMatriks"
              :key="row.satker.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="text-center font-semibold text-slate-400 text-xs">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>
              <td>
                <div class="font-bold text-slate-800 text-xs">{{ row.satker.nama }}</div>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="text-[10px] text-slate-400 font-mono">{{ row.satker.kode }}</span>
                  <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 uppercase font-semibold">
                    {{ row.satker.tipe }}
                  </span>
                </div>
              </td>
              <td
                v-for="cell in row.indikators"
                :key="cell.indikator_id"
                class="text-center py-2.5 px-2"
              >
                <div v-if="cell.target !== null">
                  <!-- Status Badge -->
                  <div
                    v-if="cell.persentase !== null"
                    class="inline-flex flex-col items-center justify-center px-2.5 py-1 rounded-lg text-xs font-black shadow-2xs"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': cell.status_color === 'green',
                      'bg-amber-50 text-amber-700 border border-amber-200': cell.status_color === 'yellow',
                      'bg-rose-50 text-rose-700 border border-rose-200': cell.status_color === 'red',
                    }"
                  >
                    <span>{{ cell.persentase.toFixed(1) }}%</span>
                    <span class="text-[9px] font-semibold opacity-75 font-mono">
                      {{ cell.realisasi }} / {{ cell.target }}
                    </span>
                  </div>
                  <div v-else class="text-slate-400 text-xs">
                    <span class="text-[11px] font-mono text-slate-500 block">Target: {{ cell.target }}</span>
                    <span class="text-[9px] text-slate-300">Belum lapor</span>
                  </div>
                </div>
                <div v-else class="text-slate-300 font-mono text-xs">
                  -
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Component -->
      <AppPagination
        v-if="filteredMatriks.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>
  </div>
</template>