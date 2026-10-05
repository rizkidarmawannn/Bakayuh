import { ref, onMounted, onUnmounted, computed } from 'vue'

export function useCountdown(deadlineIso: string | null) {
  const now = ref(new Date())
  let timer: ReturnType<typeof setInterval> | null = null

  onMounted(() => {
    timer = setInterval(() => { now.value = new Date() }, 60000)
  })

  onUnmounted(() => {
    if (timer) clearInterval(timer)
  })

  const deadline = deadlineIso ? new Date(deadlineIso) : null
  const isPast = computed(() => (deadline ? now.value > deadline : false))
  const diffMs = computed(() => (deadline ? Math.max(0, deadline.getTime() - now.value.getTime()) : 0))
  const days = computed(() => Math.floor(diffMs.value / (1000 * 60 * 60 * 24)))
  const hours = computed(() => Math.floor((diffMs.value % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)))
  const minutes = computed(() => Math.floor((diffMs.value % (1000 * 60 * 60)) / (1000 * 60)))

  const label = computed(() => {
    if (!deadline) return null
    if (isPast.value) return 'Batas waktu telah berakhir'
    if (days.value > 0) return `Sisa ${days.value} hari ${hours.value} jam`
    if (hours.value > 0) return `Sisa ${hours.value} jam ${minutes.value} menit`
    return `Sisa ${minutes.value} menit`
  })

  return { isPast, days, hours, minutes, label }
}