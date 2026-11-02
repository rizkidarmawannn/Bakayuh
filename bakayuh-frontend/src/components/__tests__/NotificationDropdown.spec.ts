import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import { useNotificationStore } from '@/composables/useNotificationStore'
import NotificationDropdown from '../common/NotificationDropdown.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/laporan', component: { template: '<div>Laporan</div>' } },
    { path: '/laporan/verifikasi', component: { template: '<div>Verifikasi</div>' } },
  ],
})

describe('useNotificationStore & NotificationDropdown', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    const notifStore = useNotificationStore()
    notifStore.resetToDefaults()
  })

  it('filters notifications correctly for operator satker AHU', () => {
    const notifStore = useNotificationStore()
    const satkerNotifs = notifStore.getNotificationsForUser('operator_satker', 'AHU')

    // Should include notifications targeted to AHU or all satkers
    expect(satkerNotifs.length).toBeGreaterThan(0)
    expect(satkerNotifs.some((n) => n.targetRole === 'operator_satker')).toBe(true)
    // Should NOT include notifications targeted exclusively to admin
    expect(satkerNotifs.every((n) => n.targetRole !== 'admin')).toBe(true)
    // Should NOT include notifications targeted specifically to other units like KI or Setjen
    expect(satkerNotifs.every((n) => !n.targetUnit || n.targetUnit === 'AHU')).toBe(true)
  })

  it('filters notifications correctly for admin_kanwil', () => {
    const notifStore = useNotificationStore()
    const adminNotifs = notifStore.getNotificationsForUser('admin_kanwil')

    expect(adminNotifs.length).toBeGreaterThan(0)
    expect(adminNotifs.some((n) => n.type === 'menunggu_verifikasi')).toBe(true)
    expect(adminNotifs.every((n) => n.targetRole === 'admin' || n.targetRole === 'all')).toBe(true)
  })

  it('marks a notification as read and marks all as read', () => {
    const notifStore = useNotificationStore()
    const notifs = notifStore.getNotificationsForUser('operator_satker', 'AHU')
    const unread = notifs.find((n) => !n.read)
    expect(unread).toBeDefined()
    const targetId = unread!.id

    notifStore.markAsRead(targetId)
    const updated = notifStore.allNotifications.value.find((n) => n.id === targetId)
    expect(updated?.read).toBe(true)

    // Mark all as read
    notifStore.markAllAsRead('operator_satker', 'AHU')
    const allSatker = notifStore.getNotificationsForUser('operator_satker', 'AHU')
    expect(allSatker.every((n) => n.read)).toBe(true)
  })

  it('renders NotificationDropdown button and opens popover on click', async () => {
    const authStore = useAuthStore()
    authStore.user = {
      id: 1,
      name: 'Operator AHU',
      username: 'ahu',
      email: 'ahu@kemenkum.go.id',
      role: 'operator_satker',
      satker_id: 1,
    } as any

    const wrapper = mount(NotificationDropdown, {
      global: {
        plugins: [router],
      },
    })

    // Bell button should exist and have the custom image
    const button = wrapper.find('button[aria-label="Notifikasi sistem"]')
    expect(button.exists()).toBe(true)
    const img = button.find('img[alt="Notifikasi"]')
    expect(img.exists()).toBe(true)

    // Initially popover is closed
    expect(wrapper.text()).not.toContain('Semua (')

    // Click bell
    await button.trigger('click')

    // Popover is open
    expect(wrapper.text()).toContain('Notifikasi')
    expect(wrapper.text()).toContain('Tenggat Waktu')
    expect(wrapper.text()).toContain('Verifikasi')
  })
})
