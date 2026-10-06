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
  <div class="space-y-4 animate-fade-in max-w-4xl pb-8">
    <!-- Clean Enterprise Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
      <div>
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium mb-1">
          <span>Data Master</span>
          <span>/</span>
          <span class="text-slate-900 font-semibold">Tahun Anggaran</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">
          Master Tahun Anggaran
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola tahun anggaran pelaporan dan tentukan tahun aktif sebagai acuan default sistem.
        </p>
      </div>

      <div v-if="isSuperAdmin" class="flex items-center gap-2">
        <button
          type="button"
          @click="modalOpen = true"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-[#091F4A] text-white hover:bg-[#0c2b64] transition-colors shadow-xs"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah Tahun</span>
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card overflow-hidden bg-white border border-slate-200 shadow-xs rounded-xl">
      <SkeletonTable v-if="loading" :rows="4" :cols="4" />

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-semibold uppercase tracking-wider">
              <th class="py-3 px-3 text-center w-12 border-r border-white/10">#</th>
              <th class="py-3 px-4 text-center border-r border-white/10">Tahun Anggaran</th>
              <th class="py-3 px-4 text-center border-r border-white/10">Status Acuan</th>
              <th class="py-3 px-4 text-center w-48">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(t, idx) in tahuns"
              :key="t.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <!-- No -->
              <td class="py-3 px-3 text-center font-medium text-slate-400">
                {{ idx + 1 }}
              </td>

              <!-- Tahun -->
              <td class="py-3 px-4 text-center">
                <span class="font-mono text-sm font-bold text-slate-900">
                  {{ t.tahun }}
                </span>
              </td>

              <!-- Status Acuan -->
              <td class="py-3 px-4 text-center">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium"
                  :class="t.is_aktif
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                    : 'bg-slate-100 text-slate-500 border border-slate-200'"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="t.is_aktif ? 'bg-emerald-500' : 'bg-slate-400'"
                  />
                  <span>{{ t.is_aktif ? 'Aktif (Default)' : 'Nonaktif' }}</span>
                </span>
              </td>

              <!-- Aksi -->
              <td class="py-3 px-4 text-center">
                <button
                  v-if="isSuperAdmin && !t.is_aktif"
                  type="button"
                  class="px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-2xs inline-flex items-center gap-1.5"
                  @click="handleSetAktif(t.id)"
                >
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Jadikan Aktif</span>
                </button>
                <span v-else-if="t.is_aktif" class="text-xs font-semibold text-emerald-700 inline-flex items-center gap-1">
                  <span>✓</span>
                  <span>Tahun Acuan Aktif</span>
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