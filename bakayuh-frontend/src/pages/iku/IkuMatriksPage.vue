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
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-kemenkum-silver/80 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Matriks Capaian IKU</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-kemenkum-gold">
          Indikator Kinerja
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-kemenkum-gold flex items-center justify-center text-kemenkum-navy-dark shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Matriks Capaian Indikator Kinerja
              </h1>
              <p class="text-xs sm:text-sm text-kemenkum-silver/90 mt-0.5 max-w-2xl leading-relaxed">
                Matriks komparasi capaian sasaran strategis, program, dan target kinerja seluruh Satuan Kerja se-Kalimantan Selatan.
              </p>
            </div>
          </div>

          <!-- Quick Action Buttons in Banner -->
          <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <a
              :href="getExportUrl()"
              target="_blank"
              download
              class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all border border-emerald-500/30 bg-emerald-600/90 text-white hover:bg-emerald-600 flex items-center gap-2 shadow-sm backdrop-blur-xs"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>Ekspor Matriks</span>
            </a>

            <button
              type="button"
              class="px-3.5 py-2 rounded-xl border border-white/20 text-xs font-bold text-white hover:bg-white/10 transition-all flex items-center gap-1.5 backdrop-blur-xs"
              :disabled="loading"
              @click="handleRefresh"
            >
              <svg class="w-4 h-4 text-kemenkum-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>Segarkan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Legend Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm space-y-4">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Filter Form -->
        <div class="flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Tahun Anggaran:</label>
            <select
              v-model="selectedTahunId"
              class="input-select text-xs py-2 pl-3 pr-8 font-semibold border-slate-200 rounded-xl focus:ring-2 focus:ring-kemenkum-navy"
              @change="fetchMatriks"
            >
              <option v-for="t in tahuns" :key="t.id" :value="t.id">
                Tahun {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <div class="w-full sm:w-72 relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama atau kode satker..."
              class="input-text text-xs py-2 pl-9 border-slate-200 rounded-xl w-full"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <!-- Legend Chips -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
          <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Capaian:</span>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-500" />
            <span>&ge; 100% Tercapai</span>
          </div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-amber-500" />
            <span>80% - 99% Hampir</span>
          </div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-rose-500" />
            <span>&lt; 80% Belum</span>
          </div>
          <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-slate-500 border border-slate-200 text-xs font-medium">
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

    <!-- Matriks Data Table Card (Spacious Layout) -->
    <div v-else class="card bg-white border border-slate-200 shadow-sm overflow-hidden">
      <!-- Card Section Title Bar (Matching performance.kemenkum.go.id) -->
      <div class="px-5 py-3.5 bg-white border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                Matriks IKU
              </span>
              <h2 class="text-sm font-black text-slate-800 tracking-tight uppercase">
                Matriks Capaian Indikator Kinerja Satker
              </h2>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">
              Komparasi realisasi target kinerja seluruh satuan kerja se-Kalimantan Selatan
            </p>
          </div>
        </div>
      </div>

      <div v-if="filteredMatriks.length === 0" class="p-12 text-center text-slate-400 text-xs">
        Tidak ada satuan kerja yang sesuai pencarian.
      </div>

      <!-- Spacious Table with Dark Navy Header & Generous Cell Widths -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="bg-[#091F4A] text-white uppercase text-[11px] tracking-wider font-extrabold select-none">
            <tr>
              <th class="w-14 text-center py-4 px-3 border-r border-white/10">#</th>
              <th class="min-w-[300px] py-4 px-5 border-r border-white/10">Satuan Kerja</th>
              <th
                v-for="ind in (matriks[0]?.indikators ?? [])"
                :key="ind.indikator_id"
                class="min-w-[180px] text-center py-4 px-3 border-r border-white/10 last:border-r-0"
              >
                <div class="font-black text-white text-xs">{{ ind.kode }}</div>
                <div class="text-[10px] text-kemenkum-silver/80 font-normal truncate max-w-[170px] mx-auto mt-0.5 normal-case" :title="ind.nama">
                  {{ ind.nama }}
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(row, idx) in paginatedMatriks"
              :key="row.satker.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <!-- Row Number -->
              <td class="text-center font-bold text-slate-400 text-xs py-4 px-3 bg-slate-50/40">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Satker Details (Spacious, No cramped wrapping) -->
              <td class="py-4 px-5">
                <div class="font-bold text-slate-800 text-xs leading-snug">
                  {{ row.satker.nama }}
                </div>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-[10px] text-slate-500 font-mono font-bold">{{ row.satker.kode }}</span>
                  <span
                    class="text-[9px] px-1.5 py-0.5 rounded font-black uppercase tracking-wider"
                    :class="{
                      'bg-kemenkum-navy text-white': row.satker.tipe === 'kanwil',
                      'bg-blue-50 text-blue-700': row.satker.tipe === 'upt',
                      'bg-slate-100 text-slate-700': row.satker.tipe === 'satker',
                    }"
                  >
                    {{ row.satker.tipe }}
                  </span>
                </div>
              </td>

              <!-- Dynamic IKU Cells -->
              <td
                v-for="cell in row.indikators"
                :key="cell.indikator_id"
                class="text-center py-4 px-3"
              >
                <div v-if="cell.target !== null">
                  <!-- Status Badge -->
                  <div
                    v-if="cell.persentase !== null"
                    class="inline-flex flex-col items-center justify-center px-3 py-1.5 rounded-xl text-xs font-black shadow-2xs min-w-[90px]"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': cell.status_color === 'green',
                      'bg-amber-50 text-amber-700 border border-amber-200': cell.status_color === 'yellow',
                      'bg-rose-50 text-rose-700 border border-rose-200': cell.status_color === 'red',
                    }"
                  >
                    <span class="text-xs">{{ cell.persentase.toFixed(1) }}%</span>
                    <span class="text-[10px] font-semibold opacity-75 font-mono mt-0.5">
                      {{ cell.realisasi }} / {{ cell.target }}
                    </span>
                  </div>
                  <div v-else class="text-slate-400 text-xs space-y-0.5">
                    <span class="text-[11px] font-mono text-slate-600 font-semibold block">Target: {{ cell.target }}</span>
                    <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full inline-block">Belum lapor</span>
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