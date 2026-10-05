import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // Public routes
    {
      path: '/',
      name: 'home',
      component: () => import('@/pages/publik/PublicPerformancePage.vue'),
      meta: { public: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { public: true },
    },
    {
      path: '/publik',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        {
          path: '',
          name: 'publik',
          component: () => import('@/pages/publik/PublikPage.vue'),
          meta: { public: true },
        },
      ],
    },
    // Authenticated application routes
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      children: [
        { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') },
        // IKU
        { path: 'iku', name: 'iku.index', component: () => import('@/pages/iku/IkuIndexPage.vue') },
        { path: 'iku/matriks', name: 'iku.matriks', component: () => import('@/pages/iku/IkuMatriksPage.vue') },
        // Renaksi
        { path: 'renaksi', name: 'renaksi.index', component: () => import('@/pages/renaksi/RenaksiIndexPage.vue') },
        { path: 'renaksi/:id', name: 'renaksi.detail', component: () => import('@/pages/renaksi/RenaksiDetailPage.vue') },
        // SAKIP
        {
          path: 'sakip',
          name: 'sakip.index',
          component: () => import('@/pages/sakip/SakipIndexPage.vue'),
          meta: { roles: ['super_admin', 'admin_kanwil'] },
        },
        {
          path: 'sakip/:satkerId',
          name: 'sakip.detail',
          component: () => import('@/pages/sakip/SakipDetailPage.vue'),
          meta: { roles: ['super_admin', 'admin_kanwil'] },
        },
        // LKE WBK/WBBM
        { path: 'lke', name: 'lke.index', component: () => import('@/pages/lke/LkeIndexPage.vue') },
        { path: 'lke/:id', name: 'lke.detail', component: () => import('@/pages/lke/LkeDetailPage.vue') },
        // RKT RB
        { path: 'rkt', redirect: '/rkt/general' },
        { path: 'rkt/general', name: 'rkt.general', component: () => import('@/pages/rkt/RktIndexPage.vue'), props: { defaultCategory: 'rkt_general' } },
        { path: 'rkt/tematik', name: 'rkt.tematik', component: () => import('@/pages/rkt/RktIndexPage.vue'), props: { defaultCategory: 'rkt_tematik' } },
        { path: 'rkt/meso', name: 'rkt.meso', component: () => import('@/pages/rkt/RktIndexPage.vue'), props: { defaultCategory: 'rkt_meso' } },
        // Master
        {
          path: 'master/satker',
          name: 'master.satker',
          component: () => import('@/pages/master/MasterSatkerPage.vue'),
          meta: { roles: ['super_admin', 'admin_kanwil'] },
        },
        {
          path: 'master/tahun',
          name: 'master.tahun',
          component: () => import('@/pages/master/MasterTahunPage.vue'),
          meta: { roles: ['super_admin'] },
        },
        {
          path: 'master/iku',
          name: 'master.iku',
          component: () => import('@/pages/master/MasterIkuPage.vue'),
          meta: { roles: ['super_admin', 'admin_kanwil'] },
        },
        // Pengguna
        {
          path: 'pengguna',
          name: 'pengguna.index',
          component: () => import('@/pages/users/PenggunaPage.vue'),
          meta: { roles: ['super_admin'] },
        },
        // Profil
        { path: 'profil', name: 'profil', component: () => import('@/pages/auth/ProfilPage.vue') },
      ],
    },
    // Fallback
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  if (to.meta.public) return true

  if (!authStore.isAuthenticated) {
    return '/login'
  }

  if (!authStore.user) {
    await authStore.fetchMe()
  }

  const requiredRoles = to.meta.roles as string[] | undefined
  if (requiredRoles && authStore.user) {
    if (!requiredRoles.includes(authStore.user.role)) {
      return '/dashboard'
    }
  }

  return true
})

export default router