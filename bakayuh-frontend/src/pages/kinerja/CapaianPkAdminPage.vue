<script setup lang="ts">
import { ref, computed } from 'vue'

const selectedPkYear = ref(2026)
const pkYearOptions = [2026, 2025, 2024]
const selectedPkUnit = ref('ALL')
const pkUnitFilterOptions = [
  { id: 'ALL', label: 'Semua unit' },
  { id: 'setjen', label: 'Setjen' },
  { id: 'ahu', label: 'AHU' },
  { id: 'ki', label: 'KI' },
  { id: 'pp', label: 'PP' },
  { id: 'bphn', label: 'BPHN' },
  { id: 'bsk', label: 'BSK' },
]

interface PkIndicatorRow {
  sasaran: string
  indikator: string
  target: string
  jan: string; feb: string; mar: string; rekapTw1: string
  apr: string; mei: string; jun: string; rekapTw2: string
  sdSemester1: string
  jul: string; agu: string
}

interface PkGroup {
  unitId: string
  unitName: string
  rows: PkIndicatorRow[]
}

const pkGroups = ref<PkGroup[]>([
  {
    unitId: 'setjen',
    unitName: 'Setjen',
    rows: [
      {
        sasaran: 'SK 7099.1 - Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        indikator: 'IKK 7099.1.1 - Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK',
        target: '100 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7099.2 - Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        indikator: 'IKK 7099.2.1 - Indeks kepuasan terhadap layanan dukungan manajemen',
        target: '3,8 Nilai',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
  {
    unitId: 'ahu',
    unitName: 'AHU',
    rows: [
      {
        sasaran: 'SK 7110.1 - Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        indikator: 'IKK 7110.1.1 - Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        target: '89 Nilai',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7110.2 - Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
        indikator: 'IKK 7110.2.1 - Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
        target: '100 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
  {
    unitId: 'ki',
    unitName: 'KI',
    rows: [
      {
        sasaran: 'SK 7121.1 - Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
        indikator: 'IKK 7121.1.1 - Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan',
        target: '95 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7121.2 - Terlaksananya Pelindungan Kekayaan Intelektual Melalui Penegakan Hukum Kekayaan Intelektual di Wilayah',
        indikator: 'IKK 7121.2.1 - Persentase penyelesaian aduan pelanggaran kekayaan intelektual di wilayah',
        target: '100 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
  {
    unitId: 'pp',
    unitName: 'PP',
    rows: [
      {
        sasaran: 'SK 7130.1 - Meningkatnya Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
        indikator: 'IKK 7130.1.1 - Persentase Harmonisasi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah di Wilayah',
        target: '99 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
  {
    unitId: 'bphn',
    unitName: 'BPHN',
    rows: [
      {
        sasaran: 'SK 7136.1 - Meningkatnya Kualitas Pembinaan Hukum Nasional di Wilayah',
        indikator: 'IKK 7136.1.1 - Persentase Desa/Kelurahan Sadar Hukum yang Dibina di Wilayah',
        target: '85 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
  {
    unitId: 'bsk',
    unitName: 'BSK',
    rows: [
      {
        sasaran: 'SK 7143.1 - Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        indikator: 'IKK 7143.1.1 - Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        target: '95,5 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
    ],
  },
])

const filteredPkGroups = computed(() => {
  if (selectedPkUnit.value === 'ALL') return pkGroups.value
  return pkGroups.value.filter((g) => g.unitId === selectedPkUnit.value)
})
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header & Top Dropdown Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Capaian Kinerja atas Perjanjian Kinerja
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Capaian per bulan, rekap triwulan, dan s.d. semester seluruh satuan kerja
        </p>
      </div>

      <!-- Top-Right Dropdowns (Year & Unit) -->
      <div class="flex items-center gap-2.5 self-start sm:self-auto flex-nowrap">
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedPkYear"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="y in pkYearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>

        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedPkUnit"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option v-for="opt in pkUnitFilterOptions" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Multi-column Matrix Table Surface -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
      <div class="table-container overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3 px-4 min-w-[240px] border-r border-white/10">Sasaran Kinerja</th>
              <th class="py-3 px-4 min-w-[280px] border-r border-white/10">Indikator Kinerja Kegiatan</th>
              <th class="py-3 px-3 text-center min-w-[70px] border-r border-white/10">Target</th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Jan</th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Feb</th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Mar</th>
              <!-- Rekap TW I (Orange Header) -->
              <th class="py-3 px-2.5 text-center min-w-[85px] bg-[#EA580C] text-white font-black border-x border-[#C2410C]">
                Rekap TW I
              </th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Apr</th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Mei</th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Jun</th>
              <!-- Rekap TW II (Orange Header) -->
              <th class="py-3 px-2.5 text-center min-w-[85px] bg-[#EA580C] text-white font-black border-x border-[#C2410C]">
                Rekap TW II
              </th>
              <!-- s.d. Semester I (Yellow Header) -->
              <th class="py-3 px-2.5 text-center min-w-[95px] bg-[#FACC15] text-slate-950 font-black border-x border-[#EAB308]">
                s.d. Semester I
              </th>
              <th class="py-3 px-2 text-center min-w-[45px] border-r border-white/10">Jul</th>
              <th class="py-3 px-2 text-center min-w-[45px]">Agu</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-for="grp in filteredPkGroups" :key="grp.unitId">
              <!-- Dark Navy Section Divider Row -->
              <tr class="bg-[#0B2A6F] text-white font-bold tracking-wide">
                <td colspan="14" class="py-2.5 px-4 text-xs font-black">
                  {{ grp.unitName }}
                </td>
              </tr>

              <!-- Indicators under this group -->
              <tr
                v-for="(row, rIdx) in grp.rows"
                :key="rIdx"
                class="hover:bg-slate-50/80 transition-colors"
              >
                <td class="py-3 px-4 font-semibold text-slate-800 text-xs border-r border-slate-100 align-top leading-snug">
                  {{ row.sasaran }}
                </td>
                <td class="py-3 px-4 text-slate-600 text-xs border-r border-slate-100 align-top leading-snug">
                  {{ row.indikator }}
                </td>
                <td class="py-3 px-3 text-center font-bold text-slate-800 text-xs border-r border-slate-100 align-top whitespace-nowrap">
                  {{ row.target }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.jan }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.feb }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.mar }}
                </td>
                <!-- Peach tinted column for Rekap TW I -->
                <td class="py-3 px-2 text-center font-mono font-bold text-orange-950 text-xs align-top bg-orange-50/70 border-x border-orange-100/80">
                  {{ row.rekapTw1 }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.apr }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.mei }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.jun }}
                </td>
                <!-- Peach tinted column for Rekap TW II -->
                <td class="py-3 px-2 text-center font-mono font-bold text-orange-950 text-xs align-top bg-orange-50/70 border-x border-orange-100/80">
                  {{ row.rekapTw2 }}
                </td>
                <!-- Amber tinted column for s.d. Semester I -->
                <td class="py-3 px-2 text-center font-mono font-black text-amber-950 text-xs align-top bg-amber-50/80 border-x border-amber-100">
                  {{ row.sdSemester1 }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top border-r border-slate-100">
                  {{ row.jul }}
                </td>
                <td class="py-3 px-2 text-center font-mono text-slate-600 text-xs align-top">
                  {{ row.agu }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Footnote -->
    <p class="text-xs text-slate-400">
      Rekap triwulan dan s.d. semester dihitung otomatis atau ditetapkan oleh unit. Data diperbarui otomatis.
    </p>
  </div>
</template>
