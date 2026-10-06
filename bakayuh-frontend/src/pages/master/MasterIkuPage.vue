<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { IndikatorKinerja, PolaritasIku, LevelIku } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const { isSuperAdmin, isAdminKanwil } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const indikators = ref<IndikatorKinerja[]>([])
const searchQuery = ref('')

const modalOpen = ref(false)
const modalSubmitting = ref(false)
const editingId = ref<number | null>(null)

const form = reactive<{
  kode: string
  nama: string
  satuan: string
  polaritas: PolaritasIku
  level: LevelIku
}>({
  kode: '',
  nama: '',
  satuan: '%',
  polaritas: 'positif',
  level: 'strategis',
})

const filteredIndikators = computed(() => {
  if (!searchQuery.value.trim()) return indikators.value
  const q = searchQuery.value.toLowerCase()
  return indikators.value.filter(
    (i) => i.nama.toLowerCase().includes(q) || i.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedIndikators,
} = usePagination(filteredIndikators, { defaultPageSize: 10 })

async function fetchIndikators() {
  loading.value = true
  try {
    const res = await apiClient.get<{ data: IndikatorKinerja[] }>('/indikator-kinerja')
    indikators.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat indikator', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingId.value = null
  form.kode = ''
  form.nama = ''
  form.satuan = '%'
  form.polaritas = 'positif'
  form.level = 'strategis'
  modalOpen.value = true
}

function openEditModal(item: IndikatorKinerja) {
  editingId.value = item.id
  form.kode = item.kode
  form.nama = item.nama
  form.satuan = item.satuan
  form.polaritas = item.polaritas
  form.level = item.level
  modalOpen.value = true
}

async function handleSubmit() {
  if (!form.kode.trim() || !form.nama.trim() || !form.satuan.trim()) {
    toastError('Validasi Gagal', 'Semua kolom wajib diisi.')
    return
  }

  modalSubmitting.value = true
  try {
    if (editingId.value) {
      await apiClient.put(`/indikator-kinerja/${editingId.value}`, form)
      toastSuccess('Berhasil', 'Indikator kinerja berhasil diperbarui.')
    } else {
      await apiClient.post('/indikator-kinerja', form)
      toastSuccess('Berhasil', 'Indikator kinerja baru berhasil ditambahkan.')
    }
    modalOpen.value = false
    await fetchIndikators()
  } catch (err: any) {
    toastError('Gagal menyimpan', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

async function handleDelete(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus indikator kinerja ini?')) return

  try {
    await apiClient.delete(`/indikator-kinerja/${id}`)
    toastSuccess('Berhasil', 'Indikator berhasil dihapus.')
    indikators.value = indikators.value.filter((i) => i.id !== id)
  } catch (err: any) {
    toastError('Gagal menghapus', err.response?.data?.message)
  }
}

onMounted(() => {
  fetchIndikators()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-slate-300 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span>Data Master</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Indikator Kinerja Utama</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-amber-400">
          Administrasi & Master
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-slate-900 shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-13h-2v6h6v-2h-4z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Master Indikator Kinerja Utama (IKU)
              </h1>
              <p class="text-xs sm:text-sm text-slate-200 mt-0.5 max-w-2xl leading-relaxed">
                Daftar indikator sasaran strategis, polaritas penilaian, dan satuan capaian kinerja instansi.
              </p>
            </div>
          </div>

          <!-- Action Button in Banner -->
          <div v-if="isSuperAdmin || isAdminKanwil" class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <button
              type="button"
              @click="openCreateModal"
              class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all border border-amber-400/40 bg-amber-500 text-slate-950 hover:bg-amber-400 flex items-center gap-2 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Tambah Indikator</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Bar Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <span class="text-xs font-bold text-slate-600">
        Total Terdaftar: <span class="font-black text-slate-900">{{ indikators.length }}</span> Indikator Master
      </span>
      <div class="w-full sm:w-72 relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode atau nama indikator..."
          class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#091F4A]"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card overflow-hidden bg-white border border-slate-200 shadow-sm rounded-xl">
      <!-- Card Title Bar -->
      <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="px-2.5 py-1 rounded bg-[#091F4A] text-white text-[10px] font-black uppercase tracking-wider">
            MASTER DATA
          </span>
          <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
            Daftar Indikator Sasaran Kinerja
          </h2>
        </div>
        <div class="text-xs font-semibold text-slate-500">
          Menampilkan: <span class="font-bold text-slate-800">{{ filteredIndikators.length }}</span> Indikator
        </div>
      </div>

      <SkeletonTable v-if="loading" :rows="6" :cols="7" />

      <div v-else-if="filteredIndikators.length === 0" class="p-16 text-center text-slate-400">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="font-bold text-sm text-slate-700">Belum ada indikator yang sesuai pencarian</p>
        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-black uppercase tracking-wider">
              <th class="py-4 px-4 text-center w-14 border-r border-white/10">No</th>
              <th class="py-4 px-4 text-center w-32 border-r border-white/10">Kode</th>
              <th class="py-4 px-5 min-w-[320px] border-r border-white/10">Nama Indikator Kinerja Sasaran</th>
              <th class="py-4 px-3 text-center w-28 border-r border-white/10">Satuan</th>
              <th class="py-4 px-3 text-center w-28 border-r border-white/10">Polaritas</th>
              <th class="py-4 px-3 text-center w-28 border-r border-white/10">Level</th>
              <th class="py-4 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr
              v-for="(ind, idx) in paginatedIndikators"
              :key="ind.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- No -->
              <td class="py-4 px-4 text-center font-bold text-slate-400">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Kode -->
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-2.5 py-1 rounded bg-[#091F4A]/10 text-[#091F4A] font-black font-mono text-[11px] border border-[#091F4A]/20">
                  {{ ind.kode }}
                </span>
              </td>

              <!-- Nama Indikator -->
              <td class="py-4 px-5">
                <div class="flex items-start gap-2.5">
                  <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                    🎯
                  </div>
                  <span class="font-bold text-slate-900 leading-snug">
                    {{ ind.nama }}
                  </span>
                </div>
              </td>

              <!-- Satuan -->
              <td class="py-4 px-3 text-center font-medium text-slate-700">
                {{ ind.satuan }}
              </td>

              <!-- Polaritas -->
              <td class="py-4 px-3 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold capitalize border shadow-xs"
                  :class="ind.polaritas === 'positif'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-rose-50 text-rose-700 border-rose-200'"
                >
                  {{ ind.polaritas }}
                </span>
              </td>

              <!-- Level -->
              <td class="py-4 px-3 text-center">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase border border-slate-200">
                  {{ ind.level }}
                </span>
              </td>

              <!-- Aksi -->
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    class="p-1.5 text-slate-500 hover:text-[#091F4A] hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100"
                    title="Ubah Indikator"
                    @click="openEditModal(ind)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="isSuperAdmin"
                    class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100"
                    title="Hapus Indikator"
                    @click="handleDelete(ind.id)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
        v-if="filteredIndikators.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>

    <!-- Create / Edit Modal -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-800">
            {{ editingId ? 'Ubah Indikator Kinerja' : 'Tambah Indikator Kinerja Baru' }}
          </h2>
          <button class="text-slate-400 hover:text-slate-600 p-1" @click="modalOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleSubmit">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kode Indikator</label>
              <input
                v-model="form.kode"
                type="text"
                placeholder="Contoh: IKU-01"
                class="input-text text-xs py-2 font-mono uppercase"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Pengukuran</label>
              <input
                v-model="form.satuan"
                type="text"
                placeholder="Contoh: %, Nilai, Dokumen"
                class="input-text text-xs py-2"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Indikator</label>
            <input
              v-model="form.nama"
              type="text"
              placeholder="Contoh: Persentase Kualitas Pelayanan Publik..."
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Polaritas</label>
              <select v-model="form.polaritas" class="input-select text-xs py-2" required>
                <option value="positif">Positif (Makin tinggi makin baik)</option>
                <option value="negatif">Negatif (Makin rendah makin baik)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Level Indikator</label>
              <select v-model="form.level" class="input-select text-xs py-2" required>
                <option value="strategis">Strategis</option>
                <option value="program">Program</option>
                <option value="kegiatan">Kegiatan</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" class="btn-secondary text-xs" @click="modalOpen = false">
              Batal
            </button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              <span>{{ modalSubmitting ? 'Menyimpan...' : 'Simpan Indikator' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>