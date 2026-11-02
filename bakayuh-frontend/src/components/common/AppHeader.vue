<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useUiStore } from '@/stores/ui'
import { useAuth } from '@/composables/useAuth'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'
import NotificationDropdown from '@/components/common/NotificationDropdown.vue'

const uiStore = useUiStore()
const { user, isOperatorSatker, isSuperAdmin, isAdminKanwil, userUnitCode } = useAuth()

const userDisplayRole = computed(() => {
  if (isOperatorSatker.value) {
    return userUnitCode.value ? `Satker ${userUnitCode.value}` : 'Operator Satker'
  }
  if (isSuperAdmin.value) return 'Super Admin'
  if (isAdminKanwil.value) return 'Admin Kanwil'
  return 'Viewer'
})

const userInitials = computed(() => {
  const name = user.value?.name || user.value?.username || 'U'
  return name.slice(0, 2).toUpperCase()
})
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

      <!-- Right Header Actions -->
      <div class="flex items-center gap-2 sm:gap-3">
        <!-- Notification Dropdown in Top Right Header -->
        <NotificationDropdown />

        <!-- Vertical Separator -->
        <div class="h-6 w-px bg-white/15 hidden sm:block" />

        <!-- User Profile Quick Chip -->
        <RouterLink
          to="/profil"
          class="flex items-center gap-2.5 p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl hover:bg-white/10 transition-colors group text-left"
          title="Buka Halaman Profil"
        >
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-kemenkum-gold to-amber-500 text-slate-900 font-extrabold text-xs flex items-center justify-center shadow-sm flex-shrink-0 group-hover:scale-105 transition-transform">
            {{ userInitials }}
          </div>
          <div class="hidden md:flex flex-col">
            <span class="text-xs font-bold text-white group-hover:text-kemenkum-gold transition-colors leading-tight truncate max-w-[140px]">
              {{ user?.name || user?.username || 'Pengguna' }}
            </span>
            <span class="text-[10px] text-kemenkum-silver font-medium leading-none mt-0.5">
              {{ userDisplayRole }}
            </span>
          </div>
        </RouterLink>
      </div>
    </div>
  </header>
</template>
