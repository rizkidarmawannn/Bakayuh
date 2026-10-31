<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

const { success: toastSuccess, error: toastError } = useToast()

const selectedYear = ref(2026)
const yearOptions = [2026, 2025, 2024, 2023]

interface SakipComponent {
  id: string
  name: string
  bobot: number
}

interface SakipRowValues {
  mandiri: string
  pembina: string
  itjen: string
  panel: string
}

const defaultCardTitle = 'KANWIL KEMENTERIAN HUKUM KALIMANTAN SELATAN'
const cardTitle = ref(defaultCardTitle)

const defaultComponents: SakipComponent[] = [
  { id: 'perencanaan', name: 'PERENCANAAN', bobot: 30 },
  { id: 'pengukuran', name: 'PENGUKURAN', bobot: 30 },
  { id: 'pelaporan', name: 'PELAPORAN', bobot: 15 },
  { id: 'evaluasi', name: 'EVALUASI AKUNTABILITAS KINERJA INTERNAL', bobot: 25 },
]

const components = ref<SakipComponent[]>([...defaultComponents])

const defaultData2026: Record<string, SakipRowValues> = {
  perencanaan: { mandiri: '30', pembina: '27', itjen: '24,6', panel: '-' },
  pengukuran: { mandiri: '27,6', pembina: '27', itjen: '26,1', panel: '-' },
  pelaporan: { mandiri: '13,5', pembina: '13,5', itjen: '12,3', panel: '-' },
  evaluasi: { mandiri: '19,47', pembina: '19,47', itjen: '19,47', panel: '-' },
}

const sakipDatabase = ref<Record<string, Record<string, SakipRowValues>>>({
  '2026': { ...defaultData2026 },
})

const lastSavedNote = ref('Terakhir diisi oleh Administrator.')

function getYearRecord(): Record<string, SakipRowValues> {
  const yStr = String(selectedYear.value)
  if (!sakipDatabase.value[yStr]) {
    const emptyRec: Record<string, SakipRowValues> = {}
    components.value.forEach((c) => {
      emptyRec[c.id] = { mandiri: '-', pembina: '-', itjen: '-', panel: '-' }
    })
    sakipDatabase.value[yStr] = emptyRec
  }
  return sakipDatabase.value[yStr]
}

const currentYearData = computed(() => getYearRecord())

function getCell(compKey: string): SakipRowValues {
  const data = currentYearData.value
  if (!data[compKey]) {
    data[compKey] = { mandiri: '-', pembina: '-', itjen: '-', panel: '-' }
  }
  return data[compKey] as SakipRowValues
}

function parseVal(v: string | number | undefined | null): number | null {
  if (v === null || v === undefined) return null
  const s = String(v).trim()
  if (s === '' || s === '-') return null
  const num = parseFloat(s.replace(',', '.'))
  return isNaN(num) ? null : num
}

function formatDec(n: number | null): string {
  if (n === null) return '-'
  if (Number.isInteger(n)) return n.toString()
  const rounded = parseFloat(n.toFixed(2))
  if (Number.isInteger(rounded)) return rounded.toString()
  return rounded.toFixed(2).replace('.', ',')
}

const totalMandiri = computed(() => {
  let hasVal = false
  let sum = 0
  for (const c of components.value) {
    const val = parseVal(currentYearData.value[c.id]?.mandiri)
    if (val !== null) {
      hasVal = true
      sum += val
    }
  }
  return hasVal ? formatDec(sum) : '-'
})

const totalPembina = computed(() => {
  let hasVal = false
  let sum = 0
  for (const c of components.value) {
    const val = parseVal(currentYearData.value[c.id]?.pembina)
    if (val !== null) {
      hasVal = true
      sum += val
    }
  }
  return hasVal ? formatDec(sum) : '-'
})

const totalItjen = computed(() => {
  let hasVal = false
  let sum = 0
  for (const c of components.value) {
    const val = parseVal(currentYearData.value[c.id]?.itjen)
    if (val !== null) {
      hasVal = true
      sum += val
    }
  }
  return hasVal ? formatDec(sum) : '-'
})

const totalPanel = computed(() => {
  let hasVal = false
  let sum = 0
  for (const c of components.value) {
    const val = parseVal(currentYearData.value[c.id]?.panel)
    if (val !== null) {
      hasVal = true
      sum += val
    }
  }
  return hasVal ? formatDec(sum) : '-'
})

// Nilai Akhir SAKIP & Sumber Nilai (Gambar 2)
const nilaiAkhirSakip = computed(() => {
  if (totalPanel.value !== '-') return totalPanel.value
  if (totalItjen.value !== '-') return totalItjen.value
  if (totalPembina.value !== '-') return totalPembina.value
  if (totalMandiri.value !== '-') return totalMandiri.value
  return '-'
})

const sumberNilaiAkhir = computed(() => {
  if (totalPanel.value !== '-') return 'total Nilai Panel'
  if (totalItjen.value !== '-') return 'total Nilai Itjen'
  if (totalPembina.value !== '-') return 'total Nilai Pembina'
  if (totalMandiri.value !== '-') return 'total Nilai Mandiri'
  return 'belum ada nilai'
})

// Modal: Atur Judul & Bobot
const isConfigModalOpen = ref(false)
const tempTitle = ref('')
const tempComponents = ref<SakipComponent[]>([])

function openConfigModal() {
  tempTitle.value = cardTitle.value
  tempComponents.value = JSON.parse(JSON.stringify(components.value))
  isConfigModalOpen.value = true
}

function closeConfigModal() {
  isConfigModalOpen.value = false
}

const totalTempBobot = computed(() => {
  return tempComponents.value.reduce((acc, c) => acc + (Number(c.bobot) || 0), 0)
})

function saveConfigModal() {
  cardTitle.value = tempTitle.value.trim() || defaultCardTitle
  components.value = JSON.parse(JSON.stringify(tempComponents.value))

  // Ensure record entries exist for all components
  Object.keys(sakipDatabase.value).forEach((y) => {
    const yRec = sakipDatabase.value[y]
    if (yRec) {
      components.value.forEach((c) => {
        if (!yRec[c.id]) {
          yRec[c.id] = { mandiri: '-', pembina: '-', itjen: '-', panel: '-' }
        }
      })
    }
  })

  saveToStorage()
  isConfigModalOpen.value = false
  toastSuccess('Berhasil', 'Pengaturan judul dan bobot SAKIP berhasil disimpan.')
}

// Save action
function saveNilai() {
  saveToStorage()
  lastSavedNote.value = 'Terakhir diisi oleh Administrator.'
  toastSuccess('Berhasil', 'Nilai SAKIP berhasil disimpan.')
}

// Export CSV
function exportCsv() {
  const headers = ['No', 'Komponen', 'Nilai Mandiri', 'Nilai Pembina', 'Nilai Itjen', 'Nilai Panel']
  const rows = components.value.map((c, idx) => {
    const row = currentYearData.value[c.id] || { mandiri: '-', pembina: '-', itjen: '-', panel: '-' }
    return [
      idx + 1,
      `"${c.name}"`,
      `"${row.mandiri || '-'}"`,
      `"${row.pembina || '-'}"`,
      `"${row.itjen || '-'}"`,
      `"${row.panel || '-'}"`,
    ]
  })
  rows.push([
    '',
    '"Total"',
    `"${totalMandiri.value}"`,
    `"${totalPembina.value}"`,
    `"${totalItjen.value}"`,
    `"${totalPanel.value}"`,
  ])

  const csvContent = [headers.join(','), ...rows.map((r) => r.join(','))].join('\r\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `SAKIP_${selectedYear.value}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  toastSuccess('Berhasil', 'Data SAKIP berhasil diekspor ke CSV.')
}

// Local Storage helpers
const STORAGE_KEY = 'bakayuh_sakip_store_v1'

function saveToStorage() {
  try {
    const payload = {
      cardTitle: cardTitle.value,
      components: components.value,
      database: sakipDatabase.value,
      lastSavedNote: lastSavedNote.value,
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload))
  } catch (e) {
    console.error('Failed to save SAKIP to localStorage:', e)
  }
}

function loadFromStorage() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (parsed.cardTitle) cardTitle.value = parsed.cardTitle
      if (parsed.components && Array.isArray(parsed.components) && parsed.components.length > 0) {
        components.value = parsed.components
      }
      if (parsed.database) {
        sakipDatabase.value = { ...sakipDatabase.value, ...parsed.database }
      }
      if (parsed.lastSavedNote) lastSavedNote.value = parsed.lastSavedNote
    }
  } catch (e) {
    console.error('Failed to load SAKIP from localStorage:', e)
  }
}

onMounted(() => {
  loadFromStorage()
})

// Keterangan Bobot Nilai (Image 2)
const keteranganBobot = [
  {
    predikat: 'AA',
    bobot: 100,
    keterangan: 'Jika kualitas seluruh kriteria telah terpenuhi (100%) dan terdapat upaya inovatif serta layak menjadi percontohan secara nasional.',
  },
  {
    predikat: 'A',
    bobot: 90,
    keterangan: 'Jika kualitas seluruh kriteria telah terpenuhi (100%) dan terdapat beberapa upaya yang bisa dihargai dari pemenuhan kriteria tersebut.',
  },
  {
    predikat: 'BB',
    bobot: 80,
    keterangan: 'Jika kualitas seluruh kriteria telah terpenuhi (100%) sesuai dengan mandat kebijakan.',
  },
  {
    predikat: 'B',
    bobot: 70,
    keterangan: 'Jika kualitas sebagian besar kriteria telah terpenuhi (>75%-100%).',
  },
  {
    predikat: 'CC',
    bobot: 60,
    keterangan: 'Jika kualitas sebagian besar kriteria telah terpenuhi (>50%-75%).',
  },
  {
    predikat: 'C',
    bobot: 50,
    keterangan: 'Jika kualitas sebagian kecil kriteria telah terpenuhi (>25%-50%).',
  },
  {
    predikat: 'D',
    bobot: 30,
    keterangan: 'Jika kriteria penilaian akuntabilitas kinerja telah mulai dipenuhi (>0%-25%).',
  },
  {
    predikat: 'E',
    bobot: 0,
    keterangan: 'Jika sama sekali tidak ada upaya dalam pemenuhan kriteria penilaian akuntabilitas kinerja.',
  },
]
</script>

<template>
  <div class="space-y-6 pb-16 animate-fade-in">
    <!-- Top Header: Title, Subtitle, and Controls (Matches Image 1) -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          SAKIP
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Khusus admin. Total dan Nilai Akhir (total Nilai Itjen) dihitung otomatis. Halaman publik menampilkan data ini.
        </p>
      </div>

      <!-- Controls: Year Selector, Atur Judul & Bobot, Ekspor CSV -->
      <div class="flex items-center gap-2.5 flex-wrap self-start lg:self-auto">
        <!-- Year Selector -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedYear"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <!-- Button: Atur Judul & Bobot -->
        <button
          type="button"
          @click="openConfigModal"
          class="bg-white border border-slate-200 rounded-lg px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 hover:bg-slate-50 transition-colors cursor-pointer"
        >
          Atur Judul &amp; Bobot
        </button>

        <!-- Button: Ekspor CSV -->
        <button
          type="button"
          @click="exportCsv"
          class="bg-white border border-slate-200 rounded-lg px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 hover:bg-slate-50 transition-colors cursor-pointer"
        >
          Ekspor CSV
        </button>
      </div>
    </div>

    <!-- Main Card 1: SAKIP Values (Matches Image 1) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-7 shadow-xs">
      <!-- Card Title -->
      <h2 class="text-sm sm:text-base font-extrabold text-[#091F4A] tracking-wider uppercase mb-5 sm:mb-6">
        {{ cardTitle }} &middot; {{ selectedYear }}
      </h2>

      <!-- Table Container -->
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-center border-collapse">
            <thead>
              <tr class="border-b border-slate-200 bg-white">
                <th class="py-3.5 px-3 text-center border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800 w-14">
                  No
                </th>
                <th class="py-3.5 px-4 text-left border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800 min-w-[200px]">
                  Komponen
                </th>
                <th class="py-3.5 px-3 text-center border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800 w-32 sm:w-40">
                  Nilai Mandiri
                </th>
                <th class="py-3.5 px-3 text-center border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800 w-32 sm:w-40">
                  Nilai Pembina
                </th>
                <th class="py-3.5 px-3 text-center border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800 w-32 sm:w-40">
                  Nilai Itjen
                </th>
                <th class="py-3.5 px-3 text-center font-bold text-xs sm:text-sm text-slate-800 w-32 sm:w-40">
                  Nilai Panel
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, idx) in components"
                :key="item.id"
                class="border-b border-slate-200 hover:bg-slate-50/40 transition-colors"
              >
                <!-- Column No -->
                <td class="py-3.5 px-3 text-center border-r border-slate-200 text-slate-700 text-xs sm:text-sm font-medium">
                  {{ idx + 1 }}
                </td>

                <!-- Column Komponen -->
                <td class="py-3.5 px-4 text-left border-r border-slate-200 font-bold text-xs sm:text-sm text-[#1D4ED8] tracking-wide">
                  {{ item.name }}
                </td>

                <!-- Column Nilai Mandiri -->
                <td class="py-2.5 px-3 border-r border-slate-200">
                  <input
                    type="text"
                    v-model="getCell(item.id).mandiri"
                    class="w-24 sm:w-28 text-center py-1.5 px-2 text-xs sm:text-sm font-medium border border-slate-200 rounded-lg text-slate-800 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-[#1D4ED8] transition-all"
                  />
                </td>

                <!-- Column Nilai Pembina -->
                <td class="py-2.5 px-3 border-r border-slate-200">
                  <input
                    type="text"
                    v-model="getCell(item.id).pembina"
                    class="w-24 sm:w-28 text-center py-1.5 px-2 text-xs sm:text-sm font-medium border border-slate-200 rounded-lg text-slate-800 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-[#1D4ED8] transition-all"
                  />
                </td>

                <!-- Column Nilai Itjen -->
                <td class="py-2.5 px-3 border-r border-slate-200">
                  <input
                    type="text"
                    v-model="getCell(item.id).itjen"
                    class="w-24 sm:w-28 text-center py-1.5 px-2 text-xs sm:text-sm font-medium border border-slate-200 rounded-lg text-slate-800 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-[#1D4ED8] transition-all"
                  />
                </td>

                <!-- Column Nilai Panel -->
                <td class="py-2.5 px-3">
                  <input
                    type="text"
                    v-model="getCell(item.id).panel"
                    class="w-24 sm:w-28 text-center py-1.5 px-2 text-xs sm:text-sm font-medium border border-slate-200 rounded-lg text-slate-800 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-[#1D4ED8] transition-all"
                  />
                </td>
              </tr>

              <!-- Total Row -->
              <tr class="bg-white">
                <td colspan="2" class="py-4 px-6 text-right border-r border-slate-200 font-bold text-xs sm:text-sm text-slate-800">
                  Total
                </td>
                <td class="py-4 px-3 text-center border-r border-slate-200 font-extrabold text-sm sm:text-base text-emerald-600">
                  {{ totalMandiri }}
                </td>
                <td class="py-4 px-3 text-center border-r border-slate-200 font-extrabold text-sm sm:text-base text-emerald-600">
                  {{ totalPembina }}
                </td>
                <td class="py-4 px-3 text-center border-r border-slate-200 font-extrabold text-sm sm:text-base text-emerald-600">
                  {{ totalItjen }}
                </td>
                <td class="py-4 px-3 text-center font-extrabold text-sm sm:text-base text-emerald-600">
                  {{ totalPanel }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bottom Section: Action Row (Left) & Nilai Akhir Widget (Right, under Nilai Panel) -->
      <div class="mt-6 flex flex-col md:flex-row md:items-start justify-between gap-6">
        <!-- Left Side: Simpan nilai & Helper Note -->
        <div class="space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <button
              type="button"
              @click="saveNilai"
              class="bg-[#1853db] hover:bg-blue-700 text-white font-medium text-xs sm:text-sm px-4 py-2 rounded-lg transition-colors shadow-2xs cursor-pointer self-start sm:self-auto active:scale-95"
            >
              Simpan nilai
            </button>
            <span class="text-xs text-slate-500">
              Nilai 0-100, kosongkan jika belum ada. Tulis desimal dengan koma (mis. 24,6).
            </span>
          </div>

          <!-- Note: Last filled -->
          <p class="text-xs text-slate-400">
            {{ lastSavedNote }}
          </p>
        </div>

        <!-- Right Side: Nilai Akhir SAKIP Widget (Format: Nilai Akhir SAKIP 2026 : [nilai], digeser 25px ke kiri) -->
        <div class="text-left md:text-right flex-shrink-0 pt-1 -translate-x-[25px]">
          <div class="flex items-baseline md:justify-end gap-2">
            <span class="text-sm sm:text-base font-bold text-slate-800">
              Nilai Akhir SAKIP {{ selectedYear }} :
            </span>
            <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
              {{ nilaiAkhirSakip }}
            </span>
          </div>
          <div class="text-xs text-slate-500 mt-0.5 font-normal">
            {{ sumberNilaiAkhir }}
          </div>
        </div>
      </div>
    </div>

    <!-- Main Card 2: Keterangan bobot nilai (Matches Image 2) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-7 shadow-xs">
      <h3 class="text-sm sm:text-base font-extrabold text-[#091F4A] tracking-wider mb-4">
        Keterangan bobot nilai
      </h3>
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <tbody>
              <tr
                v-for="(row, idx) in keteranganBobot"
                :key="idx"
                class="border-b last:border-b-0 border-slate-200 hover:bg-slate-50/50 transition-colors"
              >
                <td class="py-3.5 px-4 sm:px-5 font-semibold text-slate-800 w-48 sm:w-60 border-r border-slate-200 align-middle whitespace-nowrap">
                  {{ row.predikat }} (Bobot nilai {{ row.bobot }})
                </td>
                <td class="py-3.5 px-4 sm:px-5 text-slate-600 align-middle leading-relaxed">
                  {{ row.keterangan }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal: Atur Judul & Bobot -->
    <div
      v-if="isConfigModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-scale-in">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-base font-bold text-slate-900">
            Atur Judul &amp; Bobot Komponen
          </h3>
          <button
            type="button"
            @click="closeConfigModal"
            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <!-- Judul Instansi Input -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              Judul Satuan Kerja / Instansi
            </label>
            <input
              type="text"
              v-model="tempTitle"
              class="w-full px-3.5 py-2 text-xs sm:text-sm font-semibold border border-slate-200 rounded-lg text-slate-800 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-[#1D4ED8]"
              placeholder="Masukkan judul..."
            />
          </div>

          <!-- Komponen & Bobot List -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Komponen Penilaian &amp; Bobot
              </label>
              <span
                class="text-xs font-bold px-2 py-0.5 rounded-full"
                :class="totalTempBobot === 100 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
              >
                Total: {{ totalTempBobot }}%
              </span>
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
              <div
                v-for="(comp, cIdx) in tempComponents"
                :key="comp.id"
                class="flex items-center gap-2.5 p-2 rounded-lg bg-slate-50 border border-slate-200"
              >
                <span class="text-xs font-bold text-slate-500 w-5 text-center">
                  {{ cIdx + 1 }}.
                </span>
                <input
                  type="text"
                  v-model="comp.name"
                  class="flex-1 px-2.5 py-1.5 text-xs font-semibold bg-white border border-slate-200 rounded-md text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  placeholder="Nama komponen"
                />
                <div class="flex items-center gap-1 w-24">
                  <input
                    type="number"
                    v-model.number="comp.bobot"
                    min="0"
                    max="100"
                    class="w-16 px-2 py-1.5 text-xs font-semibold text-center bg-white border border-slate-200 rounded-md text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500"
                  />
                  <span class="text-xs font-bold text-slate-500">%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="closeConfigModal"
            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveConfigModal"
            class="px-4 py-2 text-xs font-bold text-white bg-[#1853db] hover:bg-blue-700 rounded-lg transition-colors cursor-pointer shadow-xs"
          >
            Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
