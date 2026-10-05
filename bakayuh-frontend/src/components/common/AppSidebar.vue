<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import { useAuth } from '@/composables/useAuth'

import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const uiStore = useUiStore()
const route = useRoute()
const { isSuperAdmin, isAdminKanwil } = useAuth()

// Auto-expand Data Master if current route is under /master
const isMasterRoute = computed(() => route.path.startsWith('/master'))
const masterGroupOpen = ref(isMasterRoute.value)

watch(
  () => route.path,
  (newPath) => {
    if (newPath.startsWith('/master')) {
      masterGroupOpen.value = true
    }
  }
)

function toggleMasterGroup() {
  masterGroupOpen.value = !masterGroupOpen.value
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
          <p class="text-[10px] text-kemenkum-silver/70 leading-none">Kemenkumham Kalsel</p>
        </div>
      </RouterLink>
    </div>

    <!-- Navigation Tree -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto scrollbar-thin">
      <!-- Dashboard -->
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

      <!-- Section: KEGIATAN & RB -->
      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-kemenkum-gold/60" />
        <span>Kegiatan &amp; Reformasi</span>
      </div>

      <RouterLink
        to="/lke"
        class="nav-item group"
        :class="isActive('/lke') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/lke') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span class="text-xs font-medium">LKE WBK / WBBM</span>
      </RouterLink>

      <RouterLink
        to="/rkt/general"
        class="nav-item group"
        :class="route.path === '/rkt/general' ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="route.path === '/rkt/general' ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span class="text-xs font-medium">RKT RB General</span>
      </RouterLink>

      <RouterLink
        to="/rkt/tematik"
        class="nav-item group"
        :class="route.path === '/rkt/tematik' ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="route.path === '/rkt/tematik' ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span class="text-xs font-medium">RKT RB Tematik</span>
      </RouterLink>

      <RouterLink
        to="/rkt/meso"
        class="nav-item group"
        :class="route.path === '/rkt/meso' ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="route.path === '/rkt/meso' ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <span class="text-xs font-medium">RKT RB Meso</span>
      </RouterLink>

      <!-- Section: E-PERFORMANCE -->
      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-400/60" />
        <span>E-Performance</span>
      </div>

      <RouterLink
        to="/iku"
        class="nav-item group"
        :class="route.path === '/iku' ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="route.path === '/iku' ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
        <span class="text-xs font-medium">Target &amp; Realisasi IKU</span>
      </RouterLink>

      <RouterLink
        to="/iku/matriks"
        class="nav-item group"
        :class="isActive('/iku/matriks') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/iku/matriks') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-xs font-medium">Matriks Capaian IKU</span>
      </RouterLink>

      <RouterLink
        to="/renaksi"
        class="nav-item group"
        :class="isActive('/renaksi') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/renaksi') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-xs font-medium">Rencana Aksi (Renaksi)</span>
      </RouterLink>

      <RouterLink
        v-if="isAdminKanwil || isSuperAdmin"
        to="/sakip"
        class="nav-item group"
        :class="isActive('/sakip') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/sakip') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        <span class="text-xs font-medium">Evaluasi SAKIP</span>
      </RouterLink>

      <!-- Section: ADMINISTRASI & MASTER (Collapsible Master Group + Pengguna) -->
      <template v-if="isAdminKanwil || isSuperAdmin">
        <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400/60" />
          <span>Pengaturan &amp; Master</span>
        </div>

        <!-- Collapsible Data Master Group -->
        <div class="space-y-1">
          <button
            type="button"
            class="nav-item w-full flex items-center justify-between group"
            :class="isMasterRoute ? 'bg-white/10 text-white font-semibold' : 'nav-item-inactive'"
            @click="toggleMasterGroup"
          >
            <div class="flex items-center gap-3">
              <svg class="w-4 h-4 transition-colors" :class="isMasterRoute ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
              </svg>
              <span class="text-xs font-semibold">Data Master</span>
            </div>
            <svg
              class="w-3.5 h-3.5 text-kemenkum-silver/60 transition-transform duration-200"
              :class="{ 'rotate-180 text-kemenkum-gold': masterGroupOpen }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Master Submenu Items -->
          <div
            v-show="masterGroupOpen"
            class="ml-5 pl-2.5 border-l border-white/15 space-y-1 py-1 transition-all"
          >
            <RouterLink
              to="/master/satker"
              class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-colors"
              :class="isActive('/master/satker')
                ? 'bg-kemenkum-gold/20 text-kemenkum-gold font-bold'
                : 'text-kemenkum-silver/80 hover:text-white hover:bg-white/5 font-medium'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="isActive('/master/satker') ? 'bg-kemenkum-gold' : 'bg-white/30'" />
              <span>Satuan Kerja (17 Satker)</span>
            </RouterLink>

            <RouterLink
              v-if="isSuperAdmin"
              to="/master/tahun"
              class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-colors"
              :class="isActive('/master/tahun')
                ? 'bg-kemenkum-gold/20 text-kemenkum-gold font-bold'
                : 'text-kemenkum-silver/80 hover:text-white hover:bg-white/5 font-medium'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="isActive('/master/tahun') ? 'bg-kemenkum-gold' : 'bg-white/30'" />
              <span>Tahun Anggaran</span>
            </RouterLink>

            <RouterLink
              to="/master/iku"
              class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs transition-colors"
              :class="isActive('/master/iku')
                ? 'bg-kemenkum-gold/20 text-kemenkum-gold font-bold'
                : 'text-kemenkum-silver/80 hover:text-white hover:bg-white/5 font-medium'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="isActive('/master/iku') ? 'bg-kemenkum-gold' : 'bg-white/30'" />
              <span>Indikator Kinerja (IKU)</span>
            </RouterLink>
          </div>
        </div>

        <!-- Manajemen Pengguna (Super Admin Only) -->
        <RouterLink
          v-if="isSuperAdmin"
          to="/pengguna"
          class="nav-item group"
          :class="isActive('/pengguna') ? 'nav-item-active' : 'nav-item-inactive'"
        >
          <svg class="w-4 h-4 transition-colors" :class="isActive('/pengguna') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span class="text-xs font-semibold">Manajemen Pengguna</span>
        </RouterLink>
      </template>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-white/10 text-center">
      <p class="text-[11px] text-kemenkum-silver/60 font-medium">Kanwil Kemenkum Kalsel</p>
      <p class="text-[9px] text-kemenkum-silver/40 mt-0.5">Versi 1.0 (2026)</p>
    </div>
  </aside>
</template>