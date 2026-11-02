<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  useDadukLaporanStore,
  type DadukSubmission,
} from '@/composables/useDadukLaporanStore'

const dadukStore = useDadukLaporanStore()

// Filter Options
const selectedUnit = ref('Semua unit')
const unitOptions = [
  'Semua unit',
  'Setjen',
  'AHU',
  'KI',
  'PP',
  'BPHN',
  'BSK',
]

const selectedTw = ref('Semua TW')
const twOptions = [
  'Semua TW',
  'TW 1',
  'TW 2',
  'TW 3',
  'TW 4',
]

const selectedStatus = ref('Semua Status')
const statusOptions = [
  'Semua Status',
  'Menunggu',
  'Perlu Perbaikan',
  'Diterima',
]

// Map TW to months/keys
function matchTwFilter(item: DadukSubmission, tw: string): boolean {
  if (tw === 'Semua TW') return true
  const key = item.periodeKey.toLowerCase()
  const label = item.periode.toLowerCase()

  if (tw === 'TW 1') {
    return key === 'tw1' || ['jan', 'feb', 'mar'].includes(key) || label.includes('tw 1') || label.includes('triwulan i')
  }
  if (tw === 'TW 2') {
    return key === 'tw2' || ['apr', 'mei', 'jun'].includes(key) || label.includes('tw 2') || label.includes('triwulan ii')
  }
  if (tw === 'TW 3') {
    return key === 'tw3' || ['jul', 'agu', 'sep'].includes(key) || label.includes('tw 3') || label.includes('triwulan iii')
  }
  if (tw === 'TW 4') {
    return key === 'tw4' || ['okt', 'nov', 'des'].includes(key) || label.includes('tw 4') || label.includes('triwulan iv')
  }
  return true
}

// Submissions filtered dynamically
const filteredSubmissions = computed(() => {
  return dadukStore.submissions.value.filter((item) => {
    // 1. Filter Unit
    if (selectedUnit.value !== 'Semua unit' && item.unitKode.toUpperCase() !== selectedUnit.value.toUpperCase()) {
      return false
    }

    // 2. Filter Triwulan
    if (!matchTwFilter(item, selectedTw.value)) {
      return false
    }

    // 3. Filter Status
    if (selectedStatus.value !== 'Semua Status' && item.status.toLowerCase() !== selectedStatus.value.toLowerCase()) {
      return false
    }

    return true
  })
})

// Metrics
const countMenunggu = dadukStore.countMenunggu
const countPerluPerbaikan = dadukStore.countPerluPerbaikan
const countDiterima = dadukStore.countDiterima

// Verification Modal State
const showModal = ref(false)
const activeItem = ref<DadukSubmission | null>(null)
const decisionStatus = ref<'Diterima' | 'Perlu Perbaikan'>('Diterima')
const decisionCatatan = ref('')

function openVerifyModal(item: DadukSubmission) {
  activeItem.value = item
  decisionStatus.value = item.status === 'Perlu Perbaikan' ? 'Perlu Perbaikan' : 'Diterima'
  decisionCatatan.value = item.catatanVerifikator || ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  activeItem.value = null
}

// Toast Feedback
const showToast = ref(false)
const toastMessage = ref('')
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}

function saveVerification() {
  if (!activeItem.value) return
  dadukStore.verifySubmission(activeItem.value.id, decisionStatus.value, decisionCatatan.value)
  triggerToast(`Hasil verifikasi untuk ${activeItem.value.unitKode} (${activeItem.value.periode}) berhasil disimpan!`)
  closeModal()
}

function downloadSubmittedFile(item: DadukSubmission) {
  if (item.fileDataUrl) {
    const a = document.createElement('a')
    a.href = item.fileDataUrl
    a.download = item.namaFile
    a.click()
    return
  }
  const blob = new Blob(
    [
      `DOKUMEN BUKTI DATA DUKUNG / LAPORAN\nUnit: ${item.unitName} (${item.unitKode})\nJenis: ${item.jenisLaporan}\nPeriode: ${item.periode}\nTanggal Unggah: ${item.tanggalKirim}\nStatus: ${item.status}\nCatatan Pengirim: ${item.catatanPengirim || '-'}\nCatatan Verifikator: ${item.catatanVerifikator || '-'}`,
    ],
    { type: 'application/pdf' },
  )
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = item.namaFile
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in relative">
    <!-- Toast Feedback -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Header with Filters (Image 5) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Verifikasi Daduk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Pemeriksaan dan validasi bukti data dukung rencana aksi per unit kerja
        </p>
      </div>

      <!-- Filter Dropdowns: 1 Baris Mendatar (Horizontal Row) dengan Panah Berjarak Nyaman -->
      <div class="flex items-center gap-2.5 flex-nowrap overflow-x-auto sm:overflow-visible">
        <!-- 1. Filter Unit -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedUnit"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- 2. Filter Triwulan -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedTw"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="tw in twOptions" :key="tw" :value="tw">{{ tw }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- 3. Filter Status -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedStatus"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Metric Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div
        @click="selectedStatus = 'Menunggu'"
        class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between cursor-pointer hover:border-amber-400 transition-colors"
        :class="{ 'ring-2 ring-amber-400': selectedStatus === 'Menunggu' }"
      >
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Verifikasi</p>
          <p class="text-2xl font-black text-amber-600 mt-1">{{ countMenunggu }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <div
        @click="selectedStatus = 'Perlu Perbaikan'"
        class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between cursor-pointer hover:border-rose-400 transition-colors"
        :class="{ 'ring-2 ring-rose-400': selectedStatus === 'Perlu Perbaikan' }"
      >
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Perlu Perbaikan</p>
          <p class="text-2xl font-black text-rose-600 mt-1">{{ countPerluPerbaikan }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
      </div>

      <div
        @click="selectedStatus = 'Diterima'"
        class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between cursor-pointer hover:border-emerald-400 transition-colors"
        :class="{ 'ring-2 ring-emerald-400': selectedStatus === 'Diterima' }"
      >
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase">Telah Diterima</p>
          <p class="text-2xl font-black text-emerald-600 mt-1">{{ countDiterima }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Main Content Panel: Table when items exist -->
    <div
      v-if="filteredSubmissions.length > 0"
      class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3.5 px-5">UNIT KERJA</th>
              <th class="py-3.5 px-4">JENIS DADUK / LAPORAN</th>
              <th class="py-3.5 px-4">PERIODE & PENGIRIMAN</th>
              <th class="py-3.5 px-4">BERKAS DOKUMEN</th>
              <th class="py-3.5 px-4 text-center">STATUS</th>
              <th class="py-3.5 px-4">CATATAN PENGIRIM</th>
              <th class="py-3.5 px-5 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in filteredSubmissions"
              :key="row.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <!-- UNIT KERJA -->
              <td class="py-3.5 px-5">
                <span class="font-bold text-slate-900 text-sm block leading-tight">{{ row.unitKode }}</span>
                <span class="text-[11px] text-slate-400 block mt-0.5">{{ row.unitName }}</span>
              </td>

              <!-- JENIS DADUK / LAPORAN -->
              <td class="py-3.5 px-4 font-semibold text-slate-700">
                {{ row.jenisLaporan }}
              </td>

              <!-- PERIODE & PENGIRIMAN -->
              <td class="py-3.5 px-4 text-slate-600">
                <span class="block font-medium">{{ row.periode }}</span>
                <span class="block text-[10px] text-slate-400">{{ row.tanggalKirim }}</span>
              </td>

              <!-- BERKAS DOKUMEN -->
              <td class="py-3.5 px-4">
                <button
                  type="button"
                  @click="downloadSubmittedFile(row)"
                  class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium cursor-pointer group text-left"
                >
                  <svg class="w-4 h-4 flex-shrink-0 text-slate-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span class="truncate max-w-[170px] underline-offset-2 group-hover:underline">{{ row.namaFile }}</span>
                  <span class="text-[10px] text-slate-400 font-normal">({{ row.ukuranFile }})</span>
                </button>
              </td>

              <!-- STATUS -->
              <td class="py-3.5 px-4 text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border"
                  :class="{
                    'bg-amber-50 text-amber-700 border-amber-200': row.status === 'Menunggu',
                    'bg-rose-50 text-rose-700 border-rose-200': row.status === 'Perlu Perbaikan',
                    'bg-emerald-50 text-emerald-700 border-emerald-200': row.status === 'Diterima',
                  }"
                >
                  {{ row.status }}
                </span>
                <span v-if="row.catatanVerifikator" class="block text-[10px] text-slate-500 mt-1 max-w-[150px] truncate mx-auto" :title="row.catatanVerifikator">
                  {{ row.catatanVerifikator }}
                </span>
              </td>

              <!-- CATATAN PENGIRIM -->
              <td class="py-3.5 px-4 text-slate-600 max-w-[200px]">
                <p class="truncate" :title="row.catatanPengirim || '-'">
                  {{ row.catatanPengirim || '-' }}
                </p>
              </td>

              <!-- AKSI -->
              <td class="py-3.5 px-5 text-right whitespace-nowrap">
                <button
                  type="button"
                  @click="openVerifyModal(row)"
                  class="px-3.5 py-1.5 rounded-lg bg-[#0B2A6F] hover:bg-[#071E4A] text-white text-xs font-bold transition-colors shadow-2xs cursor-pointer inline-flex items-center gap-1.5"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                  <span>Verifikasi</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State Panel (Image 5) when no submissions match the active filters -->
    <div v-else class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-12 text-center">
      <div class="max-w-md mx-auto py-12 space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center mx-auto">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-sm font-semibold text-slate-600">
          Tidak ada daduk pada filter ini.
        </p>
        <p class="text-xs text-slate-400">
          Semua data dukung telah terverifikasi atau belum ada unggahan baru dari satuan kerja.
        </p>
      </div>
    </div>

    <!-- VERIFIKASI MODAL -->
    <div
      v-if="showModal && activeItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden animate-scale-up">
        <!-- Header -->
        <div class="bg-[#0B2A6F] text-white p-5 flex items-center justify-between">
          <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-[#F5BA31] block mb-0.5">
              Verifikasi Data Dukung / Laporan
            </span>
            <h3 class="text-base font-bold">
              {{ activeItem.unitKode }} &mdash; {{ activeItem.periode }}
            </h3>
          </div>
          <button
            type="button"
            @click="closeModal"
            class="text-slate-300 hover:text-white rounded-lg p-1 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <form @submit.prevent="saveVerification" class="p-5 space-y-4 text-xs">
          <!-- Submission Info Box -->
          <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Satuan Kerja:</span>
              <span class="font-bold text-slate-800">{{ activeItem.unitName }} ({{ activeItem.unitKode }})</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Jenis / Periode:</span>
              <span class="font-bold text-slate-800">{{ activeItem.jenisLaporan }} &bull; {{ activeItem.periode }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Waktu Pengunggahan:</span>
              <span class="font-bold text-slate-800">{{ activeItem.tanggalKirim }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
              <span class="text-slate-500 font-semibold">Dokumen Berkas:</span>
              <button
                type="button"
                @click="downloadSubmittedFile(activeItem)"
                class="font-bold text-blue-600 hover:text-blue-800 underline truncate max-w-[240px] cursor-pointer"
              >
                {{ activeItem.namaFile }}
              </button>
            </div>
            <div v-if="activeItem.catatanPengirim" class="pt-1 text-slate-600">
              <span class="font-semibold text-slate-500">Catatan Pengirim:</span>
              <p class="mt-0.5 italic text-slate-700 bg-white p-2 rounded border border-slate-100">
                "{{ activeItem.catatanPengirim }}"
              </p>
            </div>
          </div>

          <!-- Decision Selector -->
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Hasil Keputusan Verifikasi</label>
            <div class="grid grid-cols-2 gap-3">
              <label
                class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                :class="decisionStatus === 'Diterima' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
              >
                <input
                  v-model="decisionStatus"
                  type="radio"
                  value="Diterima"
                  class="text-emerald-600 focus:ring-emerald-500"
                />
                <span>Terima Dokumen</span>
              </label>

              <label
                class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                :class="decisionStatus === 'Perlu Perbaikan' ? 'border-rose-500 bg-rose-50/50 text-rose-900 font-bold' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
              >
                <input
                  v-model="decisionStatus"
                  type="radio"
                  value="Perlu Perbaikan"
                  class="text-rose-600 focus:ring-rose-500"
                />
                <span>Perlu Perbaikan</span>
              </label>
            </div>
          </div>

          <!-- Catatan Verifikator -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">
              Catatan / Arahan Verifikator
              <span v-if="decisionStatus === 'Perlu Perbaikan'" class="text-rose-500">* (Wajib diisi untuk perbaikan)</span>
              <span v-else class="text-slate-400 font-normal">(Opsional)</span>
            </label>
            <textarea
              v-model="decisionCatatan"
              rows="3"
              placeholder="Berikan umpan balik atau instruksi perbaikan untuk Satuan Kerja..."
              class="w-full bg-white border border-slate-200 rounded-lg p-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
              :required="decisionStatus === 'Perlu Perbaikan'"
            />
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 text-xs font-bold rounded-lg text-white shadow-2xs transition-colors cursor-pointer"
              :class="decisionStatus === 'Diterima' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
            >
              Simpan Hasil Verifikasi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
