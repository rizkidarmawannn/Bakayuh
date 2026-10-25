<script setup lang="ts">
import { ref, computed } from 'vue'

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

const sasaranGroups = ref([
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
])

const filteredSasaranGroups = computed(() => {
  if (selectedUnit.value === 'ALL') return sasaranGroups.value
  return sasaranGroups.value.filter((g) => g.unitKode === selectedUnit.value)
})
</script>

<template>
  <div class="space-y-6 pb-12 animate-fade-in">
    <!-- Header with Filters & Action Buttons (Image 4) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Sasaran Kinerja &amp; Indikator
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Daftar sasaran strategis, indikator kinerja kegiatan, dan rencana aksi per unit kerja
        </p>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
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

        <button
          type="button"
          class="btn-secondary text-xs py-2 px-3.5 cursor-pointer shadow-xs"
        >
          Ekspor Rekap (CSV)
        </button>

        <button
          type="button"
          class="btn-primary text-xs py-2 px-4 cursor-pointer inline-flex items-center gap-1.5 shadow-xs"
        >
          <span>+</span>
          <span>Sasaran</span>
        </button>
      </div>
    </div>

    <!-- Segmented Flat Rows grouped by Unit (Image 4, Single-level, NO nested cards) -->
    <div class="space-y-6">
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
                <button type="button" class="btn-secondary text-[11px] py-1 px-2.5">
                  + Indikator
                </button>
                <button type="button" class="btn-secondary text-[11px] py-1 px-2">
                  Edit
                </button>
                <button type="button" class="btn-secondary text-[11px] py-1 px-2 text-rose-600 hover:text-rose-700">
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
                  <button type="button" class="btn-secondary text-[10px] py-0.5 px-2">
                    Edit
                  </button>
                  <button type="button" class="btn-secondary text-[10px] py-0.5 px-2 text-rose-600">
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
