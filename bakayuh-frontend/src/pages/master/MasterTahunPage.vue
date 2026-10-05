<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { TahunAnggaran } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'

const { isSuperAdmin } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const tahuns = ref<TahunAnggaran[]>([])

const modalOpen = ref(false)
const modalSubmitting = ref(false)
const inputTahun = ref<number>(new Date().getFullYear())

async function fetchTahuns() {
  loading.value = true
  try {
    const res = await apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran')
    tahuns.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat tahun anggaran', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

async function handleSetAktif(id: number) {
  try {
    await apiClient.patch(`/tahun-anggaran/${id}/set-aktif`)
    toastSuccess('Berhasil', 'Tahun anggaran aktif berhasil diubah.')
    await fetchTahuns()
  } catch (err: any) {
    toastError('Gagal mengubah tahun aktif', err.response?.data?.message)
  }
}

async function handleCreateTahun() {
  if (!inputTahun.value || inputTahun.value < 2020 || inputTahun.value > 2050) {
    toastError('Validasi Gagal', 'Masukkan tahun yang valid (2020 - 2050).')
    return
  }

  modalSubmitting.value = true
  try {
    await apiClient.post('/tahun-anggaran', {
      tahun: inputTahun.value,
      is_aktif: false,
    })
    toastSuccess('Berhasil', `Tahun anggaran ${inputTahun.value} berhasil ditambahkan.`)
    modalOpen.value = false
    await fetchTahuns()
  } catch (err: any) {
    toastError('Gagal menambah tahun', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

onMounted(() => {
  fetchTahuns()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in max-w-4xl pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Master Tahun Anggaran</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Kelola tahun anggaran pelaporan dan tentukan satu tahun yang aktif sebagai acuan default sistem
        </p>
      </div>

      <button
        v-if="isSuperAdmin"
        class="btn-primary text-xs inline-flex items-center gap-2"
        @click="modalOpen = true"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Tambah Tahun</span>
      </button>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="4" :cols="4" />

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-16 text-center">No</th>
              <th class="w-44 text-center">Tahun Anggaran</th>
              <th class="text-center w-36">Status Acuan</th>
              <th class="text-center w-44">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(t, idx) in tahuns" :key="t.id" class="hover:bg-slate-50/80">
              <td class="text-center text-xs text-slate-400 font-semibold">{{ idx + 1 }}</td>
              <td class="text-center">
                <span class="text-sm font-black text-slate-800">{{ t.tahun }}</span>
              </td>
              <td class="text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                  :class="t.is_aktif ? 'bg-emerald-50 text-emerald-700 font-black' : 'bg-slate-100 text-slate-500'"
                >
                  {{ t.is_aktif ? 'Aktif (Default)' : 'Nonaktif' }}
                </span>
              </td>
              <td class="text-center">
                <button
                  v-if="isSuperAdmin && !t.is_aktif"
                  type="button"
                  class="btn-secondary py-1 px-3 text-xs inline-flex items-center gap-1"
                  @click="handleSetAktif(t.id)"
                >
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Set Sebagai Aktif</span>
                </button>
                <span v-else-if="t.is_aktif" class="text-xs font-bold text-emerald-600">
                  Tahun Acuan Aktif
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-800">Tambah Tahun Anggaran</h2>
          <button class="text-slate-400 hover:text-slate-600 p-1" @click="modalOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleCreateTahun">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun (Angka)</label>
            <input
              v-model.number="inputTahun"
              type="number"
              min="2020"
              max="2050"
              class="input-text text-sm py-2 font-bold text-center"
              required
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" class="btn-secondary text-xs" @click="modalOpen = false">
              Batal
            </button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              <span>{{ modalSubmitting ? 'Menyimpan...' : 'Simpan Tahun' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>