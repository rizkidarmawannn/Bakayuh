<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const uiStore = useUiStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const { isOperatorSatker, isSuperAdmin } = useAuth()
const { success } = useToast()

const userMenuOpen = ref(false)

async function handleLogout() {
  userMenuOpen.value = false
  await authStore.logout()
  success('Berhasil keluar', 'Sampai jumpa kembali.')
  router.replace('/login')
}

function onDocClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.user-account-container')) {
    userMenuOpen.value = false
  }
}

watch(
  () => route.path,
  () => {
    userMenuOpen.value = false
  },
)

onMounted(() => {
  document.addEventListener('click', onDocClick)
})

onUnmounted(() => {
  document.removeEventListener('click', onDocClick)
})

// Renaksi Submenu State (Monitoring Daduk, Sasaran & Renaksi, Verifikasi Daduk)
const isRenaksiRoute = computed(() => {
  return (
    route.path.startsWith('/renaksi') ||
    route.path === '/monitoring-daduk' ||
    route.path === '/sasaran-renaksi' ||
    route.path === '/verifikasi'
  )
})
const isMonitoringDadukActive = computed(() => route.path === '/monitoring-daduk')
const isSasaranRenaksiActive = computed(() => route.path === '/sasaran-renaksi' || route.path.startsWith('/renaksi/'))
const isVerifikasiDadukActive = computed(() => route.path === '/verifikasi')
const renaksiMenuOpen = ref(isRenaksiRoute.value)

watch(isRenaksiRoute, (val) => {
  if (val) renaksiMenuOpen.value = true
})

function toggleRenaksiMenu() {
  renaksiMenuOpen.value = !renaksiMenuOpen.value
}

// Laporan Submenu State (Matriks Laporan, Verifikasi Laporan, Jenis Laporan)
const isLaporanRoute = computed(() => route.path.startsWith('/laporan'))
const isMatriksLaporanActive = computed(() => route.path === '/laporan' || route.path === '/laporan/matriks')
const isVerifikasiLaporanActive = computed(() => route.path === '/laporan/verifikasi')
const isJenisLaporanActive = computed(() => route.path === '/laporan/jenis')
const laporanMenuOpen = ref(isLaporanRoute.value)

watch(isLaporanRoute, (val) => {
  if (val) laporanMenuOpen.value = true
})

function toggleLaporanMenu() {
  laporanMenuOpen.value = !laporanMenuOpen.value
}

// Pohon Kinerja Submenu State (Lihat Pohon, Kelola Pohon)
const isPohonRoute = computed(() => route.path.startsWith('/pohon-kinerja'))
const isLihatPohonActive = computed(() => route.path === '/pohon-kinerja' || route.path === '/pohon-kinerja/lihat')
const isKelolaPohonActive = computed(() => route.path === '/pohon-kinerja/kelola')
const pohonMenuOpen = ref(isPohonRoute.value)

watch(isPohonRoute, (val) => {
  if (val) pohonMenuOpen.value = true
})

function togglePohonMenu() {
  pohonMenuOpen.value = !pohonMenuOpen.value
}

function isActive(to?: string): boolean {
  if (!to) return false
  if (to === '/dashboard') return route.path === '/dashboard'
  return route.path === to || route.path.startsWith(to + '/')
}
</script>

<template>
  <aside
    class="bg-kemenkum-navy-dark flex-shrink-0 flex flex-col h-full shadow-sidebar transition-all duration-300 z-10"
    :class="uiStore.sidebarOpen ? 'w-64' : 'w-0 -translate-x-full overflow-hidden'"
  >
    <!-- Brand Title in Sidebar with Official Pengayoman Logo (Clickable to Dashboard) -->
    <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
      <RouterLink to="/dashboard" class="flex items-center gap-3 group select-none hover:opacity-90 transition-opacity">
        <LogoPengayoman :size="34" :show-text="false" class="flex-shrink-0 group-hover:scale-105 transition-transform" />
        <div>
          <h2 class="text-white font-extrabold text-sm tracking-wider group-hover:text-kemenkum-gold transition-colors">BAKAYUH</h2>
          <p class="text-[10px] text-kemenkum-silver/70 leading-none">Kanwil Kemenkum Kalsel</p>
        </div>
      </RouterLink>
    </div>

    <!-- Navigation Tree matching reference screenshots -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
      <!-- 1. Dashboard -->
      <RouterLink
        to="/dashboard"
        class="nav-item group"
        :class="isActive('/dashboard') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/dashboard') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-xs font-semibold">Dashboard</span>
      </RouterLink>

      <!-- Section: MODUL -->
      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span>MODUL</span>
      </div>

      <!-- 2. Renaksi -->
      <!-- Case A: Operator Satker (Direct Single Item without dropdown per screenshot) -->
      <RouterLink
        v-if="isOperatorSatker"
        to="/sasaran-renaksi"
        class="nav-item group"
        :class="isRenaksiRoute ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isRenaksiRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <span class="text-xs font-semibold">Renaksi</span>
      </RouterLink>

      <!-- Case B: Admin / Super Admin (Collapsible with 3 sub-items) -->
      <div v-else>
        <button
          type="button"
          @click="toggleRenaksiMenu"
          class="w-full nav-item group justify-between cursor-pointer"
          :class="isRenaksiRoute ? 'nav-item-active' : 'nav-item-inactive'"
        >
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 transition-colors" :class="isRenaksiRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="text-xs font-semibold">Renaksi</span>
          </div>
          <svg
            class="w-3.5 h-3.5 transition-transform duration-200 text-kemenkum-silver/70 group-hover:text-white"
            :class="renaksiMenuOpen ? 'rotate-180 text-white' : ''"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Submenu: Monitoring Daduk, Sasaran & Renaksi, Verifikasi Daduk -->
        <div v-show="renaksiMenuOpen" class="mt-1 pl-6 pr-1 space-y-0.5">
          <RouterLink
            to="/monitoring-daduk"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isMonitoringDadukActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isMonitoringDadukActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Monitoring Daduk</span>
          </RouterLink>

          <RouterLink
            to="/sasaran-renaksi"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isSasaranRenaksiActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isSasaranRenaksiActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Sasaran &amp; Renaksi</span>
          </RouterLink>

          <RouterLink
            to="/verifikasi"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isVerifikasiDadukActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isVerifikasiDadukActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Verifikasi Daduk</span>
          </RouterLink>
        </div>
      </div>

      <!-- 3. Capaian PK -->
      <RouterLink
        to="/capaian-pk"
        class="nav-item group"
        :class="isActive('/capaian-pk') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/capaian-pk') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
        <span class="text-xs font-semibold">Capaian PK</span>
      </RouterLink>

      <!-- 4. Laporan -->
      <!-- Case A: Operator Satker (Direct Single Item without dropdown per screenshot) -->
      <RouterLink
        v-if="isOperatorSatker"
        to="/laporan"
        class="nav-item group"
        :class="isLaporanRoute ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isLaporanRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-xs font-semibold">Laporan</span>
      </RouterLink>

      <!-- Case B: Admin / Super Admin (Collapsible with 3 sub-items) -->
      <div v-else>
        <button
          type="button"
          @click="toggleLaporanMenu"
          class="w-full nav-item group justify-between cursor-pointer"
          :class="isLaporanRoute ? 'nav-item-active' : 'nav-item-inactive'"
        >
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 transition-colors" :class="isLaporanRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xs font-semibold">Laporan</span>
          </div>
          <svg
            class="w-3.5 h-3.5 transition-transform duration-200 text-kemenkum-silver/70 group-hover:text-white"
            :class="laporanMenuOpen ? 'rotate-180 text-white' : ''"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Submenu: Matriks Laporan, Verifikasi Laporan, Jenis Laporan -->
        <div v-show="laporanMenuOpen" class="mt-1 pl-6 pr-1 space-y-0.5">
          <RouterLink
            to="/laporan"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isMatriksLaporanActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isMatriksLaporanActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Matriks Laporan</span>
          </RouterLink>

          <RouterLink
            to="/laporan/verifikasi"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isVerifikasiLaporanActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isVerifikasiLaporanActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Verifikasi Laporan</span>
          </RouterLink>

          <RouterLink
            to="/laporan/jenis"
            class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
            :class="isJenisLaporanActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
          >
            <span v-if="isJenisLaporanActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
            <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
            <span>Jenis Laporan</span>
          </RouterLink>
        </div>
      </div>

      <!-- Admin-Only Modules: Hidden from Operator Satker per screenshots -->
      <template v-if="!isOperatorSatker">
        <!-- 5. Pohon Kinerja (Collapsible with 2 sub-items: Lihat Pohon & Kelola Pohon) -->
        <div>
          <button
            type="button"
            @click="togglePohonMenu"
            class="w-full nav-item group justify-between cursor-pointer"
            :class="isPohonRoute ? 'nav-item-active' : 'nav-item-inactive'"
          >
            <div class="flex items-center gap-3">
              <svg class="w-4 h-4 transition-colors" :class="isPohonRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a1.5 1.5 0 003 0m0-2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3" />
              </svg>
              <span class="text-xs font-semibold">Pohon Kinerja</span>
            </div>
            <svg
              class="w-3.5 h-3.5 transition-transform duration-200 text-kemenkum-silver/70 group-hover:text-white"
              :class="pohonMenuOpen ? 'rotate-180 text-white' : ''"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Submenu: Lihat Pohon & Kelola Pohon -->
          <div v-show="pohonMenuOpen" class="mt-1 pl-6 pr-1 space-y-0.5">
            <RouterLink
              to="/pohon-kinerja"
              class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
              :class="isLihatPohonActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
            >
              <span v-if="isLihatPohonActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
              <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
              <span>Lihat Pohon</span>
            </RouterLink>
            <RouterLink
              to="/pohon-kinerja/kelola"
              class="flex items-center px-3 py-1.5 rounded-lg text-xs transition-colors"
              :class="isKelolaPohonActive ? 'text-white font-bold bg-white/5' : 'text-kemenkum-silver/80 hover:bg-white/5 hover:text-white'"
            >
              <span v-if="isKelolaPohonActive" class="w-1 h-3.5 bg-[#F5BA31] rounded-full mr-2.5 flex-shrink-0" />
              <span v-else class="w-1 h-3.5 mr-2.5 flex-shrink-0" />
              <span>Kelola Pohon</span>
            </RouterLink>
          </div>
        </div>

        <!-- 6. IKPA -->
        <RouterLink
          to="/ikpa"
          class="nav-item group"
          :class="isActive('/ikpa') ? 'nav-item-active' : 'nav-item-inactive'"
        >
          <svg class="w-4 h-4 transition-colors" :class="isActive('/ikpa') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-xs font-semibold">IKPA</span>
        </RouterLink>

        <!-- 7. SAKIP -->
        <RouterLink
          to="/sakip"
          class="nav-item group"
          :class="isActive('/sakip') ? 'nav-item-active' : 'nav-item-inactive'"
        >
          <svg class="w-4 h-4 transition-colors" :class="isActive('/sakip') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
          </svg>
          <span class="text-xs font-semibold">SAKIP</span>
        </RouterLink>

        <!-- Section: PENGATURAN (Super Admin Only) -->
        <template v-if="isSuperAdmin">
          <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
            <span>PENGATURAN</span>
          </div>

          <!-- 8. Kelola Akun -->
          <RouterLink
            to="/kelola-akun"
            class="nav-item group"
            :class="isActive('/kelola-akun') ? 'nav-item-active' : 'nav-item-inactive'"
          >
            <svg class="w-4 h-4 transition-colors" :class="isActive('/kelola-akun') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-xs font-semibold">Kelola Akun</span>
          </RouterLink>
        </template>
      </template>
    </nav>

    <!-- Detail Akun Pengguna di Sidebar Paling Bawah Kiri -->
    <div class="p-3 border-t border-white/10 relative user-account-container bg-black/15">
      <!-- Upward Dropdown Menu -->
      <Transition
        enter-active-class="transition ease-out duration-150"
        enter-from-class="opacity-0 translate-y-2 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-2 scale-95"
      >
        <div
          v-if="userMenuOpen"
          class="absolute bottom-full left-3 right-3 mb-2 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 overflow-hidden"
        >
          <!-- User info header -->
          <div class="px-4 py-2 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-800 truncate">{{ authStore.user?.name ?? 'Pengguna' }}</p>
            <p class="text-[11px] text-slate-500 capitalize truncate">{{ authStore.user?.role_label ?? authStore.user?.role }}</p>
          </div>

          <!-- Profil & Password -->
          <RouterLink
            to="/profil"
            class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 font-medium transition-colors cursor-pointer"
            @click="userMenuOpen = false"
          >
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profil & Password</span>
          </RouterLink>

          <div class="my-1 border-t border-slate-100" />

          <!-- Keluar Sistem -->
          <button
            type="button"
            @click="handleLogout"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-medium transition-colors text-left cursor-pointer"
          >
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Keluar Sistem</span>
          </button>
        </div>
      </Transition>

      <!-- Account Trigger Button -->
      <button
        type="button"
        @click="userMenuOpen = !userMenuOpen"
        class="w-full flex items-center justify-between p-2 rounded-xl text-white hover:bg-white/10 transition-colors focus:outline-none cursor-pointer group text-left"
        :class="userMenuOpen ? 'bg-white/10 ring-1 ring-white/10' : ''"
      >
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-kemenkum-gold to-amber-300 flex items-center justify-center text-xs font-bold text-kemenkum-navy shadow-inner flex-shrink-0">
            {{ authStore.user?.name?.charAt(0).toUpperCase() ?? 'U' }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-white leading-tight truncate group-hover:text-kemenkum-gold transition-colors">
              {{ authStore.user?.name ?? 'Pengguna' }}
            </p>
            <p class="text-[10px] text-kemenkum-silver/80 leading-tight capitalize truncate mt-0.5">
              {{ authStore.user?.role_label ?? authStore.user?.role }}
            </p>
          </div>
        </div>

        <svg
          class="w-4 h-4 text-kemenkum-silver/70 group-hover:text-white transition-transform duration-200 flex-shrink-0 ml-1.5"
          :class="userMenuOpen ? 'rotate-180 text-white' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
      </button>

      <!-- Subtext Versi -->
      <div class="px-2 pt-2 flex items-center justify-between text-[10px] text-kemenkum-silver/40">
        <span>Kanwil Kemenkum Kalsel</span>
        <span>v2.0 (2026)</span>
      </div>
    </div>
  </aside>
</template>
