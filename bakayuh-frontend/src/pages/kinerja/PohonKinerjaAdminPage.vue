<script setup lang="ts">
import { ref, computed } from 'vue'

const selectedYear = ref(2026)
const selectedUnitFilter = ref<string>('ALL')
const viewMode = ref<'tree' | 'table'>('tree')
const searchQuery = ref('')

interface SasaranProgramItem {
  spKode: string
  spName: string
  ispKode: string
  ispName: string
}

interface SasaranKanwilItem {
  skKode: string
  skName: string
  ikkList: {
    ikkKode: string
    ikkName: string
  }[]
}

interface UnitPohonKinerja {
  id: string
  kode: string
  name: string
  pusatName: string
  kanwilName: string
  ssKode: string
  ssName: string
  ikssKode: string
  ikssName: string
  themeColor: {
    badgeBg: string
    badgeText: string
    accentBorder: string
    lightBg: string
  }
  sasaranProgram: SasaranProgramItem[]
  sasaranKanwil: SasaranKanwilItem[]
}

// Complete authentic dataset of Pohon Kinerja Kementerian Hukum RI down to Kanwil Kalsel
const pohonKinerjaData = ref<UnitPohonKinerja[]>([
  // 1. Ditjen AHU
  {
    id: 'ahu',
    kode: 'AHU',
    name: 'Administrasi Hukum Umum',
    pusatName: 'Ditjen Administrasi Hukum Umum',
    kanwilName: 'Administrasi Hukum Umum',
    ssKode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssKode: 'IKSS 1.1',
    ikssName: 'Indeks penegakan dan pelayanan Hukum',
    themeColor: {
      badgeBg: 'bg-indigo-50',
      badgeText: 'text-indigo-700',
      accentBorder: 'border-indigo-500',
      lightBg: 'bg-indigo-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-1',
        spName: 'Terwujudnya Penegakan Hukum AHU yang Profesional',
        ispKode: 'ISP-1',
        ispName: 'Indeks Penegakan Hukum AHU',
      },
      {
        spKode: 'SP-2',
        spName: 'Meningkatnya Kepuasan Terhadap Layanan AHU',
        ispKode: 'ISP-2',
        ispName: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan AHU',
      },
      {
        spKode: 'SP-3',
        spName: 'Meningkatnya Kemudahan Berusaha pada Layanan Badan Usaha dan Keperdataan',
        ispKode: 'ISP-3',
        ispName: 'Indeks kemudahan berusaha pada layanan Badan Usaha dan Keperdataan',
      },
      {
        spKode: 'SP-4',
        spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
        ispKode: 'ISP-4',
        ispName: 'Indeks Reformasi Birokrasi Kementerian Hukum',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7110.1',
        skName: 'Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7110.1.1',
            ikkName: 'Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
          },
        ],
      },
      {
        skKode: 'SK 7110.2',
        skName: 'Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7110.2.1',
            ikkName: 'Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
          },
        ],
      },
    ],
  },

  // 2. Ditjen KI
  {
    id: 'ki',
    kode: 'KI',
    name: 'Kekayaan Intelektual',
    pusatName: 'Ditjen Kekayaan Intelektual',
    kanwilName: 'Kekayaan Intelektual',
    ssKode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssKode: 'IKSS 1.1',
    ikssName: 'Indeks penegakan dan pelayanan Hukum',
    themeColor: {
      badgeBg: 'bg-emerald-50',
      badgeText: 'text-emerald-700',
      accentBorder: 'border-emerald-500',
      lightBg: 'bg-emerald-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-5',
        spName: 'Terwujudnya Penegakan Hukum Pelindungan Kekayaan Intelektual yang profesional',
        ispKode: 'ISP-5',
        ispName: 'Indeks Penegakan Hukum Pelindungan Kekayaan Intelektual',
      },
      {
        spKode: 'SP-6',
        spName: 'Meningkatnya Kepuasan Pelindungan dan pemanfaatan KI',
        ispKode: 'ISP-6',
        ispName: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pelindungan dan Pemanfaatan KI',
      },
      {
        spKode: 'SP-7',
        spName: 'Meningkatnya Kesadaran Masyarakat Indonesia terhadap Kekayaan Intelektual',
        ispKode: 'ISP-7',
        ispName: 'Indeks Kesadaran Masyarakat terhadap Kekayaan Intelektual',
      },
      {
        spKode: 'SP-8',
        spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
        ispKode: 'ISP-8',
        ispName: 'Indeks Reformasi Birokrasi Kementerian Hukum',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7121.1',
        skName: 'Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7121.1.1',
            ikkName: 'Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan',
          },
        ],
      },
      {
        skKode: 'SK 7121.2',
        skName: 'Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan',
        ikkList: [
          {
            ikkKode: 'IKK 7121.2.1',
            ikkName: 'Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani',
          },
        ],
      },
      {
        skKode: 'SK 3',
        skName: 'Meningkatnya Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 3.1',
            ikkName: 'Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
          },
        ],
      },
    ],
  },

  // 3. Ditjen PP
  {
    id: 'pp',
    kode: 'PP',
    name: 'Peraturan Perundang-Undangan',
    pusatName: 'Ditjen Peraturan Perundang-Undangan',
    kanwilName: 'Peraturan Perundang-Undangan',
    ssKode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssKode: 'IKSS 1.1',
    ikssName: 'Indeks penegakan dan pelayanan Hukum',
    themeColor: {
      badgeBg: 'bg-sky-50',
      badgeText: 'text-sky-700',
      accentBorder: 'border-sky-500',
      lightBg: 'bg-sky-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-9',
        spName: 'Indeks Kualitas Peraturan Perundang-undangan',
        ispKode: 'ISP-9',
        ispName: 'Indeks kualitas peraturan perundang-undangan',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7129.1',
        skName: 'Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7129.1.1',
            ikkName: 'Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
          },
          {
            ikkKode: 'IKK 7129.1.2',
            ikkName: 'Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
          },
        ],
      },
    ],
  },

  // 4. BPHN
  {
    id: 'bphn',
    kode: 'BPHN',
    name: 'Badan Pembinaan Hukum Nasional',
    pusatName: 'Badan Pembinaan Hukum Nasional',
    kanwilName: 'Badan Pembinaan Hukum Nasional',
    ssKode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssKode: 'IKSS 1.2',
    ikssName: 'Indeks Budaya Hukum (IPH)',
    themeColor: {
      badgeBg: 'bg-purple-50',
      badgeText: 'text-purple-700',
      accentBorder: 'border-purple-500',
      lightBg: 'bg-purple-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-10',
        spName: 'Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum',
        ispKode: 'ISP-10',
        ispName: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pembinaan Hukum Nasional',
      },
      {
        spKode: 'SP-11',
        spName: 'Meningkatnya Kepatuhan Hukum Masyarakat Indonesia',
        ispKode: 'ISP-11',
        ispName: 'Tingkat Kepatuhan Hukum Masyarakat',
      },
      {
        spKode: 'SP-12',
        spName: 'Meningkatnya Kepatuhan Hukum Lembaga Hukum',
        ispKode: 'ISP-12',
        ispName: 'Tingkat Kepatuhan Hukum Lembaga Hukum',
      },
      {
        spKode: 'SP-13',
        spName: 'Terwujudnya Manfaat dan Dampak Peraturan Perundang-undangan terhadap Pembangunan Hukum Nasional',
        ispKode: 'ISP-13',
        ispName: 'Persentase Peraturan Perundang-undangan yang Memberikan Manfaat dan Dampak terhadap Pembangunan Hukum Nasional',
      },
      {
        spKode: 'SP-14',
        spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
        ispKode: 'ISP-14',
        ispName: 'Indeks Reformasi Birokrasi Kementerian Hukum',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7133.1',
        skName: 'Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda',
        ikkList: [
          {
            ikkKode: 'IKK 7133.1.1',
            ikkName: 'Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda di Wilayah Kerja Kanwil',
          },
        ],
      },
      {
        skKode: 'SK 7136.1',
        skName: 'Meningkatnya Layanan Bantuan Hukum di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7136.1.1',
            ikkName: 'Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi',
          },
        ],
      },
      {
        skKode: 'SK 7136.2',
        skName: 'Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas',
        ikkList: [
          {
            ikkKode: 'IKK 7136.2.1',
            ikkName: 'Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah',
          },
        ],
      },
      {
        skKode: 'SK 7136.3',
        skName: 'Meningkatnya Pos Bantuan Hukum di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7136.3.1',
            ikkName: 'Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa/kelurahan',
          },
        ],
      },
    ],
  },

  // 5. BSK
  {
    id: 'bsk',
    kode: 'BSK',
    name: 'Badan Strategi Kebijakan',
    pusatName: 'Badan Strategi Kebijakan Hukum',
    kanwilName: 'Badan Strategi Kebijakan',
    ssKode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssKode: 'IKSS 1.3',
    ikssName: 'Indeks Menteri Hukum (IPH)',
    themeColor: {
      badgeBg: 'bg-teal-50',
      badgeText: 'text-teal-700',
      accentBorder: 'border-teal-500',
      lightBg: 'bg-teal-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-15',
        spName: 'Meningkatnya Kualitas Kebijakan Kementerian Hukum',
        ispKode: 'ISP-15',
        ispName: 'Indeks Kualitas Kebijakan Kementerian Hukum',
      },
      {
        spKode: 'SP-16',
        spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
        ispKode: 'ISP-16',
        ispName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7143.1',
        skName: 'Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        ikkList: [
          {
            ikkKode: 'IKK 7143.1.1',
            ikkName: 'Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah',
          },
        ],
      },
    ],
  },

  // 6. Setjen / TU & Umum
  {
    id: 'setjen',
    kode: 'Setjen',
    name: 'Bagian Tata Usaha dan Umum',
    pusatName: 'Sekretariat Jenderal',
    kanwilName: 'Bagian Tata Usaha dan Umum',
    ssKode: 'SS2',
    ssName: 'Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi',
    ikssKode: 'IKSS 2.1',
    ikssName: 'Indeks Reformasi Birokrasi Kementerian Hukum',
    themeColor: {
      badgeBg: 'bg-blue-50',
      badgeText: 'text-blue-700',
      accentBorder: 'border-blue-500',
      lightBg: 'bg-blue-50/40',
    },
    sasaranProgram: [
      {
        spKode: 'SP-17',
        spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
        ispKode: 'ISP-17',
        ispName: 'Indeks Reformasi Birokrasi General Kementerian Hukum',
      },
    ],
    sasaranKanwil: [
      {
        skKode: 'SK 7099.1',
        skName: 'Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        ikkList: [
          {
            ikkKode: 'IKK 7099.1.1',
            ikkName: 'Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK',
          },
        ],
      },
      {
        skKode: 'SK 7099.2',
        skName: 'Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        ikkList: [
          {
            ikkKode: 'IKK 7099.2.1',
            ikkName: 'Indeks kepuasan terhadap layanan dukungan manajemen',
          },
        ],
      },
    ],
  },
])

// Filter options
const unitFilterList = [
  { id: 'ALL', label: 'Semua Unit (6)' },
  { id: 'ahu', label: 'AHU' },
  { id: 'ki', label: 'KI' },
  { id: 'pp', label: 'PP' },
  { id: 'bphn', label: 'BPHN' },
  { id: 'bsk', label: 'BSK' },
  { id: 'setjen', label: 'Setjen / TU' },
]

// Filtered units
const filteredUnits = computed(() => {
  let list = pohonKinerjaData.value

  if (selectedUnitFilter.value !== 'ALL') {
    list = list.filter((u) => u.id === selectedUnitFilter.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter((u) => {
      const matchUnit =
        u.name.toLowerCase().includes(q) ||
        u.pusatName.toLowerCase().includes(q) ||
        u.kode.toLowerCase().includes(q)
      const matchSP = u.sasaranProgram.some(
        (sp) =>
          sp.spKode.toLowerCase().includes(q) ||
          sp.spName.toLowerCase().includes(q) ||
          sp.ispKode.toLowerCase().includes(q) ||
          sp.ispName.toLowerCase().includes(q)
      )
      const matchSK = u.sasaranKanwil.some(
        (sk) =>
          sk.skKode.toLowerCase().includes(q) ||
          sk.skName.toLowerCase().includes(q) ||
          sk.ikkList.some(
            (ikk) =>
              ikk.ikkKode.toLowerCase().includes(q) ||
              ikk.ikkName.toLowerCase().includes(q)
          )
      )
      return matchUnit || matchSP || matchSK
    })
  }

  return list
})

// Counts
const totalSP = computed(() =>
  pohonKinerjaData.value.reduce((acc, u) => acc + u.sasaranProgram.length, 0)
)
const totalSK = computed(() =>
  pohonKinerjaData.value.reduce((acc, u) => acc + u.sasaranKanwil.length, 0)
)
const totalIKK = computed(() =>
  pohonKinerjaData.value.reduce(
    (acc, u) =>
      acc + u.sasaranKanwil.reduce((sAcc, sk) => sAcc + sk.ikkList.length, 0),
    0
  )
)
</script>

<template>
  <div class="space-y-6 pb-16 animate-fade-in">
    <!-- Header with Breadcrumb and Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Pohon Kinerja
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Keterkaitan logis hierarki sasaran strategis kementerian, program eselon I pusat, hingga sasaran kinerja kantor wilayah
        </p>
      </div>

      <!-- Controls: Year Selector & View Mode Switcher -->
      <div class="flex items-center gap-2.5 self-start sm:self-auto flex-wrap">
        <!-- View Mode Switcher -->
        <div class="inline-flex rounded-lg border border-slate-200 bg-slate-100 p-0.5 text-xs font-semibold">
          <button
            type="button"
            @click="viewMode = 'tree'"
            class="px-3 py-1.5 rounded-md transition-all cursor-pointer inline-flex items-center gap-1.5"
            :class="viewMode === 'tree' ? 'bg-white text-[#0B2A6F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
            Cascading
          </button>
          <button
            type="button"
            @click="viewMode = 'table'"
            class="px-3 py-1.5 rounded-md transition-all cursor-pointer inline-flex items-center gap-1.5"
            :class="viewMode === 'table' ? 'bg-white text-[#0B2A6F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Matriks Tabel
          </button>
        </div>

        <!-- Year Selector with 3px Left Arrow -->
        <div class="relative inline-flex items-center flex-shrink-0">
          <select
            v-model="selectedYear"
            class="appearance-none bg-white border border-slate-200 rounded-lg pl-3.5 pr-8 py-2 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F] cursor-pointer"
          >
            <option :value="2026">Tahun 2026</option>
            <option :value="2025">Tahun 2025</option>
          </select>
          <div class="pointer-events-none absolute right-[10px] top-1/2 -translate-y-1/2 flex items-center text-slate-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Executive Banner: Kementerian Hukum Republik Indonesia (NO nested cards) -->
    <div class="rounded-2xl bg-gradient-to-r from-[#071E4A] via-[#0B2A6F] to-[#123A8C] text-white p-6 shadow-md border-b-4 border-[#F5BA31]">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white/15 text-[#F5BA31] mb-2">
            Pohon Kinerja Instansi Pemerintah
          </span>
          <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
            KEMENTERIAN HUKUM REPUBLIK INDONESIA
          </h2>
          <p class="text-xs text-slate-200 mt-1 max-w-2xl">
            Penyelarasan kinerja vertikal dari sasaran strategis kementerian, program unit eselon I pusat, hingga sasaran kinerja dan indikator kegiatan Kantor Wilayah Kalimantan Selatan.
          </p>
        </div>

        <!-- Quick Summary Stats -->
        <div class="flex items-center gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-white/10">
          <div class="text-center px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs">
            <p class="text-lg font-black text-[#F5BA31] leading-none">2</p>
            <p class="text-[10px] text-slate-200 mt-0.5">Sasaran Strategis</p>
          </div>
          <div class="text-center px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs">
            <p class="text-lg font-black text-white leading-none">{{ totalSP }}</p>
            <p class="text-[10px] text-slate-200 mt-0.5">Sasaran Program</p>
          </div>
          <div class="text-center px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs">
            <p class="text-lg font-black text-white leading-none">{{ totalSK }}</p>
            <p class="text-[10px] text-slate-200 mt-0.5">Sasaran Kanwil</p>
          </div>
          <div class="text-center px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs">
            <p class="text-lg font-black text-white leading-none">{{ totalIKK }}</p>
            <p class="text-[10px] text-slate-200 mt-0.5">IKK Kanwil</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Level 1: Sasaran Strategis (SS1 & SS2) Horizontal Banner Strip -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- SS1 Box -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs border-l-4 border-l-blue-600">
        <div class="flex items-center justify-between gap-2 mb-1.5">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-800">
            SS1 &bull; Sasaran Strategis Kementerian
          </span>
          <span class="text-[10px] font-semibold text-slate-400">Pilar Hukum</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900 leading-snug">
          Terwujudnya Kepastian hukum di seluruh wilayah NKRI
        </h3>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap gap-2 text-[11px]">
          <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
            <strong class="font-bold text-blue-700">IKSS 1.1:</strong> Indeks penegakan dan pelayanan Hukum
          </span>
          <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
            <strong class="font-bold text-purple-700">IKSS 1.2:</strong> Indeks Budaya Hukum (IPH)
          </span>
          <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
            <strong class="font-bold text-teal-700">IKSS 1.3:</strong> Indeks Menteri Hukum (IPH)
          </span>
        </div>
      </div>

      <!-- SS2 Box -->
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs border-l-4 border-l-amber-500">
        <div class="flex items-center justify-between gap-2 mb-1.5">
          <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900">
            SS2 &bull; Sasaran Strategis Kementerian
          </span>
          <span class="text-[10px] font-semibold text-slate-400">Pilar Reformasi</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900 leading-snug">
          Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi
        </h3>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap gap-2 text-[11px]">
          <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
            <strong class="font-bold text-amber-700">IKSS 2.1:</strong> Indeks Reformasi Birokrasi Kementerian Hukum
          </span>
        </div>
      </div>
    </div>

    <!-- Filter Pills & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <!-- Unit Filter Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-thin">
        <button
          v-for="unit in unitFilterList"
          :key="unit.id"
          type="button"
          @click="selectedUnitFilter = unit.id"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors cursor-pointer"
          :class="selectedUnitFilter === unit.id ? 'bg-[#0B2A6F] text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          {{ unit.label }}
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative w-full sm:w-72 flex-shrink-0">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari SP, SK, atau IKK..."
          class="w-full bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B2A6F]/20 focus:border-[#0B2A6F]"
        />
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- VIEW 1: Cascading Tree Flow (Flat, Professional, NO nested cards) -->
    <div v-if="viewMode === 'tree'" class="space-y-6">
      <div
        v-for="unit in filteredUnits"
        :key="unit.id"
        class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden"
      >
        <!-- Unit Header Section -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
          <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider" :class="[unit.themeColor.badgeBg, unit.themeColor.badgeText]">
              {{ unit.kode }}
            </span>
            <div>
              <h3 class="text-sm font-bold text-slate-900">
                {{ unit.pusatName }} &rarr; {{ unit.kanwilName }}
              </h3>
              <p class="text-[11px] text-slate-500">
                Penyelarasan Kinerja Vertikal Unit Kerja
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 text-xs">
            <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-semibold text-[11px]">
              {{ unit.ssKode }}
            </span>
            <span class="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-semibold text-[11px]">
              {{ unit.ikssKode }}
            </span>
          </div>
        </div>

        <!-- Cascading Two-Column Alignment: Pusat (SP/ISP) vs Wilayah (SK/IKK) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
          <!-- Left Column: Tingkat Pusat (Sasaran Program & Indikator Sasaran Program) -->
          <div class="p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
              <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                Tingkat Pusat (Eselon I)
              </span>
              <span class="text-[11px] font-semibold text-slate-400">
                {{ unit.sasaranProgram.length }} Sasaran Program
              </span>
            </div>

            <!-- List of SP & ISP (Flat list, NO nested card) -->
            <div class="space-y-3">
              <div
                v-for="sp in unit.sasaranProgram"
                :key="sp.spKode"
                class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/40 hover:bg-slate-50 transition-colors space-y-2"
              >
                <!-- SP Header -->
                <div class="flex items-start gap-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-[#0B2A6F] text-white flex-shrink-0">
                    {{ sp.spKode }}
                  </span>
                  <p class="text-xs font-bold text-slate-800 leading-snug">
                    {{ sp.spName }}
                  </p>
                </div>

                <!-- ISP Item -->
                <div class="pl-3 border-l-2 border-slate-200 ml-1 text-xs space-y-0.5">
                  <p class="text-[10px] font-extrabold uppercase text-slate-500">
                    Indikator Sasaran Program ({{ sp.ispKode }})
                  </p>
                  <p class="text-[11px] font-medium text-slate-700">
                    {{ sp.ispName }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Tingkat Wilayah (Sasaran Kinerja Kanwil & IKK) -->
          <div class="p-5 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
              <span class="text-xs font-extrabold uppercase tracking-wider text-[#0B2A6F]">
                Tingkat Kantor Wilayah (Kanwil Kalsel)
              </span>
              <span class="text-[11px] font-semibold text-slate-400">
                {{ unit.sasaranKanwil.length }} Sasaran Kegiatan
              </span>
            </div>

            <!-- List of SK & IKK (Flat list, NO nested card) -->
            <div class="space-y-3">
              <div
                v-for="sk in unit.sasaranKanwil"
                :key="sk.skKode"
                class="p-3.5 rounded-xl border border-blue-200/80 bg-blue-50/20 hover:bg-blue-50/40 transition-colors space-y-2.5"
              >
                <!-- SK Header -->
                <div class="flex items-start gap-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-blue-700 text-white flex-shrink-0">
                    {{ sk.skKode }}
                  </span>
                  <p class="text-xs font-bold text-slate-900 leading-snug">
                    {{ sk.skName }}
                  </p>
                </div>

                <!-- IKK List -->
                <div class="space-y-1.5 pl-3 border-l-2 border-blue-300 ml-1">
                  <div
                    v-for="ikk in sk.ikkList"
                    :key="ikk.ikkKode"
                    class="text-xs space-y-0.5"
                  >
                    <p class="text-[10px] font-extrabold text-blue-900">
                      {{ ikk.ikkKode }}
                    </p>
                    <p class="text-[11px] font-medium text-slate-700 leading-relaxed">
                      {{ ikk.ikkName }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- VIEW 2: Structured Alignment Matrix Table -->
    <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
      <div class="table-container overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
              <th class="py-3 px-4 w-28 border-r border-white/10">Unit Kerja</th>
              <th class="py-3 px-4 w-44 border-r border-white/10">Sasaran Strategis (SS &amp; IKSS)</th>
              <th class="py-3 px-4 w-72 border-r border-white/10">Sasaran Program Pusat (SP &amp; ISP)</th>
              <th class="py-3 px-4">Sasaran Kinerja Kanwil (SK &amp; IKK)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-800">
            <template v-for="unit in filteredUnits" :key="unit.id">
              <tr class="hover:bg-slate-50/70 transition-colors align-top">
                <!-- Unit Column -->
                <td class="py-4 px-4 font-bold text-slate-900 border-r border-slate-100">
                  <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-black uppercase mb-1" :class="[unit.themeColor.badgeBg, unit.themeColor.badgeText]">
                    {{ unit.kode }}
                  </span>
                  <p class="text-xs font-bold text-slate-900 leading-snug">
                    {{ unit.name }}
                  </p>
                  <p class="text-[10px] text-slate-400 mt-1">
                    {{ unit.pusatName }}
                  </p>
                </td>

                <!-- SS & IKSS Column -->
                <td class="py-4 px-4 border-r border-slate-100 text-[11px] space-y-2">
                  <div>
                    <span class="font-extrabold text-[#0B2A6F] block text-[10px] uppercase">
                      {{ unit.ssKode }}
                    </span>
                    <p class="text-slate-700 leading-snug">
                      {{ unit.ssName }}
                    </p>
                  </div>
                  <div class="pt-1.5 border-t border-slate-100">
                    <span class="font-extrabold text-blue-700 block text-[10px] uppercase">
                      {{ unit.ikssKode }}
                    </span>
                    <p class="text-slate-600 leading-snug">
                      {{ unit.ikssName }}
                    </p>
                  </div>
                </td>

                <!-- Sasaran Program (SP & ISP) Column -->
                <td class="py-4 px-4 border-r border-slate-100 space-y-3">
                  <div
                    v-for="sp in unit.sasaranProgram"
                    :key="sp.spKode"
                    class="space-y-1 pb-2 border-b border-slate-100 last:border-b-0 last:pb-0"
                  >
                    <div class="flex items-start gap-1.5">
                      <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-[#0B2A6F] text-white flex-shrink-0">
                        {{ sp.spKode }}
                      </span>
                      <p class="text-xs font-bold text-slate-800 leading-snug">
                        {{ sp.spName }}
                      </p>
                    </div>
                    <div class="pl-2 border-l border-slate-300 ml-1">
                      <p class="text-[10px] font-bold text-slate-500">
                        {{ sp.ispKode }}:
                      </p>
                      <p class="text-[11px] text-slate-600 leading-snug">
                        {{ sp.ispName }}
                      </p>
                    </div>
                  </div>
                </td>

                <!-- Sasaran Kinerja Kanwil (SK & IKK) Column -->
                <td class="py-4 px-4 space-y-3">
                  <div
                    v-for="sk in unit.sasaranKanwil"
                    :key="sk.skKode"
                    class="space-y-1.5 pb-2.5 border-b border-slate-100 last:border-b-0 last:pb-0"
                  >
                    <div class="flex items-start gap-1.5">
                      <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-blue-700 text-white flex-shrink-0">
                        {{ sk.skKode }}
                      </span>
                      <p class="text-xs font-bold text-slate-900 leading-snug">
                        {{ sk.skName }}
                      </p>
                    </div>

                    <div class="space-y-1 pl-2 border-l border-blue-300 ml-1">
                      <div
                        v-for="ikk in sk.ikkList"
                        :key="ikk.ikkKode"
                        class="text-[11px] leading-snug"
                      >
                        <span class="font-bold text-blue-900 text-[10px]">{{ ikk.ikkKode }}:</span>
                        <span class="text-slate-700 ml-1">{{ ikk.ikkName }}</span>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
