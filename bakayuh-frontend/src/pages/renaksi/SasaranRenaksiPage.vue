<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const route = useRoute()
const { isOperatorSatker, userUnitCode } = useAuth()

const selectedUnit = ref('ALL')
const unitOptions = [
  { id: 'ALL', label: 'Semua unit' },
  { id: 'Setjen', label: 'Setjen' },
  { id: 'AHU', label: 'AHU' },
  { id: 'KI', label: 'KI' },
  { id: 'PP', label: 'PP' },
  { id: 'BPHN', label: 'BPHN' },
  { id: 'BSK', label: 'BSK' },
]

interface IkkItem {
  ikkKode: string
  ikkNama: string
  status: string
}

interface SkItem {
  skKode: string
  skNama: string
  ikkList: IkkItem[]
}

interface SasaranGroup {
  unitKode: string
  items: SkItem[]
}

const STORAGE_KEY_SASARAN = 'bakayuh_sasaran_renaksi_data'

const defaultSasaranGroups: SasaranGroup[] = [
  {
    unitKode: 'Setjen',
    items: [
      {
        skKode: 'SK 7099.1',
        skNama: 'Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        ikkList: [
          { ikkKode: 'IKK 7099.1.1', ikkNama: 'Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK', status: 'Belum ada renaksi' },
        ],
      },
      {
        skKode: 'SK 7099.2',
        skNama: 'Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        ikkList: [
          { ikkKode: 'IKK 7099.2.1', ikkNama: 'Indeks kepuasan terhadap layanan dukungan manajemen', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
  {
    unitKode: 'AHU',
    items: [
      {
        skKode: 'SK 7110.1',
        skNama: 'Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7110.1.1', ikkNama: 'Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah', status: 'Belum ada renaksi' },
        ],
      },
      {
        skKode: 'SK 7110.2',
        skNama: 'Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7110.2.1', ikkNama: 'Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
  {
    unitKode: 'KI',
    items: [
      {
        skKode: 'SK 7121.1',
        skNama: 'Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
        ikkList: [
          { ikkKode: 'IKK 7121.1.1', ikkNama: 'Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
  {
    unitKode: 'PP',
    items: [
      {
        skKode: 'SK 7130.1',
        skNama: 'Meningkatnya Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7130.1.1', ikkNama: 'Persentase Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
  {
    unitKode: 'BPHN',
    items: [
      {
        skKode: 'SK 7136.1',
        skNama: 'Meningkatnya Kualitas Pembinaan Hukum Nasional di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7136.1.1', ikkNama: 'Persentase Desa/Kelurahan Sadar Hukum yang Dibina di Wilayah', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
  {
    unitKode: 'BSK',
    items: [
      {
        skKode: 'SK 7143.1',
        skNama: 'Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7143.1.1', ikkNama: 'Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah', status: 'Belum ada renaksi' },
        ],
      },
    ],
  },
]

const sasaranGroups = ref<SasaranGroup[]>(defaultSasaranGroups)

onMounted(() => {
  if (isOperatorSatker.value) {
    selectedUnit.value = userUnitCode.value || 'AHU'
  } else if (route.query.unit && typeof route.query.unit === 'string') {
    // Tangkap query ?unit=Setjen dari Dashboard Monitoring Daduk
    const match = unitOptions.find((u) => u.id.toLowerCase() === (route.query.unit as string).toLowerCase())
    if (match) {
      selectedUnit.value = match.id
    }
  }

  try {
    const stored = localStorage.getItem(STORAGE_KEY_SASARAN)
    if (stored) {
      sasaranGroups.value = JSON.parse(stored)
    }
  } catch {
    // fallback
  }
})

function saveToStorage() {
  localStorage.setItem(STORAGE_KEY_SASARAN, JSON.stringify(sasaranGroups.value))
}

const filteredSasaranGroups = computed(() => {
  if (isOperatorSatker.value) {
    const targetUnit = (userUnitCode.value || 'AHU').toUpperCase()
    return sasaranGroups.value.filter((g) => g.unitKode.toUpperCase() === targetUnit)
  }
  if (selectedUnit.value === 'ALL') return sasaranGroups.value
  return sasaranGroups.value.filter((g) => g.unitKode === selectedUnit.value)
})

// ========================================================
// EKSPOR REKAP (CSV)
// ========================================================
function exportSasaranCsv() {
  const headers = [
    'No',
    'Unit Kerja',
    'Kode SK',
    'Sasaran Kinerja',
    'Kode IKK',
    'Indikator Kinerja Kegiatan',
    'Status Renaksi',
  ]

  const rows: (string | number)[][] = []
  let counter = 1

  for (const group of filteredSasaranGroups.value) {
    for (const sk of group.items) {
      for (const ikk of sk.ikkList) {
        rows.push([
          counter++,
          `"${group.unitKode}"`,
          `"${sk.skKode}"`,
          `"${sk.skNama.replace(/"/g, '""')}"`,
          `"${ikk.ikkKode}"`,
          `"${ikk.ikkNama.replace(/"/g, '""')}"`,
          `"${ikk.status}"`,
        ])
      }
    }
  }

  const csvContent = '\uFEFF' + [headers.join(','), ...rows.map((r) => r.join(','))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  const unitLabel = selectedUnit.value === 'ALL' ? 'Semua_Unit' : selectedUnit.value
  link.setAttribute('download', `Rekap_Sasaran_Renaksi_${unitLabel}_2026.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)

  triggerToast('Rekap Sasaran & Renaksi berhasil diekspor ke CSV!')
}

// ========================================================
// MODAL TAMBAH SASARAN KINERJA
// ========================================================
const showAddSasaranModal = ref(false)
const newSasaranForm = ref({
  unitKode: 'Setjen',
  skKode: '',
  skNama: '',
})

function openAddSasaranModal() {
  newSasaranForm.value = {
    unitKode: selectedUnit.value === 'ALL' ? 'Setjen' : selectedUnit.value,
    skKode: '',
    skNama: '',
  }
  showAddSasaranModal.value = true
}

function closeAddSasaranModal() {
  showAddSasaranModal.value = false
}

function saveNewSasaran() {
  if (!newSasaranForm.value.skKode.trim() || !newSasaranForm.value.skNama.trim()) {
    alert('Kode Sasaran dan Nama Sasaran wajib diisi!')
    return
  }

  let group = sasaranGroups.value.find((g) => g.unitKode === newSasaranForm.value.unitKode)
  if (!group) {
    group = { unitKode: newSasaranForm.value.unitKode, items: [] }
    sasaranGroups.value.push(group)
  }

  group.items.push({
    skKode: newSasaranForm.value.skKode.trim(),
    skNama: newSasaranForm.value.skNama.trim(),
    ikkList: [],
  })

  saveToStorage()
  triggerToast('Sasaran Kinerja baru berhasil ditambahkan!')
  closeAddSasaranModal()
}

// ========================================================
// MODAL TAMBAH INDIKATOR (IKK)
// ========================================================
const showAddIndikatorModal = ref(false)
const selectedSkTarget = ref<{ unitKode: string; skKode: string } | null>(null)
const newIndikatorForm = ref({
  ikkKode: '',
  ikkNama: '',
})

function openAddIndikatorModal(unitKode: string, skKode: string) {
  selectedSkTarget.value = { unitKode, skKode }
  newIndikatorForm.value = {
    ikkKode: '',
    ikkNama: '',
  }
  showAddIndikatorModal.value = true
}

function closeAddIndikatorModal() {
  showAddIndikatorModal.value = false
  selectedSkTarget.value = null
}

function saveNewIndikator() {
  if (!selectedSkTarget.value) return
  if (!newIndikatorForm.value.ikkKode.trim() || !newIndikatorForm.value.ikkNama.trim()) {
    alert('Kode dan Nama Indikator wajib diisi!')
    return
  }

  const group = sasaranGroups.value.find((g) => g.unitKode === selectedSkTarget.value?.unitKode)
  if (group) {
    const sk = group.items.find((s) => s.skKode === selectedSkTarget.value?.skKode)
    if (sk) {
      sk.ikkList.push({
        ikkKode: newIndikatorForm.value.ikkKode.trim(),
        ikkNama: newIndikatorForm.value.ikkNama.trim(),
        status: 'Belum ada renaksi',
      })
      saveToStorage()
      triggerToast('Indikator Kinerja Kegiatan berhasil ditambahkan!')
    }
  }
  closeAddIndikatorModal()
}

// Hapus Sasaran
function deleteSasaran(unitKode: string, skKode: string) {
  if (confirm(`Yakin ingin menghapus sasaran "${skKode}"?`)) {
    const group = sasaranGroups.value.find((g) => g.unitKode === unitKode)
    if (group) {
      group.items = group.items.filter((s) => s.skKode !== skKode)
      saveToStorage()
      triggerToast(`Sasaran "${skKode}" berhasil dihapus.`)
    }
  }
}

// Hapus Indikator
function deleteIndikator(unitKode: string, skKode: string, ikkKode: string) {
  if (confirm(`Yakin ingin menghapus indikator "${ikkKode}"?`)) {
    const group = sasaranGroups.value.find((g) => g.unitKode === unitKode)
    if (group) {
      const sk = group.items.find((s) => s.skKode === skKode)
      if (sk) {
        sk.ikkList = sk.ikkList.filter((i) => i.ikkKode !== ikkKode)
        saveToStorage()
        triggerToast(`Indikator "${ikkKode}" berhasil dihapus.`)
      }
    }
  }
}

// ========================================================
// TOAST FEEDBACK
// ========================================================
const toastMessage = ref('')
const showToast = ref(false)
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in relative">
    <!-- Toast Feedback Notification -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Header with Filters & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          {{ isOperatorSatker ? `Sasaran Kinerja ${userUnitCode || 'AHU'}` : 'Sasaran Kinerja & Indikator' }}
        </h1>
        <p v-if="!isOperatorSatker" class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Daftar sasaran strategis, indikator kinerja kegiatan, dan rencana aksi per unit kerja
        </p>
      </div>

      <div v-if="!isOperatorSatker" class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
        <!-- Filter Unit Dropdown -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedUnit"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="u in unitOptions" :key="u.id" :value="u.id">{{ u.label }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Tombol Ekspor Rekap (CSV) -->
        <button
          type="button"
          @click="exportSasaranCsv"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs active:scale-95 transition-all"
        >
          Ekspor Rekap (CSV)
        </button>

        <!-- Tombol + Sasaran -->
        <button
          type="button"
          @click="openAddSasaranModal"
          class="btn-primary text-xs py-2 px-4 cursor-pointer inline-flex items-center gap-1.5 shadow-xs active:scale-95 transition-all"
        >
          <span>+</span>
          <span>Sasaran</span>
        </button>
      </div>
    </div>

    <!-- Case A: Operator Satker View (Matches Image 1) -->
    <div v-if="isOperatorSatker" class="space-y-4">
      <template v-for="group in filteredSasaranGroups" :key="group.unitKode">
        <div
          v-for="sk in group.items"
          :key="sk.skKode"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-3"
        >
          <!-- Unit Badge -->
          <div>
            <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200/80">
              {{ group.unitKode }}
            </span>
          </div>

          <!-- SK Title -->
          <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">
            {{ sk.skKode }} &bull; {{ sk.skNama }}
          </h2>

          <!-- IKK Items List -->
          <div class="space-y-2 mt-3">
            <div
              v-for="ikk in sk.ikkList"
              :key="ikk.ikkKode"
              class="p-3.5 bg-white border border-slate-200/90 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
            >
              <span class="text-xs font-semibold text-slate-800">
                {{ ikk.ikkKode }} &bull; {{ ikk.ikkNama }}
              </span>
              <span class="text-[11px] text-slate-400 font-normal">
                {{ ikk.status || 'Belum ada renaksi' }}
              </span>
            </div>
            <div v-if="sk.ikkList.length === 0" class="p-3.5 bg-white border border-slate-200/90 rounded-xl text-xs text-slate-400 italic">
              Belum ada renaksi
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Case B: Admin View (Segmented Flat Rows with management actions) -->
    <div v-else class="space-y-6">
      <div
        v-for="group in filteredSasaranGroups"
        :key="group.unitKode"
        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden p-5 sm:p-6 space-y-4"
      >
        <!-- Unit Header Tag -->
        <div class="text-xs font-black text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100 flex items-center justify-between">
          <span>{{ group.unitKode }}</span>
          <span class="text-[11px] font-normal text-slate-400">{{ group.items.length }} Sasaran Kinerja</span>
        </div>

        <!-- SK Items -->
        <div class="space-y-3.5">
          <div
            v-for="sk in group.items"
            :key="sk.skKode"
            class="border border-slate-200 rounded-xl overflow-hidden"
          >
            <!-- SK Title Header -->
            <div class="p-3.5 bg-slate-50/70 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-50 text-blue-700 border border-blue-200 flex-shrink-0">
                  {{ group.unitKode }}
                </span>
                <span class="text-xs font-bold text-slate-900 leading-snug">
                  {{ sk.skKode }} &bull; {{ sk.skNama }}
                </span>
              </div>
              <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
                <button
                  type="button"
                  @click="openAddIndikatorModal(group.unitKode, sk.skKode)"
                  class="btn-secondary text-[11px] py-1 px-2.5 cursor-pointer shadow-2xs"
                >
                  + Indikator
                </button>
                <button
                  type="button"
                  @click="deleteSasaran(group.unitKode, sk.skKode)"
                  class="btn-secondary text-[11px] py-1 px-2 text-rose-600 hover:text-rose-700 cursor-pointer shadow-2xs"
                >
                  Hapus
                </button>
              </div>
            </div>

            <!-- IKK Children List (Flat divided list) -->
            <div class="divide-y divide-slate-100 bg-white">
              <div
                v-for="ikk in sk.ikkList"
                :key="ikk.ikkKode"
                class="p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 hover:bg-slate-50/50 transition-colors"
              >
                <div class="text-xs text-slate-700 min-w-0">
                  <span class="font-bold text-slate-800">{{ ikk.ikkKode }}</span> &bull; {{ ikk.ikkNama }}
                </div>
                <div class="flex items-center gap-3 self-end sm:self-auto flex-shrink-0">
                  <span class="text-[11px] text-slate-400 font-medium">
                    {{ ikk.status }}
                  </span>
                  <button
                    type="button"
                    @click="deleteIndikator(group.unitKode, sk.skKode, ikk.ikkKode)"
                    class="btn-secondary text-[10px] py-0.5 px-2 text-rose-600 cursor-pointer shadow-2xs"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <!-- Kosong -->
              <div v-if="sk.ikkList.length === 0" class="p-3.5 text-center text-xs text-slate-400 italic">
                Belum ada indikator kinerja kegiatan. Klik "+ Indikator" untuk menambahkan.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL TAMBAH SASARAN -->
    <!-- ======================================================== -->
    <div
      v-if="showAddSasaranModal"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-scale-up">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="text-base font-bold text-slate-900">Tambah Sasaran Kinerja</h2>
          <button type="button" @click="closeAddSasaranModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Unit Kerja</label>
            <select
              v-model="newSasaranForm.unitKode"
              class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            >
              <option v-for="u in unitOptions.filter((o) => o.id !== 'ALL')" :key="u.id" :value="u.id">
                {{ u.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Sasaran (SK)</label>
            <input
              v-model="newSasaranForm.skKode"
              type="text"
              placeholder="Contoh: SK 7099.3"
              class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Sasaran Kinerja</label>
            <textarea
              v-model="newSasaranForm.skNama"
              rows="3"
              placeholder="Masukkan uraian sasaran kinerja..."
              class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="closeAddSasaranModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveNewSasaran"
            class="px-4 py-2 text-xs font-semibold rounded-lg bg-[#0B2A6F] text-white hover:bg-[#081F52]"
          >
            Simpan Sasaran
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL TAMBAH INDIKATOR -->
    <!-- ======================================================== -->
    <div
      v-if="showAddIndikatorModal"
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-scale-up">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="text-base font-bold text-slate-900">Tambah Indikator (IKK)</h2>
          <button type="button" @click="closeAddIndikatorModal" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 text-slate-600 text-[11px]">
            Menambahkan ke sasaran: <strong>{{ selectedSkTarget?.skKode }}</strong> (Unit: {{ selectedSkTarget?.unitKode }})
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kode Indikator (IKK)</label>
            <input
              v-model="newIndikatorForm.ikkKode"
              type="text"
              placeholder="Contoh: IKK 7099.1.2"
              class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Indikator Kinerja Kegiatan</label>
            <textarea
              v-model="newIndikatorForm.ikkNama"
              rows="3"
              placeholder="Masukkan uraian indikator kinerja kegiatan..."
              class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="closeAddIndikatorModal"
            class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveNewIndikator"
            class="px-4 py-2 text-xs font-semibold rounded-lg bg-[#0B2A6F] text-white hover:bg-[#081F52]"
          >
            Simpan Indikator
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
