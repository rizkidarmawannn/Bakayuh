<script setup lang="ts">
import { ref } from 'vue'
import { RouterView, RouterLink, useRoute } from 'vue-router'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const route = useRoute()
const mobileMenuOpen = ref(false)

function isTabActive(tabName: string): boolean {
  const current = (route.query.tab as string) || 'iku'
  return current === tabName
}
</script>

<template>
  <div class="min-h-screen bg-surface flex flex-col">
    <!-- Public Header -->
    <header class="bg-kemenkum-navy border-b border-white/10 shadow-md sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Logo & Branding (Left) -->
        <RouterLink to="/publik" class="flex items-center gap-3 group select-none flex-shrink-0">
          <LogoPengayoman :size="40" :show-text="false" class="group-hover:scale-105 transition-transform duration-150 flex-shrink-0" />
          <div>
            <p class="text-white font-extrabold text-sm sm:text-base leading-tight tracking-wide group-hover:text-kemenkum-gold transition-colors">
              KEMENTERIAN HUKUM
            </p>
            <p class="text-kemenkum-silver text-[11px] leading-tight">
              Portal Transparansi Kinerja &amp; RB &mdash; Kanwil Kalsel
            </p>
          </div>
        </RouterLink>

        <!-- Right Side: Navigation Items Berdampingan dengan Tombol Login -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Desktop Navigation: E-Performance Modules -->
          <nav class="hidden md:flex items-center gap-1">
            <RouterLink
              :to="{ path: '/publik', query: { tab: 'iku' } }"
              replace
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              :class="isTabActive('iku') ? 'bg-white/15 text-kemenkum-gold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
            >
              Target &amp; Realisasi IKU
            </RouterLink>

            <RouterLink
              :to="{ path: '/publik', query: { tab: 'matriks' } }"
              replace
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              :class="isTabActive('matriks') ? 'bg-white/15 text-kemenkum-gold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
            >
              Matriks Capaian IKU
            </RouterLink>

            <RouterLink
              :to="{ path: '/publik', query: { tab: 'renaksi' } }"
              replace
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              :class="isTabActive('renaksi') ? 'bg-white/15 text-kemenkum-gold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
            >
              Rencana Aksi
            </RouterLink>

            <RouterLink
              :to="{ path: '/publik', query: { tab: 'sakip' } }"
              replace
              class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
              :class="isTabActive('sakip') ? 'bg-white/15 text-kemenkum-gold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
            >
              Evaluasi SAKIP
            </RouterLink>
          </nav>

          <RouterLink to="/login" class="btn-primary text-xs py-2 px-4 shadow">
            Login
          </RouterLink>

          <!-- Mobile Menu Button -->
          <button
            type="button"
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-kemenkum-silver hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
            aria-label="Toggle menu"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-kemenkum-navy-dark border-t border-white/10 px-4 py-3 space-y-1 animate-fade-in">
        <RouterLink
          :to="{ path: '/publik', query: { tab: 'iku' } }"
          replace
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 rounded-lg text-xs font-medium"
          :class="isTabActive('iku') ? 'bg-white/15 text-kemenkum-gold font-bold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
        >
          Target &amp; Realisasi IKU
        </RouterLink>
        <RouterLink
          :to="{ path: '/publik', query: { tab: 'matriks' } }"
          replace
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 rounded-lg text-xs font-medium"
          :class="isTabActive('matriks') ? 'bg-white/15 text-kemenkum-gold font-bold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
        >
          Matriks Capaian IKU
        </RouterLink>
        <RouterLink
          :to="{ path: '/publik', query: { tab: 'renaksi' } }"
          replace
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 rounded-lg text-xs font-medium"
          :class="isTabActive('renaksi') ? 'bg-white/15 text-kemenkum-gold font-bold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
        >
          Rencana Aksi (Renaksi)
        </RouterLink>
        <RouterLink
          :to="{ path: '/publik', query: { tab: 'sakip' } }"
          replace
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 rounded-lg text-xs font-medium"
          :class="isTabActive('sakip') ? 'bg-white/15 text-kemenkum-gold font-bold' : 'text-kemenkum-silver hover:text-white hover:bg-white/5'"
        >
          Evaluasi SAKIP
        </RouterLink>
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