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
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-slate-300 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span>Data Master</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Tahun Anggaran</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-amber-400">
          Administrasi & Master
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-slate-900 shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zm-7 5h5v5h-5v-5z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Master Tahun Anggaran
              </h1>
              <p class="text-xs sm:text-sm text-slate-200 mt-0.5 max-w-xl leading-relaxed">
                Kelola tahun anggaran pelaporan dan tentukan tahun aktif sebagai acuan default sistem.
              </p>
            </div>
          </div>

          <!-- Action Button in Banner -->
          <div v-if="isSuperAdmin" class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <button
              type="button"
              @click="modalOpen = true"
              class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all border border-amber-400/40 bg-amber-500 text-slate-950 hover:bg-amber-400 flex items-center gap-2 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Tambah Tahun</span>
            </button>
          </div>
        </div>
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
            Daftar Tahun Anggaran
          </h2>
        </div>
        <div class="text-xs font-semibold text-slate-500">
          Total: <span class="font-bold text-slate-800">{{ tahuns.length }}</span> Periode
        </div>
      </div>

      <SkeletonTable v-if="loading" :rows="4" :cols="4" />

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-black uppercase tracking-wider">
              <th class="py-4 px-4 text-center w-16 border-r border-white/10">No</th>
              <th class="py-4 px-5 text-center min-w-[200px] border-r border-white/10">Tahun Anggaran</th>
              <th class="py-4 px-4 text-center min-w-[180px] border-r border-white/10">Status Acuan</th>
              <th class="py-4 px-5 text-center min-w-[200px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr
              v-for="(t, idx) in tahuns"
              :key="t.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- No -->
              <td class="py-4 px-4 text-center font-bold text-slate-400">
                {{ idx + 1 }}
              </td>

              <!-- Tahun -->
              <td class="py-4 px-5 text-center">
                <span class="inline-block px-3 py-1 rounded-lg bg-[#091F4A]/10 text-[#091F4A] font-black text-sm font-mono border border-[#091F4A]/20">
                  {{ t.tahun }}
                </span>
              </td>

              <!-- Status Acuan -->
              <td class="py-4 px-4 text-center">
                <span
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border shadow-xs"
                  :class="t.is_aktif
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-slate-100 text-slate-500 border-slate-200'"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="t.is_aktif ? 'bg-emerald-500' : 'bg-slate-400'"
                  />
                  <span>{{ t.is_aktif ? 'Aktif (Default)' : 'Nonaktif' }}</span>
                </span>
              </td>

              <!-- Aksi -->
              <td class="py-4 px-5 text-center">
                <button
                  v-if="isSuperAdmin && !t.is_aktif"
                  type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-xs inline-flex items-center gap-1.5"
                  @click="handleSetAktif(t.id)"
                >
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Set Sebagai Aktif</span>
                </button>
                <span v-else-if="t.is_aktif" class="text-xs font-black text-emerald-700 inline-flex items-center gap-1">
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