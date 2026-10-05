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
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Master Indikator Kinerja Utama (IKU)</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Daftar indikator sasaran strategis, polaritas penilaian, dan satuan capaian kinerja instansi
        </p>
      </div>

      <button
        v-if="isSuperAdmin || isAdminKanwil"
        class="btn-primary text-xs inline-flex items-center gap-2"
        @click="openCreateModal"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Tambah Indikator</span>
      </button>
    </div>

    <!-- Search Bar -->
    <div class="card p-4 bg-white flex justify-between items-center">
      <span class="text-xs font-bold text-slate-600">Total: {{ indikators.length }} Indikator Master</span>
      <div class="w-72 relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode atau nama indikator..."
          class="input-text text-xs py-1.5 pl-8"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="6" :cols="6" />

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="w-28">Kode</th>
              <th>Nama Indikator Kinerja Sasaran</th>
              <th class="w-24 text-center">Satuan</th>
              <th class="w-28 text-center">Polaritas</th>
              <th class="w-28 text-center">Level</th>
              <th class="w-24 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(ind, idx) in paginatedIndikators" :key="ind.id" class="hover:bg-slate-50/80">
              <td class="text-center text-xs text-slate-400 font-semibold">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
              <td class="text-xs font-mono font-bold text-slate-800">{{ ind.kode }}</td>
              <td class="text-xs font-bold text-slate-800">{{ ind.nama }}</td>
              <td class="text-center text-xs font-semibold text-slate-600">{{ ind.satuan }}</td>
              <td class="text-center">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold capitalize"
                  :class="ind.polaritas === 'positif' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                >
                  {{ ind.polaritas }}
                </span>
              </td>
              <td class="text-center">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                  {{ ind.level }}
                </span>
              </td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    class="p-1 text-slate-400 hover:text-kemenkum-navy rounded transition-colors"
                    title="Ubah"
                    @click="openEditModal(ind)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="isSuperAdmin"
                    class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
                    title="Hapus"
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