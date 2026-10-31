<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const uiStore = useUiStore()
</script>

<template>
  <header class="bg-kemenkum-navy border-b border-white/10 shadow-sm z-20 flex-shrink-0">
    <div class="flex items-center h-16 px-4 sm:px-6 gap-3 sm:gap-4">
      <!-- Sidebar Toggle Button -->
      <button
        type="button"
        @click="uiStore.toggleSidebar()"
        class="text-kemenkum-silver hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none cursor-pointer"
        aria-label="Toggle navigation"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Clickable Brand / Institution Header - shown only when sidebar is closed -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-x-2"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 -translate-x-2"
      >
        <RouterLink
          v-if="!uiStore.sidebarOpen"
          to="/dashboard"
          class="flex items-center gap-3 group select-none"
        >
          <LogoPengayoman :size="38" :show-text="false" class="group-hover:scale-105 transition-transform duration-150" />
          <div class="flex flex-col">
            <span class="text-white font-extrabold text-base tracking-wider group-hover:text-kemenkum-gold transition-colors leading-none">
              BAKAYUH
            </span>
            <span class="text-kemenkum-silver text-[11px] font-medium leading-tight tracking-normal mt-1">
              Kanwil Kementerian Hukum Kalimantan Selatan
            </span>
          </div>
        </RouterLink>
      </Transition>

      <!-- Spacer -->
      <div class="flex-1" />
    </div>
  </header>
</template>
