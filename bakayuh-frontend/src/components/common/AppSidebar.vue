<script setup lang="ts">
import { useRoute, RouterLink } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const uiStore = useUiStore()
const route = useRoute()

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

    <!-- Navigation Tree (Old legacy items removed per user request) -->
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

      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-400/60" />
        <span>Kinerja &amp; Rencana Aksi</span>
      </div>

      <!-- 2. Sasaran & Renaksi -->
      <RouterLink
        to="/sasaran-renaksi"
        class="nav-item group"
        :class="isActive('/sasaran-renaksi') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/sasaran-renaksi') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <span class="text-xs font-semibold">Sasaran &amp; Renaksi</span>
      </RouterLink>

      <!-- 3. Monitoring Daduk -->
      <RouterLink
        to="/monitoring-daduk"
        class="nav-item group"
        :class="isActive('/monitoring-daduk') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/monitoring-daduk') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span class="text-xs font-semibold">Monitoring Daduk</span>
      </RouterLink>

      <!-- 4. Verifikasi Daduk -->
      <RouterLink
        to="/verifikasi"
        class="nav-item group"
        :class="isActive('/verifikasi') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/verifikasi') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-xs font-semibold">Verifikasi Daduk</span>
      </RouterLink>

      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400/60" />
        <span>Pelaporan &amp; Anggaran</span>
      </div>

      <!-- 5. Capaian PK -->
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

      <!-- 6. Laporan -->
      <RouterLink
        to="/laporan"
        class="nav-item group"
        :class="isActive('/laporan') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/laporan') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-xs font-semibold">Laporan</span>
      </RouterLink>

      <!-- 7. Pohon Kinerja -->
      <RouterLink
        to="/pohon-kinerja"
        class="nav-item group"
        :class="isActive('/pohon-kinerja') ? 'nav-item-active' : 'nav-item-inactive'"
      >
        <svg class="w-4 h-4 transition-colors" :class="isActive('/pohon-kinerja') ? 'text-kemenkum-gold' : 'text-kemenkum-silver/70 group-hover:text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a1.5 1.5 0 003 0m0-2h10a2 2 0 002-2V7a2 2 0 00-2-2h-3" />
        </svg>
        <span class="text-xs font-semibold">Pohon Kinerja</span>
      </RouterLink>

      <!-- 8. IKPA -->
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

      <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-kemenkum-silver/50 uppercase tracking-widest flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-purple-400/60" />
        <span>Pengaturan Akun</span>
      </div>

      <!-- 9. Kelola Akun -->
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
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-white/10 text-center">
      <p class="text-[11px] text-kemenkum-silver/60 font-medium">Kanwil Kemenkum Kalsel</p>
      <p class="text-[9px] text-kemenkum-silver/40 mt-0.5">Versi 2.0 (2026)</p>
    </div>
  </aside>
</template>
