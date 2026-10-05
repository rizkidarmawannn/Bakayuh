import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { UserRole } from '@/types'

export function useAuth() {
  const authStore = useAuthStore()

  const user = computed(() => authStore.user)
  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const role = computed(() => authStore.user?.role)

  const isSuperAdmin = computed(() => authStore.user?.role === 'super_admin')
  const isAdminKanwil = computed(() => authStore.user?.role === 'admin_kanwil')
  const isOperatorSatker = computed(() => authStore.user?.role === 'operator_satker')
  const isViewer = computed(() => authStore.user?.role === 'viewer')
  const canVerify = computed(() => ['super_admin', 'admin_kanwil'].includes(authStore.user?.role ?? ''))

  const hasRole = (...roles: UserRole[]) => {
    return authStore.user ? roles.includes(authStore.user.role) : false
  }

  return {
    user,
    isAuthenticated,
    role,
    isSuperAdmin,
    isAdminKanwil,
    isOperatorSatker,
    isViewer,
    canVerify,
    hasRole,
  }
}