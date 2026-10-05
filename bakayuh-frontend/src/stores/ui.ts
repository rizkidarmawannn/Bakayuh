import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useUiStore = defineStore('ui', () => {
  const sidebarOpen = ref(true)
  const selectedTahunId = ref<number | null>(null)

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
  }

  function setTahun(id: number) {
    selectedTahunId.value = id
  }

  return { sidebarOpen, selectedTahunId, toggleSidebar, setTahun }
})