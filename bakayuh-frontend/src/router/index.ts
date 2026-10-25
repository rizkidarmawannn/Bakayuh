import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    if (to.path === from.path) {
      return false
    }
    return { top: 0 }
  },
  routes: [
    // Public routes
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { public: true },
    },
    // Public Portal (Root URL: http://127.0.0.1:5173/)
    {
      path: '/',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        {
          path: '',
          name: 'beranda',
          component: () => import('@/pages/publik/PublikPage.vue'),
          meta: { public: true },
        },
      ],
    },
    // Redirect old /publik to /
    {
      path: '/publik',
      redirect: '/',
    },
    // Authenticated application routes
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      children: [
        { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') },

        // Dedicated Module Tabs per new design
        { path: 'sasaran-renaksi', name: 'sasaran-renaksi', component: () => import('@/pages/renaksi/SasaranRenaksiPage.vue') },
        { path: 'monitoring-daduk', name: 'monitoring-daduk', component: () => import('@/pages/renaksi/MonitoringDadukPage.vue') },
        { path: 'verifikasi', name: 'verifikasi', component: () => import('@/pages/renaksi/VerifikasiDadukPage.vue') },
        { path: 'capaian-pk', name: 'capaian-pk', component: () => import('@/pages/kinerja/CapaianPkAdminPage.vue') },
        { path: 'laporan', name: 'laporan', component: () => import('@/pages/kinerja/LaporanAdminPage.vue') },
        { path: 'pohon-kinerja', name: 'pohon-kinerja', component: () => import('@/pages/kinerja/PohonKinerjaAdminPage.vue') },
        { path: 'ikpa', name: 'ikpa', component: () => import('@/pages/kinerja/IkpaAdminPage.vue') },
        {
          path: 'kelola-akun',
          name: 'kelola-akun',
          component: () => import('@/pages/users/PenggunaPage.vue'),
          meta: { roles: ['super_admin'] },
        },

        // Backward compatibility redirects & details
        { path: 'renaksi', redirect: '/sasaran-renaksi' },
        { path: 'renaksi/:id', name: 'renaksi.detail', component: () => import('@/pages/renaksi/RenaksiDetailPage.vue') },
        { path: 'iku', redirect: '/capaian-pk' },
        { path: 'iku/matriks', redirect: '/capaian-pk' },
        { path: 'pengguna', redirect: '/kelola-akun' },

        // Profil
        { path: 'profil', name: 'profil', component: () => import('@/pages/auth/ProfilPage.vue') },

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
    if (to.path === '/iku' || to.path === '/capaian-pk') return '/?tab=capaian_pk'
    if (to.path === '/iku/matriks') return '/?tab=matriks'
    if (to.path === '/renaksi' || to.path === '/sasaran-renaksi') return '/?tab=renaksi'
    if (to.path === '/sakip' || to.path === '/ikpa') return '/?tab=ikpa'
    if (to.path === '/laporan') return '/?tab=laporan'
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