<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from '@/components/common/AppSidebar.vue'
import AppHeader from '@/components/common/AppHeader.vue'

const authStore = useAuthStore()
const router = useRouter()

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.replace('/login')
  } else if (!authStore.user) {
    await authStore.fetchMe()
  }
})
</script>

<template>
  <div class="flex h-screen bg-surface overflow-hidden">
    <!-- Collapsible Sidebar -->
    <AppSidebar />

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <!-- Institutional Top Bar -->
      <AppHeader />

      <!-- Scrollable Workspace -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 scrollbar-thin">
        <div class="max-w-7xl mx-auto animate-fade-in">
          <RouterView />
        </div>
      </main>
    </div>
  </div>
</template>