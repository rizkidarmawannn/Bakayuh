import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { apiClient } from '@/composables/useApi'
import type { User, LoginCredentials } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('bakayuh_token'))

  const isAuthenticated = computed(() => !!token.value)

  async function login(credentials: LoginCredentials): Promise<void> {
    const res = await apiClient.post<{ data: { token: string; user: User } }>(
      '/auth/login',
      credentials
    )
    token.value = res.data.data.token
    user.value = res.data.data.user
    localStorage.setItem('bakayuh_token', token.value)
  }

  async function fetchMe(): Promise<void> {
    if (!token.value) return
    try {
      const res = await apiClient.get<{ data: User }>('/auth/me')
      user.value = res.data.data
    } catch {
      await logout()
    }
  }

  async function logout(): Promise<void> {
    try {
      if (token.value) {
        await apiClient.post('/auth/logout')
      }
    } catch {
      // silent
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('bakayuh_token')
    }
  }

  return { user, token, isAuthenticated, login, logout, fetchMe }
})