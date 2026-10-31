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

  const userUnitCode = computed(() => {
    const u = authStore.user
    if (!u) return ''
    const uname = (u.username || '').toLowerCase()
    if (uname === 'ahu' || uname.includes('ahu')) return 'AHU'
    if (uname === 'ki' || uname.includes('ki')) return 'KI'
    if (uname === 'setjen' || uname.includes('setjen') || uname.includes('tu')) return 'Setjen'
    if (uname === 'bsk' || uname.includes('bsk')) return 'BSK'
    if (uname === 'bhpn' || uname === 'bphn' || uname.includes('bhpn') || uname.includes('bphn')) return 'BPHN'
    if (uname === 'pp' || uname.includes('pp')) return 'PP'

    const name = (u.name || '').toUpperCase()
    if (name.includes('AHU')) return 'AHU'
    if (name.includes('KI')) return 'KI'
    if (name.includes('SETJEN')) return 'Setjen'
    if (name.includes('BSK')) return 'BSK'
    if (name.includes('BHPN') || name.includes('BPHN')) return 'BPHN'
    if (name.includes('PP')) return 'PP'

    return ''
  })

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
    userUnitCode,
    hasRole,
  }
}
