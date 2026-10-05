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

// Search query
const searchQuery = ref('')

// Summary Stats
const stats = computed(() => {
  const total = items.value.length
  let terverifikasi = 0
  let menunggu = 0
  let perbaikan = 0
  let belumLapor = 0
  let totalProgres = 0

  items.value.forEach((item) => {
    const s = item.realisasi?.status
    if (s === 'terverifikasi') terverifikasi++
    else if (s === 'menunggu_verifikasi') menunggu++
    else if (s === 'perlu_perbaikan') perbaikan++
    else belumLapor++

    totalProgres += item.realisasi?.persentase_selesai ?? 0
  })

  const avgProgres = total > 0 ? (totalProgres / total).toFixed(1) : '0'

  return { total, terverifikasi, menunggu, perbaikan, belumLapor, avgProgres }
})

// Filtered items by active tab and search query
const filteredItems = computed(() => {
  let list = items.value
  if (activeTriwulanTab.value !== 'ALL') {
    list = list.filter((item) => item.triwulan === activeTriwulanTab.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(
      (item) =>
        item.nama_aksi.toLowerCase().includes(q) ||
        (item.target_output?.toLowerCase().includes(q) ?? false) ||
        (item.satker?.nama.toLowerCase().includes(q) ?? false) ||
        (item.indikator?.nama.toLowerCase().includes(q) ?? false) ||
        (item.indikator?.kode.toLowerCase().includes(q) ?? false)
    )
  }
  return list
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
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-slate-300 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Rencana Aksi Perjanjian Kinerja</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-amber-400">
          Rencana Aksi PK
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-slate-900 shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Capaian Rencana Aksi
              </h1>
              <p class="text-xs sm:text-sm text-slate-200 mt-0.5 max-w-2xl leading-relaxed">
                Monitoring dan evaluasi realisasi rencana aksi perjanjian kinerja Satuan Kerja se-Kalimantan Selatan.
              </p>
            </div>
          </div>

          <!-- Action Button in Banner -->
          <div v-if="isSuperAdmin || isAdminKanwil || isOperatorSatker" class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <button
              type="button"
              @click="openCreateModal"
              class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all border border-amber-400/40 bg-amber-500 text-slate-950 hover:bg-amber-400 flex items-center gap-2 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Tambah Rencana Aksi</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 4 KPI Stat Cards (Matching Image 4) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Rekapitulasi Kinerja -->
      <div class="card p-5 bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#091F4A] flex items-center justify-center shrink-0 border border-blue-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rekapitulasi Kinerja</p>
          <p class="text-sm font-black text-slate-800 truncate mt-0.5" :title="selectedSatkerId ? (satkers.find(s => s.id === selectedSatkerId)?.nama || 'Satuan Kerja') : 'Seluruh Satuan Kerja (Kalsel)'">
            {{ selectedSatkerId ? (satkers.find(s => s.id === selectedSatkerId)?.nama || 'Satuan Kerja') : 'Seluruh Satuan Kerja' }}
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Wilayah Kalimantan Selatan</p>
        </div>
      </div>

      <!-- Card 2: Total Rencana Aksi -->
      <div class="card p-5 bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0 border border-indigo-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Rencana Aksi</p>
          <p class="text-2xl font-black text-slate-900 mt-0.5">
            {{ stats.total }} <span class="text-xs font-bold text-slate-500">Aksi</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Target Perjanjian Kinerja</p>
        </div>
      </div>

      <!-- Card 3: Aksi Terverifikasi -->
      <div class="card p-5 bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Aksi Terverifikasi</p>
          <p class="text-2xl font-black text-emerald-700 mt-0.5">
            {{ stats.terverifikasi }} <span class="text-xs font-bold text-emerald-600">Terlapor</span>
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">{{ stats.menunggu }} menunggu verifikasi</p>
        </div>
      </div>

      <!-- Card 4: Rata-rata Progres -->
      <div class="card p-5 bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Rata-rata Progres</p>
          <p class="text-2xl font-black text-slate-900 mt-0.5">
            {{ stats.avgProgres }}%
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Realisasi Fisik Triwulan</p>
        </div>
      </div>
    </div>

    <!-- Filter & Triwulan Tabs Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm space-y-4">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
          <!-- Tahun Anggaran -->
          <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Tahun Anggaran:</label>
            <select
              v-model="selectedTahunId"
              class="form-select text-xs py-2 pl-3 pr-8 font-semibold border-slate-200 rounded-xl focus:ring-2 focus:ring-[#091F4A]"
              @change="fetchRenaksi"
            >
              <option v-for="t in tahuns" :key="t.id" :value="t.id">
                {{ t.tahun }} {{ t.is_aktif ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <!-- Satker Filter -->
          <div v-if="!isOperatorSatker" class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Satuan Kerja:</label>
            <select
              v-model="selectedSatkerId"
              class="form-select text-xs py-2 pl-3 pr-8 font-semibold border-slate-200 rounded-xl max-w-xs focus:ring-2 focus:ring-[#091F4A]"
              @change="fetchRenaksi"
            >
              <option :value="null">Semua Satuan Kerja ({{ satkers.length }})</option>
              <option v-for="s in satkers" :key="s.id" :value="s.id">
                [{{ s.kode }}] {{ s.nama }}
              </option>
            </select>
          </div>

          <div v-else class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Satuan Kerja:</label>
            <span class="text-xs font-bold text-slate-800 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
              {{ user?.satker?.nama || 'Satuan Kerja Anda' }}
            </span>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="w-full lg:w-72">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari aksi, output, satker..."
              class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#091F4A]"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Triwulan Tabs Bar -->
      <div class="flex items-center gap-2 border-t border-slate-100 pt-3 overflow-x-auto">
        <button
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
          :class="activeTriwulanTab === 'ALL'
            ? 'bg-[#091F4A] text-white shadow-xs'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          @click="activeTriwulanTab = 'ALL'"
        >
          <span>Semua Triwulan</span>
          <span
            class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
            :class="activeTriwulanTab === 'ALL' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
          >
            {{ items.length }}
          </span>
        </button>

        <button
          v-for="tw in ['TW1', 'TW2', 'TW3', 'TW4']"
          :key="tw"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5"
          :class="activeTriwulanTab === tw
            ? 'bg-[#091F4A] text-white shadow-xs'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          @click="activeTriwulanTab = tw"
        >
          <span>{{ tw }}</span>
          <span
            class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
            :class="activeTriwulanTab === tw ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
          >
            {{ items.filter(i => i.triwulan === tw).length }}
          </span>
        </button>
      </div>
    </div>

    <!-- Table Section -->
    <div class="card overflow-hidden bg-white border border-slate-200 shadow-sm rounded-xl">
      <!-- Card Title Bar -->
      <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="px-2.5 py-1 rounded bg-[#091F4A] text-white text-[10px] font-black uppercase tracking-wider">
            REKAPITULASI RENCANA AKSI
          </span>
          <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
            Capaian Rencana Aksi Perjanjian Kinerja
          </h2>
        </div>
        <div class="text-xs font-semibold text-slate-500">
          Menampilkan: <span class="font-bold text-slate-800">{{ filteredItems.length }}</span> Rencana Aksi
        </div>
      </div>

      <SkeletonTable v-if="loading" :rows="5" :cols="8" />

      <div v-else-if="filteredItems.length === 0" class="p-16 text-center text-slate-400">
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
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-black uppercase tracking-wider">
              <th class="py-4 px-4 text-center w-12 border-r border-white/10">#</th>
              <th class="py-4 px-4 text-center w-24 border-r border-white/10">Kode</th>
              <th v-if="!isOperatorSatker" class="py-4 px-5 min-w-[220px] border-r border-white/10">Satuan Kerja</th>
              <th class="py-4 px-5 min-w-[280px] border-r border-white/10">Indikator Terkait</th>
              <th class="py-4 px-5 min-w-[320px] border-r border-white/10">Rencana Aksi & Target Output</th>
              <th class="py-4 px-4 text-center w-24 border-r border-white/10">Periode</th>
              <th class="py-4 px-5 min-w-[170px] border-r border-white/10">Progres Pelaksanaan</th>
              <th class="py-4 px-4 text-center w-36 border-r border-white/10">Status</th>
              <th class="py-4 px-4 text-center w-32">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr
              v-for="(item, idx) in paginatedItems"
              :key="item.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- No -->
              <td class="py-4 px-4 text-center font-bold text-slate-400">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Kode -->
              <td class="py-4 px-4 text-center">
                <span class="inline-block px-2 py-0.5 rounded bg-[#091F4A]/10 text-[#091F4A] font-black font-mono text-[10px] border border-[#091F4A]/20">
                  {{ item.indikator?.kode || 'RA' }}
                </span>
              </td>

              <!-- Satker -->
              <td v-if="!isOperatorSatker" class="py-4 px-5">
                <span class="font-bold text-slate-800 leading-snug block">{{ item.satker?.nama || '-' }}</span>
                <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">{{ item.satker?.kode }}</span>
              </td>

              <!-- Indikator -->
              <td class="py-4 px-5">
                <span class="font-semibold text-slate-800 line-clamp-2 leading-relaxed" :title="item.indikator?.nama">
                  {{ item.indikator?.nama || '-' }}
                </span>
                <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-mono">
                  Target IKU
                </span>
              </td>

              <!-- Aksi & Output -->
              <td class="py-4 px-5">
                <div class="flex items-start gap-2">
                  <div class="w-5 h-5 rounded-full bg-blue-100 text-[#091F4A] flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold">
                    📌
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 leading-snug">{{ item.nama_aksi }}</p>
                    <p v-if="item.target_output" class="text-[11px] text-slate-600 mt-1 line-clamp-2">
                      <span class="font-semibold text-slate-700">Output:</span> {{ item.target_output }}
                    </p>
                  </div>
                </div>
              </td>

              <!-- Periode -->
              <td class="py-4 px-4 text-center">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-black tracking-wider bg-slate-100 text-[#091F4A] border border-slate-200">
                  {{ item.triwulan }}
                </span>
              </td>

              <!-- Progres -->
              <td class="py-4 px-5">
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 font-medium">Realisasi</span>
                    <span class="font-black text-slate-800">
                      {{ item.realisasi?.persentase_selesai ?? 0 }}%
                    </span>
                  </div>
                  <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200/50">
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

              <!-- Status -->
              <td class="py-4 px-4 text-center">
                <StatusBadge
                  :label="getStatusBadgeConfig(item.realisasi?.status).label"
                  :color="getStatusBadgeConfig(item.realisasi?.status).color"
                />
              </td>

              <!-- Aksi -->
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <router-link
                    :to="`/renaksi/${item.id}`"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-xs inline-flex items-center gap-1"
                  >
                    <span>Lapor / Detail</span>
                  </router-link>
                  <button
                    v-if="isSuperAdmin || isAdminKanwil"
                    class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
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