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
  <div class="space-y-4 animate-fade-in pb-6">
    <!-- Official Kemenkumham Hero Banner (Compact & Sleek) -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-4 sm:p-5 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-1.5">
        <div class="flex items-center gap-2 text-[11px] text-kemenkum-silver/80 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Matriks Capaian IKU</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-kemenkum-gold flex items-center justify-center text-kemenkum-navy-dark shadow-sm shrink-0">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
              </svg>
            </div>
            <div>
              <div class="text-[10px] font-black uppercase tracking-widest text-amber-400">
                Indikator Kinerja
              </div>
              <h1 class="text-lg sm:text-xl font-black text-white tracking-wide uppercase">
                Matriks Capaian Indikator Kinerja
              </h1>
            </div>
          </div>

          <!-- Quick Action Buttons in Banner -->
          <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
            <a
              :href="getExportUrl()"
              target="_blank"
              download
              class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border border-emerald-500/30 bg-emerald-600/90 text-white hover:bg-emerald-600 flex items-center gap-1.5 shadow-sm"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Ekspor Excel</span>
            </a>

            <button
              type="button"
              class="px-3 py-1.5 rounded-xl border border-white/20 text-xs font-bold text-white hover:bg-white/10 transition-all flex items-center gap-1.5"
              :disabled="loading"
              @click="handleRefresh"
            >
              <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>Segarkan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Legend Card (Compact Single Bar) -->
    <div class="card p-3.5 bg-white border border-slate-200 shadow-sm space-y-2.5">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Filter Form -->
        <div class="flex flex-wrap items-center gap-2.5">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Tahun:</label>
            <select
              v-model="selectedTahunId"
              class="input-select text-xs py-1.5 pl-2.5 pr-7 font-semibold border-slate-200 rounded-lg focus:ring-2 focus:ring-[#091F4A]"
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
              class="input-text text-xs py-1.5 pl-8 border-slate-200 rounded-lg w-full"
            />
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Compact Legend Chips -->
        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
          <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Capaian:</span>
          <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" />
            <span>&ge; 100%</span>
          </div>
          <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500" />
            <span>80% - 99%</span>
          </div>
          <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500" />
            <span>&lt; 80%</span>
          </div>
          <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-slate-50 text-slate-500 border border-slate-200 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-300" />
            <span>Belum Lapor</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="card p-3 border-l-4 border-red-500 bg-red-50 text-red-800 text-xs">
      {{ error }}
    </div>

    <!-- Loading Skeleton -->
    <SkeletonTable v-if="loading" :rows="6" :cols="6" />

    <!-- Matriks Data Table Card (Compact Density to Fit Screen) -->
    <div v-else class="card bg-white border border-slate-200 shadow-sm overflow-hidden rounded-xl">
      <!-- Card Section Title Bar -->
      <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-[#091F4A] text-white">
            MATRIKS IKU
          </span>
          <h2 class="text-xs sm:text-sm font-black text-slate-800 tracking-tight uppercase">
            Matriks Capaian Indikator Kinerja Satker
          </h2>
        </div>
        <div class="text-[11px] font-semibold text-slate-500">
          Total: <span class="font-bold text-slate-800">{{ filteredMatriks.length }}</span> Satuan Kerja
        </div>
      </div>

      <div v-if="filteredMatriks.length === 0" class="p-10 text-center text-slate-400 text-xs">
        Tidak ada satuan kerja yang sesuai pencarian.
      </div>

      <!-- Compact Table with Dark Navy Header & Fitted Rows -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse">
          <thead class="bg-[#091F4A] text-white uppercase text-[11px] tracking-wider font-extrabold select-none">
            <tr>
              <th class="w-12 text-center py-2.5 px-3 border-r border-white/10">#</th>
              <th class="min-w-[260px] py-2.5 px-4 border-r border-white/10">Satuan Kerja</th>
              <th
                v-for="ind in (matriks[0]?.indikators ?? [])"
                :key="ind.indikator_id"
                class="min-w-[150px] text-center py-2.5 px-3 border-r border-white/10 last:border-r-0"
              >
                <div class="font-black text-white text-xs">{{ ind.kode }}</div>
                <div class="text-[10px] text-slate-300 font-normal truncate max-w-[140px] mx-auto mt-0.5 normal-case" :title="ind.nama">
                  {{ ind.nama }}
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(row, idx) in paginatedMatriks"
              :key="row.satker.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- Row Number -->
              <td class="text-center font-bold text-slate-400 text-xs py-2 px-3 bg-slate-50/40">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Satker Details (Compact Single Line Layout) -->
              <td class="py-2 px-4">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900 text-xs truncate max-w-[220px]" :title="row.satker.nama">
                    {{ row.satker.nama }}
                  </span>
                  <span
                    class="text-[9px] px-1.5 py-0.2 rounded font-black uppercase tracking-wider shrink-0"
                    :class="{
                      'bg-[#091F4A] text-white': row.satker.tipe === 'kanwil',
                      'bg-blue-100 text-blue-800 border border-blue-200': row.satker.tipe === 'upt',
                      'bg-slate-100 text-slate-700 border border-slate-200': row.satker.tipe === 'satker',
                    }"
                  >
                    {{ row.satker.tipe }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono shrink-0">{{ row.satker.kode }}</span>
                </div>
              </td>

              <!-- Dynamic IKU Cells (Compact Chips) -->
              <td
                v-for="cell in row.indikators"
                :key="cell.indikator_id"
                class="text-center py-2 px-2.5"
              >
                <div v-if="cell.target !== null">
                  <!-- Status Badge -->
                  <div
                    v-if="cell.persentase !== null"
                    class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black shadow-2xs min-w-[76px]"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': cell.status_color === 'green',
                      'bg-amber-50 text-amber-700 border border-amber-200': cell.status_color === 'yellow',
                      'bg-rose-50 text-rose-700 border border-rose-200': cell.status_color === 'red',
                    }"
                  >
                    <span>{{ cell.persentase.toFixed(1) }}%</span>
                    <span class="text-[10px] font-semibold opacity-75 font-mono">
                      ({{ cell.realisasi }}/{{ cell.target }})
                    </span>
                  </div>
                  <div v-else class="text-slate-400 text-[11px]">
                    <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-mono">
                      Target: {{ cell.target }}
                    </span>
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
        :page-size-options="[5, 10, 20]"
      />
    </div>
  </div>
</template>