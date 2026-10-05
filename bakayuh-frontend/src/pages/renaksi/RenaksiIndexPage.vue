<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { RencanaAksi, SatuanKerja, IndikatorKinerja, TahunAnggaran, Triwulan, StatusColor } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const router = useRouter()
const { isSuperAdmin, isAdminKanwil, isOperatorSatker, user } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const items = ref<RencanaAksi[]>([])
const satkers = ref<SatuanKerja[]>([])
const indikators = ref<IndikatorKinerja[]>([])
const tahuns = ref<TahunAnggaran[]>([])

const selectedTahunId = ref<number | null>(null)
const selectedSatkerId = ref<number | null>(null)
const activeTriwulanTab = ref<string>('ALL') // 'ALL' | 'TW1' | 'TW2' | 'TW3' | 'TW4'

// Create Modal State
const createModalOpen = ref(false)
const modalSubmitting = ref(false)

const createForm = reactive<{
  tahun_anggaran_id: number
  satker_id: number
  indikator_id: number
  nama_aksi: string
  triwulan: Triwulan
  target_output: string
}>({
  tahun_anggaran_id: 0,
  satker_id: 0,
  indikator_id: 0,
  nama_aksi: '',
  triwulan: 'TW1',
  target_output: '',
})

// Summary Stats
const stats = computed(() => {
  const total = items.value.length
  let terverifikasi = 0
  let menunggu = 0
  let perbaikan = 0
  let belumLapor = 0

  items.value.forEach((item) => {
    const s = item.realisasi?.status
    if (s === 'terverifikasi') terverifikasi++
    else if (s === 'menunggu_verifikasi') menunggu++
    else if (s === 'perlu_perbaikan') perbaikan++
    else belumLapor++
  })

  return { total, terverifikasi, menunggu, perbaikan, belumLapor }
})

// Filtered items by active tab
const filteredItems = computed(() => {
  if (activeTriwulanTab.value === 'ALL') {
    return items.value
  }
  return items.value.filter((item) => item.triwulan === activeTriwulanTab.value)
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems,
} = usePagination(filteredItems, { defaultPageSize: 10 })

function getStatusBadgeConfig(status?: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'terverifikasi':
      return { label: 'Terverifikasi', color: 'green' }
    case 'menunggu_verifikasi':
      return { label: 'Menunggu Verifikasi', color: 'yellow' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'red' }
    default:
      return { label: 'Belum Lapor', color: 'gray' }
  }
}

async function loadInitialData() {
  try {
    const [resSatker, resInd, resTahun] = await Promise.all([
      apiClient.get<{ data: SatuanKerja[] }>('/satker'),
      apiClient.get<{ data: IndikatorKinerja[] }>('/indikator-kinerja'),
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran'),
    ])

    satkers.value = resSatker.data.data
    indikators.value = resInd.data.data
    tahuns.value = resTahun.data.data

    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) selectedTahunId.value = aktif.id

    if (isOperatorSatker.value && user.value?.satker_id) {
      selectedSatkerId.value = user.value.satker_id
    }
  } catch (err) {
    console.error('Error loading initial data:', err)
  }
}

async function fetchRenaksi() {
  loading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (selectedSatkerId.value) params.satker_id = selectedSatkerId.value

    const res = await apiClient.get<{ data: RencanaAksi[] }>('/rencana-aksi', { params })
    items.value = res.data.data
  } catch (err: any) {
    toastError('Gagal memuat Rencana Aksi', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  createForm.tahun_anggaran_id = selectedTahunId.value ?? (tahuns.value[0]?.id || 0)
  createForm.satker_id = selectedSatkerId.value ?? (satkers.value[0]?.id || 0)
  createForm.indikator_id = indikators.value[0]?.id || 0
  createForm.nama_aksi = ''
  createForm.triwulan = (activeTriwulanTab.value !== 'ALL' ? activeTriwulanTab.value : 'TW1') as Triwulan
  createForm.target_output = ''
  createModalOpen.value = true
}

async function handleCreateRenaksi() {
  if (!createForm.nama_aksi.trim()) {
    toastError('Validasi Gagal', 'Nama aksi wajib diisi.')
    return
  }

  modalSubmitting.value = true
  try {
    await apiClient.post('/rencana-aksi', createForm)
    toastSuccess('Berhasil', 'Rencana Aksi berhasil ditambahkan.')
    createModalOpen.value = false
    await fetchRenaksi()
  } catch (err: any) {
    toastError('Gagal menyimpan', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

async function handleDeleteRenaksi(id: number) {
  if (!confirm('Apakah Anda yakin ingin menghapus rencana aksi ini?')) return

  try {
    await apiClient.delete(`/rencana-aksi/${id}`)
    toastSuccess('Berhasil', 'Rencana aksi berhasil dihapus.')
    items.value = items.value.filter((i) => i.id !== id)
  } catch (err: any) {
    toastError('Gagal menghapus', err.response?.data?.message)
  }
}

onMounted(async () => {
  await loadInitialData()
  await fetchRenaksi()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Rencana Aksi (Renaksi) Perjanjian Kinerja</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Monitoring dan pelaporan capaian rencana aksi per triwulan se-Kemenkumham Kalsel
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="isSuperAdmin || isAdminKanwil || isOperatorSatker"
          class="btn-primary inline-flex items-center gap-2 text-xs"
          @click="openCreateModal"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Rencana Aksi
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
      <div class="card p-4 bg-white border border-slate-200">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Renaksi</p>
        <p class="text-2xl font-black text-slate-800 mt-1">{{ stats.total }}</p>
      </div>
      <div class="card p-4 bg-emerald-50/50 border border-emerald-100">
        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Terverifikasi</p>
        <p class="text-2xl font-black text-emerald-700 mt-1">{{ stats.terverifikasi }}</p>
      </div>
      <div class="card p-4 bg-amber-50/50 border border-amber-100">
        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Menunggu Verif</p>
        <p class="text-2xl font-black text-amber-700 mt-1">{{ stats.menunggu }}</p>
      </div>
      <div class="card p-4 bg-rose-50/50 border border-rose-100">
        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Perlu Perbaikan</p>
        <p class="text-2xl font-black text-rose-700 mt-1">{{ stats.perbaikan }}</p>
      </div>
      <div class="card p-4 bg-slate-50 border border-slate-200">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Belum Lapor</p>
        <p class="text-2xl font-black text-slate-600 mt-1">{{ stats.belumLapor }}</p>
      </div>
    </div>

    <!-- Filter & Triwulan Tabs -->
    <div class="card p-4 bg-white space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <!-- Tahun Anggaran -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Anggaran</label>
          <select
            v-model="selectedTahunId"
            class="input-select text-xs py-2"
            @change="fetchRenaksi"
          >
            <option v-for="t in tahuns" :key="t.id" :value="t.id">
              {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
            </option>
          </select>
        </div>

        <!-- Satker Filter -->
        <div v-if="!isOperatorSatker">
          <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Kerja</label>
          <select
            v-model="selectedSatkerId"
            class="input-select text-xs py-2"
            @change="fetchRenaksi"
          >
            <option :value="null">Semua Satuan Kerja ({{ satkers.length }})</option>
            <option v-for="s in satkers" :key="s.id" :value="s.id">
              [{{ s.kode }}] {{ s.nama }}
            </option>
          </select>
        </div>

        <div v-else>
          <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Kerja</label>
          <input
            type="text"
            disabled
            :value="user?.satker?.nama || 'Satuan Kerja Anda'"
            class="input-text text-xs py-2 bg-slate-50 text-slate-600 cursor-not-allowed"
          />
        </div>
      </div>

      <!-- Triwulan Tabs Bar -->
      <div class="flex items-center gap-1 border-b border-slate-200 pt-2 overflow-x-auto">
        <button
          class="px-4 py-2 text-xs font-bold transition-all border-b-2 -mb-px whitespace-nowrap"
          :class="activeTriwulanTab === 'ALL'
            ? 'border-kemenkum-navy text-kemenkum-navy'
            : 'border-transparent text-slate-500 hover:text-slate-700'"
          @click="activeTriwulanTab = 'ALL'"
        >
          Semua Triwulan
        </button>
        <button
          v-for="tw in ['TW1', 'TW2', 'TW3', 'TW4']"
          :key="tw"
          class="px-4 py-2 text-xs font-bold transition-all border-b-2 -mb-px whitespace-nowrap flex items-center gap-1.5"
          :class="activeTriwulanTab === tw
            ? 'border-kemenkum-navy text-kemenkum-navy'
            : 'border-transparent text-slate-500 hover:text-slate-700'"
          @click="activeTriwulanTab = tw"
        >
          <span>{{ tw }}</span>
          <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
            {{ items.filter(i => i.triwulan === tw).length }}
          </span>
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="5" :cols="6" />

      <div v-else-if="filteredItems.length === 0" class="p-12 text-center text-slate-400">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-sm font-bold text-slate-700">Belum ada Rencana Aksi</p>
        <p class="text-xs text-slate-400 mt-1">
          Pilih filter lain atau tambahkan rencana aksi baru untuk triwulan ini.
        </p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th v-if="!isOperatorSatker" class="w-48">Satuan Kerja</th>
              <th class="w-20 text-center">Periode</th>
              <th class="w-56">Indikator Terkait</th>
              <th>Rencana Aksi & Output</th>
              <th class="w-44">Progres Pelaksanaan</th>
              <th class="w-36 text-center">Status</th>
              <th class="w-28 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in paginatedItems" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="text-center text-xs font-semibold text-slate-400">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
              <td v-if="!isOperatorSatker" class="text-xs">
                <span class="font-bold text-slate-800">{{ item.satker?.nama || '-' }}</span>
                <span class="block text-[10px] text-slate-400">{{ item.satker?.kode }}</span>
              </td>
              <td class="text-center">
                <span class="px-2 py-1 rounded text-[11px] font-black tracking-wider bg-slate-100 text-slate-700">
                  {{ item.triwulan }}
                </span>
              </td>
              <td class="text-xs">
                <span class="font-medium text-slate-700 line-clamp-2" :title="item.indikator?.nama">
                  {{ item.indikator?.nama || '-' }}
                </span>
                <span class="inline-block mt-0.5 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-mono">
                  {{ item.indikator?.kode }}
                </span>
              </td>
              <td class="text-xs">
                <p class="font-bold text-slate-800">{{ item.nama_aksi }}</p>
                <p v-if="item.target_output" class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">
                  <span class="font-medium text-slate-600">Output:</span> {{ item.target_output }}
                </p>
              </td>
              <td class="text-xs">
                <div class="space-y-1">
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 font-medium">Realisasi</span>
                    <span class="font-black text-slate-800">
                      {{ item.realisasi?.persentase_selesai ?? 0 }}%
                    </span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div
                      class="h-2 rounded-full transition-all duration-500"
                      :class="{
                        'bg-emerald-500': (item.realisasi?.persentase_selesai ?? 0) >= 100,
                        'bg-blue-600': (item.realisasi?.persentase_selesai ?? 0) >= 50 && (item.realisasi?.persentase_selesai ?? 0) < 100,
                        'bg-amber-500': (item.realisasi?.persentase_selesai ?? 0) > 0 && (item.realisasi?.persentase_selesai ?? 0) < 50,
                        'bg-slate-300': (item.realisasi?.persentase_selesai ?? 0) === 0,
                      }"
                      :style="{ width: `${Math.min(100, item.realisasi?.persentase_selesai ?? 0)}%` }"
                    ></div>
                  </div>
                  <div v-if="item.realisasi?.bukti_dukung?.length" class="text-[10px] text-slate-400 flex items-center gap-1">
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    {{ item.realisasi.bukti_dukung.length }} berkas bukti
                  </div>
                </div>
              </td>
              <td class="text-center">
                <StatusBadge
                  :label="getStatusBadgeConfig(item.realisasi?.status).label"
                  :color="getStatusBadgeConfig(item.realisasi?.status).color"
                />
              </td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <router-link
                    :to="`/renaksi/${item.id}`"
                    class="btn-secondary py-1 px-2.5 text-[11px] inline-flex items-center gap-1"
                  >
                    <span>Lapor / Detail</span>
                  </router-link>
                  <button
                    v-if="isSuperAdmin || isAdminKanwil"
                    class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
                    title="Hapus"
                    @click="handleDeleteRenaksi(item.id)"
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
        v-if="filteredItems.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>

    <!-- Create Modal -->
    <div
      v-if="createModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-base font-bold text-slate-800">Tambah Rencana Aksi</h2>
            <p class="text-xs text-slate-400">Rencana pelaksanaan kegiatan perjanjian kinerja</p>
          </div>
          <button
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
            @click="createModalOpen = false"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleCreateRenaksi">
          <!-- Tahun & Triwulan -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Anggaran</label>
              <select v-model="createForm.tahun_anggaran_id" class="input-select text-xs py-2" required>
                <option v-for="t in tahuns" :key="t.id" :value="t.id">
                  {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Triwulan Target</label>
              <select v-model="createForm.triwulan" class="input-select text-xs py-2" required>
                <option value="TW1">Triwulan I (TW1)</option>
                <option value="TW2">Triwulan II (TW2)</option>
                <option value="TW3">Triwulan III (TW3)</option>
                <option value="TW4">Triwulan IV (TW4)</option>
              </select>
            </div>
          </div>

          <!-- Satker -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Kerja</label>
            <select
              v-if="!isOperatorSatker"
              v-model="createForm.satker_id"
              class="input-select text-xs py-2"
              required
            >
              <option v-for="s in satkers" :key="s.id" :value="s.id">
                [{{ s.kode }}] {{ s.nama }}
              </option>
            </select>
            <input
              v-else
              type="text"
              disabled
              :value="user?.satker?.nama || ''"
              class="input-text text-xs py-2 bg-slate-50 cursor-not-allowed"
            />
          </div>

          <!-- Indikator -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Indikator Kinerja Sasaran</label>
            <select v-model="createForm.indikator_id" class="input-select text-xs py-2" required>
              <option v-for="ind in indikators" :key="ind.id" :value="ind.id">
                [{{ ind.kode }}] {{ ind.nama }}
              </option>
            </select>
          </div>

          <!-- Nama Aksi -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">
              Nama Rencana Aksi <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="createForm.nama_aksi"
              type="text"
              placeholder="Contoh: Penguatan Integritas Pegawai Melalui Sosialisasi Antikorupsi"
              class="input-text text-xs py-2"
              required
            />
          </div>

          <!-- Target Output -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Target Output / Keluaran</label>
            <textarea
              v-model="createForm.target_output"
              rows="3"
              placeholder="Contoh: 1 Laporan Kegiatan Sosialisasi Antikorupsi & Daftar Hadir Pegawai"
              class="input-text text-xs py-2 resize-none"
            ></textarea>
          </div>

          <!-- Modal Action Buttons -->
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              class="btn-secondary text-xs"
              @click="createModalOpen = false"
            >
              Batal
            </button>
            <button
              type="submit"
              class="btn-primary text-xs"
              :disabled="modalSubmitting"
            >
              <span v-if="modalSubmitting">Menyimpan...</span>
              <span v-else>Simpan Rencana Aksi</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>