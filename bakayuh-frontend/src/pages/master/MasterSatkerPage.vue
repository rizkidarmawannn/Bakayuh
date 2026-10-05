<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { SatuanKerja, TipeSatker } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const { isSuperAdmin, isAdminKanwil } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const satkers = ref<SatuanKerja[]>([])
const searchQuery = ref('')
const selectedTipe = ref<string>('ALL')

// Modal State
const modalOpen = ref(false)
const modalSubmitting = ref(false)
const editingId = ref<number | null>(null)

const form = reactive<{
  kode: string
  nama: string
  tipe: TipeSatker
}>({
  kode: '',
  nama: '',
  tipe: 'upt',
})

const filteredSatkers = computed(() => {
  return satkers.value.filter((s) => {
    const matchesSearch =
      s.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      s.kode.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesTipe = selectedTipe.value === 'ALL' || s.tipe === selectedTipe.value
    return matchesSearch && matchesTipe
  })
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedSatkers,
} = usePagination(filteredSatkers, { defaultPageSize: 10 })

async function fetchSatkers() {
  loading.value = true
  try {
    const res = await apiClient.get<{ data: SatuanKerja[] }>('/satker')
    satkers.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat data satker', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingId.value = null
  form.kode = ''
  form.nama = ''
  form.tipe = 'upt'
  modalOpen.value = true
}

function openEditModal(item: SatuanKerja) {
  editingId.value = item.id
  form.kode = item.kode
  form.nama = item.nama
  form.tipe = item.tipe
  modalOpen.value = true
}

async function handleSubmit() {
  if (!form.kode.trim() || !form.nama.trim()) {
    toastError('Validasi Gagal', 'Kode dan nama satuan kerja wajib diisi.')
    return
  }

  modalSubmitting.value = true
  try {
    if (editingId.value) {
      await apiClient.put(`/satker/${editingId.value}`, form)
      toastSuccess('Berhasil', 'Data satuan kerja berhasil diperbarui.')
    } else {
      await apiClient.post('/satker', form)
      toastSuccess('Berhasil', 'Satuan kerja baru berhasil ditambahkan.')
    }
    modalOpen.value = false
    await fetchSatkers()
  } catch (err: any) {
    toastError('Gagal menyimpan', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

async function handleDelete(id: number) {
  if (!confirm('Hapus satuan kerja ini?')) return

  try {
    await apiClient.delete(`/satker/${id}`)
    toastSuccess('Berhasil', 'Satuan kerja berhasil dihapus.')
    satkers.value = satkers.value.filter((s) => s.id !== id)
  } catch (err: any) {
    toastError('Gagal menghapus', err.response?.data?.message)
  }
}

onMounted(() => {
  fetchSatkers()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Master Satuan Kerja</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Daftar seluruh Satuan Kerja, Unit Pelaksana Teknis (UPT), dan Kantor Wilayah Kemenkumham Kalsel
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
        <span>Tambah Satuan Kerja</span>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="card p-4 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <button
          v-for="t in ['ALL', 'kanwil', 'upt', 'satker']"
          :key="t"
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border"
          :class="selectedTipe === t
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy'
            : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
          @click="selectedTipe = t"
        >
          {{ t === 'ALL' ? 'Semua Tipe' : t.toUpperCase() }}
        </button>
      </div>

      <div class="w-full sm:w-72 relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode atau nama satker..."
          class="input-text text-xs py-1.5 pl-8"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Satker Table -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="6" :cols="5" />

      <div v-else-if="filteredSatkers.length === 0" class="p-12 text-center text-slate-400">
        Belum ada satuan kerja yang sesuai filter.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th class="w-32">Kode Satker</th>
              <th>Nama Satuan Kerja</th>
              <th class="w-28 text-center">Tipe</th>
              <th class="w-28 text-center">Status</th>
              <th class="w-28 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(s, idx) in paginatedSatkers" :key="s.id" class="hover:bg-slate-50/80">
              <td class="text-center text-xs text-slate-400 font-semibold">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
              <td class="text-xs font-mono font-bold text-slate-700">{{ s.kode }}</td>
              <td class="text-xs font-bold text-slate-800">{{ s.nama }}</td>
              <td class="text-center">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                  :class="{
                    'bg-kemenkum-navy text-white': s.tipe === 'kanwil',
                    'bg-blue-50 text-blue-700': s.tipe === 'upt',
                    'bg-slate-100 text-slate-700': s.tipe === 'satker',
                  }"
                >
                  {{ s.tipe }}
                </span>
              </td>
              <td class="text-center">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="s.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                >
                  {{ s.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    class="p-1 text-slate-400 hover:text-kemenkum-navy rounded transition-colors"
                    title="Ubah"
                    @click="openEditModal(s)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    v-if="isSuperAdmin"
                    class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
                    title="Hapus"
                    @click="handleDelete(s.id)"
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
        v-if="filteredSatkers.length > 0"
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
            {{ editingId ? 'Ubah Satuan Kerja' : 'Tambah Satuan Kerja Baru' }}
          </h2>
          <button class="text-slate-400 hover:text-slate-600 p-1" @click="modalOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Satker (Maks 20)</label>
            <input
              v-model="form.kode"
              type="text"
              placeholder="Contoh: LP-BJM"
              class="input-text text-xs py-2 font-mono uppercase"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Satuan Kerja</label>
            <input
              v-model="form.nama"
              type="text"
              placeholder="Contoh: Lembaga Pemasyarakatan Kelas IIA Banjarmasin"
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Instansi</label>
            <select v-model="form.tipe" class="input-select text-xs py-2" required>
              <option value="upt">Unit Pelaksana Teknis (UPT)</option>
              <option value="kanwil">Kantor Wilayah (Kanwil)</option>
              <option value="satker">Satuan Kerja Lainnya</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" class="btn-secondary text-xs" @click="modalOpen = false">
              Batal
            </button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              <span>{{ modalSubmitting ? 'Menyimpan...' : 'Simpan Satker' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>