<script setup lang="ts">
import { ref } from 'vue'
import { RouterView, RouterLink, useRoute } from 'vue-router'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const route = useRoute()
const mobileMenuOpen = ref(false)

const navItems = [
  { name: 'beranda', label: 'Beranda', to: '/' },
  { name: 'pohon', label: 'Pohon Kinerja', to: { path: '/', query: { tab: 'pohon' } } },
  { name: 'renaksi', label: 'Renaksi', to: { path: '/', query: { tab: 'renaksi' } } },
  { name: 'capaian_pk', label: 'Capaian PK', to: { path: '/', query: { tab: 'capaian_pk' } } },
  { name: 'laporan', label: 'Laporan', to: { path: '/', query: { tab: 'laporan' } } },
  { name: 'ikpa', label: 'IKPA', to: { path: '/', query: { tab: 'ikpa' } } },
]

function isNavActive(itemName: string): boolean {
  const currentTab = (route.query.tab as string) || ''
  if (itemName === 'beranda') {
    return !currentTab || currentTab === 'beranda'
  }
  if (itemName === 'pohon') {
    return currentTab === 'pohon'
  }
  if (itemName === 'renaksi') {
    return currentTab === 'renaksi'
  }
  if (itemName === 'capaian_pk') {
    return currentTab === 'capaian_pk' || currentTab === 'iku' || currentTab === 'matriks'
  }
  if (itemName === 'laporan') {
    return currentTab === 'laporan'
  }
  if (itemName === 'ikpa') {
    return currentTab === 'ikpa' || currentTab === 'sakip'
  }
  return false
}
</script>

<template>
  <div class="min-h-screen bg-surface flex flex-col">
    <!-- Public Header (Matches BAKAYUH design spec) -->
    <header class="bg-[#0B2A6F] border-b border-white/10 shadow-md sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Logo & Branding (Left) -->
        <RouterLink to="/" class="flex items-center gap-2.5 select-none flex-shrink-0 group">
          <LogoPengayoman :size="36" :show-text="false" class="flex-shrink-0 group-hover:scale-105 transition-transform duration-150" />
          <div class="flex flex-col justify-center">
            <span class="text-white font-black text-base sm:text-lg tracking-wider leading-none">
              BAKAYUH
            </span>
            <span class="text-white/85 text-[10px] sm:text-[11px] leading-tight font-normal tracking-normal mt-0.5">
              Kanwil Kementerian Hukum Kalimantan Selatan
            </span>
          </div>
        </RouterLink>

        <!-- Center: Navigation Items (Beranda, Pohon Kinerja, Renaksi, Capaian PK, IKPA) -->
        <nav class="hidden md:flex items-center justify-center h-full gap-2 lg:gap-4 flex-1">
          <RouterLink
            v-for="item in navItems"
            :key="item.name"
            :to="item.to"
            replace
            class="relative h-16 flex items-center px-3 lg:px-4 text-xs sm:text-sm transition-colors duration-150 select-none group"
            :class="isNavActive(item.name) ? 'text-white font-bold' : 'text-slate-200 hover:text-white font-medium'"
          >
            <span class="py-1">{{ item.label }}</span>
            <!-- Yellow underline bar when active, exactly matching the screenshot -->
            <span
              v-if="isNavActive(item.name)"
              class="absolute bottom-0 left-2 right-2 h-[3px] bg-[#F9C823] rounded-full"
            />
          </RouterLink>
        </nav>

        <!-- Right Side: Separated Login Button & Mobile Menu Toggle -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <RouterLink
            to="/login"
            class="hidden md:inline-flex items-center text-xs font-bold px-4 py-1.5 rounded-lg text-white bg-white/10 hover:bg-white/20 transition-all border border-white/20 shadow-sm"
          >
            Login
          </RouterLink>

          <!-- Mobile Menu Button -->
          <button
            type="button"
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-slate-200 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
            aria-label="Toggle menu"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-[#091F4A] border-t border-white/10 px-4 py-3 space-y-1 animate-fade-in">
        <RouterLink
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          replace
          @click="mobileMenuOpen = false"
          class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition-colors"
          :class="isNavActive(item.name) ? 'bg-white/15 text-[#F9C823] font-bold' : 'text-slate-200 hover:text-white hover:bg-white/5 font-medium'"
        >
          <span>{{ item.label }}</span>
          <span v-if="isNavActive(item.name)" class="w-2 h-2 rounded-full bg-[#F9C823]" />
        </RouterLink>

        <div class="pt-2 border-t border-white/10 mt-2">
          <RouterLink
            to="/login"
            @click="mobileMenuOpen = false"
            class="block w-full text-center py-2 px-4 rounded-lg bg-[#F9C823] text-slate-950 font-bold text-xs hover:bg-amber-400 transition-colors shadow"
          >
            Login Sistem
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Main View -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="bg-kemenkum-navy-dark border-t border-white/10 py-6 text-center text-xs text-kemenkum-silver/70">
      <p>&copy; {{ new Date().getFullYear() }} Kantor Wilayah Kementerian Hukum Kalimantan Selatan</p>
      <p class="text-[10px] text-kemenkum-silver/40 mt-1">BAKAYUH &mdash; Basis Akuntabilitas Kanwil untuk Kinerja Yang Unggul dan Harmonis</p>
    </footer>
  </div>
</template>