<script setup lang="ts">
import { RouterView, RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-surface flex flex-col">
    <!-- Public Header -->
    <header class="bg-kemenkum-navy border-b border-white/10 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <RouterLink to="/publik" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-kemenkum-gold to-kemenkum-gold-light flex items-center justify-center shadow-md">
            <span class="text-kemenkum-navy font-black text-lg">B</span>
          </div>
          <div>
            <p class="text-white font-extrabold text-sm sm:text-base leading-tight tracking-wide">
              KEMENTERIAN HUKUM
            </p>
            <p class="text-kemenkum-silver text-[11px] leading-tight">
              Portal Transparansi Kinerja &amp; RB &mdash; Kanwil Kalsel
            </p>
          </div>
        </RouterLink>

        <RouterLink to="/login" class="btn-primary text-xs py-2 px-4 shadow">
          Masuk Aplikasi
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