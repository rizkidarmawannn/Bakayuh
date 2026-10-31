<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Tab state: 'lihat' | 'kelola'
const activeTab = ref<'lihat' | 'kelola'>(route.path.includes('/kelola') ? 'kelola' : 'lihat')

watch(
  () => route.path,
  (newPath) => {
    activeTab.value = newPath.includes('/kelola') ? 'kelola' : 'lihat'
    if (activeTab.value === 'lihat') {
      nextTick(() => {
        updateFitZoom()
      })
    }
  }
)

function switchTab(tab: 'lihat' | 'kelola') {
  activeTab.value = tab
  if (tab === 'kelola') {
    router.push('/pohon-kinerja/kelola')
  } else {
    router.push('/pohon-kinerja')
  }
}

// Zoom control state for Lihat Pohon
const diagramCardRef = ref<HTMLElement | null>(null)
const fitZoom = ref<number>(0.55)
const zoomMode = ref<'fit' | number>('fit')
let resizeObserver: ResizeObserver | null = null

// Unscaled base grid width = 2716px (sum of 42 minmax columns + 41 column gaps)
const BASE_GRID_WIDTH = 2716

function updateFitZoom() {
  if (typeof window === 'undefined') return
  if (diagramCardRef.value) {
    // clientWidth of card minus left & right padding (24px)
    const available = diagramCardRef.value.clientWidth - 24
    if (available > 100) {
      const scale = available / BASE_GRID_WIDTH
      // Clamp scale to a reasonable range
      fitZoom.value = Math.max(0.2, Math.min(scale, 1.05))
    }
  } else {
    const w = window.innerWidth
    fitZoom.value = Math.max(0.2, Math.min((w - 280) / BASE_GRID_WIDTH, 1.0))
  }
}

const currentZoom = computed(() => {
  if (zoomMode.value === 'fit') {
    return fitZoom.value
  }
  return zoomMode.value
})

function setZoom(level: 'fit' | number) {
  zoomMode.value = level
  if (level === 'fit') {
    updateFitZoom()
  }
}

onMounted(() => {
  loadKelolaData()
  nextTick(() => {
    updateFitZoom()
  })
  window.addEventListener('resize', updateFitZoom)
  if (typeof window !== 'undefined' && 'ResizeObserver' in window) {
    resizeObserver = new ResizeObserver(() => {
      if (zoomMode.value === 'fit') {
        updateFitZoom()
      }
    })
    if (diagramCardRef.value) {
      resizeObserver.observe(diagramCardRef.value)
    }
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateFitZoom)
  }
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  }
})

const wrapStyle = computed(() => ({
  zoom: currentZoom.value,
}))

// IKK Database & Details Modal
interface IkkDetailInfo {
  id: string
  code: string
  name: string
  unit: string
  eselonI: string
  skCode: string
  skName: string
  spCode: string
  spName: string
  ssCode: string
  ssName: string
  target: string
  satuan: string
  pj: string
}

const ikkDatabase: Record<string, IkkDetailInfo> = {
  'IK-4d7f831c': {
    id: 'IK-4d7f831c',
    code: 'IKK 7110.1.1',
    name: 'Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
    unit: 'Administrasi Hukum Umum',
    eselonI: 'Ditjen Administrasi Hukum Umum',
    skCode: 'SK 7110.1',
    skName: 'Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah',
    spCode: 'SP-1',
    spName: 'Terwujudnya Penegakan Hukum AHU yang Profesional',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '85',
    satuan: 'Indeks / Persentase',
    pj: 'Subbidang Pelayanan AHU',
  },
  'IK-e1d8dd50': {
    id: 'IK-e1d8dd50',
    code: 'IKK 7110.2.1',
    name: 'Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah',
    unit: 'Administrasi Hukum Umum',
    eselonI: 'Ditjen Administrasi Hukum Umum',
    skCode: 'SK 7110.2',
    skName: 'Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah',
    spCode: 'SP-2',
    spName: 'Meningkatnya Kepuasan Terhadap Layanan AHU',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%)',
    pj: 'MPWN / MPD Notaris & Subbid AHU',
  },
  'IK-90eda68f': {
    id: 'IK-90eda68f',
    code: 'IKK 7121.1.1',
    name: 'Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan',
    unit: 'Kekayaan Intelektual',
    eselonI: 'Ditjen Kekayaan Intelektual',
    skCode: 'SK 7121.1',
    skName: 'Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah',
    spCode: 'SP-6',
    spName: 'Meningkatnya Kepuasan Pelindungan dan pemanfaatan KI',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '90',
    satuan: 'Nilai Kepatuhan',
    pj: 'Subbidang Pelayanan KI',
  },
  'IK-f2e9aa50': {
    id: 'IK-f2e9aa50',
    code: 'IKK 7121.2.1',
    name: 'Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani',
    unit: 'Kekayaan Intelektual',
    eselonI: 'Ditjen Kekayaan Intelektual',
    skCode: 'SK 7121.2',
    skName: 'Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan',
    spCode: 'SP-5',
    spName: 'Terwujudnya Penegakan Hukum Pelindungan Kekayaan Intelektual yang profesional',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%)',
    pj: 'PPNS KI & Subbidang Pelayanan KI',
  },
  'IK-198021bd': {
    id: 'IK-198021bd',
    code: 'IKK 3.1',
    name: 'Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
    unit: 'Kekayaan Intelektual',
    eselonI: 'Ditjen Kekayaan Intelektual',
    skCode: 'SK 3',
    skName: 'Meningkatnya Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah',
    spCode: 'SP-7',
    spName: 'Meningkatnya Kesadaran Masyarakat Indonesia terhadap Kekayaan Intelektual',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: 'Level 3',
    satuan: 'Tingkat Maturitas',
    pj: 'Subbidang Pelayanan KI',
  },
  'IK-17e887b8': {
    id: 'IK-17e887b8',
    code: 'IKK 7129.1.1',
    name: 'Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
    unit: 'Peraturan Perundang-Undangan',
    eselonI: 'Ditjen Peraturan Perundang-Undangan',
    skCode: 'SK 7129.1',
    skName: 'Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
    spCode: 'SP-9',
    spName: 'Indeks Kualitas Peraturan Perundang-undangan',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%)',
    pj: 'Subbidang Fasilitasi Pembentukan Produk Hukum Daerah',
  },
  'IK-b50bd4f0': {
    id: 'IK-b50bd4f0',
    code: 'IKK 7129.1.2',
    name: 'Persentase Perancangan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum',
    unit: 'Peraturan Perundang-Undangan',
    eselonI: 'Ditjen Peraturan Perundang-Undangan',
    skCode: 'SK 7129.1',
    skName: 'Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah',
    spCode: 'SP-9',
    spName: 'Indeks Kualitas Peraturan Perundang-undangan',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%)',
    pj: 'Jabatan Fungsional Perancang Peraturan Perundang-undangan',
  },
  'IK-5d2c6e21': {
    id: 'IK-5d2c6e21',
    code: 'IKK 7133.1.1',
    name: 'Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda di Wilayah Kerja Kanwil',
    unit: 'Badan Pembinaan Hukum Nasional',
    eselonI: 'Badan Pembinaan Hukum Nasional',
    skCode: 'SK 7133.1',
    skName: 'Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda',
    spCode: 'SP-13',
    spName: 'Terwujudnya Manfaat dan Dampak Peraturan Perundang-undangan terhadap Pembangunan Hukum Nasional',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '85',
    satuan: 'Persen (%)',
    pj: 'JF Analis Hukum & Subbidang Penyuluhan Bantuan Hukum',
  },
  'IK-5b0dc8bd': {
    id: 'IK-5b0dc8bd',
    code: 'IKK 7136.1.1',
    name: 'Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi',
    unit: 'Badan Pembinaan Hukum Nasional',
    eselonI: 'Badan Pembinaan Hukum Nasional',
    skCode: 'SK 7136.1',
    skName: 'Meningkatnya Layanan Bantuan Hukum di Wilayah',
    spCode: 'SP-10',
    spName: 'Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%)',
    pj: 'Panitia Pengawas Daerah Bantuan Hukum (Panwasda)',
  },
  'IK-09dd1d45': {
    id: 'IK-09dd1d45',
    code: 'IKK 7136.2.1',
    name: 'Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah',
    unit: 'Badan Pembinaan Hukum Nasional',
    eselonI: 'Badan Pembinaan Hukum Nasional',
    skCode: 'SK 7136.2',
    skName: 'Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas',
    spCode: 'SP-10',
    spName: 'Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '95',
    satuan: 'Persen JDIH Terintegrasi',
    pj: 'Tim Pengelola JDIH Wilayah Kalsel',
  },
  'IK-83004743': {
    id: 'IK-83004743',
    code: 'IKK 7136.3.1',
    name: 'Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa/kelurahan',
    unit: 'Badan Pembinaan Hukum Nasional',
    eselonI: 'Badan Pembinaan Hukum Nasional',
    skCode: 'SK 7136.3',
    skName: 'Meningkatnya Pos Bantuan Hukum di Wilayah',
    spCode: 'SP-11',
    spName: 'Meningkatnya Kepatuhan Hukum Masyarakat Indonesia',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '60',
    satuan: 'Persen (%) Desa Sadar Hukum',
    pj: 'Subbidang Penyuluhan Bantuan Hukum',
  },
  'IK-486e1417': {
    id: 'IK-486e1417',
    code: 'IKK 7143.1.1',
    name: 'Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah',
    unit: 'Badan Strategi Kebijakan',
    eselonI: 'Badan Strategi Kebijakan Hukum',
    skCode: 'SK 7143.1',
    skName: 'Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah',
    spCode: 'SP-15',
    spName: 'Meningkatnya Kualitas Kebijakan Kementerian Hukum',
    ssCode: 'SS1',
    ssName: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    target: '100',
    satuan: 'Persen (%) Rekomendasi',
    pj: 'Bidang HAM & Analis Kebijakan',
  },
  'IK-6defb0d6': {
    id: 'IK-6defb0d6',
    code: 'IKK 7099.1.1',
    name: 'Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK',
    unit: 'Bagian Tata Usaha dan Umum',
    eselonI: 'Sekretariat Jenderal',
    skCode: 'SK 7099.1',
    skName: 'Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK',
    spCode: 'SP-17',
    spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ssCode: 'SS2',
    ssName: 'Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi',
    target: '100',
    satuan: 'Persen (%) TLRHP',
    pj: 'Bagian Program dan Humas / Subbag Keuangan',
  },
  'IK-30accf02': {
    id: 'IK-30accf02',
    code: 'IKK 7099.2.1',
    name: 'Indeks kepuasan terhadap layanan dukungan manajemen',
    unit: 'Bagian Tata Usaha dan Umum',
    eselonI: 'Sekretariat Jenderal',
    skCode: 'SK 7099.2',
    skName: 'Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen',
    spCode: 'SP-17',
    spName: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ssCode: 'SS2',
    ssName: 'Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi',
    target: '3.60',
    satuan: 'Skala 4.00',
    pj: 'Bagian Tata Usaha dan Umum',
  },
}

const showIkkModal = ref(false)
const selectedIkk = ref<IkkDetailInfo | null>(null)

function openIkk(id: string) {
  const item = ikkDatabase[id]
  if (item) {
    selectedIkk.value = item
    showIkkModal.value = true
  }
}

function closeIkkModal() {
  showIkkModal.value = false
  selectedIkk.value = null
}

function goToRenaksi() {
  closeIkkModal()
  router.push('/sasaran-renaksi')
}

// ==========================================
// KELOLA POHON KINERJA (MECHANISM & DATA)
// ==========================================

interface IkssItem {
  id: string
  code: string
  name: string
}

interface SsItem {
  id: string
  code: string
  name: string
  ikssList: IkssItem[]
}

interface UnitItem {
  id: string
  code: string
  pusatName: string
  kanwilName: string
  ikssCode: string
  urutan: number
}

interface IspItem {
  id: string
  code: string
  name: string
}

interface SpItem {
  id: string
  unitCode: string
  code: string
  name: string
  ispList: IspItem[]
}

const ssData = ref<SsItem[]>([
  {
    id: 'ss1',
    code: 'SS1',
    name: 'Terwujudnya Kepastian hukum di seluruh wilayah NKRI',
    ikssList: [
      { id: 'ikss1_1', code: 'IKSS 1.1', name: 'Indeks penegakan dan pelayanan Hukum' },
      { id: 'ikss1_2', code: 'IKSS 1.2', name: 'Indeks Budaya Hukum (IPH)' },
      { id: 'ikss1_3', code: 'IKSS 1.3', name: 'Indeks Menteri Hukum (IPH)' },
    ],
  },
  {
    id: 'ss2',
    code: 'SS2',
    name: 'Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi',
    ikssList: [
      { id: 'ikss2_1', code: 'IKSS 2.1', name: 'Indeks Reformasi Birokrasi Kementerian Hukum' },
    ],
  },
])

const unitData = ref<UnitItem[]>([
  { id: 'u_setjen', code: 'Setjen', pusatName: 'Sekretariat Jenderal', kanwilName: 'Bagian Tata Usaha dan Umum', ikssCode: 'IKSS 2.1', urutan: 6 },
  { id: 'u_ahu', code: 'AHU', pusatName: 'Ditjen Administrasi Hukum Umum', kanwilName: 'Administrasi Hukum Umum', ikssCode: 'IKSS 1.1', urutan: 1 },
  { id: 'u_ki', code: 'KI', pusatName: 'Ditjen Kekayaan Intelektual', kanwilName: 'Kekayaan Intelektual', ikssCode: 'IKSS 1.1', urutan: 2 },
  { id: 'u_pp', code: 'PP', pusatName: 'Ditjen Peraturan Perundang-Undangan', kanwilName: 'Peraturan Perundang-Undangan', ikssCode: 'IKSS 1.1', urutan: 3 },
  { id: 'u_bphn', code: 'BPHN', pusatName: 'Badan Pembinaan Hukum Nasional', kanwilName: 'Badan Pembinaan Hukum Nasional', ikssCode: 'IKSS 1.2', urutan: 4 },
  { id: 'u_bsk', code: 'BSK', pusatName: 'Badan Strategi Kebijakan Hukum', kanwilName: 'Badan Strategi Kebijakan', ikssCode: 'IKSS 1.3', urutan: 5 },
])

const spData = ref<SpItem[]>([
  // AHU
  {
    id: 'sp1',
    unitCode: 'AHU',
    code: 'SP-1',
    name: 'Terwujudnya Penegakan Hukum AHU yang Profesional',
    ispList: [{ id: 'isp1', code: 'ISP-1', name: 'Indeks Penegakan Hukum AHU' }],
  },
  {
    id: 'sp2',
    unitCode: 'AHU',
    code: 'SP-2',
    name: 'Meningkatnya Kepuasan Terhadap Layanan AHU',
    ispList: [{ id: 'isp2', code: 'ISP-2', name: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan AHU' }],
  },
  {
    id: 'sp3',
    unitCode: 'AHU',
    code: 'SP-3',
    name: 'Meningkatnya Kemudahan Berusaha pada Layanan Badan Usaha dan Keperdataan',
    ispList: [{ id: 'isp3', code: 'ISP-3', name: 'Indeks kemudahan berusaha pada layanan Badan Usaha dan Keperdataan' }],
  },
  {
    id: 'sp4',
    unitCode: 'AHU',
    code: 'SP-4',
    name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ispList: [{ id: 'isp4', code: 'ISP-4', name: 'Indeks Reformasi Birokrasi Kementerian Hukum' }],
  },
  // KI
  {
    id: 'sp5',
    unitCode: 'KI',
    code: 'SP-5',
    name: 'Terwujudnya Penegakan Hukum Pelindungan Kekayaan Intelektual yang profesional',
    ispList: [{ id: 'isp5', code: 'ISP-5', name: 'Indeks Penegakan Hukum Pelindungan Kekayaan Intelektual' }],
  },
  {
    id: 'sp6',
    unitCode: 'KI',
    code: 'SP-6',
    name: 'Meningkatnya Kepuasan Pelindungan dan pemanfaatan KI',
    ispList: [{ id: 'isp6', code: 'ISP-6', name: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pelindungan dan Pemanfaatan KI' }],
  },
  {
    id: 'sp7',
    unitCode: 'KI',
    code: 'SP-7',
    name: 'Meningkatnya Kesadaran Masyarakat Indonesia terhadap Kekayaan Intelektual',
    ispList: [{ id: 'isp7', code: 'ISP-7', name: 'Indeks Kesadaran Masyarakat terhadap Kekayaan Intelektual' }],
  },
  {
    id: 'sp8',
    unitCode: 'KI',
    code: 'SP-8',
    name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ispList: [{ id: 'isp8', code: 'ISP-8', name: 'Indeks Reformasi Birokrasi Kementerian Hukum' }],
  },
  // PP
  {
    id: 'sp9',
    unitCode: 'PP',
    code: 'SP-9',
    name: 'Indeks Kualitas Peraturan Perundang-undangan',
    ispList: [{ id: 'isp9', code: 'ISP-9', name: 'Indeks kualitas peraturan perundang-undangan' }],
  },
  // BPHN
  {
    id: 'sp10',
    unitCode: 'BPHN',
    code: 'SP-10',
    name: 'Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum',
    ispList: [{ id: 'isp10', code: 'ISP-10', name: 'Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pembinaan Hukum Nasional' }],
  },
  {
    id: 'sp11',
    unitCode: 'BPHN',
    code: 'SP-11',
    name: 'Meningkatnya Kepatuhan Hukum Masyarakat Indonesia',
    ispList: [{ id: 'isp11', code: 'ISP-11', name: 'Tingkat Kepatuhan Hukum Masyarakat' }],
  },
  {
    id: 'sp12',
    unitCode: 'BPHN',
    code: 'SP-12',
    name: 'Meningkatnya Kepatuhan Hukum Lembaga Hukum',
    ispList: [{ id: 'isp12', code: 'ISP-12', name: 'Tingkat Kepatuhan Hukum Lembaga Hukum' }],
  },
  {
    id: 'sp13',
    unitCode: 'BPHN',
    code: 'SP-13',
    name: 'Terwujudnya Manfaat dan Dampak Peraturan Perundang-undangan terhadap Pembangunan Hukum Nasional',
    ispList: [{ id: 'isp13', code: 'ISP-13', name: 'Persentase Peraturan Perundang-undangan yang Memberikan Manfaat dan Dampak terhadap Pembangunan Hukum Nasional' }],
  },
  {
    id: 'sp14',
    unitCode: 'BPHN',
    code: 'SP-14',
    name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ispList: [{ id: 'isp14', code: 'ISP-14', name: 'Indeks Reformasi Birokrasi Kementerian Hukum' }],
  },
  // BSK
  {
    id: 'sp15',
    unitCode: 'BSK',
    code: 'SP-15',
    name: 'Meningkatnya Kualitas Kebijakan Kementerian Hukum',
    ispList: [{ id: 'isp15', code: 'ISP-15', name: 'Indeks Kualitas Kebijakan Kementerian Hukum' }],
  },
  {
    id: 'sp16',
    unitCode: 'BSK',
    code: 'SP-16',
    name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ispList: [{ id: 'isp16', code: 'ISP-16', name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum' }],
  },
  // Setjen
  {
    id: 'sp17',
    unitCode: 'Setjen',
    code: 'SP-17',
    name: 'Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum',
    ispList: [{ id: 'isp17', code: 'ISP-17', name: 'Indeks Reformasi Birokrasi General Kementerian Hukum' }],
  },
])

const unitPusatNameMap: Record<string, string> = {
  AHU: 'Ditjen Administrasi Hukum Umum',
  KI: 'Ditjen Kekayaan Intelektual',
  PP: 'Ditjen Peraturan Perundang-Undangan',
  BPHN: 'Badan Pembinaan Hukum Nasional',
  BSK: 'Badan Strategi Kebijakan Hukum',
  Setjen: 'Sekretariat Jenderal',
}

const groupedSp = computed(() => {
  const order = ['AHU', 'KI', 'PP', 'BPHN', 'BSK', 'Setjen']
  return order.map((code) => ({
    unitCode: code,
    unitPusatName: unitPusatNameMap[code] || code,
    spList: spData.value.filter((sp) => sp.unitCode === code),
  }))
})

// LocalStorage Persistence
const KELOLA_STORAGE_KEY = 'bakayuh_kelola_pohon_v2'

function saveKelolaData() {
  if (typeof window !== 'undefined') {
    const payload = {
      ssData: ssData.value,
      unitData: unitData.value,
      spData: spData.value,
    }
    localStorage.setItem(KELOLA_STORAGE_KEY, JSON.stringify(payload))
  }
}

function loadKelolaData() {
  if (typeof window !== 'undefined') {
    const raw = localStorage.getItem(KELOLA_STORAGE_KEY)
    if (raw) {
      try {
        const parsed = JSON.parse(raw)
        if (parsed.ssData) ssData.value = parsed.ssData
        if (parsed.unitData) unitData.value = parsed.unitData
        if (parsed.spData) spData.value = parsed.spData
      } catch (e) {
        console.warn('Failed parsing saved pohon data', e)
      }
    }
  }
}

// Toast Feedback
const toastMessage = ref('')
const showToast = ref(false)
function triggerToast(msg: string) {
  toastMessage.value = msg
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2500)
}

// Modal State for Add & Edit
type ModalTarget = 'ss' | 'ikss' | 'unit' | 'sp' | 'isp'
const showEditModal = ref(false)
const modalTarget = ref<ModalTarget>('ss')
const modalMode = ref<'add' | 'edit'>('add')
const modalParentId = ref('')
const modalEditId = ref('')

const formModel = ref({
  code: '',
  name: '',
  unitCode: 'AHU',
  pusatName: '',
  kanwilName: '',
  ikssCode: 'IKSS 1.1',
  urutan: 1,
})

// Handlers for SS / IKSS
function openAddSsModal() {
  modalTarget.value = 'ss'
  modalMode.value = 'add'
  modalEditId.value = ''
  formModel.value.code = `SS${ssData.value.length + 1}`
  formModel.value.name = ''
  showEditModal.value = true
}

function openEditSsModal(ss: SsItem) {
  modalTarget.value = 'ss'
  modalMode.value = 'edit'
  modalEditId.value = ss.id
  formModel.value.code = ss.code
  formModel.value.name = ss.name
  showEditModal.value = true
}

function deleteSs(ssId: string) {
  if (confirm('Yakin ingin menghapus Sasaran Strategis ini beserta semua indikatornya?')) {
    ssData.value = ssData.value.filter((s) => s.id !== ssId)
    saveKelolaData()
    triggerToast('Sasaran Strategis berhasil dihapus.')
  }
}

function openAddIkssModal(ssId: string) {
  modalTarget.value = 'ikss'
  modalMode.value = 'add'
  modalParentId.value = ssId
  modalEditId.value = ''
  const targetSs = ssData.value.find((s) => s.id === ssId)
  const num = targetSs ? targetSs.ikssList.length + 1 : 1
  formModel.value.code = `IKSS ${targetSs?.code.replace('SS', '') || '1'}.${num}`
  formModel.value.name = ''
  showEditModal.value = true
}

function openEditIkssModal(ssId: string, ikss: IkssItem) {
  modalTarget.value = 'ikss'
  modalMode.value = 'edit'
  modalParentId.value = ssId
  modalEditId.value = ikss.id
  formModel.value.code = ikss.code
  formModel.value.name = ikss.name
  showEditModal.value = true
}

function deleteIkss(ssId: string, ikssId: string) {
  if (confirm('Hapus Indikator Kinerja Sasaran Strategis ini?')) {
    const ss = ssData.value.find((s) => s.id === ssId)
    if (ss) {
      ss.ikssList = ss.ikssList.filter((i) => i.id !== ikssId)
      saveKelolaData()
      triggerToast('IKSS berhasil dihapus.')
    }
  }
}

// Handlers for Unit
function openAddUnitModal() {
  modalTarget.value = 'unit'
  modalMode.value = 'add'
  modalEditId.value = ''
  formModel.value.code = ''
  formModel.value.pusatName = ''
  formModel.value.kanwilName = ''
  formModel.value.ikssCode = 'IKSS 1.1'
  formModel.value.urutan = unitData.value.length + 1
  showEditModal.value = true
}

function openEditUnitModal(unit: UnitItem) {
  modalTarget.value = 'unit'
  modalMode.value = 'edit'
  modalEditId.value = unit.id
  formModel.value.code = unit.code
  formModel.value.pusatName = unit.pusatName
  formModel.value.kanwilName = unit.kanwilName
  formModel.value.ikssCode = unit.ikssCode
  formModel.value.urutan = unit.urutan
  showEditModal.value = true
}

function deleteUnit(unitId: string) {
  if (confirm('Hapus Unit ini?')) {
    unitData.value = unitData.value.filter((u) => u.id !== unitId)
    saveKelolaData()
    triggerToast('Unit berhasil dihapus.')
  }
}

// Handlers for SP / ISP
function openAddSpModal() {
  modalTarget.value = 'sp'
  modalMode.value = 'add'
  modalEditId.value = ''
  formModel.value.unitCode = 'AHU'
  formModel.value.code = `SP-${spData.value.length + 1}`
  formModel.value.name = ''
  showEditModal.value = true
}

function openEditSpModal(sp: SpItem) {
  modalTarget.value = 'sp'
  modalMode.value = 'edit'
  modalEditId.value = sp.id
  formModel.value.unitCode = sp.unitCode
  formModel.value.code = sp.code
  formModel.value.name = sp.name
  showEditModal.value = true
}

function deleteSp(spId: string) {
  if (confirm('Hapus Sasaran Program ini beserta semua indikatornya?')) {
    spData.value = spData.value.filter((p) => p.id !== spId)
    saveKelolaData()
    triggerToast('Sasaran Program berhasil dihapus.')
  }
}

function openAddIspModal(spId: string) {
  modalTarget.value = 'isp'
  modalMode.value = 'add'
  modalParentId.value = spId
  modalEditId.value = ''
  const sp = spData.value.find((p) => p.id === spId)
  const spNum = sp?.code.replace('SP-', '') || '1'
  formModel.value.code = `ISP-${spNum}`
  formModel.value.name = ''
  showEditModal.value = true
}

function openEditIspModal(spId: string, isp: IspItem) {
  modalTarget.value = 'isp'
  modalMode.value = 'edit'
  modalParentId.value = spId
  modalEditId.value = isp.id
  formModel.value.code = isp.code
  formModel.value.name = isp.name
  showEditModal.value = true
}

function deleteIsp(spId: string, ispId: string) {
  if (confirm('Hapus Indikator Sasaran Program ini?')) {
    const sp = spData.value.find((p) => p.id === spId)
    if (sp) {
      sp.ispList = sp.ispList.filter((i) => i.id !== ispId)
      saveKelolaData()
      triggerToast('ISP berhasil dihapus.')
    }
  }
}

// Save Modal Action
function saveModal() {
  if (modalTarget.value === 'ss') {
    if (modalMode.value === 'add') {
      ssData.value.push({
        id: 'ss_' + Date.now(),
        code: formModel.value.code.trim(),
        name: formModel.value.name.trim(),
        ikssList: [],
      })
    } else {
      const item = ssData.value.find((s) => s.id === modalEditId.value)
      if (item) {
        item.code = formModel.value.code.trim()
        item.name = formModel.value.name.trim()
      }
    }
  } else if (modalTarget.value === 'ikss') {
    const ss = ssData.value.find((s) => s.id === modalParentId.value)
    if (ss) {
      if (modalMode.value === 'add') {
        ss.ikssList.push({
          id: 'ikss_' + Date.now(),
          code: formModel.value.code.trim(),
          name: formModel.value.name.trim(),
        })
      } else {
        const item = ss.ikssList.find((i) => i.id === modalEditId.value)
        if (item) {
          item.code = formModel.value.code.trim()
          item.name = formModel.value.name.trim()
        }
      }
    }
  } else if (modalTarget.value === 'unit') {
    if (modalMode.value === 'add') {
      unitData.value.push({
        id: 'u_' + Date.now(),
        code: formModel.value.code.trim(),
        pusatName: formModel.value.pusatName.trim(),
        kanwilName: formModel.value.kanwilName.trim(),
        ikssCode: formModel.value.ikssCode.trim(),
        urutan: Number(formModel.value.urutan) || 1,
      })
    } else {
      const item = unitData.value.find((u) => u.id === modalEditId.value)
      if (item) {
        item.code = formModel.value.code.trim()
        item.pusatName = formModel.value.pusatName.trim()
        item.kanwilName = formModel.value.kanwilName.trim()
        item.ikssCode = formModel.value.ikssCode.trim()
        item.urutan = Number(formModel.value.urutan) || 1
      }
    }
  } else if (modalTarget.value === 'sp') {
    if (modalMode.value === 'add') {
      spData.value.push({
        id: 'sp_' + Date.now(),
        unitCode: formModel.value.unitCode,
        code: formModel.value.code.trim(),
        name: formModel.value.name.trim(),
        ispList: [],
      })
    } else {
      const item = spData.value.find((p) => p.id === modalEditId.value)
      if (item) {
        item.unitCode = formModel.value.unitCode
        item.code = formModel.value.code.trim()
        item.name = formModel.value.name.trim()
      }
    }
  } else if (modalTarget.value === 'isp') {
    const sp = spData.value.find((p) => p.id === modalParentId.value)
    if (sp) {
      if (modalMode.value === 'add') {
        sp.ispList.push({
          id: 'isp_' + Date.now(),
          code: formModel.value.code.trim(),
          name: formModel.value.name.trim(),
        })
      } else {
        const item = sp.ispList.find((i) => i.id === modalEditId.value)
        if (item) {
          item.code = formModel.value.code.trim()
          item.name = formModel.value.name.trim()
        }
      }
    }
  }

  saveKelolaData()
  showEditModal.value = false
  triggerToast('Perubahan berhasil disimpan!')
}
</script>

<template>
  <div class="space-y-5 pb-16 animate-fade-in relative">
    <!-- Toast Notification -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 z-50 bg-[#0B2A6F] text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-slide-up border border-white/20"
    >
      <svg class="w-4 h-4 text-[#F5BA31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
      <span>{{ toastMessage }}</span>
    </div>

    <!-- VIEW 1: LIHAT POHON VIEW -->
    <div v-if="activeTab === 'lihat'" class="space-y-4">
      <!-- Page Head & Zoom Controls -->
      <div class="page-head">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-[#0B2A6F] tracking-tight">Pohon Kinerja</h2>
          <div class="crumb text-xs text-slate-500 mt-0.5">
            Kementerian &rarr; Sasaran Strategis &rarr; Eselon I &rarr; Kantor Wilayah
          </div>
        </div>

        <div class="tools">
          <button
            type="button"
            class="btn xs only-d cursor-pointer"
            :class="zoomMode === 'fit' ? 'primary' : ''"
            @click="setZoom('fit')"
          >
            Pas layar
          </button>
          <button
            type="button"
            class="btn xs only-d cursor-pointer"
            :class="zoomMode === 0.7 ? 'primary' : ''"
            @click="setZoom(0.7)"
          >
            70%
          </button>
          <button
            type="button"
            class="btn xs only-d cursor-pointer"
            :class="zoomMode === 1 ? 'primary' : ''"
            @click="setZoom(1.0)"
          >
            100%
          </button>
          <button
            type="button"
            class="btn xs only-d cursor-pointer"
            :class="zoomMode === 1.3 ? 'primary' : ''"
            @click="setZoom(1.3)"
          >
            130%
          </button>
          <button
            type="button"
            class="btn cursor-pointer bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-colors"
            @click="switchTab('kelola')"
          >
            Kelola Data
          </button>
        </div>
      </div>

      <!-- DESKTOP VIEW: FULL 42-COLUMN GRID DIAGRAM IN CARD WRAPPER -->
      <div class="only-d">
        <div ref="diagramCardRef" class="pk-card">
          <div
            class="pk-wrap"
            id="pkWrap"
            :class="zoomMode === 'fit' ? 'is-fit' : 'is-scroll'"
            :style="wrapStyle"
          >
<div class="pk-grid" style="grid-template-columns:minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(43.33px,0.3333fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(32.50px,0.2500fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr) minmax(130.00px,1.0000fr)"><div class="pk-strip s0" style="grid-row:4/11;grid-column:1/span 4"></div><div class="pk-strip s1" style="grid-row:4/11;grid-column:5/span 12"></div><div class="pk-strip s0" style="grid-row:4/11;grid-column:17/span 2"></div><div class="pk-strip s1" style="grid-row:4/11;grid-column:19/span 20"></div><div class="pk-strip s0" style="grid-row:4/11;grid-column:39/span 2"></div><div class="pk-strip s1" style="grid-row:4/11;grid-column:41/span 2"></div><div class="pk-c arrow" style="grid-row:4;grid-column:1/span 4"><div class="pk-box pk-unit"><div class="pk-text">Ditjen Administrasi Hukum Umum</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:1/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-1</div><div class="pk-text">Terwujudnya Penegakan Hukum AHU yang Profesional</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:1/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-1</div><div class="pk-text">Indeks Penegakan Hukum AHU</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:2/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-2</div><div class="pk-text">Meningkatnya Kepuasan Terhadap Layanan AHU</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:2/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-2</div><div class="pk-text">Indeks Kepuasan Masyarakat (IKM) terhadap Layanan AHU</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:3/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-3</div><div class="pk-text">Meningkatnya Kemudahan Berusaha pada Layanan Badan Usaha dan Keperdataan</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:3/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-3</div><div class="pk-text">Indeks kemudahan berusaha pada layanan Badan Usaha dan Keperdataan</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:4/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-4</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:4/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-4</div><div class="pk-text">Indeks Reformasi Birokrasi Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:1/span 4"><div class="pk-box pk-kw"><div class="pk-text">Administrasi Hukum Umum</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:1/span 2"><div class="pk-box pk-sk"><div class="pk-code">SK 7110.1</div><div class="pk-text">Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:1/span 2"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-4d7f831c')"><div class="pk-code">IKK 7110.1.1</div><div class="pk-text">Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:3/span 2"><div class="pk-box pk-sk"><div class="pk-code">SK 7110.2</div><div class="pk-text">Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:3/span 2"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-e1d8dd50')"><div class="pk-code">IKK 7110.2.1</div><div class="pk-text">Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah</div></div></div><div class="pk-c arrow" style="grid-row:4;grid-column:5/span 12"><div class="pk-box pk-unit"><div class="pk-text">Ditjen Kekayaan Intelektual</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:5/span 3"><div class="pk-box pk-sp"><div class="pk-code">SP-5</div><div class="pk-text">Terwujudnya Penegakan Hukum Pelindungan Kekayaan Intelektual yang profesional</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:5/span 3"><div class="pk-box pk-sp"><div class="pk-code">ISP-5</div><div class="pk-text">Indeks Penegakan Hukum Pelindungan Kekayaan Intelektual</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:8/span 3"><div class="pk-box pk-sp"><div class="pk-code">SP-6</div><div class="pk-text">Meningkatnya Kepuasan Pelindungan dan pemanfaatan KI</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:8/span 3"><div class="pk-box pk-sp"><div class="pk-code">ISP-6</div><div class="pk-text">Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pelindungan dan Pemanfaatan KI</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:11/span 3"><div class="pk-box pk-sp"><div class="pk-code">SP-7</div><div class="pk-text">Meningkatnya Kesadaran Masyarakat Indonesia terhadap Kekayaan Intelektual</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:11/span 3"><div class="pk-box pk-sp"><div class="pk-code">ISP-7</div><div class="pk-text">Indeks Kesadaran Masyarakat terhadap Kekayaan Intelektual</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:14/span 3"><div class="pk-box pk-sp"><div class="pk-code">SP-8</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:14/span 3"><div class="pk-box pk-sp"><div class="pk-code">ISP-8</div><div class="pk-text">Indeks Reformasi Birokrasi Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:5/span 12"><div class="pk-box pk-kw"><div class="pk-text">Kekayaan Intelektual</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:5/span 4"><div class="pk-box pk-sk"><div class="pk-code">SK 7121.1</div><div class="pk-text">Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:5/span 4"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-90eda68f')"><div class="pk-code">IKK 7121.1.1</div><div class="pk-text">Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:9/span 4"><div class="pk-box pk-sk"><div class="pk-code">SK 7121.2</div><div class="pk-text">Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan</div></div></div><div class="pk-c " style="grid-row:10;grid-column:9/span 4"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-f2e9aa50')"><div class="pk-code">IKK 7121.2.1</div><div class="pk-text">Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:13/span 4"><div class="pk-box pk-sk"><div class="pk-code">SK 3</div><div class="pk-text">Meningkatnya Maturats Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:13/span 4"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-198021bd')"><div class="pk-code">IKK 3.1</div><div class="pk-text">Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah</div></div></div><div class="pk-c arrow" style="grid-row:4;grid-column:17/span 2"><div class="pk-box pk-unit"><div class="pk-text">Ditjen Peraturan Perundang-Undangan</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:17/span 2"><div class="pk-box pk-sp"><div class="pk-code">SP-9</div><div class="pk-text">Indeks Kualitas Peraturan Perundang-undangan</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:17/span 2"><div class="pk-box pk-sp"><div class="pk-code">ISP-9</div><div class="pk-text">Indeks kualitas peraturan perundang-undangan</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:17/span 2"><div class="pk-box pk-kw"><div class="pk-text">Peraturan Perundang-Undangan</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:17/span 2"><div class="pk-box pk-sk"><div class="pk-code">SK 7129.1</div><div class="pk-text">Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:17/span 1"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-17e887b8')"><div class="pk-code">IKK 7129.1.1</div><div class="pk-text">Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum</div></div></div><div class="pk-c " style="grid-row:10;grid-column:18/span 1"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-b50bd4f0')"><div class="pk-code">IKK 7129.1.2</div><div class="pk-text">Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:3;grid-column:1/span 18"><div class="pk-box pk-ikss"><div class="pk-code">IKSS 1.1</div><div class="pk-text">Indeks penegakan dan pelayanan Hukum</div></div></div><div class="pk-c arrow" style="grid-row:4;grid-column:19/span 20"><div class="pk-box pk-unit"><div class="pk-text">Badan Pembinaan Hukum Nasional</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:19/span 4"><div class="pk-box pk-sp"><div class="pk-code">SP-10</div><div class="pk-text">Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:19/span 4"><div class="pk-box pk-sp"><div class="pk-code">ISP-10</div><div class="pk-text">Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pembinaan Hukum Nasional</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:23/span 4"><div class="pk-box pk-sp"><div class="pk-code">SP-11</div><div class="pk-text">Meningkatnya Kepatuhan Hukum Masyarakat Indonesia</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:23/span 4"><div class="pk-box pk-sp"><div class="pk-code">ISP-11</div><div class="pk-text">Tingkat Kepatuhan Hukum Masyarakat</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:27/span 4"><div class="pk-box pk-sp"><div class="pk-code">SP-12</div><div class="pk-text">Meningkatnya Kepatuhan Hukum Lembaga Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:27/span 4"><div class="pk-box pk-sp"><div class="pk-code">ISP-12</div><div class="pk-text">Tingkat Kepatuhan Hukum Lembaga Hukum</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:31/span 4"><div class="pk-box pk-sp"><div class="pk-code">SP-13</div><div class="pk-text">Terwujudnya Manfaat dan Dampak Peraturan Perundang-undangan terhadap Pembangunan Hukum Nasional</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:31/span 4"><div class="pk-box pk-sp"><div class="pk-code">ISP-13</div><div class="pk-text">Persentase Peraturan Perundang-undangan yang Memberikan Manfaat dan Dampak terhadap Pembangunan Hukum Nasional</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:35/span 4"><div class="pk-box pk-sp"><div class="pk-code">SP-14</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:35/span 4"><div class="pk-box pk-sp"><div class="pk-code">ISP-14</div><div class="pk-text">Indeks Reformasi Birokrasi Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:19/span 20"><div class="pk-box pk-kw"><div class="pk-text">Badan Pembinaan Hukum Nasional</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:19/span 5"><div class="pk-box pk-sk"><div class="pk-code">SK 7133.1</div><div class="pk-text">Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda</div></div></div><div class="pk-c " style="grid-row:10;grid-column:19/span 5"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-5d2c6e21')"><div class="pk-code">IKK 7133.1.1</div><div class="pk-text">Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda di Wilayah Kerja Kanwil</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:24/span 5"><div class="pk-box pk-sk"><div class="pk-code">SK 7136.1</div><div class="pk-text">Meningkatnya Layanan Bantuan Hukum di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:24/span 5"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-5b0dc8bd')"><div class="pk-code">IKK 7136.1.1</div><div class="pk-text">Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:29/span 5"><div class="pk-box pk-sk"><div class="pk-code">SK 7136.2</div><div class="pk-text">Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas</div></div></div><div class="pk-c " style="grid-row:10;grid-column:29/span 5"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-09dd1d45')"><div class="pk-code">IKK 7136.2.1</div><div class="pk-text">Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:34/span 5"><div class="pk-box pk-sk"><div class="pk-code">SK 7136.3</div><div class="pk-text">Meningkatnya Pos Bantuan Hukum di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:34/span 5"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-83004743')"><div class="pk-code">IKK 7136.3.1</div><div class="pk-text">Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa//kelurahan</div></div></div><div class="pk-c arrow" style="grid-row:3;grid-column:19/span 20"><div class="pk-box pk-ikss"><div class="pk-code">IKSS 1.2</div><div class="pk-text">Indeks Budaya Hukum (IPH)</div></div></div><div class="pk-c arrow" style="grid-row:4;grid-column:39/span 2"><div class="pk-box pk-unit"><div class="pk-text">Badan Strategi Kebijakan Hukum</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:39/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-15</div><div class="pk-text">Meningkatnya Kualitas Kebijakan Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:39/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-15</div><div class="pk-text">Indeks Kualitas Kebijakan Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:40/span 1"><div class="pk-box pk-sp"><div class="pk-code">SP-16</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:40/span 1"><div class="pk-box pk-sp"><div class="pk-code">ISP-16</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:39/span 2"><div class="pk-box pk-kw"><div class="pk-text">Badan Strategi Kebijakan</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:39/span 2"><div class="pk-box pk-sk"><div class="pk-code">SK 7143.1</div><div class="pk-text">Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah</div></div></div><div class="pk-c " style="grid-row:10;grid-column:39/span 2"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-486e1417')"><div class="pk-code">IKK 7143.1.1</div><div class="pk-text">Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah</div></div></div><div class="pk-c arrow" style="grid-row:3;grid-column:39/span 2"><div class="pk-box pk-ikss"><div class="pk-code">IKSS 1.3</div><div class="pk-text">Indeks Menteri Hukum (IPH)</div></div></div><div class="pk-c arrow" style="grid-row:2;grid-column:1/span 40"><div class="pk-box pk-ss"><div class="pk-code">SS1</div><div class="pk-text">Terwujudnya Kepastian hukum di seluruh wilayah NKRI</div></div></div><div class="pk-c arrow" style="grid-row:4;grid-column:41/span 2"><div class="pk-box pk-unit"><div class="pk-text">Sekretariat Jenderal</div></div></div><div class="pk-c arrow" style="grid-row:5;grid-column:41/span 2"><div class="pk-box pk-sp"><div class="pk-code">SP-17</div><div class="pk-text">Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:6;grid-column:41/span 2"><div class="pk-box pk-sp"><div class="pk-code">ISP-17</div><div class="pk-text">Indeks Reformasi Birokrasi General Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:8;grid-column:41/span 2"><div class="pk-box pk-kw"><div class="pk-text">Bagian Tata Usaha dan Umum</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:41/span 1"><div class="pk-box pk-sk"><div class="pk-code">SK 7099.1</div><div class="pk-text">Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK</div></div></div><div class="pk-c " style="grid-row:10;grid-column:41/span 1"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-6defb0d6')"><div class="pk-code">IKK 7099.1.1</div><div class="pk-text">Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK</div></div></div><div class="pk-c arrow" style="grid-row:9;grid-column:42/span 1"><div class="pk-box pk-sk"><div class="pk-code">SK 7099.2</div><div class="pk-text">Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen</div></div></div><div class="pk-c " style="grid-row:10;grid-column:42/span 1"><div class="pk-box pk-ikk pk-click" @click="openIkk('IK-30accf02')"><div class="pk-code">IKK 7099.2.1</div><div class="pk-text">Indeks kepuasan terhadap layanan dukungan manajemen</div></div></div><div class="pk-c arrow" style="grid-row:3;grid-column:41/span 2"><div class="pk-box pk-ikss"><div class="pk-code">IKSS 2.1</div><div class="pk-text">Indeks Reformasi Birokrasi Kementerian Hukum</div></div></div><div class="pk-c arrow" style="grid-row:2;grid-column:41/span 2"><div class="pk-box pk-ss"><div class="pk-code">SS2</div><div class="pk-text">Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi</div></div></div><div class="pk-c arrow" style="grid-row:1;grid-column:1/span 42"><div class="pk-km">KEMENTERIAN HUKUM REPUBLIK INDONESIA</div></div><div class="pk-c arrow gap" style="grid-row:7;grid-column:1/span 42"><div class="pk-band">KANTOR WILAYAH</div></div></div>
        </div>
        </div>
      </div>

      <!-- MOBILE VIEW: ACCORDION COLLAPSIBLE TREE -->
      <div class="only-m">
        <div class="po">
          <div class="po-km">KEMENTERIAN HUKUM REPUBLIK INDONESIA</div>
          <details class="po-s" open>
            <summary><span class="po-c">SS1</span>Terwujudnya Kepastian hukum di seluruh wilayah NKRI</summary>
            <div class="po-b">
              <details class="po-k">
                <summary><span class="po-c">IKSS 1.1</span>Indeks penegakan dan pelayanan Hukum</summary>
                <div class="po-b">
                  <details class="po-u">
                    <summary>Ditjen Administrasi Hukum Umum</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-1</span>Terwujudnya Penegakan Hukum AHU yang Profesional<div class="po-i2"><span class="po-c">ISP-1</span>Indeks Penegakan Hukum AHU</div></div>
                      <div class="po-i"><span class="po-c">SP-2</span>Meningkatnya Kepuasan Terhadap Layanan AHU<div class="po-i2"><span class="po-c">ISP-2</span>Indeks Kepuasan Masyarakat (IKM) terhadap Layanan AHU</div></div>
                      <div class="po-i"><span class="po-c">SP-3</span>Meningkatnya Kemudahan Berusaha pada Layanan Badan Usaha dan Keperdataan<div class="po-i2"><span class="po-c">ISP-3</span>Indeks kemudahan berusaha pada layanan Badan Usaha dan Keperdataan</div></div>
                      <div class="po-i"><span class="po-c">SP-4</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-4</span>Indeks Reformasi Birokrasi Kementerian Hukum</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Administrasi Hukum Umum</div>
                      <div class="po-i"><span class="po-c">SK 7110.1</span>Meningkatnya Kualitas Pemahaman Masyarakat terhadap Layanan AHU di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-4d7f831c')"><span class="po-c">IKK 7110.1.1</span>Tingkat Pemahaman Masyarakat terhadap Layanan AHU di Wilayah</div></div>
                      <div class="po-i"><span class="po-c">SK 7110.2</span>Terwujudnya Penyelesaian Pengaduan Masyarakat terkait Dugaan Pelanggaran Jabatan Notaris di Kantor Wilayah<div class="po-i2 po-click" @click="openIkk('IK-e1d8dd50')"><span class="po-c">IKK 7110.2.1</span>Persentase Penyelesaian Laporan Pengaduan Masyarakat terkait Dugaan Pelanggaran Perilaku Notaris dan Pelaksanaan Jabatan Notaris di Kantor Wilayah</div></div>
                    </div>
                  </details>
                  <details class="po-u">
                    <summary>Ditjen Kekayaan Intelektual</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-5</span>Terwujudnya Penegakan Hukum Pelindungan Kekayaan Intelektual yang profesional<div class="po-i2"><span class="po-c">ISP-5</span>Indeks Penegakan Hukum Pelindungan Kekayaan Intelektual</div></div>
                      <div class="po-i"><span class="po-c">SP-6</span>Meningkatnya Kepuasan Pelindungan dan pemanfaatan KI<div class="po-i2"><span class="po-c">ISP-6</span>Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pelindungan dan Pemanfaatan KI</div></div>
                      <div class="po-i"><span class="po-c">SP-7</span>Meningkatnya Kesadaran Masyarakat Indonesia terhadap Kekayaan Intelektual<div class="po-i2"><span class="po-c">ISP-7</span>Indeks Kesadaran Masyarakat terhadap Kekayaan Intelektual</div></div>
                      <div class="po-i"><span class="po-c">SP-8</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-8</span>Indeks Reformasi Birokrasi Kementerian Hukum</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Kekayaan Intelektual</div>
                      <div class="po-i"><span class="po-c">SK 7121.1</span>Meningkatnya Layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah<div class="po-i2 po-click" @click="openIkk('IK-90eda68f')"><span class="po-c">IKK 7121.1.1</span>Tingkat kepatuhan layanan pelindungan dan pemanfaatan Kekayaan Intelektual di wilayah terhadap Standar Pelayanan</div></div>
                      <div class="po-i"><span class="po-c">SK 7121.2</span>Optimalnya Penanganan dan Penyelesaian Sengketa Hak Kekayaan Intelektual di kewilayahan<div class="po-i2 po-click" @click="openIkk('IK-f2e9aa50')"><span class="po-c">IKK 7121.2.1</span>Persentase Pelanggaran Kekayaan Intelektual di Kewilayahan yang Selesai Ditangani</div></div>
                      <div class="po-i"><span class="po-c">SK 3</span>Meningkatnya Maturats Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-198021bd')"><span class="po-c">IKK 3.1</span>Tingkat Maturitas Pengelolaan Pemanfaatan Kekayaan Intelektual di Wilayah</div></div>
                    </div>
                  </details>
                  <details class="po-u">
                    <summary>Ditjen Peraturan Perundang-Undangan</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-9</span>Indeks Kualitas Peraturan Perundang-undangan<div class="po-i2"><span class="po-c">ISP-9</span>Indeks kualitas peraturan perundang-undangan</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Peraturan Perundang-Undangan</div>
                      <div class="po-i"><span class="po-c">SK 7129.1</span>Terwujudnya Fasilitasi Perencanaan dan Perancangan Peraturan Perundang-undangan Daerah di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-17e887b8')"><span class="po-c">IKK 7129.1.1</span>Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum</div><div class="po-i2 po-click" @click="openIkk('IK-b50bd4f0')"><span class="po-c">IKK 7129.1.2</span>Persentase Perencanaan Peraturan Perundang-undangan di Daerah yang Difasilitasi oleh Kantor Wilayah Kementerian Hukum</div></div>
                    </div>
                  </details>
                </div>
              </details>
              <details class="po-k">
                <summary><span class="po-c">IKSS 1.2</span>Indeks Budaya Hukum (IPH)</summary>
                <div class="po-b">
                  <details class="po-u">
                    <summary>Badan Pembinaan Hukum Nasional</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-10</span>Meningkatnya Kepuasan terhadap Layanan Pembinaan Hukum<div class="po-i2"><span class="po-c">ISP-10</span>Indeks Kepuasan Masyarakat (IKM) terhadap Layanan Pembinaan Hukum Nasional</div></div>
                      <div class="po-i"><span class="po-c">SP-11</span>Meningkatnya Kepatuhan Hukum Masyarakat Indonesia<div class="po-i2"><span class="po-c">ISP-11</span>Tingkat Kepatuhan Hukum Masyarakat</div></div>
                      <div class="po-i"><span class="po-c">SP-12</span>Meningkatnya Kepatuhan Hukum Lembaga Hukum<div class="po-i2"><span class="po-c">ISP-12</span>Tingkat Kepatuhan Hukum Lembaga Hukum</div></div>
                      <div class="po-i"><span class="po-c">SP-13</span>Terwujudnya Manfaat dan Dampak Peraturan Perundang-undangan terhadap Pembangunan Hukum Nasional<div class="po-i2"><span class="po-c">ISP-13</span>Persentase Peraturan Perundang-undangan yang Memberikan Manfaat dan Dampak terhadap Pembangunan Hukum Nasional</div></div>
                      <div class="po-i"><span class="po-c">SP-14</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-14</span>Indeks Reformasi Birokrasi Kementerian Hukum</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Badan Pembinaan Hukum Nasional</div>
                      <div class="po-i"><span class="po-c">SK 7133.1</span>Terwujudnya Pelaksanaan Rencana Tindak Lanjut Hasil Analisis dan Evaluasi Pelaksanaan Perda<div class="po-i2 po-click" @click="openIkk('IK-5d2c6e21')"><span class="po-c">IKK 7133.1.1</span>Persentase Hasil Analisis dan Evaluasi Pelaksanaan Peraturan Daerah yang Ditindaklanjuti oleh Pemda di Wilayah Kerja Kanwil</div></div>
                      <div class="po-i"><span class="po-c">SK 7136.1</span>Meningkatnya Layanan Bantuan Hukum di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-5b0dc8bd')"><span class="po-c">IKK 7136.1.1</span>Persentase Pemberian Bantuan Hukum secara Litigasi dan Non Litigasi</div></div>
                      <div class="po-i"><span class="po-c">SK 7136.2</span>Terwujudnya Layanan Pengelolaan Dokumen dan Informasi Hukum di Wilayah yang Berkualitas<div class="po-i2 po-click" @click="openIkk('IK-09dd1d45')"><span class="po-c">IKK 7136.2.1</span>Tingkat penyelesaian layanan pengelolaan dokumen dan informasi hukum nasional di wilayah</div></div>
                      <div class="po-i"><span class="po-c">SK 7136.3</span>Meningkatnya Pos Bantuan Hukum di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-83004743')"><span class="po-c">IKK 7136.3.1</span>Persentase pos bantuan hukum di wilayah yang terbentuk terhadap total desa//kelurahan</div></div>
                    </div>
                  </details>
                </div>
              </details>
              <details class="po-k">
                <summary><span class="po-c">IKSS 1.3</span>Indeks Menteri Hukum (IPH)</summary>
                <div class="po-b">
                  <details class="po-u">
                    <summary>Badan Strategi Kebijakan Hukum</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-15</span>Meningkatnya Kualitas Kebijakan Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-15</span>Indeks Kualitas Kebijakan Kementerian Hukum</div></div>
                      <div class="po-i"><span class="po-c">SP-16</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-16</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Badan Strategi Kebijakan</div>
                      <div class="po-i"><span class="po-c">SK 7143.1</span>Ditindaklanjutinya Rekomendasi Strategi Kebijakan Hukum di Wilayah<div class="po-i2 po-click" @click="openIkk('IK-486e1417')"><span class="po-c">IKK 7143.1.1</span>Persentase Tindak Lanjut Rekomendasi Strategi Kebijakan Hukum di Wilayah</div></div>
                    </div>
                  </details>
                </div>
              </details>
            </div>
          </details>
          <details class="po-s" open>
            <summary><span class="po-c">SS2</span>Terwujudnya Reformasi Birokrasi Kementerian Hukum dalam mendukung pencapaian Reformasi Birokrasi</summary>
            <div class="po-b">
              <details class="po-k">
                <summary><span class="po-c">IKSS 2.1</span>Indeks Reformasi Birokrasi Kementerian Hukum</summary>
                <div class="po-b">
                  <details class="po-u">
                    <summary>Sekretariat Jenderal</summary>
                    <div class="po-b">
                      <div class="po-lbl">Eselon I — sasaran &amp; indikator program</div>
                      <div class="po-i"><span class="po-c">SP-17</span>Meningkatnya pelaksanaan Reformasi Birokrasi General Kementerian Hukum<div class="po-i2"><span class="po-c">ISP-17</span>Indeks Reformasi Birokrasi General Kementerian Hukum</div></div>
                      <div class="po-lbl kw">Kantor Wilayah — Bagian Tata Usaha dan Umum</div>
                      <div class="po-i"><span class="po-c">SK 7099.1</span>Terwujudnya tindak lanjut rekomendasi hasil pemeriksaan BPK<div class="po-i2 po-click" @click="openIkk('IK-6defb0d6')"><span class="po-c">IKK 7099.1.1</span>Persentase tindak lanjut rekomendasi hasil pemeriksaan BPK</div></div>
                      <div class="po-i"><span class="po-c">SK 7099.2</span>Meningkatnya kepuasan unit eselon II terhadap layanan dukungan manajemen<div class="po-i2 po-click" @click="openIkk('IK-30accf02')"><span class="po-c">IKK 7099.2.1</span>Indeks kepuasan terhadap layanan dukungan manajemen</div></div>
                    </div>
                  </details>
                </div>
              </details>
            </div>
          </details>
          <p class="small muted">Ketuk untuk membuka tiap tingkat.</p>
        </div>
      </div>

      <p class="small muted text-xs text-slate-500 italic mt-3">
        Klik indikator kinerja kegiatan (IKK) untuk membuka penjelasannya. Teks Kantor Wilayah diambil dari data Sasaran &amp; Indikator.
      </p>
    </div>

    <!-- VIEW 2: KELOLA POHON KINERJA (EXACT MECHANISM MATCHING USER SPECIFICATION) -->
    <div v-else class="space-y-6">
      <!-- Header: Title, Subtitle, and 'Lihat Pohon' Button -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
            Kelola Pohon Kinerja
          </h1>
          <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
            Kode SK dan IKK (tingkat Kantor Wilayah) diisi lewat Sasaran &amp; Renaksi &rarr; Edit sasaran / indikator.
          </p>
        </div>

        <button
          type="button"
          @click="switchTab('lihat')"
          class="self-start sm:self-auto px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:border-slate-300 transition-all cursor-pointer inline-flex items-center gap-1.5"
        >
          Lihat Pohon
        </button>
      </div>

      <!-- CARD 1: Sasaran Strategis & Indikator (SS / IKSS) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h2 class="text-sm sm:text-base font-bold text-[#0B2A6F]">
            Sasaran Strategis &amp; Indikator (SS / IKSS)
          </h2>
          <button
            type="button"
            @click="openAddSsModal"
            class="px-3 py-1.5 rounded-lg bg-[#0B2A6F] text-white text-xs font-bold hover:bg-[#071E4A] transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1"
          >
            + SS
          </button>
        </div>

        <div class="divide-y divide-slate-100">
          <div
            v-for="ss in ssData"
            :key="ss.id"
            class="py-3.5 first:pt-4 last:pb-0 space-y-2.5"
          >
            <!-- SS Row -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="text-xs sm:text-sm text-slate-800">
                <span class="font-black text-slate-900">{{ ss.code }}</span>
                <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
                <span>{{ ss.name }}</span>
              </div>
              <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
                <button
                  type="button"
                  @click="openAddIkssModal(ss.id)"
                  class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                >
                  + IKSS
                </button>
                <button
                  type="button"
                  @click="openEditSsModal(ss)"
                  class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                >
                  Edit
                </button>
                <button
                  type="button"
                  @click="deleteSs(ss.id)"
                  class="px-2.5 py-1 rounded border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 text-[11px] font-semibold transition-colors cursor-pointer"
                >
                  Hapus
                </button>
              </div>
            </div>

            <!-- Indented IKSS List -->
            <div v-if="ss.ikssList.length" class="space-y-2 pl-4 sm:pl-8 pt-1">
              <div
                v-for="ikss in ss.ikssList"
                :key="ikss.id"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 py-1.5 border-t border-slate-50"
              >
                <div class="text-xs text-slate-700">
                  <span class="font-extrabold text-slate-900">{{ ikss.code }}</span>
                  <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
                  <span>{{ ikss.name }}</span>
                </div>
                <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
                  <button
                    type="button"
                    @click="openEditIkssModal(ss.id, ikss)"
                    class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                  >
                    Edit
                  </button>
                  <button
                    type="button"
                    @click="deleteIkss(ss.id, ikss.id)"
                    class="px-2.5 py-1 rounded border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 text-[11px] font-semibold transition-colors cursor-pointer"
                  >
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CARD 2: Unit (Eselon I & Kantor Wilayah) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h2 class="text-sm sm:text-base font-bold text-[#0B2A6F]">
            Unit (Eselon I &amp; Kantor Wilayah)
          </h2>
          <button
            type="button"
            @click="openAddUnitModal"
            class="px-3 py-1.5 rounded-lg bg-[#0B2A6F] text-white text-xs font-bold hover:bg-[#071E4A] transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1"
          >
            + Unit
          </button>
        </div>

        <div class="divide-y divide-slate-100">
          <div
            v-for="unit in unitData"
            :key="unit.id"
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 py-3.5 first:pt-4 last:pb-0"
          >
            <div class="text-xs sm:text-sm text-slate-800">
              <span class="font-black text-slate-900">{{ unit.code }}</span>
              <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
              <span>{{ unit.pusatName }} / {{ unit.kanwilName }}</span>
              <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
              <span class="text-slate-600 font-medium">{{ unit.ikssCode }}</span>
              <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
              <span class="text-slate-500 font-medium">urutan {{ unit.urutan }}</span>
            </div>
            <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
              <button
                type="button"
                @click="openEditUnitModal(unit)"
                class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
              >
                Edit
              </button>
              <button
                type="button"
                @click="deleteUnit(unit.id)"
                class="px-2.5 py-1 rounded border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 text-[11px] font-semibold transition-colors cursor-pointer"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- CARD 3: Sasaran & Indikator Program (SP / ISP) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 sm:p-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <h2 class="text-sm sm:text-base font-bold text-[#0B2A6F]">
            Sasaran &amp; Indikator Program (SP / ISP)
          </h2>
          <button
            type="button"
            @click="openAddSpModal"
            class="px-3 py-1.5 rounded-lg bg-[#0B2A6F] text-white text-xs font-bold hover:bg-[#071E4A] transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1"
          >
            + SP
          </button>
        </div>

        <div class="space-y-6 pt-4">
          <div
            v-for="grp in groupedSp"
            :key="grp.unitCode"
            class="space-y-3 pb-5 border-b border-slate-100 last:border-b-0 last:pb-0"
          >
            <!-- Unit Header -->
            <h3 class="text-xs sm:text-sm font-extrabold text-slate-900">
              {{ grp.unitPusatName }}
            </h3>

            <!-- SP List for Unit -->
            <div class="space-y-2.5 divide-y divide-slate-50">
              <div
                v-for="sp in grp.spList"
                :key="sp.id"
                class="pt-2.5 first:pt-0 space-y-2"
              >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                  <div class="text-xs sm:text-sm text-slate-800">
                    <span class="font-black text-slate-900">{{ sp.code }}</span>
                    <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
                    <span>{{ sp.name }}</span>
                  </div>
                  <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
                    <button
                      type="button"
                      @click="openAddIspModal(sp.id)"
                      class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                    >
                      + ISP
                    </button>
                    <button
                      type="button"
                      @click="openEditSpModal(sp)"
                      class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
                      @click="deleteSp(sp.id)"
                      class="px-2.5 py-1 rounded border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 text-[11px] font-semibold transition-colors cursor-pointer"
                    >
                      Hapus
                    </button>
                  </div>
                </div>

                <!-- Indented ISP List -->
                <div v-if="sp.ispList.length" class="space-y-2 pl-4 sm:pl-8 pt-1">
                  <div
                    v-for="isp in sp.ispList"
                    :key="isp.id"
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 py-1.5 border-t border-slate-50"
                  >
                    <div class="text-xs text-slate-700">
                      <span class="font-extrabold text-slate-900">{{ isp.code }}</span>
                      <span class="text-slate-400 mx-1.5 font-bold">&middot;</span>
                      <span>{{ isp.name }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 self-end sm:self-auto flex-shrink-0">
                      <button
                        type="button"
                        @click="openEditIspModal(sp.id, isp)"
                        class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-[11px] font-semibold transition-colors cursor-pointer"
                      >
                        Edit
                      </button>
                      <button
                        type="button"
                        @click="deleteIsp(sp.id, isp.id)"
                        class="px-2.5 py-1 rounded border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 text-[11px] font-semibold transition-colors cursor-pointer"
                      >
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
    </div>

    <!-- MODAL: ADD / EDIT DIALOG (FOR SS, IKSS, UNIT, SP, ISP) -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">
        <div class="bg-[#0B2A6F] text-white p-4 flex items-center justify-between">
          <h3 class="text-sm font-bold uppercase tracking-wider">
            {{ modalMode === 'add' ? 'Tambah' : 'Edit' }}
            <span v-if="modalTarget === 'ss'">Sasaran Strategis (SS)</span>
            <span v-else-if="modalTarget === 'ikss'">Indikator Sasaran Strategis (IKSS)</span>
            <span v-else-if="modalTarget === 'unit'">Unit Eselon I &amp; Kanwil</span>
            <span v-else-if="modalTarget === 'sp'">Sasaran Program (SP)</span>
            <span v-else-if="modalTarget === 'isp'">Indikator Sasaran Program (ISP)</span>
          </h3>
          <button type="button" @click="showEditModal = false" class="text-slate-300 hover:text-white cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveModal" class="p-5 space-y-3.5 text-xs">
          <!-- Unit Selector (for SP) -->
          <div v-if="modalTarget === 'sp'">
            <label class="block font-semibold text-slate-700 mb-1">Unit Eselon I</label>
            <select
              v-model="formModel.unitCode"
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium"
            >
              <option value="AHU">Ditjen Administrasi Hukum Umum (AHU)</option>
              <option value="KI">Ditjen Kekayaan Intelektual (KI)</option>
              <option value="PP">Ditjen Peraturan Perundang-Undangan (PP)</option>
              <option value="BPHN">Badan Pembinaan Hukum Nasional (BPHN)</option>
              <option value="BSK">Badan Strategi Kebijakan Hukum (BSK)</option>
              <option value="Setjen">Sekretariat Jenderal (Setjen)</option>
            </select>
          </div>

          <!-- Code Field -->
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Kode</label>
            <input
              v-model="formModel.code"
              type="text"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-bold text-slate-800"
            />
          </div>

          <!-- Name Field (for SS, IKSS, SP, ISP) -->
          <div v-if="modalTarget !== 'unit'">
            <label class="block font-semibold text-slate-700 mb-1">Deskripsi / Judul</label>
            <textarea
              v-model="formModel.name"
              rows="3"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium text-slate-800"
            ></textarea>
          </div>

          <!-- Unit specific fields -->
          <template v-if="modalTarget === 'unit'">
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Nama Tingkat Pusat (Eselon I)</label>
              <input
                v-model="formModel.pusatName"
                type="text"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Nama Tingkat Kantor Wilayah</label>
              <input
                v-model="formModel.kanwilName"
                type="text"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-semibold text-slate-700 mb-1">IKSS Terkait</label>
                <input
                  v-model="formModel.ikssCode"
                  type="text"
                  required
                  placeholder="IKSS 1.1"
                  class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium"
                />
              </div>
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Urutan</label>
                <input
                  v-model="formModel.urutan"
                  type="number"
                  min="1"
                  required
                  class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 font-medium"
                />
              </div>
            </div>
          </template>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 rounded-lg border border-slate-200 bg-white font-semibold text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-lg bg-[#0B2A6F] text-white font-bold hover:bg-[#091F4A] cursor-pointer"
            >
              Simpan Data
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: DETAIL IKK POPUP (LIHAT POHON) -->
    <div
      v-if="showIkkModal && selectedIkk"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs animate-fade-in"
    >
      <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform animate-scale-up">
        <div class="bg-gradient-to-r from-[#071E4A] to-[#0B2A6F] text-white p-5 flex items-start justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-[#F5BA31] text-[#071E4A]">
                {{ selectedIkk.code }}
              </span>
              <span class="text-xs text-slate-300">{{ selectedIkk.unit }}</span>
            </div>
            <h3 class="text-base font-bold leading-snug text-white">
              {{ selectedIkk.name }}
            </h3>
          </div>
          <button
            type="button"
            @click="closeIkkModal"
            class="text-slate-300 hover:text-white rounded-lg p-1 transition-colors cursor-pointer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
              Sasaran Strategis Kementerian ({{ selectedIkk.ssCode }})
            </span>
            <p class="font-bold text-[#0B2A6F] text-xs">
              {{ selectedIkk.ssName }}
            </p>
          </div>

          <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-400 block mb-1">
              {{ selectedIkk.eselonI }} &bull; Sasaran Program ({{ selectedIkk.spCode }})
            </span>
            <p class="font-bold text-indigo-950 text-xs">
              {{ selectedIkk.spName }}
            </p>
          </div>

          <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 block mb-1">
              Sasaran Kegiatan Kantor Wilayah ({{ selectedIkk.skCode }})
            </span>
            <p class="font-bold text-emerald-950 text-xs">
              {{ selectedIkk.skName }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Target Kinerja</p>
              <p class="text-sm font-black text-slate-800 mt-0.5">{{ selectedIkk.target }} {{ selectedIkk.satuan }}</p>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Penanggung Jawab (PJ)</p>
              <p class="text-xs font-bold text-slate-800 mt-0.5">{{ selectedIkk.pj }}</p>
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
          <button
            type="button"
            @click="closeIkkModal"
            class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
          >
            Tutup
          </button>
          <button
            type="button"
            @click="goToRenaksi"
            class="px-4 py-2 rounded-lg bg-[#0B2A6F] text-xs font-bold text-white hover:bg-[#091F4A] transition-colors inline-flex items-center gap-1.5 shadow-xs cursor-pointer"
          >
            Buka Rencana Aksi Terkait
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scoped Styling for the 42-Column Pohon Kinerja Diagram & Mobile Details */

.page-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.crumb {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
}

.tools {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.tools .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #334155;
  transition: all 0.15s ease;
}

.tools .btn.xs {
  padding: 5px 10px;
  font-size: 11px;
}

.tools .btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.tools .btn.primary {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
  font-weight: 700;
  box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
}

/* Visibility classes */
@media (min-width: 1025px) {
  .only-m {
    display: none !important;
  }
  .only-d {
    display: block !important;
  }
}
@media (max-width: 1024px) {
  .only-d {
    display: none !important;
  }
  .only-m {
    display: block !important;
  }
}

/* Card Container & Grid Wrapper */
.pk-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  padding: 16px 12px;
  position: relative;
  width: 100%;
  box-sizing: border-box;
}

.pk-wrap {
  width: 100%;
  box-sizing: border-box;
  padding: 4px 4px 16px;
  transform-origin: top center;
  transition: zoom 0.15s ease-out;
}

.pk-wrap.is-fit {
  overflow-x: hidden;
  overflow-y: hidden;
  display: flex;
  justify-content: center;
}

.pk-wrap.is-scroll {
  overflow-x: auto;
  overflow-y: visible;
  display: block;
}

.pk-grid {
  display: grid;
  row-gap: 12px;
  column-gap: 6px;
  position: relative;
  width: max-content;
  margin: 0 auto;
}

/* Column Strips */
.pk-strip {
  border-radius: 8px;
  pointer-events: none;
  z-index: 0;
}
.pk-strip.s0 {
  background: rgba(248, 250, 252, 0.6);
  border: 1px dashed #e2e8f0;
}
.pk-strip.s1 {
  background: rgba(241, 245, 249, 0.6);
  border: 1px dashed #cbd5e1;
}

/* Grid Cells */
.pk-c {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: stretch;
}
.pk-c.gap {
  margin-top: 8px;
  margin-bottom: 8px;
}

/* Downward amber triangle arrows matching reference */
.pk-c.arrow::after {
  content: "▼";
  position: absolute;
  bottom: -11px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 8px;
  line-height: 1;
  color: #f59e0b;
  pointer-events: none;
  z-index: 10;
}

/* Top Ministry Banner */
.pk-km {
  background: #2563eb;
  color: #ffffff;
  font-weight: 800;
  font-size: 13px;
  letter-spacing: 0.04em;
  padding: 10px 16px;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
}

/* Separator Band */
.pk-band {
  background: #0284c7;
  color: #ffffff;
  font-weight: 800;
  font-size: 12.5px;
  letter-spacing: 0.05em;
  padding: 8px 16px;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);
}

/* Base Box */
.pk-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 6px 6px;
  border-radius: 6px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  height: 100%;
  box-sizing: border-box;
  transition: all 0.15s ease;
}

.pk-code {
  font-size: 9.5px;
  font-weight: 800;
  letter-spacing: 0.02em;
  padding: 2px 5px;
  border-radius: 3px;
  margin-bottom: 3px;
  display: inline-block;
  width: fit-content;
}

.pk-text {
  font-size: 10px;
  font-weight: 500;
  line-height: 1.3;
  color: #1e293b;
  word-break: break-word;
}

/* Box Variants */

/* Row 2: Sasaran Strategis - Pastel Yellow */
.pk-box.pk-ss {
  background: #fef08a;
  border: 1.5px solid #eab308;
}
.pk-box.pk-ss .pk-code {
  background: #fde047;
  color: #854d0e;
  font-weight: 800;
}
.pk-box.pk-ss .pk-text {
  font-size: 11.5px;
  font-weight: 700;
  color: #1e293b;
}

/* Row 3: Indikator Sasaran Strategis - Pastel Yellow */
.pk-box.pk-ikss {
  background: #fef9c3;
  border: 1.5px solid #eab308;
}
.pk-box.pk-ikss .pk-code {
  background: #fde047;
  color: #854d0e;
  font-weight: 800;
}
.pk-box.pk-ikss .pk-text {
  font-size: 10.5px;
  font-weight: 600;
  color: #1e293b;
}

/* Row 4: Unit Eselon I - Solid Royal Blue */
.pk-box.pk-unit {
  background: #1d4ed8;
  color: #ffffff;
  border: 1px solid #1e40af;
}
.pk-box.pk-unit .pk-text {
  font-size: 11.5px;
  font-weight: 700;
  color: #ffffff;
}

/* Rows 5 & 6: Sasaran Program & Indikator Sasaran Program - White Card with Amber Badge */
.pk-box.pk-sp {
  background: #ffffff;
  border: 1px solid #e2e8f0;
}
.pk-box.pk-sp .pk-code {
  background: #f59e0b;
  color: #ffffff;
  font-weight: 800;
}
.pk-box.pk-sp .pk-text {
  font-size: 10px;
  font-weight: 500;
  color: #1e293b;
}

/* Row 8: Kanwil Units - Vibrant Cyan / Sky Blue */
.pk-box.pk-kw {
  background: #0284c7;
  color: #ffffff;
  border: 1px solid #0369a1;
}
.pk-box.pk-kw .pk-text {
  font-size: 11px;
  font-weight: 700;
  color: #ffffff;
}

/* Row 9: SK - White Card with Amber Badge */
.pk-box.pk-sk {
  background: #ffffff;
  border: 1px solid #e2e8f0;
}
.pk-box.pk-sk .pk-code {
  background: #f59e0b;
  color: #ffffff;
  font-weight: 800;
}
.pk-box.pk-sk .pk-text {
  font-size: 10px;
  font-weight: 500;
  color: #1e293b;
}

/* Row 10: IKK - White Card with Light Blue Badge */
.pk-box.pk-ikk {
  background: #ffffff;
  border: 1px solid #e2e8f0;
}
.pk-box.pk-ikk .pk-code {
  background: #e0f2fe;
  color: #0369a1;
  font-weight: 800;
}
.pk-box.pk-ikk .pk-text {
  font-size: 9.5px;
  font-weight: 500;
  color: #1e293b;
}
.pk-box.pk-ikk.pk-click {
  cursor: pointer;
}
.pk-box.pk-ikk.pk-click:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);
  border-color: #0284c7;
  background: #f0f9ff;
}

/* Mobile Accordion Styles (.po) */
.po {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.po-km {
  background: #0B2A6F;
  color: #ffffff;
  font-weight: 800;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 8px;
  text-align: center;
  border-bottom: 3px solid #f5ba31;
}
details.po-s, details.po-k, details.po-u {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
details.po-s > summary {
  background: #f8fafc;
  padding: 10px 12px;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
  color: #0b2a6f;
  border-bottom: 1px solid transparent;
  display: flex;
  align-items: center;
  gap: 8px;
}
details.po-s[open] > summary {
  border-bottom-color: #e2e8f0;
}
details.po-k > summary {
  background: #f1f5f9;
  padding: 8px 12px;
  font-weight: 600;
  font-size: 11.5px;
  cursor: pointer;
  color: #1e40af;
  display: flex;
  align-items: center;
  gap: 8px;
}
details.po-u > summary {
  background: #0f172a;
  color: #ffffff;
  padding: 8px 12px;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
}
.po-b {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.po-lbl {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  padding-top: 4px;
}
.po-lbl.kw {
  color: #1d4ed8;
  font-weight: 800;
  border-top: 1px dashed #cbd5e1;
  margin-top: 6px;
  padding-top: 8px;
}
.po-i {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 11px;
  font-weight: 600;
  color: #334155;
}
.po-i2 {
  margin-top: 6px;
  padding: 6px 8px;
  background: #ffffff;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  font-size: 10.5px;
  color: #475569;
}
.po-i2.po-click {
  cursor: pointer;
  border-color: #93c5fd;
  background: #f0f9ff;
}
.po-i2.po-click:active {
  background: #e0f2fe;
}
.po-c {
  display: inline-block;
  font-size: 9.5px;
  font-weight: 800;
  background: #0b2a6f;
  color: #ffffff;
  padding: 1px 5px;
  border-radius: 3px;
  margin-right: 6px;
}
</style>
