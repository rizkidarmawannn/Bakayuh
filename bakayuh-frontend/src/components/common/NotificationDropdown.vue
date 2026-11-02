<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { onClickOutside } from '@vueuse/core'
import { useAuth } from '@/composables/useAuth'
import bellIconUrl from '@/assets/bell-notification.png'
import {
  useNotificationStore,
  type AppNotification,
  type NotificationType,
} from '@/composables/useNotificationStore'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ClockIcon,
  XCircleIcon,
  DocumentArrowUpIcon,
  CheckIcon,
  TrashIcon,
  ChevronRightIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { user, role, userUnitCode, isOperatorSatker, isSuperAdmin, isAdminKanwil } = useAuth()
const notifStore = useNotificationStore()

const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

// Close when clicking outside
onClickOutside(dropdownRef, () => {
  isOpen.value = false
})

function toggleDropdown() {
  isOpen.value = !isOpen.value
}

// Filter tabs
type FilterTab = 'all' | 'unread' | 'tenggat' | 'verifikasi'
const activeTab = ref<FilterTab>('all')

// Current user notifications
const userNotifications = computed(() => {
  return notifStore.getNotificationsForUser(role.value, userUnitCode.value)
})

const unreadCount = computed(() => {
  return userNotifications.value.filter((n) => !n.read).length
})

const filteredNotifications = computed(() => {
  const list = userNotifications.value
  if (activeTab.value === 'unread') {
    return list.filter((n) => !n.read)
  }
  if (activeTab.value === 'tenggat') {
    return list.filter((n) => n.category === 'tenggat' || n.type === 'mendekati_tenggat' || n.type === 'melewati_tenggat')
  }
  if (activeTab.value === 'verifikasi') {
    return list.filter(
      (n) =>
        n.category === 'verifikasi' ||
        ['ditolak', 'diterima', 'menunggu_verifikasi'].includes(n.type),
    )
  }
  return list
})

function handleNotificationClick(notif: AppNotification) {
  notifStore.markAsRead(notif.id)
  isOpen.value = false
  if (notif.linkUrl) {
    router.push(notif.linkUrl)
  }
}

function handleMarkAllAsRead() {
  notifStore.markAllAsRead(role.value, userUnitCode.value)
}

function handleDeleteNotification(e: Event, id: string) {
  e.stopPropagation()
  notifStore.deleteNotification(id)
}

function handleClearAll() {
  if (confirm('Bersihkan semua riwayat notifikasi?')) {
    notifStore.clearAll(role.value, userUnitCode.value)
  }
}

function getTypeBadge(type: NotificationType) {
  switch (type) {
    case 'mendekati_tenggat':
      return {
        label: 'Mendekati Tenggat',
        classes: 'bg-amber-50 text-amber-700 border-amber-200/80',
        iconBg: 'bg-amber-100 text-amber-600 border-amber-200',
      }
    case 'melewati_tenggat':
      return {
        label: 'Melewati Tenggat',
        classes: 'bg-rose-50 text-rose-700 border-rose-200/80',
        iconBg: 'bg-rose-100 text-rose-600 border-rose-200',
      }
    case 'ditolak':
      return {
        label: 'Perlu Perbaikan',
        classes: 'bg-orange-50 text-orange-700 border-orange-200/80',
        iconBg: 'bg-orange-100 text-orange-600 border-orange-200',
      }
    case 'diterima':
      return {
        label: 'Disetujui / Diterima',
        classes: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
        iconBg: 'bg-emerald-100 text-emerald-600 border-emerald-200',
      }
    case 'menunggu_verifikasi':
      return {
        label: 'Menunggu Verifikasi',
        classes: 'bg-blue-50 text-blue-700 border-blue-200/80',
        iconBg: 'bg-blue-100 text-blue-600 border-blue-200',
      }
    default:
      return {
        label: 'Informasi',
        classes: 'bg-slate-50 text-slate-700 border-slate-200/80',
        iconBg: 'bg-slate-100 text-slate-600 border-slate-200',
      }
  }
}

const roleBadgeLabel = computed(() => {
  if (isOperatorSatker.value) {
    return `Satker: ${userUnitCode.value || 'UPT'}`
  }
  if (isSuperAdmin.value) {
    return 'Super Admin Kanwil'
  }
  if (isAdminKanwil.value) {
    return 'Verifikator Kanwil'
  }
  return 'Viewer'
})
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block text-left">
    <!-- Notification Bell Trigger Button -->
    <button
      type="button"
      @click="toggleDropdown"
      class="relative p-1.5 sm:p-2 rounded-xl text-kemenkum-silver hover:text-white hover:bg-white/10 active:scale-95 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-kemenkum-gold/50 cursor-pointer flex items-center justify-center"
      :class="{ 'bg-white/15 text-white ring-2 ring-kemenkum-gold/40': isOpen }"
      :aria-expanded="isOpen"
      aria-label="Notifikasi sistem"
    >
      <img
        :src="bellIconUrl"
        alt="Notifikasi"
        class="w-6 h-6 object-contain drop-shadow select-none transition-transform duration-150"
      />

      <!-- Unread Badge Indicator -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex items-center justify-center min-w-[19px] h-[19px] px-1 text-[10px] font-extrabold text-white bg-gradient-to-r from-red-500 to-rose-600 rounded-full shadow-md border-2 border-kemenkum-navy animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Dropdown Popover -->
    <Transition
      enter-active-class="transition duration-200 ease-out origin-top-right"
      enter-from-class="transform scale-95 opacity-0 -translate-y-2"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in origin-top-right"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-2"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2.5 w-[330px] sm:w-[420px] md:w-[460px] bg-white rounded-2xl shadow-2xl border border-slate-200/90 z-50 overflow-hidden flex flex-col max-h-[85vh]"
        style="box-shadow: 0 20px 45px -10px rgba(11, 42, 111, 0.25);"
      >
        <!-- Dropdown Header -->
        <div class="px-4 py-3.5 bg-gradient-to-r from-[#0B2A6F] to-[#123e9a] text-white flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="p-1 rounded-lg bg-white/15 backdrop-blur-sm flex items-center justify-center">
                <img :src="bellIconUrl" alt="Notifikasi" class="w-5 h-5 object-contain" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-sm tracking-wide leading-none text-white">Notifikasi</h3>
                  <span
                    v-if="unreadCount > 0"
                    class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-400 text-slate-900 rounded-full leading-none shadow-sm"
                  >
                    {{ unreadCount }} baru
                  </span>
                </div>
                <p class="text-[11px] text-slate-200 mt-0.5 flex items-center gap-1.5">
                  <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                  <span>{{ roleBadgeLabel }}</span>
                </p>
              </div>
            </div>

            <!-- Quick Action: Tandai Semua Dibaca -->
            <button
              v-if="unreadCount > 0"
              @click="handleMarkAllAsRead"
              type="button"
              class="text-[11px] font-medium text-slate-200 hover:text-white bg-white/10 hover:bg-white/20 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1 cursor-pointer"
              title="Tandai semua notifikasi sudah dibaca"
            >
              <CheckIcon class="w-3.5 h-3.5 text-kemenkum-gold" />
              <span>Tandai dibaca</span>
            </button>
          </div>
        </div>

        <!-- Filter Chips / Tabs -->
        <div class="px-3 py-2 bg-slate-50 border-b border-slate-200/80 flex items-center gap-1.5 overflow-x-auto scrollbar-none flex-shrink-0">
          <button
            type="button"
            @click="activeTab = 'all'"
            class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer whitespace-nowrap"
            :class="
              activeTab === 'all'
                ? 'bg-[#0B2A6F] text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
            "
          >
            Semua ({{ userNotifications.length }})
          </button>
          <button
            type="button"
            @click="activeTab = 'unread'"
            class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
            :class="
              activeTab === 'unread'
                ? 'bg-[#0B2A6F] text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
            "
          >
            <span>Belum Dibaca</span>
            <span
              v-if="unreadCount > 0"
              class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
              :class="activeTab === 'unread' ? 'bg-amber-400 text-slate-900' : 'bg-red-500 text-white'"
            >
              {{ unreadCount }}
            </span>
          </button>
          <button
            type="button"
            @click="activeTab = 'tenggat'"
            class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
            :class="
              activeTab === 'tenggat'
                ? 'bg-[#0B2A6F] text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
            "
          >
            <ClockIcon class="w-3.5 h-3.5" />
            <span>Tenggat Waktu</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'verifikasi'"
            class="px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1"
            :class="
              activeTab === 'verifikasi'
                ? 'bg-[#0B2A6F] text-white shadow-sm'
                : 'text-slate-600 hover:bg-slate-200/70 hover:text-slate-900'
            "
          >
            <CheckCircleIcon class="w-3.5 h-3.5" />
            <span>Verifikasi</span>
          </button>
        </div>

        <!-- Scrollable Notifications List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 max-h-[380px] scrollbar-thin">
          <!-- Empty State -->
          <div
            v-if="filteredNotifications.length === 0"
            class="py-12 px-6 text-center flex flex-col items-center justify-center text-slate-500"
          >
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
              <img :src="bellIconUrl" alt="Tidak ada notifikasi" class="w-7 h-7 object-contain opacity-40 grayscale" />
            </div>
            <p class="font-bold text-sm text-slate-800">Tidak ada notifikasi</p>
            <p class="text-xs text-slate-500 max-w-[240px] mt-1">
              {{
                activeTab === 'unread'
                  ? 'Semua notifikasi telah Anda baca.'
                  : 'Belum ada notifikasi pada kategori yang dipilih.'
              }}
            </p>
          </div>

          <!-- Notification Items -->
          <div
            v-for="notif in filteredNotifications"
            :key="notif.id"
            @click="handleNotificationClick(notif)"
            class="p-3.5 transition-colors cursor-pointer group relative flex gap-3 items-start"
            :class="[
              notif.read ? 'bg-white hover:bg-slate-50/90' : 'bg-blue-50/30 hover:bg-blue-50/70',
            ]"
          >
            <!-- Type Icon Badge -->
            <div
              class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center border mt-0.5 shadow-2xs"
              :class="getTypeBadge(notif.type).iconBg"
            >
              <!-- Icons per type -->
              <ClockIcon v-if="notif.type === 'mendekati_tenggat'" class="w-4 h-4 stroke-2" />
              <ExclamationTriangleIcon v-else-if="notif.type === 'melewati_tenggat'" class="w-4 h-4 stroke-2" />
              <XCircleIcon v-else-if="notif.type === 'ditolak'" class="w-4 h-4 stroke-2" />
              <CheckCircleIcon v-else-if="notif.type === 'diterima'" class="w-4 h-4 stroke-2" />
              <DocumentArrowUpIcon v-else-if="notif.type === 'menunggu_verifikasi'" class="w-4 h-4 stroke-2" />
              <InformationCircleIcon v-else class="w-4 h-4 stroke-2" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 pr-4">
              <!-- Top line: Type pill + Time -->
              <div class="flex items-center justify-between gap-2 mb-1">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border"
                  :class="getTypeBadge(notif.type).classes"
                >
                  {{ getTypeBadge(notif.type).label }}
                </span>
                <span class="text-[10px] font-medium text-slate-500 whitespace-nowrap">
                  {{ notif.time }}
                </span>
              </div>

              <!-- Title -->
              <h4
                class="text-xs font-bold leading-tight"
                :class="notif.read ? 'text-slate-800' : 'text-[#0B2A6F]'"
              >
                {{ notif.title }}
              </h4>

              <!-- Message -->
              <p class="text-[11px] text-slate-600 mt-1 leading-snug line-clamp-3">
                {{ notif.message }}
              </p>

              <!-- Optional Catatan Verifikator box -->
              <div
                v-if="notif.meta?.catatan && notif.type === 'ditolak'"
                class="mt-1.5 p-2 rounded-lg bg-orange-50/90 border border-orange-200/70 text-[10px] text-orange-800 italic"
              >
                <span class="font-bold not-italic">Catatan: </span>
                "{{ notif.meta.catatan }}"
              </div>

              <!-- Bottom Meta / Action CTA -->
              <div class="mt-2 flex items-center justify-between text-[11px] pt-1">
                <span
                  v-if="notif.meta?.deadlineDate"
                  class="text-[10px] font-semibold text-slate-500 flex items-center gap-1"
                >
                  <ClockIcon class="w-3 h-3 text-slate-400" />
                  <span>Batas: {{ notif.meta.deadlineDate }}</span>
                </span>
                <span v-else class="text-[10px] text-slate-400">
                  {{ notif.meta?.periode || '' }}
                </span>

                <span
                  class="inline-flex items-center gap-0.5 text-[11px] font-semibold text-[#0B2A6F] group-hover:text-blue-700 transition-colors"
                >
                  <span>Buka detail</span>
                  <ChevronRightIcon class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" />
                </span>
              </div>
            </div>

            <!-- Unread Dot Indicator -->
            <div
              v-if="!notif.read"
              class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 mt-2 shadow-sm animate-pulse"
              title="Belum dibaca"
            />

            <!-- Delete action button on hover -->
            <button
              type="button"
              @click="(e) => handleDeleteNotification(e, notif.id)"
              class="absolute top-2 right-2 p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors opacity-0 group-hover:opacity-100 cursor-pointer"
              title="Hapus notifikasi ini"
            >
              <TrashIcon class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Dropdown Footer -->
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between text-xs flex-shrink-0">
          <RouterLink
            :to="isOperatorSatker ? '/laporan' : '/laporan/verifikasi'"
            @click="isOpen = false"
            class="font-semibold text-[#0B2A6F] hover:text-blue-700 hover:underline flex items-center gap-1"
          >
            <span>{{ isOperatorSatker ? 'Lihat Matriks Laporan' : 'Lihat Ruang Verifikasi' }}</span>
            <ChevronRightIcon class="w-3.5 h-3.5" />
          </RouterLink>

          <button
            v-if="userNotifications.length > 0"
            @click="handleClearAll"
            type="button"
            class="text-slate-500 hover:text-red-600 font-medium transition-colors cursor-pointer text-[11px]"
          >
            Hapus Riwayat
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
