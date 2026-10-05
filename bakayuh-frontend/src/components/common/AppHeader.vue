<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { useToast } from '@/composables/useToast'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()
const router = useRouter()
const { success } = useToast()

const userMenuOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  success('Berhasil keluar', 'Sampai jumpa kembali.')
  router.replace('/login')
}
</script>

<template>
  <header class="bg-kemenkum-navy border-b border-white/10 shadow-sm z-20 flex-shrink-0">
    <div class="flex items-center h-16 px-4 sm:px-6 gap-3 sm:gap-4">
      <!-- Sidebar Toggle Button -->
      <button
        type="button"
        @click="uiStore.toggleSidebar()"
        class="text-kemenkum-silver hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none"
        aria-label="Toggle navigation"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Clickable Brand / Institution Header (PRD 8.1) - shown only when sidebar is closed -->
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
            <span class="text-white font-extrabold text-sm sm:text-base leading-tight tracking-wider group-hover:text-kemenkum-gold transition-colors">
              KEMENTERIAN HUKUM
            </span>
            <span class="text-kemenkum-silver text-[11px] sm:text-xs font-medium leading-tight tracking-wider">
              KANTOR WILAYAH KALIMANTAN SELATAN
            </span>
          </div>
        </RouterLink>
      </Transition>

      <!-- Spacer -->
      <div class="flex-1" />


      <!-- User Menu Profile -->
      <div class="relative">
        <button
          type="button"
          @click="userMenuOpen = !userMenuOpen"
          class="flex items-center gap-2.5 text-white hover:bg-white/10 rounded-xl px-2.5 py-1.5 transition-colors focus:outline-none"
        >
          <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-kemenkum-gold to-amber-300 flex items-center justify-center text-xs font-bold text-kemenkum-navy shadow-inner">
            {{ authStore.user?.name?.charAt(0).toUpperCase() ?? 'U' }}
          </div>
          <div class="hidden md:block text-left max-w-[160px]">
            <p class="text-xs font-semibold text-white leading-tight truncate">
              {{ authStore.user?.name ?? 'Pengguna' }}
            </p>
            <p class="text-[10px] text-kemenkum-silver leading-tight capitalize truncate mt-0.5">
              {{ authStore.user?.role_label ?? authStore.user?.role }}
            </p>
          </div>
          <svg class="w-4 h-4 text-kemenkum-silver" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="userMenuOpen"
            class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-lg border border-surface-border py-1.5 z-50 overflow-hidden"
          >
            <div class="px-4 py-2 border-b border-slate-100 md:hidden">
              <p class="text-xs font-bold text-slate-800">{{ authStore.user?.name }}</p>
              <p class="text-[11px] text-slate-500 capitalize">{{ authStore.user?.role_label }}</p>
            </div>
            <RouterLink
              to="/profil"
              class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 font-medium transition-colors"
              @click="userMenuOpen = false"
            >
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Profil & Password
            </RouterLink>
            <div class="my-1 border-t border-slate-100" />
            <button
              type="button"
              @click="handleLogout"
              class="flex w-full items-center gap-2.5 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 font-medium transition-colors text-left"
            >
              <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Keluar Sistem
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>