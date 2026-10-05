import axios, { type AxiosError, type AxiosResponse } from 'axios'
import { ref } from 'vue'

export const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('bakayuh_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401 && !window.location.pathname.includes('/login')) {
      localStorage.removeItem('bakayuh_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export function extractErrors(err: unknown): Record<string, string> {
  const axiosErr = err as AxiosError<{ errors?: Record<string, string[]> }>
  const errors = axiosErr.response?.data?.errors ?? {}
  return Object.fromEntries(
    Object.entries(errors).map(([key, msgs]) => [key, msgs?.[0] ?? ''])
  )
}