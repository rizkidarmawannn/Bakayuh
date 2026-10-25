<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiClient } from '@/composables/useApi'
import type {
  TargetIku,
  MatriksRow,
  RencanaAksi,
  PerbandinganSakipRow,
  SatuanKerja,
  TahunAnggaran,
  StatusColor,
} from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'
import gedungKanwilUrl from '@/assets/gedung-kanwil.jpg'

const props = defineProps<{
  defaultTab?: string
}>()

const route = useRoute()
const router = useRouter()

// ==========================================
// Tab State (No Scroll Jump)
// ==========================================
type ActiveTab = 'beranda' | 'iku' | 'matriks' | 'renaksi' | 'sakip' | 'pohon' | 'laporan' | 'ikpa'

function normalizeTab(rawTab?: string): ActiveTab {
  if (!rawTab || rawTab === 'beranda') return 'beranda'
  if (rawTab === 'pohon') return 'pohon'
  if (rawTab === 'renaksi') return 'renaksi'
  if (rawTab === 'capaian_pk' || rawTab === 'iku' || rawTab === 'matriks') return 'iku'
  if (rawTab === 'laporan') return 'laporan'
  if (rawTab === 'ikpa' || rawTab === 'sakip') return 'ikpa'
  return 'beranda'
}

const initialTab = computed<ActiveTab>(() => {
  const qTab = (route.query.tab as string) || props.defaultTab
  return normalizeTab(qTab)
})

const activeTab = ref<ActiveTab>(initialTab.value)

watch(
  () => route.query.tab,
  (newTab) => {
    activeTab.value = normalizeTab(newTab as string)
  }
)

function setTab(tab: ActiveTab) {
  activeTab.value = tab
  if (tab === 'beranda') {
    router.replace({ path: '/' })
  } else {
    router.replace({
      path: '/',
      query: {
        ...route.query,
        tab: tab === 'iku' ? 'capaian_pk' : (tab === 'sakip' ? 'ikpa' : tab),
      },
    })
  }
}

function selectModule(tab: ActiveTab) {
  setTab(tab)
  setTimeout(() => {
    const el = document.getElementById('modul-detail-panel')
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  }, 60)
}

function scrollToRingkasanKinerja() {
  const el = document.getElementById('ringkasan-kinerja')
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

// ==========================================
// Beranda Performance Summary Data
// ==========================================
const selectedRenaksiTw = ref<'TW 1' | 'TW 2' | 'TW 3' | 'TW 4'>('TW 4')

const renaksiUnits = [
  { name: 'Setjen', label: '-' },
  { name: 'AHU', label: '-' },
  { name: 'KI', label: '-' },
  { name: 'PP', label: '-' },
  { name: 'BPHN', label: '-' },
  { name: 'BSK', label: '-' },
]

const capaianPkUnits = [
  { name: 'Setjen', label: '2/2', percent: 100, color: 'bg-[#16A34A]' },
  { name: 'AHU', label: '0/2', percent: 0, color: 'bg-slate-200' },
  { name: 'KI', label: '0/3', percent: 0, color: 'bg-slate-200' },
  { name: 'PP', label: '0/2', percent: 0, color: 'bg-slate-200' },
  { name: 'BPHN', label: '0/4', percent: 0, color: 'bg-slate-200' },
  { name: 'BSK', label: '0/1', percent: 0, color: 'bg-slate-200' },
]

const ikpaUnits = [
  { name: 'Setjen', score: '95,85', width: 95.85, color: 'bg-[#F59E0B]' },
  { name: 'Ditjen AHU', score: '98,99', width: 98.99, color: 'bg-[#16A34A]' },
  { name: 'Ditjen KI', score: '96,98', width: 96.98, color: 'bg-[#16A34A]' },
  { name: 'Ditjen PP', score: '100', width: 100, color: 'bg-[#2563EB]' },
  { name: 'BPHN', score: '91,58', width: 91.58, color: 'bg-[#F59E0B]' },
  { name: 'BSK', score: '95,54', width: 95.54, color: 'bg-[#F59E0B]' },
]

const laporanUnits = [
  { name: 'Setjen', label: '0/12', percent: 0 },
  { name: 'AHU', label: '0/12', percent: 0 },
  { name: 'KI', label: '0/12', percent: 0 },
  { name: 'PP', label: '0/12', percent: 0 },
  { name: 'BPHN', label: '0/12', percent: 0 },
  { name: 'BSK', label: '0/12', percent: 0 },
]

// ==========================================
// Renaksi Tab Data (Image 1)
// ==========================================
const renaksiPeriod = ref<'ALL' | 'TW1' | 'TW2' | 'TW3' | 'TW4'>('TW4')
const renaksiPeriodOptions = [
  { id: 'ALL', label: 'Semua', deadline: 'Tenggat periode pengumpulan tahun 2026' },
  { id: 'TW1', label: 'TW I', deadline: 'Tenggat pengumpulan TW 1: 31/03/2026' },
  { id: 'TW2', label: 'TW II', deadline: 'Tenggat pengumpulan TW 2: 30/06/2026' },
  { id: 'TW3', label: 'TW III', deadline: 'Tenggat pengumpulan TW 3: 30/09/2026' },
  { id: 'TW4', label: 'TW IV', deadline: 'Tenggat pengumpulan TW 4: 31/12/2026' },
]

const currentRenaksiDeadline = computed(() => {
  const opt = renaksiPeriodOptions.find((o) => o.id === renaksiPeriod.value)
  return opt ? opt.deadline : 'Tenggat pengumpulan TW 4: 31/12/2026'
})

const renaksiViewMode = ref<'cards' | 'table'>('cards')

const renaksiUnitsDetailed = ref([
  {
    id: 'tu',
    kode: 'Setjen',
    name: 'Bagian Tata Usaha dan Umum',
    badgeClass: 'bg-blue-50 text-blue-700 border-blue-200',
    totalRenaksi: 2,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 2,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7099.1',
        skName: 'Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        ikkList: [
          { ikkKode: 'IKK 7099.1.1', ikkName: 'Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7099.2',
        skName: 'Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        ikkList: [
          { ikkKode: 'IKK 7099.2.1', ikkName: 'Indeks kepuasan terhadap layanan dukungan manajemen', status: 'Belum diunggah' },
        ],
      },
    ],
  },
  {
    id: 'ahu',
    kode: 'AHU',
    name: 'Administrasi Hukum Umum',
    badgeClass: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    totalRenaksi: 2,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 2,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7110.1',
        skName: 'Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7110.1.1', ikkName: 'Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7110.2',
        skName: 'Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7110.2.1', ikkName: 'Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris', status: 'Belum diunggah' },
        ],
      },
    ],
  },
  {
    id: 'ki',
    kode: 'KI',
    name: 'Kekayaan Intelektual',
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    totalRenaksi: 3,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 3,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7121.1',
        skName: 'Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
        ikkList: [
          { ikkKode: 'IKK 7121.1.1', ikkName: 'Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7121.2',
        skName: 'Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan',
        ikkList: [
          { ikkKode: 'IKK 7121.2.1', ikkName: 'Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 3',
        skName: 'Meningkatnya Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 3.1', ikkName: 'Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah', status: 'Belum diunggah' },
        ],
      },
    ],
  },
  {
    id: 'pp',
    kode: 'PP',
    name: 'Peraturan Perundang-Undangan',
    badgeClass: 'bg-sky-50 text-sky-700 border-sky-200',
    totalRenaksi: 1,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 1,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7129.1',
        skName: 'Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7129.1.1', ikkName: 'Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi', status: 'Belum diunggah' },
          { ikkKode: 'IKK 7129.1.2', ikkName: 'Persentase Perancangan Peraturan Perundang-undangan di Daerah yang Difasilitasi', status: 'Belum diunggah' },
        ],
      },
    ],
  },
  {
    id: 'bphn',
    kode: 'BPHN',
    name: 'Badan Pembinaan Hukum Nasional',
    badgeClass: 'bg-purple-50 text-purple-700 border-purple-200',
    totalRenaksi: 4,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 4,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7133.1',
        skName: 'Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda',
        ikkList: [
          { ikkKode: 'IKK 7133.1.1', ikkName: 'Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7136.1',
        skName: 'Meningkatnya Layanan Bantuan Hukum di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7136.1.1', ikkName: 'Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7136.2',
        skName: 'Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas',
        ikkList: [
          { ikkKode: 'IKK 7136.2.1', ikkName: 'Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah', status: 'Belum diunggah' },
        ],
      },
      {
        skKode: 'SK 7136.3',
        skName: 'Meningkatnya Pos Bantuan Hukum di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7136.3.1', ikkName: 'Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa/kelurahan', status: 'Belum diunggah' },
        ],
      },
    ],
  },
  {
    id: 'bsk',
    kode: 'BSK',
    name: 'Badan Strategi Kebijakan',
    badgeClass: 'bg-teal-50 text-teal-700 border-teal-200',
    totalRenaksi: 1,
    diterima: 0,
    menunggu: 0,
    perbaikan: 0,
    belum: 1,
    percent: 0,
    skList: [
      {
        skKode: 'SK 7143.1',
        skName: 'Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
        ikkList: [
          { ikkKode: 'IKK 7143.1.1', ikkName: 'Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah', status: 'Belum diunggah' },
        ],
      },
    ],
  },
])

const renaksiSummaryTotals = computed(() => {
  const total = renaksiUnitsDetailed.value.reduce((acc, u) => acc + u.totalRenaksi, 0)
  const diterima = renaksiUnitsDetailed.value.reduce((acc, u) => acc + u.diterima, 0)
  const menunggu = renaksiUnitsDetailed.value.reduce((acc, u) => acc + u.menunggu, 0)
  const perbaikan = renaksiUnitsDetailed.value.reduce((acc, u) => acc + u.perbaikan, 0)
  const belum = renaksiUnitsDetailed.value.reduce((acc, u) => acc + u.belum, 0)
  const percentDiterima = total > 0 ? Math.round((diterima / total) * 100) : 0

  return { total, diterima, menunggu, perbaikan, belum, percentDiterima }
})

const expandedRenaksiUnit = ref<string | null>(null)
function toggleRenaksiUnit(id: string) {
  expandedRenaksiUnit.value = expandedRenaksiUnit.value === id ? null : id
}

// ==========================================
// Capaian PK Tab Data (Images 2, 3, 4)
// ==========================================
const selectedPkYear = ref(2026)
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

interface CapaianPkRow {
  sasaran: string
  indikator: string
  target: string
  jan: string
  feb: string
  mar: string
  rekapTw1: string
  apr: string
  mei: string
  jun: string
  rekapTw2: string
  sdSemester1: string
  jul: string
  agu: string
}

interface CapaianPkGroup {
  unitId: string
  unitName: string
  rows: CapaianPkRow[]
}

const pkGroups = ref<CapaianPkGroup[]>([
  {
    unitId: 'setjen',
    unitName: 'Setjen',
    rows: [
      {
        sasaran: 'SK 7099.1 - Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
        indikator: 'IKK 7099.1.1 - Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK',
        target: '100 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '100 %', mei: '100 %', jun: '100 %', rekapTw2: '100 %',
        sdSemester1: '100 %',
        jul: '100 %', agu: '100 %',
      },
      {
        sasaran: 'SK 7099.2 - Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
        indikator: 'IKK 7099.2.1 - Indeks kepuasan terhadap layanan dukungan manajemen',
        target: '3,90 Indeks',
        jan: '3,94', feb: '3,94', mar: '3,94', rekapTw1: '3,94',
        apr: '3,96', mei: '3,96', jun: '3,96', rekapTw2: '3,96',
        sdSemester1: '3,95',
        jul: '3,97', agu: '3,97',
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
        target: '3,22 Indeks',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7110.2 - Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
        indikator: 'IKK 7110.2.1 - Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
        target: '98,2 %',
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
        target: '85 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7121.2 - Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan',
        indikator: 'IKK 7121.2.1 - Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani',
        target: '31 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 3 - Meningkatnya Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
        indikator: 'IKK 3.1 - Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
        target: '2,5 (Level)',
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
        sasaran: 'SK 7129.1 - Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
        indikator: 'IKK 7129.1.1 - Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
        target: '100 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7129.1 - Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
        indikator: 'IKK 7129.1.2 - Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
        target: '100 %',
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
        sasaran: 'SK 7133.1 - Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda',
        indikator: 'IKK 7133.1.1 - Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda di Wilayah Kerja Kanwil',
        target: '15 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7136.1 - Meningkatnya Layanan Bantuan Hukum di Wilayah',
        indikator: 'IKK 7136.1.1 - Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi',
        target: '89 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7136.2 - Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas',
        indikator: 'IKK 7136.2.1 - Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah',
        target: '70 %',
        jan: '-', feb: '-', mar: '-', rekapTw1: '-',
        apr: '-', mei: '-', jun: '-', rekapTw2: '-',
        sdSemester1: '-',
        jul: '-', agu: '-',
      },
      {
        sasaran: 'SK 7136.3 - Meningkatnya Pos Bantuan Hukum di Wilayah',
        indikator: 'IKK 7136.3.1 - Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa/kelurahan',
        target: '11 %',
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

// ==========================================
// Tab Laporan (Progres Laporan Bulanan & Triwulan)
// ==========================================
type LaporanMode = 'bulanan' | 'triwulan'
const laporanPeriodMode = ref<LaporanMode>('bulanan')
const laporanSelectedYear = ref<number>(2026)
const laporanYearOptions = [2026, 2025, 2024]

interface LaporanUnitRow {
  unitKode: string
  unitName: string
  bulananStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  triwulanStatus: Record<string, 'terlambat' | 'belum_waktunya' | 'diterima' | 'menunggu' | 'perbaikan' | 'waktunya'>
  kepatuhan: number
}

const laporanBulananCols = [
  { key: 'jan', label: 'JAN', date: '10/02' },
  { key: 'feb', label: 'FEB', date: '10/03' },
  { key: 'mar', label: 'MAR', date: '10/04' },
  { key: 'apr', label: 'APR', date: '10/05' },
  { key: 'mei', label: 'MEI', date: '10/06' },
  { key: 'jun', label: 'JUN', date: '10/07' },
  { key: 'jul', label: 'JUL', date: '10/08' },
  { key: 'agu', label: 'AGU', date: '10/09' },
  { key: 'sep', label: 'SEP', date: '10/10' },
  { key: 'okt', label: 'OKT', date: '10/11' },
  { key: 'nov', label: 'NOV', date: '10/12' },
  { key: 'des', label: 'DES', date: '10/01' },
]

const laporanTriwulanCols = [
  { key: 'tw1', label: 'TW I', date: '10/04' },
  { key: 'tw2', label: 'TW II', date: '10/07' },
  { key: 'tw3', label: 'TW III', date: '10/10' },
  { key: 'tw4', label: 'TW IV', date: '10/01' },
]

const laporanUnitsData = ref<LaporanUnitRow[]>([
  {
    unitKode: 'Setjen',
    unitName: 'Bagian Tata Usaha dan Umum',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'AHU',
    unitName: 'Administrasi Hukum Umum',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'KI',
    unitName: 'Kekayaan Intelektual',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'PP',
    unitName: 'Peraturan Perundang-Undangan',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'BPHN',
    unitName: 'Badan Pembinaan Hukum Nasional',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
  {
    unitKode: 'BSK',
    unitName: 'Badan Strategi Kebijakan',
    bulananStatus: {
      jan: 'terlambat', feb: 'terlambat', mar: 'terlambat', apr: 'terlambat',
      mei: 'terlambat', jun: 'terlambat', jul: 'terlambat', agu: 'terlambat',
      sep: 'terlambat', okt: 'belum_waktunya', nov: 'belum_waktunya', des: 'belum_waktunya',
    },
    triwulanStatus: {
      tw1: 'terlambat', tw2: 'terlambat', tw3: 'terlambat', tw4: 'belum_waktunya',
    },
    kepatuhan: 0,
  },
])

// ==========================================
// Tab IKPA (Indikator Kinerja Pelaksanaan Anggaran)
// ==========================================
const selectedIkpaPeriod = ref('September 2026')
const ikpaPeriodOptions = ['September 2026', 'Agustus 2026', 'Juli 2026', 'Semester I 2026']

interface IkpaTableRow {
  no: number
  satker: string
  kode: string
  revisiDipa: number | string
  deviasiHal3: number | string
  penyerapanAnggaran: number | string
  belanjaKontraktual: number | string
  penyelesaianTagihan: number | string
  pengelolaanUpTup: number | string
  capaianOutput: number | string
  nilaiAkhir: number | string
}

const ikpaTableRows = ref<IkpaTableRow[]>([
  {
    no: 1,
    satker: 'Setjen',
    kode: '692016',
    revisiDipa: 100,
    deviasiHal3: 93.27,
    penyerapanAnggaran: 84.3,
    belanjaKontraktual: 100,
    penyelesaianTagihan: 100,
    pengelolaanUpTup: 99.97,
    capaianOutput: 100,
    nilaiAkhir: 95.85,
  },
  {
    no: 2,
    satker: 'Ditjen AHU',
    kode: '692056',
    revisiDipa: 100,
    deviasiHal3: 100,
    penyerapanAnggaran: 97.12,
    belanjaKontraktual: 96.67,
    penyelesaianTagihan: 100,
    pengelolaanUpTup: '-',
    capaianOutput: 100,
    nilaiAkhir: 98.99,
  },
  {
    no: 3,
    satker: 'Ditjen KI',
    kode: '693003',
    revisiDipa: 75,
    deviasiHal3: 100,
    penyerapanAnggaran: 98.89,
    belanjaKontraktual: 100,
    penyelesaianTagihan: 100,
    pengelolaanUpTup: '-',
    capaianOutput: 100,
    nilaiAkhir: 96.98,
  },
  {
    no: 4,
    satker: 'Ditjen PP',
    kode: '693036',
    revisiDipa: 100,
    deviasiHal3: 100,
    penyerapanAnggaran: 100,
    belanjaKontraktual: '-',
    penyelesaianTagihan: '-',
    pengelolaanUpTup: 100,
    capaianOutput: 100,
    nilaiAkhir: 100,
  },
  {
    no: 5,
    satker: 'BPHN',
    kode: '693104',
    revisiDipa: 100,
    deviasiHal3: 89.7,
    penyerapanAnggaran: 76.72,
    belanjaKontraktual: '-',
    penyelesaianTagihan: '-',
    pengelolaanUpTup: 94.61,
    capaianOutput: 100,
    nilaiAkhir: 91.58,
  },
  {
    no: 6,
    satker: 'BSK',
    kode: '693138',
    revisiDipa: 100,
    deviasiHal3: 100,
    penyerapanAnggaran: 86.23,
    belanjaKontraktual: '-',
    penyelesaianTagihan: '-',
    pengelolaanUpTup: 91.86,
    capaianOutput: 100,
    nilaiAkhir: 95.54,
  },
])

const ikpaSummaryRow = {
  label: 'Nilai Rata Kantor Wilayah',
  revisiDipa: '95,83',
  deviasiHal3: '97,16',
  penyerapanAnggaran: '90,54',
  belanjaKontraktual: '98,89',
  penyelesaianTagihan: '100',
  pengelolaanUpTup: '96,61',
  capaianOutput: '100',
  nilaiAkhir: '96,49',
}

function formatIkpaVal(val: number | string): string {
  if (typeof val === 'number') {
    return val.toString().replace('.', ',')
  }
  return val || '-'
}

function getIkpaCellClass(val: number | string): string {
  if (val === '-' || val === null || val === undefined || val === '') {
    return 'bg-white text-slate-400 font-normal'
  }
  const num = typeof val === 'number' ? val : parseFloat(val.toString().replace(',', '.'))
  if (isNaN(num)) return 'bg-white text-slate-400 font-normal'
  if (num >= 100) {
    // Sempurna (100) -> soft light-blue
    return 'bg-[#DCE8F7] text-slate-800 font-medium'
  }
  if (num >= 96) {
    // Memenuhi Target (96 s.d. < 100) -> vibrant light green
    return 'bg-[#98D65F] text-slate-800 font-medium'
  }
  // Belum Memenuhi Target (< 96) -> pastel yellow
  return 'bg-[#FEF08A] text-slate-800 font-medium'
}

// ==========================================
// Common Metadata (Satker & Tahun)
// ==========================================
interface PublikSummary {
  tahun: number
  total_satker: number
  rata_rata_capaian_iku: number
  nilai_sakip_rerata: number
  status_zi: string
}

const summaryLoading = ref(true)
const summary = ref<PublikSummary | null>(null)
const satkers = ref<SatuanKerja[]>([])
const tahuns = ref<TahunAnggaran[]>([])
const selectedTahunId = ref<number | null>(null)

async function fetchMetadata() {
  summaryLoading.value = true
  try {
    const [resSummary, resSatker, resTahun] = await Promise.all([
      apiClient.get<{ data: PublikSummary }>('/publik/summary'),
      apiClient.get<{ data: SatuanKerja[] }>('/satker').catch(() => ({ data: { data: [] } })),
      apiClient.get<{ data: TahunAnggaran[] }>('/tahun-anggaran').catch(() => ({ data: { data: [] } })),
    ])

    summary.value = resSummary.data.data
    satkers.value = resSatker.data.data
    tahuns.value = resTahun.data.data

    const aktif = tahuns.value.find((t) => t.is_aktif)
    if (aktif) {
      selectedTahunId.value = aktif.id
    } else if (tahuns.value.length > 0 && tahuns.value[0]) {
      selectedTahunId.value = tahuns.value[0].id
    }
  } catch (err) {
    console.error('Failed to load portal metadata:', err)
  } finally {
    summaryLoading.value = false
  }
}

// ==========================================
// 1. Tab: Target & Realisasi IKU
// ==========================================
const ikuLoading = ref(false)
const ikuTargets = ref<TargetIku[]>([])
const ikuSatkerFilter = ref<number | null>(null)
const ikuSearchQuery = ref('')

const filteredIkuTargets = computed(() => {
  let list = ikuTargets.value
  if (ikuSatkerFilter.value) {
    list = list.filter((t) => t.satker_id === ikuSatkerFilter.value)
  }
  if (ikuSearchQuery.value.trim()) {
    const q = ikuSearchQuery.value.toLowerCase()
    list = list.filter(
      (t) =>
        (t.indikator?.nama?.toLowerCase().includes(q) ?? false) ||
        (t.indikator?.kode?.toLowerCase().includes(q) ?? false) ||
        (t.satker?.nama?.toLowerCase().includes(q) ?? false)
    )
  }
  return list
})

const {
  currentPage: ikuCurrentPage,
  pageSize: ikuPageSize,
  totalItems: ikuTotalItems,
  paginatedItems: paginatedIkuTargets,
} = usePagination(filteredIkuTargets, { defaultPageSize: 10 })

async function fetchIkuData() {
  ikuLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (ikuSatkerFilter.value) params.satker_id = ikuSatkerFilter.value

    const res = await apiClient.get<{ data: TargetIku[] }>('/target-iku', { params })
    ikuTargets.value = res.data.data
  } catch (err) {
    console.error('Failed to load IKU data:', err)
  } finally {
    ikuLoading.value = false
  }
}

// ==========================================
// 2. Tab: Matriks Capaian IKU
// ==========================================
const matriksLoading = ref(false)
const matriksRows = ref<MatriksRow[]>([])
const matriksSearch = ref('')

const filteredMatriksRows = computed(() => {
  if (!matriksSearch.value.trim()) return matriksRows.value
  const q = matriksSearch.value.toLowerCase()
  return matriksRows.value.filter(
    (row) =>
      row.satker.nama.toLowerCase().includes(q) ||
      row.satker.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage: matriksCurrentPage,
  pageSize: matriksPageSize,
  totalItems: matriksTotalItems,
  paginatedItems: paginatedMatriksRows,
} = usePagination(filteredMatriksRows, { defaultPageSize: 10 })

async function fetchMatriksData() {
  matriksLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    const res = await apiClient.get<{ data: MatriksRow[] }>('/target-iku/matriks', { params })
    matriksRows.value = res.data.data
  } catch (err) {
    console.error('Failed to load matrix data:', err)
  } finally {
    matriksLoading.value = false
  }
}

// ==========================================
// 3. Tab: Rencana Aksi (Renaksi)
// ==========================================
const renaksiLoading = ref(false)
const renaksiList = ref<RencanaAksi[]>([])
const renaksiSatkerFilter = ref<number | null>(null)
const renaksiTriwulanFilter = ref<string>('ALL')
const renaksiSearch = ref('')

const filteredRenaksiList = computed(() => {
  let list = renaksiList.value
  if (renaksiSatkerFilter.value) {
    list = list.filter((r) => r.satker_id === renaksiSatkerFilter.value)
  }
  if (renaksiTriwulanFilter.value !== 'ALL') {
    list = list.filter((r) => r.triwulan === renaksiTriwulanFilter.value)
  }
  if (renaksiSearch.value.trim()) {
    const q = renaksiSearch.value.toLowerCase()
    list = list.filter(
      (r) =>
        r.nama_aksi.toLowerCase().includes(q) ||
        (r.satker?.nama?.toLowerCase().includes(q) ?? false) ||
        (r.indikator?.nama?.toLowerCase().includes(q) ?? false)
    )
  }
  return list
})

const {
  currentPage: renaksiCurrentPage,
  pageSize: renaksiPageSize,
  totalItems: renaksiTotalItems,
  paginatedItems: paginatedRenaksiList,
} = usePagination(filteredRenaksiList, { defaultPageSize: 10 })

async function fetchRenaksiData() {
  renaksiLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value
    if (renaksiSatkerFilter.value) params.satker_id = renaksiSatkerFilter.value
    if (renaksiTriwulanFilter.value !== 'ALL') params.triwulan = renaksiTriwulanFilter.value

    const res = await apiClient.get<{ data: RencanaAksi[] }>('/rencana-aksi', { params })
    renaksiList.value = res.data.data
  } catch (err) {
    console.error('Failed to load renaksi data:', err)
  } finally {
    renaksiLoading.value = false
  }
}

function getRenaksiStatusBadge(status?: string): { label: string; color: StatusColor } {
  switch (status) {
    case 'terverifikasi':
      return { label: 'Terverifikasi', color: 'green' }
    case 'menunggu_verifikasi':
      return { label: 'Menunggu Verifikasi', color: 'blue' }
    case 'perlu_perbaikan':
      return { label: 'Perlu Perbaikan', color: 'yellow' }
    default:
      return { label: 'Belum Lapor', color: 'gray' }
  }
}

// ==========================================
// 4. Tab: Evaluasi SAKIP
// ==========================================
const sakipLoading = ref(false)
const sakipRows = ref<PerbandinganSakipRow[]>([])
const sakipSearch = ref('')

const filteredSakipRows = computed(() => {
  if (!sakipSearch.value.trim()) return sakipRows.value
  const q = sakipSearch.value.toLowerCase()
  return sakipRows.value.filter(
    (row) =>
      row.nama.toLowerCase().includes(q) ||
      row.kode.toLowerCase().includes(q)
  )
})

const {
  currentPage: sakipCurrentPage,
  pageSize: sakipPageSize,
  totalItems: sakipTotalItems,
  paginatedItems: paginatedSakipRows,
} = usePagination(filteredSakipRows, { defaultPageSize: 10 })

async function fetchSakipData() {
  sakipLoading.value = true
  try {
    const params: Record<string, any> = {}
    if (selectedTahunId.value) params.tahun_anggaran_id = selectedTahunId.value

    const res = await apiClient.get<{ data: PerbandinganSakipRow[] }>('/evaluasi-sakip/perbandingan', { params })
    sakipRows.value = res.data.data
  } catch (err) {
    console.error('Failed to load SAKIP data:', err)
  } finally {
    sakipLoading.value = false
  }
}

function getPredikatBadge(predikat?: string | null): { label: string; color: StatusColor } {
  if (!predikat) return { label: 'Belum Dievaluasi', color: 'gray' }
  switch (predikat) {
    case 'AA':
      return { label: 'AA - Sangat Memuaskan', color: 'green' }
    case 'A':
      return { label: 'A - Memuaskan', color: 'green' }
    case 'BB':
      return { label: 'BB - Sangat Baik', color: 'blue' }
    case 'B':
      return { label: 'B - Baik', color: 'blue' }
    case 'CC':
      return { label: 'CC - Cukup', color: 'yellow' }
    case 'C':
      return { label: 'C - Kurang', color: 'red' }
    default:
      return { label: `${predikat} - Sangat Kurang`, color: 'red' }
  }
}

// Watchers for tab switching
watch(
  () => activeTab.value,
  (tab) => {
    if (tab === 'iku' && ikuTargets.value.length === 0) fetchIkuData()
    if (tab === 'matriks' && matriksRows.value.length === 0) fetchMatriksData()
    if (tab === 'renaksi' && renaksiList.value.length === 0) fetchRenaksiData()
    if (tab === 'sakip' && sakipRows.value.length === 0) fetchSakipData()
  },
  { immediate: true }
)

// Re-fetch on Year change
watch(
  () => selectedTahunId.value,
  () => {
    if (activeTab.value === 'iku') fetchIkuData()
    else if (activeTab.value === 'matriks') fetchMatriksData()
    else if (activeTab.value === 'renaksi') fetchRenaksiData()
    else if (activeTab.value === 'sakip') fetchSakipData()
  }
)

onMounted(async () => {
  await fetchMetadata()
  if (activeTab.value === 'iku') fetchIkuData()
  else if (activeTab.value === 'matriks') fetchMatriksData()
  else if (activeTab.value === 'renaksi') fetchRenaksiData()
  else if (activeTab.value === 'sakip') fetchSakipData()
})
</script>

<template>
  <div class="space-y-8 animate-fade-in">
    <!-- Hero Banner (Government & Public Transparency Header) -->
    <div class="rounded-3xl p-6 sm:p-8 lg:p-10 bg-[#0B2A6F] text-white shadow-xl relative overflow-hidden">
      <!-- Background subtle pattern -->
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none" />

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
        <!-- Left Column: Content -->
        <div class="lg:col-span-7 flex flex-col items-start">
          <!-- Pill Badge -->
          <span class="inline-block px-3.5 py-1 rounded-lg bg-white text-[#0B2A6F] font-black text-xs uppercase tracking-wider mb-4 shadow-sm">
            BAKAYUH
          </span>

          <!-- Title -->
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white leading-tight">
            Sistem Akuntabilitas Kinerja<br class="hidden sm:inline" />
            Kantor Wilayah Kementerian<br class="hidden sm:inline" />
            Hukum Kalimantan Selatan
          </h1>

          <!-- Subtext -->
          <p class="text-xs sm:text-sm text-white/90 mt-4 leading-relaxed max-w-xl">
            Aplikasi untuk memudahkan pemantauan dan pengendalian kinerja dalam rangka meningkatkan akuntabilitas dan kinerja unit kerja, mulai dari pohon kinerja, rencana aksi, capaian perjanjian kinerja, hingga pelaksanaan anggaran.
          </p>

          <!-- Button -->
          <button
            type="button"
            @click="scrollToRingkasanKinerja"
            class="mt-6 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-[#F9C823] hover:bg-amber-400 text-slate-950 transition-all shadow-md inline-flex items-center gap-2 group cursor-pointer"
          >
            <span>Eksplorasi Modul Kinerja</span>
            <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
          </button>

          <!-- Global Year Selector in Hero -->
          <div class="mt-6 flex flex-wrap items-center gap-2.5 pt-4 border-t border-white/10 w-full">
            <span class="text-xs text-white/70 font-medium">Tahun Anggaran:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
              <button
                v-for="t in tahuns"
                :key="t.id"
                type="button"
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all border"
                :class="selectedTahunId === t.id
                  ? 'bg-[#F9C823] text-slate-950 border-[#F9C823] shadow-sm'
                  : 'bg-white/10 text-white border-white/20 hover:bg-white/20'"
                @click="selectedTahunId = t.id"
              >
                {{ t.tahun }}
                <span v-if="t.is_aktif" class="text-[10px] ml-1 opacity-80">(Aktif)</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Right Column: Building Photo -->
        <div class="lg:col-span-5 flex justify-center lg:justify-end">
          <div class="w-full max-w-md lg:max-w-none rounded-2xl lg:rounded-3xl overflow-hidden shadow-2xl border border-white/15 aspect-[16/10] bg-slate-900 group">
            <img
              :src="gedungKanwilUrl"
              alt="Gedung Kantor Wilayah Kementerian Hukum Kalimantan Selatan"
              class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out"
              loading="eager"
            />
          </div>
        </div>
      </div>

      <!-- 4 Horizontal Metric Summary Cards Floating Across Hero -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8 pt-2 relative z-10">
        <!-- 1. Renaksi diterima -->
        <div
          role="button"
          tabindex="0"
          @click="setTab('renaksi')"
          class="bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-100 flex items-center gap-4 text-slate-800 transition-all hover:shadow-xl hover:-translate-y-0.5 cursor-pointer select-none group"
        >
          <div class="relative w-14 h-14 flex-shrink-0 flex items-center justify-center">
            <svg class="w-14 h-14 -rotate-90" viewBox="0 0 48 48">
              <circle cx="24" cy="24" r="19" stroke="#E2E8F0" stroke-width="4.5" fill="none" />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center font-bold text-xs text-slate-800">0%</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-[#0B2A6F] text-sm sm:text-base leading-snug group-hover:text-blue-600 transition-colors">
              Renaksi diterima
            </span>
            <span class="text-xs text-slate-500 mt-0.5">TW 4 &bull; 0 dari 0</span>
          </div>
        </div>

        <!-- 2. Capaian PK terisi -->
        <div
          role="button"
          tabindex="0"
          @click="setTab('iku')"
          class="bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-100 flex items-center gap-4 text-slate-800 transition-all hover:shadow-xl hover:-translate-y-0.5 cursor-pointer select-none group"
        >
          <div class="relative w-14 h-14 flex-shrink-0 flex items-center justify-center">
            <svg class="w-14 h-14 -rotate-90" viewBox="0 0 48 48">
              <circle cx="24" cy="24" r="19" stroke="#E2E8F0" stroke-width="4.5" fill="none" />
              <circle
                cx="24" cy="24" r="19"
                stroke="#1D4ED8"
                stroke-width="4.5"
                fill="none"
                stroke-linecap="round"
                stroke-dasharray="119.38"
                stroke-dashoffset="102.67"
              />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center font-bold text-xs text-slate-800">14%</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-[#0B2A6F] text-sm sm:text-base leading-snug group-hover:text-blue-600 transition-colors">
              Capaian PK terisi
            </span>
            <span class="text-xs text-slate-500 mt-0.5">2 dari 14 indikator (2026)</span>
          </div>
        </div>

        <!-- 3. Nilai IKPA -->
        <div
          role="button"
          tabindex="0"
          @click="setTab('sakip')"
          class="bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-100 flex items-center gap-4 text-slate-800 transition-all hover:shadow-xl hover:-translate-y-0.5 cursor-pointer select-none group"
        >
          <div class="flex-shrink-0 flex items-center justify-center">
            <span class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">96,49</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-[#0B2A6F] text-sm sm:text-base leading-snug group-hover:text-blue-600 transition-colors">
              Nilai IKPA
            </span>
            <span class="text-xs text-slate-500 mt-0.5">rata-rata September 2026</span>
          </div>
        </div>

        <!-- 4. Laporan diterima -->
        <div
          role="button"
          tabindex="0"
          @click="setTab('laporan')"
          class="bg-white rounded-2xl p-4 sm:p-5 shadow-lg border border-slate-100 flex items-center gap-4 text-slate-800 transition-all hover:shadow-xl hover:-translate-y-0.5 cursor-pointer select-none group"
        >
          <div class="relative w-14 h-14 flex-shrink-0 flex items-center justify-center">
            <svg class="w-14 h-14 -rotate-90" viewBox="0 0 48 48">
              <circle cx="24" cy="24" r="19" stroke="#E2E8F0" stroke-width="4.5" fill="none" />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center font-bold text-xs text-slate-800">0%</span>
          </div>
          <div class="flex flex-col min-w-0">
            <span class="font-bold text-[#0B2A6F] text-sm sm:text-base leading-snug group-hover:text-blue-600 transition-colors">
              Laporan diterima
            </span>
            <span class="text-xs text-slate-500 mt-0.5">0 dari 72 jatuh tempo (2026)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION: RINGKASAN KINERJA (Tampilan Utama Beranda) -->
    <div v-if="activeTab === 'beranda'" id="ringkasan-kinerja" class="space-y-6 pt-2 animate-fade-in">
      <div>
        <h2 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
          Ringkasan Kinerja
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Data diperbarui otomatis. Pilih panel untuk melihat selengkapnya.
        </p>
      </div>

      <!-- Grid Row 1: Renaksi (Left) and Capaian PK (Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        <!-- Card Left: Renaksi — progres data dukung -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
                Renaksi &mdash; progres data dukung
              </h3>
              <button
                type="button"
                @click="setTab('renaksi')"
                class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1 cursor-pointer"
              >
                <span>Lihat detail</span>
                <span>&rarr;</span>
              </button>
            </div>

            <!-- 4 Quarters Boxes -->
            <div class="grid grid-cols-4 gap-2 sm:gap-3 my-5">
              <button
                v-for="tw in (['TW 1', 'TW 2', 'TW 3', 'TW 4'] as const)"
                :key="tw"
                type="button"
                @click="selectedRenaksiTw = tw"
                class="rounded-xl p-3 flex flex-col items-center justify-center text-center transition-all cursor-pointer border"
                :class="selectedRenaksiTw === tw
                  ? 'bg-blue-50/70 border-blue-200 shadow-xs'
                  : 'bg-white border-slate-100 hover:border-slate-200 hover:bg-slate-50/50'"
              >
                <div class="relative w-11 h-11 flex items-center justify-center">
                  <svg class="w-11 h-11 -rotate-90" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="14" stroke="#E2E8F0" stroke-width="3.5" fill="none" />
                  </svg>
                  <span class="absolute inset-0 flex items-center justify-center font-bold text-[11px] text-slate-800">0%</span>
                </div>
                <span class="font-bold text-xs sm:text-sm mt-2" :class="selectedRenaksiTw === tw ? 'text-[#0B2A6F]' : 'text-slate-800'">
                  {{ tw }}
                </span>
                <span class="text-[10px] text-slate-400 mt-0.5">belum ada</span>
              </button>
            </div>

            <!-- Subheading per unit -->
            <div class="text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-3">
              PER UNIT &bull; {{ selectedRenaksiTw }}
            </div>

            <!-- Units list -->
            <div class="space-y-2.5">
              <div v-for="u in renaksiUnits" :key="u.name" class="flex items-center gap-3">
                <span class="w-14 sm:w-16 text-xs font-semibold text-slate-700">{{ u.name }}</span>
                <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                  <div class="h-full rounded-full bg-slate-200" style="width: 0%" />
                </div>
                <span class="w-4 text-right text-xs font-medium text-slate-400">{{ u.label }}</span>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 mt-6 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-[#16A34A]" />
              <span>Diterima</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-[#EAB308]" />
              <span>Menunggu</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]" />
              <span>Perbaikan</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-[#CBD5E1]" />
              <span>Belum</span>
            </div>
          </div>
        </div>

        <!-- Card Right: Capaian PK 2026 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
                Capaian PK 2026
              </h3>
              <button
                type="button"
                @click="setTab('iku')"
                class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1 cursor-pointer"
              >
                <span>Lihat detail</span>
                <span>&rarr;</span>
              </button>
            </div>
            <p class="text-xs text-slate-500 mt-1 mb-5">
              Indikator yang sudah memiliki capaian tahun ini.
            </p>

            <!-- Units list -->
            <div class="space-y-3 pt-2">
              <div v-for="u in capaianPkUnits" :key="u.name" class="flex items-center gap-3">
                <span class="w-14 sm:w-16 text-xs font-semibold text-slate-700">{{ u.name }}</span>
                <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="u.color"
                    :style="{ width: u.percent + '%' }"
                  />
                </div>
                <span class="w-8 text-right text-xs font-medium text-slate-500">{{ u.label }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid Row 2: IKPA (Left) and Pohon Kinerja (Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
        <!-- Card Left: IKPA — September 2026 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
                IKPA &mdash; September 2026
              </h3>
              <button
                type="button"
                @click="setTab('ikpa')"
                class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1 cursor-pointer"
              >
                <span>Lihat detail</span>
                <span>&rarr;</span>
              </button>
            </div>

            <!-- Top Half: Gauge on Left, Units on Right -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center mt-4">
              <!-- Gauge Meter (sm:col-span-5) -->
              <div class="sm:col-span-5 flex flex-col items-center justify-center py-2">
                <div class="relative w-44 h-24 flex items-end justify-center">
                  <svg class="w-44 h-24 overflow-visible" viewBox="0 0 176 96">
                    <!-- Background Arc -->
                    <path
                      d="M 16 88 A 72 72 0 0 1 160 88"
                      fill="none"
                      stroke="#E2E8F0"
                      stroke-width="12"
                      stroke-linecap="round"
                    />
                    <!-- Progress Arc (Green #16A34A) -->
                    <path
                      d="M 16 88 A 72 72 0 0 1 160 88"
                      fill="none"
                      stroke="#16A34A"
                      stroke-width="12"
                      stroke-linecap="round"
                      stroke-dasharray="226.2"
                      stroke-dashoffset="18"
                    />
                    <!-- Knob indicator at the tip -->
                    <circle cx="152" cy="74" r="5.5" fill="#FFFFFF" stroke="#0B2A6F" stroke-width="2.5" />
                  </svg>
                  <!-- Center value -->
                  <div class="absolute bottom-0 inset-x-0 flex flex-col items-center justify-end text-center">
                    <span class="text-3xl font-black text-[#0B2A6F] tracking-tight leading-none">96,49</span>
                    <span class="text-[11px] text-slate-500 font-medium mt-1">rata-rata Nilai Akhir</span>
                  </div>
                </div>
              </div>

              <!-- Unit Scores (sm:col-span-7) -->
              <div class="sm:col-span-7 space-y-2">
                <div v-for="u in ikpaUnits" :key="u.name" class="flex items-center gap-2.5">
                  <span class="w-20 text-xs font-semibold text-slate-700 truncate" :title="u.name">{{ u.name }}</span>
                  <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full" :class="u.color" :style="{ width: u.width + '%' }" />
                  </div>
                  <span class="w-10 text-right text-xs font-medium text-slate-600">{{ u.score }}</span>
                </div>
              </div>
            </div>

            <!-- Bottom Half: Line Chart Tren Nilai Rata-rata -->
            <div class="mt-6 pt-4 border-t border-slate-100">
              <div class="text-[11px] font-bold tracking-wider uppercase text-slate-500 mb-2">
                TREN NILAI RATA-RATA
              </div>
              <div class="w-full h-32 relative">
                <svg class="w-full h-full overflow-visible" viewBox="0 0 400 110" preserveAspectRatio="none">
                  <!-- Y=97 -->
                  <text x="30" y="24" fill="#94A3B8" font-size="10" text-anchor="end">97</text>
                  <line x1="40" y1="20" x2="390" y2="20" stroke="#F1F5F9" stroke-width="1" />

                  <!-- Y=96 Target Line -->
                  <text x="30" y="64" fill="#94A3B8" font-size="10" text-anchor="end">96</text>
                  <line x1="40" y1="60" x2="390" y2="60" stroke="#22C55E" stroke-width="1.2" stroke-dasharray="4 3" />
                  <text x="390" y="55" fill="#16A34A" font-size="10" font-weight="600" text-anchor="end">target 96</text>

                  <!-- Y=95 -->
                  <text x="30" y="104" fill="#94A3B8" font-size="10" text-anchor="end">95</text>
                  <line x1="40" y1="100" x2="390" y2="100" stroke="#F1F5F9" stroke-width="1" />

                  <!-- Data point Sep 26 (x=215, y=41) -->
                  <text x="215" y="32" fill="#0B2A6F" font-size="11" font-weight="700" text-anchor="middle">96,49</text>
                  <circle cx="215" cy="41" r="4.5" fill="#FFFFFF" stroke="#2563EB" stroke-width="2.5" />

                  <!-- X-axis label -->
                  <text x="215" y="112" fill="#64748B" font-size="10" text-anchor="middle">Sep 26</text>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Right: Pohon Kinerja -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
                Pohon Kinerja
              </h3>
              <button
                type="button"
                @click="setTab('pohon')"
                class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1 cursor-pointer"
              >
                <span>Lihat pohon</span>
                <span>&rarr;</span>
              </button>
            </div>
            <p class="text-xs text-slate-500 mt-1 mb-5">
              Keterkaitan kinerja dari Kementerian hingga Kantor Wilayah.
            </p>

            <!-- 2x2 Metric Grid -->
            <div class="grid grid-cols-2 gap-3.5 sm:gap-4">
              <!-- 1. Sasaran Strategis -->
              <div class="bg-[#F0F5FF] rounded-2xl p-5 flex flex-col items-center justify-center text-center transition-all hover:bg-blue-100/50">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F] tracking-tight">2</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-700 mt-1.5">Sasaran Strategis</span>
              </div>
              <!-- 2. Unit Eselon I -->
              <div class="bg-[#F0F5FF] rounded-2xl p-5 flex flex-col items-center justify-center text-center transition-all hover:bg-blue-100/50">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F] tracking-tight">6</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-700 mt-1.5">Unit Eselon I</span>
              </div>
              <!-- 3. Sasaran Program -->
              <div class="bg-[#F0F5FF] rounded-2xl p-5 flex flex-col items-center justify-center text-center transition-all hover:bg-blue-100/50">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F] tracking-tight">17</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-700 mt-1.5">Sasaran Program</span>
              </div>
              <!-- 4. Sasaran Kegiatan -->
              <div class="bg-[#F0F5FF] rounded-2xl p-5 flex flex-col items-center justify-center text-center transition-all hover:bg-blue-100/50">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F] tracking-tight">13</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-700 mt-1.5">Sasaran Kegiatan</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 3: Full Width Laporan Bulanan & Triwulan 2026 -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between">
          <h3 class="text-base sm:text-lg font-bold text-[#0B2A6F]">
            Laporan bulanan &amp; triwulan 2026
          </h3>
          <button
            type="button"
            @click="setTab('laporan')"
            class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center gap-1 cursor-pointer"
          >
            <span>Lihat detail</span>
            <span>&rarr;</span>
          </button>
        </div>

        <!-- Content 2 Columns: Dials on Left, Units on Right -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center mt-5">
          <!-- Left: 2 Circular Dials -->
          <div class="lg:col-span-5 flex items-center justify-around sm:justify-center sm:gap-12">
            <!-- Dial 1 -->
            <div class="flex flex-col items-center text-center">
              <div class="relative w-20 h-20 flex items-center justify-center">
                <svg class="w-20 h-20 -rotate-90" viewBox="0 0 64 64">
                  <circle cx="32" cy="32" r="26" stroke="#E2E8F0" stroke-width="5" fill="none" />
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-base font-bold text-slate-800">0%</span>
              </div>
              <span class="font-bold text-xs sm:text-sm text-[#0B2A6F] mt-2.5">Laporan Bulanan</span>
              <span class="text-xs text-slate-500 mt-0.5">0 dari 54</span>
            </div>

            <!-- Dial 2 -->
            <div class="flex flex-col items-center text-center">
              <div class="relative w-20 h-20 flex items-center justify-center">
                <svg class="w-20 h-20 -rotate-90" viewBox="0 0 64 64">
                  <circle cx="32" cy="32" r="26" stroke="#E2E8F0" stroke-width="5" fill="none" />
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-base font-bold text-slate-800">0%</span>
              </div>
              <span class="font-bold text-xs sm:text-sm text-[#0B2A6F] mt-2.5">Laporan Triwulan</span>
              <span class="text-xs text-slate-500 mt-0.5">0 dari 18</span>
            </div>
          </div>

          <!-- Right: Units bars -->
          <div class="lg:col-span-7 space-y-2.5">
            <div v-for="u in laporanUnits" :key="u.name" class="flex items-center gap-3">
              <span class="w-14 sm:w-16 text-xs font-semibold text-slate-700">{{ u.name }}</span>
              <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-slate-200" :style="{ width: u.percent + '%' }" />
              </div>
              <span class="w-8 text-right text-xs font-medium text-slate-400">{{ u.label }}</span>
            </div>

            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 pt-3">
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#16A34A]" />
                <span>Diterima</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#EAB308]" />
                <span>Menunggu</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]" />
                <span>Perbaikan</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#CBD5E1]" />
                <span>Belum</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Footnote -->
        <p class="text-xs text-slate-500 mt-6 pt-3 border-t border-slate-100">
          Dihitung dari laporan yang sudah jatuh tempo atau sudah dikirim.
        </p>
      </div>
    </div>

    <!-- ACTIVE MODULE DETAIL WRAPPER (Displayed when a module tab is selected) -->
    <div v-if="activeTab !== 'beranda'" id="modul-detail-panel" class="space-y-4 pt-2">
      <!-- TAB CONTENT: 0. POHON KINERJA (Logic Model) -->
      <div v-if="activeTab === 'pohon'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-200 gap-3">
            <div>
              <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Modul Perencanaan Kinerja</span>
              <h3 class="text-xl font-black text-slate-900 mt-1">Arsitektur Pohon Kinerja (Logic Model)</h3>
              <p class="text-xs text-slate-500 mt-0.5">Penyelarasan mandat perundang-undangan dan sasaran strategis Kanwil Kementerian Hukum Kalimantan Selatan</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold self-start sm:self-auto">
              <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse" />
              17 Satker Terkoneksi
            </span>
          </div>

          <!-- 3-Tier Logic Model Breakdown -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Level 1: Ultimate Outcome -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-blue-100 text-blue-800">Tingkat 1 &bull; Impact</span>
                <span class="text-xs font-bold text-slate-400">Outcome Utama</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Kepastian Hukum &amp; Pelayanan Publik Berkualitas</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Terwujudnya supremasi hukum yang berkeadilan, penegakan HAM, kepastian hukum keimigrasian &amp; pemasyarakatan yang humanis di wilayah Kalimantan Selatan.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Indeks Kepuasan Masyarakat (IKM), Indeks Reformasi Hukum (IRH).
              </div>
            </div>

            <!-- Level 2: Intermediate Outcome -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-amber-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-amber-100 text-amber-800">Tingkat 2 &bull; Intermediate</span>
                <span class="text-xs font-bold text-slate-400">Sasaran Program</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Akuntabilitas &amp; Reformasi Birokrasi Satker Binaan</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Optimalisasi tata kelola birokrasi berintegritas tinggi bebas korupsi (WBK/WBBM) dan peningkatan maturitas SPIP di seluruh 17 Unit Pelaksana Teknis se-Kalsel.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Nilai Evaluasi SAKIP &ge; BB, Predikat WBK/WBBM Satker.
              </div>
            </div>

            <!-- Level 3: Output & Activities -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-emerald-50/40 border border-slate-200 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Tingkat 3 &bull; Output</span>
                <span class="text-xs font-bold text-slate-400">Rencana Aksi</span>
              </div>
              <h4 class="text-sm font-black text-slate-900 leading-snug">Pelaksanaan Rencana Aksi Triwulanan Terukur</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Realisasi rencana aksi kinerja secara bertahap mulai dari TW I hingga TW IV dengan verifikasi kelayakan bukti dukung 100% tepat waktu.
              </p>
              <div class="mt-4 pt-3 border-t border-slate-200/70 text-[11px] text-slate-500">
                <span class="font-bold text-slate-700">Indikator Kunci:</span> Kepatuhan Upload Renaksi &ge; 95%, Capaian Output Terverifikasi.
              </div>
            </div>
          </div>

          <!-- Cascading Action Banner -->
          <div class="p-5 bg-gradient-to-r from-kemenkum-navy to-slate-900 text-white rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1">
              <h4 class="text-sm font-bold text-kemenkum-gold">Penyelarasan Cascading Target Perjanjian Kinerja</h4>
              <p class="text-xs text-slate-300">
                Setiap sasaran strategis Kantor Wilayah diturunkan menjadi target Indikator Kinerja Utama (IKU) dan Perjanjian Kinerja (PK) tahunan pada seluruh satuan kerja binaan.
              </p>
            </div>
            <button
              type="button"
              @click="setTab('iku')"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-[#F9C823] text-slate-950 hover:bg-amber-400 transition-colors shadow flex-shrink-0"
            >
              Lihat Capaian PK &rarr;
            </button>
          </div>
        </div>
      </div>

      <!-- TAB CONTENT: 1. CAPAIAN KINERJA ATAS PERJANJIAN KINERJA (Images 2, 3, 4) -->
      <div v-if="activeTab === 'iku' || activeTab === 'matriks'" class="space-y-4 animate-fade-in">
        <!-- Header & Top-Right Dropdown Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
              Capaian Kinerja atas Perjanjian Kinerja
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              Capaian per bulan, rekap triwulan, dan s.d. semester
            </p>
          </div>

          <!-- Top-Right Dropdowns (Year & Unit) -->
          <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <!-- Year Dropdown -->
            <div class="relative">
              <select
                v-model="selectedPkYear"
                class="text-xs font-semibold py-1.5 px-3 pr-8 rounded-lg border border-slate-300 bg-white shadow-xs appearance-none cursor-pointer focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option :value="2026">2026</option>
                <option :value="2025">2025</option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>

            <!-- Unit Dropdown -->
            <div class="relative">
              <select
                v-model="selectedPkUnit"
                class="text-xs font-semibold py-1.5 px-3 pr-8 rounded-lg border border-slate-300 bg-white shadow-xs appearance-none cursor-pointer focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
                <option v-for="opt in pkUnitFilterOptions" :key="opt.id" :value="opt.id">
                  {{ opt.label }}
                </option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>
          </div>
        </div>

        <!-- Matrix Table Container -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="text-white text-[11px] font-bold tracking-wider bg-[#091F4A]">
                  <th class="py-3 px-4 min-w-[200px] border-r border-white/10">Sasaran Kinerja</th>
                  <th class="py-3 px-4 min-w-[240px] border-r border-white/10">Indikator Kinerja Kegiatan</th>
                  <th class="py-3 px-3 text-center w-24 border-r border-white/10">Target</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Jan</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Feb</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Mar</th>
                  <!-- Rekap TW I (Orange Header) -->
                  <th class="py-3 px-3 bg-[#EA580C] text-white text-center min-w-[90px] border-r border-orange-700">Rekap TW I</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Apr</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Mei</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Jun</th>
                  <!-- Rekap TW II (Orange Header) -->
                  <th class="py-3 px-3 bg-[#EA580C] text-white text-center min-w-[90px] border-r border-orange-700">Rekap TW II</th>
                  <!-- s.d. Semester I (Yellow Header) -->
                  <th class="py-3 px-3 bg-[#FACC15] text-[#0F172A] font-black text-center min-w-[100px] border-r border-amber-300">s.d. Semester I</th>
                  <th class="py-3 px-2.5 text-center w-14 border-r border-white/10">Jul</th>
                  <th class="py-3 px-2.5 text-center w-14">Agu</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="grp in filteredPkGroups" :key="grp.unitId">
                  <!-- Dark Section Row for Unit -->
                  <tr>
                    <td colspan="14" class="bg-[#0F172A] text-white font-black py-2.5 px-4 text-xs tracking-wider uppercase">
                      {{ grp.unitName }}
                    </td>
                  </tr>

                  <!-- Data rows under unit -->
                  <tr
                    v-for="(row, rIdx) in grp.rows"
                    :key="rIdx"
                    class="border-b border-slate-100 hover:bg-slate-50/70 transition-colors"
                  >
                    <td class="py-3 px-4 text-slate-800 font-medium text-xs align-top border-r border-slate-100 leading-snug">
                      {{ row.sasaran }}
                    </td>
                    <td class="py-3 px-4 text-slate-700 text-xs align-top border-r border-slate-100 leading-snug">
                      {{ row.indikator }}
                    </td>
                    <td class="py-3 px-3 text-center font-mono font-bold text-slate-800 text-xs align-top border-r border-slate-100 whitespace-nowrap">
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
                    <!-- Column Tint: Rekap TW I -->
                    <td class="py-3 px-2.5 text-center font-mono font-bold text-slate-800 text-xs align-top bg-orange-50/70 border-r border-orange-100/80">
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
                    <!-- Column Tint: Rekap TW II -->
                    <td class="py-3 px-2.5 text-center font-mono font-bold text-slate-800 text-xs align-top bg-orange-50/70 border-r border-orange-100/80">
                      {{ row.rekapTw2 }}
                    </td>
                    <!-- Column Tint: s.d. Semester I -->
                    <td class="py-3 px-2.5 text-center font-mono font-bold text-slate-900 text-xs align-top bg-amber-50/80 border-r border-amber-100">
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
        <p class="text-xs text-slate-400 mt-2">
          Rekap triwulan dan s.d. semester dihitung otomatis atau ditetapkan oleh unit. Data diperbarui otomatis.
        </p>
      </div>

      <!-- TAB CONTENT: 3. PROGRES RENAKSI (Matches Screenshot) -->
      <div v-else-if="activeTab === 'renaksi'" class="space-y-6 animate-fade-in">
        <!-- Title, Subtitle, and View Mode Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
              Progres Renaksi &amp; Data Dukung
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              Rencana aksi dan pemenuhan data dukung per triwulan seluruh unit kerja Kanwil Kalsel
            </p>
          </div>

          <!-- View Mode Switcher (Kartu Unit vs Tabel Rekap) -->
          <div class="inline-flex rounded-lg border border-slate-200 bg-slate-100 p-0.5 text-xs font-semibold self-start sm:self-auto">
            <button
              type="button"
              @click="renaksiViewMode = 'cards'"
              class="px-3 py-1.5 rounded-md transition-all cursor-pointer inline-flex items-center gap-1.5"
              :class="renaksiViewMode === 'cards' ? 'bg-white text-[#0B2A6F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
              </svg>
              Kartu Unit
            </button>
            <button
              type="button"
              @click="renaksiViewMode = 'table'"
              class="px-3 py-1.5 rounded-md transition-all cursor-pointer inline-flex items-center gap-1.5"
              :class="renaksiViewMode === 'table' ? 'bg-white text-[#0B2A6F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Tabel Rekapitulasi
            </button>
          </div>
        </div>

        <!-- Period Tabs (Pills) & Deadline Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-2xs">
          <!-- Triwulan Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-thin">
            <button
              v-for="p in renaksiPeriodOptions"
              :key="p.id"
              type="button"
              class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap cursor-pointer border"
              :class="renaksiPeriod === p.id
                ? 'bg-[#0B2A6F] text-white border-[#0B2A6F] shadow-xs'
                : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
              @click="renaksiPeriod = p.id as any"
            >
              {{ p.label }}
            </button>
          </div>

          <!-- Deadline Note with Icon -->
          <div class="inline-flex items-center gap-1.5 text-xs text-slate-600 font-medium px-2.5 py-1 rounded-md bg-slate-50 border border-slate-200/70 self-start sm:self-auto">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ currentRenaksiDeadline }}</span>
          </div>
        </div>

        <!-- 5 Summary Metric Cards (Polished Executive Styling) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
          <!-- 1. Total Renaksi -->
          <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs flex flex-col justify-between border-l-4 border-l-[#0B2A6F]">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Renaksi</span>
              <span class="w-2 h-2 rounded-full bg-[#0B2A6F]" />
            </div>
            <div class="my-2">
              <span class="text-3xl font-black text-[#0B2A6F]">{{ renaksiSummaryTotals.total }}</span>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">6 Satuan Kerja</span>
          </div>

          <!-- 2. Sudah Diterima -->
          <div class="bg-white rounded-xl p-4 border border-emerald-200 shadow-2xs flex flex-col justify-between border-l-4 border-l-emerald-500 bg-emerald-50/20">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Sudah Diterima</span>
              <span class="w-2 h-2 rounded-full bg-emerald-500" />
            </div>
            <div class="my-2">
              <span class="text-3xl font-black text-emerald-700">{{ renaksiSummaryTotals.percentDiterima }}%</span>
            </div>
            <span class="text-[11px] text-emerald-700 font-semibold">{{ renaksiSummaryTotals.diterima }} data dukung disetujui</span>
          </div>

          <!-- 3. Menunggu Verifikasi -->
          <div class="bg-white rounded-xl p-4 border border-amber-200 shadow-2xs flex flex-col justify-between border-l-4 border-l-amber-500 bg-amber-50/20">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Menunggu Review</span>
              <span class="w-2 h-2 rounded-full bg-amber-500" />
            </div>
            <div class="my-2">
              <span class="text-3xl font-black text-amber-700">{{ renaksiSummaryTotals.menunggu }}</span>
            </div>
            <span class="text-[11px] text-amber-700 font-semibold">Menunggu verifikasi admin</span>
          </div>

          <!-- 4. Perlu Perbaikan -->
          <div class="bg-white rounded-xl p-4 border border-rose-200 shadow-2xs flex flex-col justify-between border-l-4 border-l-rose-500 bg-rose-50/20">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Perlu Perbaikan</span>
              <span class="w-2 h-2 rounded-full bg-rose-500" />
            </div>
            <div class="my-2">
              <span class="text-3xl font-black text-rose-700">{{ renaksiSummaryTotals.perbaikan }}</span>
            </div>
            <span class="text-[11px] text-rose-700 font-semibold">Perlu revisi berkas</span>
          </div>

          <!-- 5. Belum Ada Daduk -->
          <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs flex flex-col justify-between border-l-4 border-l-slate-400 col-span-2 sm:col-span-1 bg-slate-50/40">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Belum Ada Daduk</span>
              <span class="w-2 h-2 rounded-full bg-slate-400" />
            </div>
            <div class="my-2">
              <span class="text-3xl font-black text-slate-800">{{ renaksiSummaryTotals.belum }}</span>
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Belum diunggah satker</span>
          </div>
        </div>

        <!-- VIEW 1: Kartu Unit (Rich Administrative Panel with Real SK & IKK) -->
        <div v-if="renaksiViewMode === 'cards'" class="space-y-3 pt-1">
          <div
            v-for="u in renaksiUnitsDetailed"
            :key="u.id"
            class="bg-white rounded-xl border border-slate-200 shadow-2xs transition-all hover:border-slate-300 overflow-hidden"
          >
            <!-- Header Bar -->
            <div
              role="button"
              tabindex="0"
              @click="toggleRenaksiUnit(u.id)"
              class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 cursor-pointer select-none hover:bg-slate-50/50 transition-colors"
            >
              <!-- Left: Unit Badge & Name -->
              <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider border" :class="u.badgeClass">
                  {{ u.kode }}
                </span>
                <div>
                  <h3 class="font-bold text-sm sm:text-base text-slate-900 leading-snug">
                    {{ u.name }}
                  </h3>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ u.skList.length }} Sasaran Kinerja &bull; {{ u.totalRenaksi }} Rencana Aksi
                  </p>
                </div>
              </div>

              <!-- Center/Right: Progress & Status Badges -->
              <div class="flex items-center gap-4 self-end md:self-auto">
                <!-- Mini Progress Bar -->
                <div class="flex items-center gap-2.5">
                  <div class="w-24 sm:w-32 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                    <div
                      class="h-full rounded-full transition-all duration-300"
                      :class="u.percent > 0 ? 'bg-emerald-500' : 'bg-slate-300'"
                      :style="{ width: `${u.percent}%` }"
                    />
                  </div>
                  <span class="text-xs font-bold text-slate-700 w-8 text-right">{{ u.percent }}%</span>
                </div>

                <!-- Status Pills -->
                <div class="hidden sm:flex items-center gap-1.5 text-[11px]">
                  <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                    {{ u.diterima }} Diterima
                  </span>
                  <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-200">
                    {{ u.menunggu }} Review
                  </span>
                  <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold border border-slate-200">
                    {{ u.belum }} Belum
                  </span>
                </div>

                <!-- Expand Chevron Icon -->
                <div class="text-slate-400 p-1">
                  <svg
                    class="w-4 h-4 transition-transform duration-200"
                    :class="{ 'rotate-180': expandedRenaksiUnit === u.id }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
            </div>

            <!-- Expanded Body: Real Sasaran Kinerja & IKK Breakdown (NO nested card) -->
            <div
              v-if="expandedRenaksiUnit === u.id"
              class="px-5 pb-5 pt-3 border-t border-slate-100 bg-slate-50/50 space-y-3 text-xs"
            >
              <div class="flex items-center justify-between pb-1">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                  Rincian Sasaran Kinerja &amp; Indikator Kegiatan
                </span>
                <span class="text-[11px] text-slate-400">
                  Target Triwulan IV 2026
                </span>
              </div>

              <!-- List of SK & IKK -->
              <div class="space-y-2.5">
                <div
                  v-for="sk in u.skList"
                  :key="sk.skKode"
                  class="p-3 rounded-lg border border-slate-200 bg-white space-y-2"
                >
                  <div class="flex items-start gap-2">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#0B2A6F] text-white flex-shrink-0">
                      {{ sk.skKode }}
                    </span>
                    <p class="font-bold text-slate-800 leading-snug">
                      {{ sk.skName }}
                    </p>
                  </div>

                  <div class="space-y-1.5 pl-3 border-l-2 border-slate-200 ml-1">
                    <div
                      v-for="ikk in sk.ikkList"
                      :key="ikk.ikkKode"
                      class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-[11px]"
                    >
                      <div class="space-x-1">
                        <span class="font-bold text-blue-900">{{ ikk.ikkKode }}:</span>
                        <span class="text-slate-700">{{ ikk.ikkName }}</span>
                      </div>
                      <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 self-start sm:self-auto whitespace-nowrap">
                        {{ ikk.status }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- VIEW 2: Tabel Rekapitulasi Data Dukung (Official Administrative Matrix) -->
        <div v-else class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
          <div class="table-container overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
                  <th class="py-3 px-4 w-12 text-center border-r border-white/10">No</th>
                  <th class="py-3 px-4 border-r border-white/10">Satuan Kerja</th>
                  <th class="py-3 px-4 text-center w-28 border-r border-white/10">Total Renaksi</th>
                  <th class="py-3 px-4 text-center w-28 border-r border-white/10">Belum Ada</th>
                  <th class="py-3 px-4 text-center w-28 border-r border-white/10">Menunggu</th>
                  <th class="py-3 px-4 text-center w-28 border-r border-white/10">Perbaikan</th>
                  <th class="py-3 px-4 text-center w-28 border-r border-white/10">Diterima</th>
                  <th class="py-3 px-4 w-36">Progres Daduk</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-800">
                <tr
                  v-for="(u, idx) in renaksiUnitsDetailed"
                  :key="u.id"
                  class="hover:bg-slate-50/70 transition-colors"
                >
                  <td class="py-3 px-4 text-center text-slate-500 font-medium">
                    {{ idx + 1 }}
                  </td>
                  <td class="py-3 px-4 font-bold text-slate-900 flex items-center gap-2.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="u.badgeClass">
                      {{ u.kode }}
                    </span>
                    <span>{{ u.name }}</span>
                  </td>
                  <td class="py-3 px-4 text-center font-bold text-slate-800">
                    {{ u.totalRenaksi }}
                  </td>
                  <td class="py-3 px-4 text-center text-slate-600 font-semibold">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                      {{ u.belum }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center font-semibold">
                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                      {{ u.menunggu }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center font-semibold">
                    <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700">
                      {{ u.perbaikan }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center font-semibold">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                      {{ u.diterima }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                        <div
                          class="h-full rounded-full transition-all duration-300"
                          :class="u.percent > 0 ? 'bg-emerald-500' : 'bg-slate-300'"
                          :style="{ width: `${u.percent}%` }"
                        />
                      </div>
                      <span class="text-xs font-bold text-slate-700 w-7 text-right">{{ u.percent }}%</span>
                    </div>
                  </td>
                </tr>

                <!-- Summary Total Row -->
                <tr class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                  <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-[11px]">
                    Total Keseluruhan:
                  </td>
                  <td class="py-3 px-4 text-center text-blue-900 font-black">
                    {{ renaksiSummaryTotals.total }}
                  </td>
                  <td class="py-3 px-4 text-center text-slate-600">
                    {{ renaksiSummaryTotals.belum }}
                  </td>
                  <td class="py-3 px-4 text-center text-amber-700">
                    {{ renaksiSummaryTotals.menunggu }}
                  </td>
                  <td class="py-3 px-4 text-center text-rose-700">
                    {{ renaksiSummaryTotals.perbaikan }}
                  </td>
                  <td class="py-3 px-4 text-center text-emerald-700">
                    {{ renaksiSummaryTotals.diterima }}
                  </td>
                  <td class="py-3 px-4 font-black text-emerald-700">
                    {{ renaksiSummaryTotals.percentDiterima }}%
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footnote -->
        <p class="text-xs text-slate-400 pt-2 flex items-center gap-2">
          <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" />
          <span>Hijau = diterima, kuning = menunggu verifikasi, merah = perlu perbaikan, abu-abu = belum diunggah. Klik nama unit untuk melihat rincian sasaran kinerja &amp; indikator kegiatan.</span>
        </p>
      </div>

      <!-- TAB CONTENT: 4. PROGRES LAPORAN (Matches Image 1) -->
      <div v-else-if="activeTab === 'laporan'" class="space-y-6 animate-fade-in">
        <!-- Header with Title & Year Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
              Progres Laporan
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              Kepatuhan pengumpulan laporan per unit, tahun {{ laporanSelectedYear }}
            </p>
          </div>
          <div class="self-start sm:self-auto">
            <select
              v-model="laporanSelectedYear"
              class="border border-slate-200 rounded-xl px-3.5 py-1.5 text-xs sm:text-sm font-semibold bg-white text-slate-700 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="y in laporanYearOptions" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
        </div>

        <!-- Period Mode Switcher (Bulanan / Triwulan Pills) -->
        <div class="flex items-center gap-2 sm:gap-2.5">
          <button
            type="button"
            class="px-5 py-2 rounded-full text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer border"
            :class="laporanPeriodMode === 'bulanan'
              ? 'bg-[#1D4ED8] text-white border-[#1D4ED8] shadow-sm'
              : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
            @click="laporanPeriodMode = 'bulanan'"
          >
            Laporan Bulanan
          </button>
          <button
            type="button"
            class="px-5 py-2 rounded-full text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer border"
            :class="laporanPeriodMode === 'triwulan'
              ? 'bg-[#1D4ED8] text-white border-[#1D4ED8] shadow-sm'
              : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'"
            @click="laporanPeriodMode = 'triwulan'"
          >
            Laporan Triwulan
          </button>
        </div>

        <!-- Summary Metric Horizontal Card -->
        <div class="space-y-2">
          <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-7">
            <div class="flex flex-wrap items-center gap-8 lg:gap-14">
              <!-- Circular Progress Gauge on Left -->
              <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                  <path
                    class="text-slate-100"
                    stroke-width="3.5"
                    stroke="currentColor"
                    fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                  <path
                    class="text-blue-600 transition-all duration-500"
                    stroke-width="3.5"
                    stroke-dasharray="0, 100"
                    stroke-linecap="round"
                    stroke="currentColor"
                    fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                  <span class="text-xs sm:text-sm font-black text-[#0B2A6F]">0%</span>
                  <span class="text-[9px] text-slate-400 font-medium leading-none mt-0.5">diterima</span>
                </div>
              </div>

              <!-- Metric Items -->
              <div class="flex flex-col">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
                <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Diterima</span>
              </div>

              <div class="flex flex-col">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
                <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Menunggu verifikasi</span>
              </div>

              <div class="flex flex-col">
                <span class="text-3xl sm:text-4xl font-black text-[#0B2A6F]">0</span>
                <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Perlu perbaikan</span>
              </div>

              <div class="flex flex-col">
                <span class="text-3xl sm:text-4xl font-black text-rose-600">
                  {{ laporanPeriodMode === 'bulanan' ? 54 : 18 }}
                </span>
                <span class="text-xs sm:text-sm font-medium text-slate-600 mt-1">Terlambat</span>
              </div>
            </div>
          </div>
          <!-- Note under the card -->
          <p class="text-xs text-slate-500">
            Dihitung dari {{ laporanPeriodMode === 'bulanan' ? 54 : 18 }} laporan yang sudah jatuh tempo atau sudah dikirim.
          </p>
        </div>

        <!-- Table View -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
          <div class="table-container overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider border-b border-white/10">
                  <th class="py-3 px-4 min-w-[220px] border-r border-white/10">UNIT</th>
                  <template v-if="laporanPeriodMode === 'bulanan'">
                    <th
                      v-for="c in laporanBulananCols"
                      :key="c.key"
                      class="py-2.5 px-2 text-center min-w-[48px] border-r border-white/10"
                    >
                      <span class="block font-bold text-white text-xs">{{ c.label }}</span>
                      <span class="block text-[10px] text-slate-300 font-mono font-normal">{{ c.date }}</span>
                    </th>
                  </template>
                  <template v-else>
                    <th
                      v-for="c in laporanTriwulanCols"
                      :key="c.key"
                      class="py-2.5 px-3 text-center min-w-[70px] border-r border-white/10"
                    >
                      <span class="block font-bold text-white text-xs">{{ c.label }}</span>
                      <span class="block text-[10px] text-slate-300 font-mono font-normal">{{ c.date }}</span>
                    </th>
                  </template>
                  <th class="py-3 px-4 min-w-[140px]">KEPATUHAN</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="row in laporanUnitsData"
                  :key="row.unitKode"
                  class="hover:bg-slate-50/70 transition-colors"
                >
                  <td class="py-3 px-4 align-middle">
                    <span class="font-bold text-slate-900 text-sm block leading-tight">{{ row.unitKode }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">{{ row.unitName }}</span>
                  </td>

                  <!-- Bulanan Cells -->
                  <template v-if="laporanPeriodMode === 'bulanan'">
                    <td
                      v-for="c in laporanBulananCols"
                      :key="c.key"
                      class="py-3 px-2 text-center align-middle"
                    >
                      <div
                        v-if="row.bulananStatus[c.key] === 'terlambat'"
                        class="w-6 h-6 rounded-md border border-rose-300 bg-rose-50/60 text-rose-500 flex items-center justify-center mx-auto text-xs font-bold shadow-2xs"
                        title="Terlambat"
                      >
                        &times;
                      </div>
                      <span
                        v-else-if="row.bulananStatus[c.key] === 'belum_waktunya'"
                        class="text-slate-300 font-bold text-sm block"
                        title="Belum waktunya"
                      >
                        &ndash;
                      </span>
                      <span v-else class="text-slate-300 font-bold">&ndash;</span>
                    </td>
                  </template>

                  <!-- Triwulanan Cells -->
                  <template v-else>
                    <td
                      v-for="c in laporanTriwulanCols"
                      :key="c.key"
                      class="py-3 px-3 text-center align-middle"
                    >
                      <div
                        v-if="row.triwulanStatus[c.key] === 'terlambat'"
                        class="w-6 h-6 rounded-md border border-rose-300 bg-rose-50/60 text-rose-500 flex items-center justify-center mx-auto text-xs font-bold shadow-2xs"
                        title="Terlambat"
                      >
                        &times;
                      </div>
                      <span
                        v-else-if="row.triwulanStatus[c.key] === 'belum_waktunya'"
                        class="text-slate-300 font-bold text-sm block"
                        title="Belum waktunya"
                      >
                        &ndash;
                      </span>
                      <span v-else class="text-slate-300 font-bold">&ndash;</span>
                    </td>
                  </template>

                  <!-- Kepatuhan Bar Column -->
                  <td class="py-3 px-4 align-middle">
                    <div class="flex items-center gap-2.5">
                      <div class="w-20 sm:w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div
                          class="h-full bg-emerald-500 rounded-full transition-all"
                          :style="{ width: row.kepatuhan + '%' }"
                        />
                      </div>
                      <span class="text-xs font-bold text-slate-700 font-mono">{{ row.kepatuhan }}%</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Legend (Bottom) -->
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-600 pt-1">
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold">&check;</span>
            <span>Diterima</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-amber-100 text-amber-700 text-xs font-bold">&bull;</span>
            <span>Menunggu verifikasi</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-orange-100 text-orange-700 text-[10px] font-bold">!</span>
            <span>Perlu perbaikan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded border border-rose-300 bg-rose-50 text-rose-600 text-[10px] font-bold">&times;</span>
            <span>Terlambat</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded bg-blue-100 text-blue-700 text-[10px] font-bold">T</span>
            <span>Waktunya dikumpulkan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400 font-bold text-sm leading-none">&ndash;</span>
            <span>Belum waktunya</span>
          </div>
        </div>

        <!-- Footnote -->
        <p class="text-xs text-slate-400">
          Halaman ini hanya menampilkan status pengumpulan. Isi laporan tidak dipublikasikan. Data diperbarui otomatis.
        </p>
      </div>

      <!-- TAB CONTENT: 5. IKPA (Matches Image 2) -->
      <div v-else-if="activeTab === 'ikpa' || activeTab === 'sakip'" class="space-y-6 animate-fade-in">
        <!-- Header with Title & Period Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0B2A6F] tracking-tight">
              Indikator Kinerja Pelaksanaan Anggaran (IKPA)
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              Nilai per satuan kerja, {{ selectedIkpaPeriod }}
            </p>
          </div>
          <div class="self-start sm:self-auto">
            <select
              v-model="selectedIkpaPeriod"
              class="border border-slate-200 rounded-xl px-3.5 py-1.5 text-xs sm:text-sm font-semibold bg-white text-slate-700 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="opt in ikpaPeriodOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
          </div>
        </div>

        <!-- Multi-tier Header IKPA Table -->
        <div class="bg-white rounded-none border border-slate-300 shadow-xs overflow-hidden">
          <div class="table-container overflow-x-auto">
            <table class="w-full text-center border-collapse text-xs">
              <thead>
                <!-- Level 1 Header (Dark Navy #091F4A with White Text) -->
                <tr class="bg-[#091F4A] text-white text-[11px] font-bold uppercase tracking-wider">
                  <th rowspan="2" class="py-3 px-2 border border-white/20 w-12 align-middle">
                    No
                  </th>
                  <th rowspan="2" class="py-3 px-4 border border-white/20 text-left min-w-[130px] align-middle">
                    Nama Satker/DIPA
                  </th>
                  <th rowspan="2" class="py-3 px-3 border border-white/20 w-24 align-middle">
                    Kode Satker
                  </th>
                  <th colspan="2" class="py-2.5 px-3 border border-white/20 align-middle">
                    Kualitas Perencanaan Anggaran
                  </th>
                  <th colspan="4" class="py-2.5 px-3 border border-white/20 align-middle">
                    Kualitas Pelaksanaan Anggaran
                  </th>
                  <th colspan="1" class="py-2.5 px-3 border border-white/20 align-middle">
                    Kualitas Hasil Pelaksanaan Anggaran
                  </th>
                  <th rowspan="2" class="py-3 px-4 border border-white/20 min-w-[100px] align-middle">
                    Nilai Akhir<br />September
                  </th>
                </tr>
                <!-- Level 2 Sub-headers (Dark Navy #0C2B64 with White Text) -->
                <tr class="bg-[#0C2B64] text-white text-[11px] font-bold uppercase tracking-wider">
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[75px] align-middle">
                    Revisi<br />DIPA
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                    Deviasi Halaman<br />III DIPA
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                    Penyerapan<br />Anggaran
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[85px] align-middle">
                    Belanja<br />Kontraktual
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[85px] align-middle">
                    Penyelesaian<br />Tagihan
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[95px] align-middle">
                    Pengelolaan UP<br />dan TUP
                  </th>
                  <th class="py-2.5 px-2.5 border border-white/20 min-w-[90px] align-middle">
                    Capaian Output
                  </th>
                </tr>
              </thead>
              <tbody>
                <!-- 6 Satker Rows -->
                <tr
                  v-for="row in ikpaTableRows"
                  :key="row.no"
                  class="transition-colors hover:brightness-95"
                >
                  <td class="py-3 px-2 border border-slate-300 text-slate-700 font-mono text-xs bg-white">
                    {{ row.no }}
                  </td>
                  <td class="py-3 px-4 border border-slate-300 text-left font-bold text-slate-800 text-xs bg-white">
                    {{ row.satker }}
                  </td>
                  <td class="py-3 px-3 border border-slate-300 text-slate-600 font-mono text-xs bg-white">
                    {{ row.kode }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.revisiDipa)">
                    {{ formatIkpaVal(row.revisiDipa) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.deviasiHal3)">
                    {{ formatIkpaVal(row.deviasiHal3) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.penyerapanAnggaran)">
                    {{ formatIkpaVal(row.penyerapanAnggaran) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.belanjaKontraktual)">
                    {{ formatIkpaVal(row.belanjaKontraktual) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.penyelesaianTagihan)">
                    {{ formatIkpaVal(row.penyelesaianTagihan) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.pengelolaanUpTup)">
                    {{ formatIkpaVal(row.pengelolaanUpTup) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs" :class="getIkpaCellClass(row.capaianOutput)">
                    {{ formatIkpaVal(row.capaianOutput) }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono text-xs font-bold" :class="getIkpaCellClass(row.nilaiAkhir)">
                    {{ formatIkpaVal(row.nilaiAkhir) }}
                  </td>
                </tr>

                <!-- Summary Average Row -->
                <tr class="bg-[#DCE8F3] text-slate-900 font-bold text-xs">
                  <td colspan="3" class="py-3 px-4 border border-slate-300 text-center font-bold">
                    {{ ikpaSummaryRow.label }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.revisiDipa }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.deviasiHal3 }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.penyerapanAnggaran }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.belanjaKontraktual }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.penyelesaianTagihan }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.pengelolaanUpTup }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.capaianOutput }}
                  </td>
                  <td class="py-3 px-2 border border-slate-300 font-mono font-bold">
                    {{ ikpaSummaryRow.nilaiAkhir }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- IKPA Color Legend -->
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-700 pt-1">
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#DCE8F7] inline-block" />
            <span>Sempurna (100)</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#98D65F] inline-block" />
            <span>Memenuhi Target (96 s.d. kurang dari 100)</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-xs border border-slate-300 bg-[#FEF08A] inline-block" />
            <span>Belum Memenuhi Target (kurang dari 96)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Transparency Legal Statement (Borderless Clean Footer Text) -->
    <div class="pt-2 pb-6 text-center text-xs text-slate-400 leading-relaxed max-w-2xl mx-auto">
      Portal Transparansi Kinerja Kanwil Kementerian Hukum Kalimantan Selatan &bull; UU No. 14 Tahun 2008 &amp; PermenPAN-RB tentang SAKIP.
    </div>
  </div>
</template>