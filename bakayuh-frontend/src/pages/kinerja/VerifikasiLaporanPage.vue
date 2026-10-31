<script setup lang="ts">
import { ref, computed } from 'vue'

export interface SubmissionItem {
  id: string
  unitKode: string
  unitName: string
  jenisLaporan: string
  periode: string
  tanggalKirim: string
  namaFile: string
  ukuranFile: string
  status: 'Menunggu' | 'Perlu Perbaikan' | 'Diterima'
  catatanVerifikator?: string
}

const submissions = ref<SubmissionItem[]>([
  {
    id: 'sub-1',
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    tanggalKirim: '09/10/2026 14:20',
    namaFile: 'Laporan_Bulanan_Setjen_Sep2026.pdf',
    ukuranFile: '2.4 MB',
    status: 'Menunggu',
    catatanVerifikator: '',
  },
  {
    id: 'sub-2',
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    jenisLaporan: 'Laporan Triwulan',
    periode: 'Triwulan III (TW 3)',
    tanggalKirim: '08/10/2026 11:05',
    namaFile: 'Laporan_TW3_AHU_2026_Final.pdf',
    ukuranFile: '4.1 MB',
    status: 'Menunggu',
    catatanVerifikator: '',
  },
  {
    id: 'sub-3',
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'Agustus 2026',
    tanggalKirim: '09/09/2026 16:45',
    namaFile: 'Laporan_Bulanan_KI_Agustus2026.pdf',
    ukuranFile: '1.8 MB',
    status: 'Diterima',
    catatanVerifikator: 'Data dukung dan laporan lengkap sesuai format standar.',
  },
  {
    id: 'sub-4',
    unitKode: 'PP',
    unitName: 'Peraturan Perundang-Undangan',
    jenisLaporan: 'Laporan Triwulan',
    periode: 'Triwulan II (TW 2)',
    tanggalKirim: '09/07/2026 10:15',
    namaFile: 'Laporan_TW2_PP_Revisi.pdf',
    ukuranFile: '3.2 MB',
    status: 'Diterima',
    catatanVerifikator: 'Revisi matriks capaian telah diperbaiki dan diverifikasi.',
  },
  {
    id: 'sub-5',
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    tanggalKirim: '10/10/2026 09:30',
    namaFile: 'Laporan_BPHN_Sep_Draft.pdf',
    ukuranFile: '1.2 MB',
    status: 'Perlu Perbaikan',
    catatanVerifikator: 'Tanda tangan pimpinan belum terlampir, mohon perbaiki.',
  },
  {
    id: 'sub-6',
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
    jenisLaporan: 'Laporan Bulanan',
    periode: 'September 2026',
    tanggalKirim: '09/10/2026 15:10',
    namaFile: 'Laporan_Kinerja_BSK_Sep2026.pdf',
    ukuranFile: '2.9 MB',
    status: 'Menunggu',
    catatanVerifikator: '',
  },
])

// Filters
const filterUnit = ref('Semua unit')
const unitOptions = ['Semua unit', 'Setjen', 'AHU', 'KI', 'PP', 'BPHN', 'BSK']

const filterJenis = ref('Semua jenis')
const jenisOptions = ['Semua jenis', 'Laporan Bulanan', 'Laporan Triwulan']

const filterStatus = ref('Semua status')
const statusOptions = ['Semua status', 'Menunggu', 'Perlu Perbaikan', 'Diterima']

const filteredSubmissions = computed(() => {
  return submissions.value.filter((item) => {
    if (filterUnit.value !== 'Semua unit' && item.unitKode !== filterUnit.value) return false
    if (filterJenis.value !== 'Semua jenis' && item.jenisLaporan !== filterJenis.value) return false
    if (filterStatus.value !== 'Semua status' && item.status !== filterStatus.value) return false
    return true
  })
})

// Metrics
const countMenunggu = computed(() => submissions.value.filter((s) => s.status === 'Menunggu').length)
const countPerluPerbaikan = computed(() => submissions.value.filter((s) => s.status === 'Perlu Perbaikan').length)
const countDiterima = computed(() => submissions.value.filter((s) => s.status === 'Diterima').length)

// Verification Modal
const showModal = ref(false)
const activeItem = ref<SubmissionItem | null>(null)
const decisionStatus = ref<'Diterima' | 'Perlu Perbaikan'>('Diterima')
const decisionCatatan = ref('')

function openVerifyModal(item: SubmissionItem) {
  activeItem.value = item
  decisionStatus.value = item.status === 'Perlu Perbaikan' ? 'Perlu Perbaikan' : 'Diterima'
  decisionCatatan.value = item.catatanVerifikator || ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  activeItem.value = null
}

// Toast
const toastMessage = ref('')
const showToast = ref(false)
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}

function saveVerification() {
  if (!activeItem.value) return
  activeItem.value.status = decisionStatus.value
  activeItem.value.catatanVerifikator = decisionCatatan.value.trim()
  triggerToast(`Verifikasi untuk ${activeItem.value.unitKode} (${activeItem.value.jenisLaporan}) berhasil disimpan!`)
  closeModal()
}
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in relative">
    <!-- Toast Feedback -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Page Header & Filter Row -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          Verifikasi Laporan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Pemeriksaan dan validasi bukti fisik/dokumen pengumpulan laporan kinerja per unit kerja.
        </p>
      </div>

      <!-- Filter Dropdowns in a single neat row -->
      <div class="flex items-center gap-2.5 flex-nowrap overflow-x-auto sm:overflow-visible">
        <!-- Filter Unit -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="filterUnit"
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

        <!-- Filter Jenis -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="filterJenis"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="j in jenisOptions" :key="j" :value="j">{{ j }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Filter Status -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="filterStatus"
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
      <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between">
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

      <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between">
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

      <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs flex items-center justify-between">
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

    <!-- Submissions Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3.5 px-5">UNIT KERJA</th>
              <th class="py-3.5 px-4">JENIS LAPORAN</th>
              <th class="py-3.5 px-4">PERIODE</th>
              <th class="py-3.5 px-4">DOKUMEN</th>
              <th class="py-3.5 px-4 text-center">STATUS</th>
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

              <!-- JENIS LAPORAN -->
              <td class="py-3.5 px-4 font-semibold text-slate-700">
                {{ row.jenisLaporan }}
              </td>

              <!-- PERIODE -->
              <td class="py-3.5 px-4 text-slate-600">
                <span class="block font-medium">{{ row.periode }}</span>
                <span class="block text-[10px] text-slate-400">{{ row.tanggalKirim }}</span>
              </td>

              <!-- DOKUMEN -->
              <td class="py-3.5 px-4">
                <div class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium cursor-pointer">
                  <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <span class="truncate max-w-[180px]">{{ row.namaFile }}</span>
                  <span class="text-[10px] text-slate-400 font-normal">({{ row.ukuranFile }})</span>
                </div>
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
              </td>

              <!-- AKSI -->
              <td class="py-3.5 px-5 text-right whitespace-nowrap">
                <button
                  type="button"
                  @click="openVerifyModal(row)"
                  class="px-3.5 py-1.5 rounded-lg bg-[#0B2A6F] hover:bg-[#071E4A] text-white text-xs font-bold transition-colors shadow-2xs cursor-pointer inline-flex items-center gap-1"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                  <span>Verifikasi</span>
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="filteredSubmissions.length === 0">
              <td colspan="6" class="py-12 text-center text-slate-400">
                Tidak ada laporan yang cocok dengan kriteria filter saat ini.
              </td>
            </tr>
          </tbody>
        </table>
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
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-300 block mb-0.5">
              Verifikasi Pengumpulan Laporan
            </span>
            <h3 class="text-base font-bold">
              {{ activeItem.unitKode }} &mdash; {{ activeItem.jenisLaporan }}
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
          <!-- Submission Info -->
          <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Unit:</span>
              <span class="font-bold text-slate-800">{{ activeItem.unitName }} ({{ activeItem.unitKode }})</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Periode:</span>
              <span class="font-bold text-slate-800">{{ activeItem.periode }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500 font-semibold">Waktu Unggah:</span>
              <span class="font-bold text-slate-800">{{ activeItem.tanggalKirim }}</span>
            </div>
            <div class="flex justify-between items-center pt-1 border-t border-slate-200">
              <span class="text-slate-500 font-semibold">Berkas:</span>
              <span class="font-bold text-blue-600 truncate max-w-[240px]">{{ activeItem.namaFile }}</span>
            </div>
          </div>

          <!-- Decision Selector -->
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Keputusan Verifikasi</label>
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
                <span>Terima Laporan</span>
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

          <!-- Catatan / Feedback -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan / Masukan Verifikator</label>
            <textarea
              v-model="decisionCatatan"
              rows="3"
              placeholder="Berikan catatan perbaikan jika status perlu perbaikan, atau apresiasi jika laporan lengkap..."
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            ></textarea>
          </div>

          <!-- Footer -->
          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-lg border border-slate-200 bg-white font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-lg bg-[#0B2A6F] text-white font-bold hover:bg-[#071E4A] transition-colors cursor-pointer"
            >
              Simpan Keputusan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
