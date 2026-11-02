import { ref } from 'vue'

export type NotificationType =
  | 'mendekati_tenggat'
  | 'melewati_tenggat'
  | 'ditolak'
  | 'diterima'
  | 'menunggu_verifikasi'
  | 'info'

export type NotificationCategory = 'semua' | 'tenggat' | 'verifikasi'

export interface AppNotification {
  id: string
  type: NotificationType
  category: NotificationCategory
  title: string
  message: string
  time: string
  timestamp: number
  read: boolean
  targetRole: 'operator_satker' | 'admin' | 'all'
  targetUnit?: string // 'AHU', 'KI', 'Setjen', 'PP', 'BPHN', 'BSK'
  linkUrl?: string
  meta?: {
    unitKode?: string
    unitName?: string
    periode?: string
    catatan?: string
    deadlineDate?: string
    submissionId?: string
  }
}

const STORAGE_KEY_NOTIFICATIONS = 'bakayuh_notifications_v3'

const initialNotifications: AppNotification[] = [
  // ==========================================
  // NOTIFIKASI UNTUK SATKER (OPERATOR SATKER)
  // ==========================================

  // 1. Mendekati Waktu Tenggat (Oktober tenggat 10 Nov 2026)
  {
    id: 'notif-satker-deadline-okt',
    type: 'mendekati_tenggat',
    category: 'tenggat',
    title: 'Peringatan: Tenggat Waktu Laporan Oktober 2026',
    message:
      'Batas pengumpulan Laporan Bulanan Oktober 2026 berakhir pada 10 November 2026 (tersisa 8 hari). Harap segera melengkapi dan mengunggah berkas data dukung sebelum batas waktu ditutup.',
    time: '2 jam yang lalu',
    timestamp: Date.now() - 2 * 3600 * 1000,
    read: false,
    targetRole: 'operator_satker',
    linkUrl: '/laporan',
    meta: {
      deadlineDate: '10 November 2026',
      periode: 'Oktober 2026',
    },
  },

  // 2. Melewati Masa Tenggat (September tenggat 10 Okt 2026)
  {
    id: 'notif-satker-overdue-sep-ahu',
    type: 'melewati_tenggat',
    category: 'tenggat',
    title: 'Pemberitahuan: Laporan September 2026 Melewati Masa Tenggat',
    message:
      'Laporan Bulanan September 2026 telah melewati batas waktu pengumpulan (10 Oktober 2026). Status pelaporan unit Anda saat ini tercatat Melewati Masa Tenggat. Harap segera menyampaikan laporan ke Kanwil.',
    time: '1 hari yang lalu',
    timestamp: Date.now() - 24 * 3600 * 1000,
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'AHU',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'AHU',
      periode: 'September 2026',
      deadlineDate: '10 Oktober 2026',
    },
  },
  {
    id: 'notif-satker-overdue-sep-ki',
    type: 'melewati_tenggat',
    category: 'tenggat',
    title: 'Pemberitahuan: Laporan September 2026 Melewati Masa Tenggat',
    message:
      'Laporan Bulanan September 2026 telah melewati batas waktu pengumpulan (10 Oktober 2026). Harap segera menyampaikan laporan dan data dukung ke Kanwil.',
    time: '1 hari yang lalu',
    timestamp: Date.now() - 24 * 3600 * 1000,
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'KI',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'KI',
      periode: 'September 2026',
      deadlineDate: '10 Oktober 2026',
    },
  },
  {
    id: 'notif-satker-overdue-sep-pp',
    type: 'melewati_tenggat',
    category: 'tenggat',
    title: 'Pemberitahuan: Laporan September 2026 Melewati Masa Tenggat',
    message:
      'Laporan Bulanan September 2026 telah melewati batas waktu pengumpulan (10 Oktober 2026). Segera selesaikan dan unggah berkas.',
    time: '1 hari yang lalu',
    timestamp: Date.now() - 24 * 3600 * 1000,
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'PP',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'PP',
      periode: 'September 2026',
      deadlineDate: '10 Oktober 2026',
    },
  },
  {
    id: 'notif-satker-overdue-sep-bsk',
    type: 'melewati_tenggat',
    category: 'tenggat',
    title: 'Pemberitahuan: Laporan September 2026 Melewati Masa Tenggat',
    message:
      'Laporan Bulanan September 2026 telah melewati batas waktu pengumpulan (10 Oktober 2026). Harap segera mengunggah dokumen data dukung.',
    time: '1 hari yang lalu',
    timestamp: Date.now() - 24 * 3600 * 1000,
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'BSK',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'BSK',
      periode: 'September 2026',
      deadlineDate: '10 Oktober 2026',
    },
  },

  // 3. Laporan Ditolak / Perlu Perbaikan (Satker)
  {
    id: 'notif-satker-revisi-setjen-tw2',
    type: 'ditolak',
    category: 'verifikasi',
    title: 'Laporan Perlu Perbaikan: Data Dukung TW 2',
    message:
      'Verifikator Kanwil mengembalikan berkas pelaporan dengan catatan: "Surat tugas dan daftar hadir belum lengkap, mohon dilengkapi."',
    time: '08/07/2026 15:30',
    timestamp: new Date('2026-07-08T15:30:00').getTime(),
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'Setjen',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'Setjen',
      periode: 'Triwulan II (TW 2)',
      catatan: 'Surat tugas dan daftar hadir belum lengkap, mohon dilengkapi.',
    },
  },
  {
    id: 'notif-satker-revisi-ki-tw2',
    type: 'ditolak',
    category: 'verifikasi',
    title: 'Laporan Perlu Perbaikan: Bukti Layanan Merek TW 2',
    message:
      'Verifikator Kanwil mengembalikan berkas pelaporan dengan catatan: "Bukti tanda terima PNBP belum dilampirkan."',
    time: '09/07/2026 10:15',
    timestamp: new Date('2026-07-09T10:15:00').getTime(),
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'KI',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'KI',
      periode: 'Triwulan II (TW 2)',
      catatan: 'Bukti tanda terima PNBP belum dilampirkan.',
    },
  },
  {
    id: 'notif-satker-revisi-bsk-tw3',
    type: 'ditolak',
    category: 'verifikasi',
    title: 'Laporan Perlu Perbaikan: Analisis Kebijakan TW 3',
    message:
      'Verifikator Kanwil mengembalikan berkas pelaporan dengan catatan: "Rekomendasi belum disertai matriks tindak lanjut satuan kerja."',
    time: '11/10/2026 13:45',
    timestamp: new Date('2026-10-11T13:45:00').getTime(),
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'BSK',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'BSK',
      periode: 'Triwulan III (TW 3)',
      catatan: 'Rekomendasi belum disertai matriks tindak lanjut satuan kerja.',
    },
  },
  {
    id: 'notif-satker-revisi-bphn-sep',
    type: 'ditolak',
    category: 'verifikasi',
    title: 'Laporan Perlu Perbaikan: Laporan Bulanan September',
    message:
      'Verifikator Kanwil meminta perbaikan pada dokumen dokumentasi kegiatan pembinaan Desa Sadar Hukum.',
    time: '12/10/2026 09:00',
    timestamp: new Date('2026-10-12T09:00:00').getTime(),
    read: false,
    targetRole: 'operator_satker',
    targetUnit: 'BPHN',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'BPHN',
      periode: 'September 2026',
      catatan: 'Dokumen bukti dukung belum lengkap.',
    },
  },

  // 4. Laporan Diterima (Satker)
  {
    id: 'notif-satker-acc-ahu-tw2',
    type: 'diterima',
    category: 'verifikasi',
    title: 'Laporan Disetujui: Evaluasi Kenotariatan TW 2 Diterima',
    message:
      'Verifikator Kanwil telah menerima dan menyetujui berkas pelaporan unit Anda. Catatan: "Berkas verifikasi terbukti valid dan akurat."',
    time: '09/07/2026 14:10',
    timestamp: new Date('2026-07-09T14:10:00').getTime(),
    read: true,
    targetRole: 'operator_satker',
    targetUnit: 'AHU',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'AHU',
      periode: 'Triwulan II (TW 2)',
      catatan: 'Berkas verifikasi terbukti valid dan akurat.',
    },
  },
  {
    id: 'notif-satker-acc-setjen-tw1',
    type: 'diterima',
    category: 'verifikasi',
    title: 'Laporan Disetujui: Matriks Capaian TW 1 Diterima',
    message:
      'Verifikator Kanwil telah menerima laporan unit Anda. Catatan: "Data dukung lengkap dan sesuai format."',
    time: '08/04/2026 11:20',
    timestamp: new Date('2026-04-08T11:20:00').getTime(),
    read: true,
    targetRole: 'operator_satker',
    targetUnit: 'Setjen',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'Setjen',
      periode: 'Triwulan I (TW 1)',
      catatan: 'Data dukung lengkap dan sesuai format.',
    },
  },
  {
    id: 'notif-satker-acc-pp-tw2',
    type: 'diterima',
    category: 'verifikasi',
    title: 'Laporan Disetujui: Harmonisasi Perda TW 2 Diterima',
    message:
      'Verifikator Kanwil telah menerima laporan unit Anda. Catatan: "Matriks harmonisasi lengkap dengan berita acara."',
    time: '10/07/2026 16:00',
    timestamp: new Date('2026-07-10T16:00:00').getTime(),
    read: true,
    targetRole: 'operator_satker',
    targetUnit: 'PP',
    linkUrl: '/laporan',
    meta: {
      unitKode: 'PP',
      periode: 'Triwulan II (TW 2)',
      catatan: 'Matriks harmonisasi lengkap dengan berita acara.',
    },
  },

  // ==========================================
  // NOTIFIKASI UNTUK ADMINISTRATOR KANWIL
  // ==========================================

  // 1. Laporan Baru Masuk Menunggu Verifikasi
  {
    id: 'notif-admin-sub-ahu-tw3',
    type: 'menunggu_verifikasi',
    category: 'verifikasi',
    title: 'Laporan Baru: [AHU] Data Dukung Renaksi TW 3',
    message:
      'Unit Administrasi Hukum Umum (AHU) telah mengunggah berkas "Data_Dukung_AHU_TW3_2026.pdf" untuk periode Triwulan III. Siap untuk diverifikasi.',
    time: '09/10/2026 10:25',
    timestamp: new Date('2026-10-09T10:25:00').getTime(),
    read: false,
    targetRole: 'admin',
    linkUrl: '/laporan/verifikasi',
    meta: {
      unitKode: 'AHU',
      unitName: 'Administrasi Hukum Umum',
      periode: 'Triwulan III (TW 3)',
      submissionId: 'sub-daduk-ahu-1',
    },
  },
  {
    id: 'notif-admin-sub-setjen-tw3',
    type: 'menunggu_verifikasi',
    category: 'verifikasi',
    title: 'Laporan Baru: [Setjen] Data Dukung Renaksi TW 3',
    message:
      'Unit Bagian Tata Usaha dan Umum telah mengunggah berkas "Data_Dukung_TW3_Setjen_Layanan_Umum.pdf". Menunggu validasi verifikator Kanwil.',
    time: '08/10/2026 14:10',
    timestamp: new Date('2026-10-08T14:10:00').getTime(),
    read: false,
    targetRole: 'admin',
    linkUrl: '/laporan/verifikasi',
    meta: {
      unitKode: 'Setjen',
      unitName: 'Bagian Tata Usaha dan Umum',
      periode: 'Triwulan III (TW 3)',
      submissionId: 'sub-daduk-setjen-1',
    },
  },
  {
    id: 'notif-admin-sub-bphn-tw3',
    type: 'menunggu_verifikasi',
    category: 'verifikasi',
    title: 'Laporan Baru: [BPHN] Data Dukung Renaksi TW 3',
    message:
      'Unit BPHN telah mengunggah berkas "Data_Dukung_BPHN_TW3_Penyuluhan.pdf" terkait pembinaan Desa Sadar Hukum TW 3. Siap diverifikasi.',
    time: '10/10/2026 09:15',
    timestamp: new Date('2026-10-10T09:15:00').getTime(),
    read: false,
    targetRole: 'admin',
    linkUrl: '/laporan/verifikasi',
    meta: {
      unitKode: 'BPHN',
      unitName: 'Badan Pembinaan Hukum Nasional',
      periode: 'Triwulan III (TW 3)',
      submissionId: 'sub-daduk-bphn-1',
    },
  },

  // 2. Satker Melewati Masa Tenggat
  {
    id: 'notif-admin-overdue-summary',
    type: 'melewati_tenggat',
    category: 'tenggat',
    title: 'Peringatan Kepatuhan: Satuan Kerja Melewati Masa Tenggat',
    message:
      'Terdapat 4 satuan kerja (AHU, KI, PP, BSK) yang belum mengunggah Laporan Bulanan September 2026 padahal batas tenggat 10 Oktober telah terlewati.',
    time: '3 hari yang lalu',
    timestamp: Date.now() - 3 * 24 * 3600 * 1000,
    read: false,
    targetRole: 'admin',
    linkUrl: '/monitoring-daduk',
    meta: {
      deadlineDate: '10 Oktober 2026',
    },
  },

  // 3. Mendekati Waktu Tenggat (Admin monitoring)
  {
    id: 'notif-admin-deadline-okt',
    type: 'mendekati_tenggat',
    category: 'tenggat',
    title: 'Pengingat Jadwal: Batas Pelaporan Oktober Segera Tiba',
    message:
      'Batas pengumpulan Laporan Bulanan Oktober 2026 untuk seluruh satuan kerja jatuh tempo pada tanggal 10 November 2026 (tersisa 8 hari). Pantau kepatuhan melalui dashboard monitoring.',
    time: '5 jam yang lalu',
    timestamp: Date.now() - 5 * 3600 * 1000,
    read: false,
    targetRole: 'admin',
    linkUrl: '/laporan',
    meta: {
      deadlineDate: '10 November 2026',
    },
  },

  // 4. Laporan Dalam Status Perbaikan
  {
    id: 'notif-admin-revisi-pending',
    type: 'ditolak',
    category: 'verifikasi',
    title: 'Menunggu Revisi Satker: 3 Berkas Dalam Status Perbaikan',
    message:
      'Terdapat 3 berkas laporan (dari unit Setjen, KI, dan BSK) yang telah dikembalikan dengan catatan revisi dan sedang menunggu perbaikan dari operator satker.',
    time: '2 hari yang lalu',
    timestamp: Date.now() - 2 * 24 * 3600 * 1000,
    read: true,
    targetRole: 'admin',
    linkUrl: '/laporan/verifikasi',
  },

  // 5. Rekap Verifikasi Berhasil (Diterima)
  {
    id: 'notif-admin-acc-summary',
    type: 'diterima',
    category: 'verifikasi',
    title: 'Rekap Verifikasi: Laporan TW 2 Disetujui',
    message:
      'Laporan Triwulan II untuk unit AHU, PP, dan Setjen telah selesai diverifikasi dan berstatus Diterima. Skor kepatuhan unit telah diperbarui.',
    time: '3 hari yang lalu',
    timestamp: Date.now() - 3 * 24 * 3600 * 1000,
    read: true,
    targetRole: 'admin',
    linkUrl: '/laporan/verifikasi',
  },
]

// Singleton reactive state
const notificationsState = ref<AppNotification[]>(loadFromStorage())

function loadFromStorage(): AppNotification[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY_NOTIFICATIONS)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed
      }
    }
  } catch (e) {
    console.error('Failed to load notifications from localStorage:', e)
  }
  return JSON.parse(JSON.stringify(initialNotifications))
}

function saveToStorage() {
  try {
    localStorage.setItem(STORAGE_KEY_NOTIFICATIONS, JSON.stringify(notificationsState.value))
  } catch (e) {
    console.error('Failed to save notifications to localStorage:', e)
  }
}

export function useNotificationStore() {
  const allNotifications = notificationsState

  // Filter notifications based on active role and unit code
  function getNotificationsForUser(userRole?: string, unitCode?: string) {
    const isAdmin = ['super_admin', 'admin_kanwil'].includes(userRole ?? '')
    const isSatker = userRole === 'operator_satker'
    const currentUnit = (unitCode || '').toUpperCase()

    return allNotifications.value.filter((n) => {
      // If admin, show notifications targeting admin or all
      if (isAdmin) {
        return n.targetRole === 'admin' || n.targetRole === 'all'
      }

      // If operator satker, show notifications targeting satker or all
      if (isSatker) {
        if (n.targetRole !== 'operator_satker' && n.targetRole !== 'all') {
          return false
        }
        // If a specific target unit is specified, must match current unit
        if (n.targetUnit) {
          return n.targetUnit.toUpperCase() === currentUnit
        }
        return true
      }

      // Default (e.g. viewer): show all non-admin
      return n.targetRole !== 'admin'
    })
  }

  function markAsRead(id: string) {
    const item = allNotifications.value.find((n) => n.id === id)
    if (item && !item.read) {
      item.read = true
      saveToStorage()
    }
  }

  function markAllAsRead(userRole?: string, unitCode?: string) {
    const userNotifs = getNotificationsForUser(userRole, unitCode)
    let changed = false
    userNotifs.forEach((n) => {
      if (!n.read) {
        n.read = true
        changed = true
      }
    })
    if (changed) {
      saveToStorage()
    }
  }

  function deleteNotification(id: string) {
    const idx = allNotifications.value.findIndex((n) => n.id === id)
    if (idx !== -1) {
      allNotifications.value.splice(idx, 1)
      saveToStorage()
    }
  }

  function clearAll(userRole?: string, unitCode?: string) {
    const idsToRemove = new Set(getNotificationsForUser(userRole, unitCode).map((n) => n.id))
    notificationsState.value = notificationsState.value.filter((n) => !idsToRemove.has(n.id))
    saveToStorage()
  }

  function addNotification(notif: Omit<AppNotification, 'id' | 'timestamp' | 'time'> & { time?: string }) {
    const now = new Date()
    const formattedTime =
      notif.time ||
      `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

    const newNotif: AppNotification = {
      ...notif,
      id: `notif-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
      time: formattedTime,
      timestamp: now.getTime(),
    }

    // Insert at front
    notificationsState.value.unshift(newNotif)
    saveToStorage()
    return newNotif
  }

  function resetToDefaults() {
    notificationsState.value = JSON.parse(JSON.stringify(initialNotifications))
    saveToStorage()
  }

  return {
    allNotifications,
    getNotificationsForUser,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearAll,
    addNotification,
    resetToDefaults,
  }
}
