<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { extractErrors } from '@/composables/useApi'
import LogoPengayoman from '@/components/icons/LogoPengayoman.vue'

const router = useRouter()
const authStore = useAuthStore()
const { error: toastError, success: toastSuccess } = useToast()

const form = reactive({
  username: '',
  password: '',
})

const errors = reactive<Record<string, string>>({})
const loading = ref(false)
const showPassword = ref(false)

async function handleLogin() {
  loading.value = true
  Object.keys(errors).forEach((k) => delete errors[k])

  try {
    await authStore.login({
      username: form.username.trim(),
      email: form.username.trim(),
      password: form.password,
    })
    toastSuccess('Login Berhasil', `Selamat datang, ${authStore.user?.name}!`)
    router.replace('/dashboard')
  } catch (err: any) {
    const extracted = extractErrors(err)
    if (Object.keys(extracted).length) {
      Object.assign(errors, extracted)
    } else {
      toastError('Login Gagal', err.response?.data?.message ?? 'Username atau password yang Anda masukkan salah.')
    }
  } finally {
    loading.value = false
  }
}

function fillDemo(username: string, pass: string) {
  form.username = username
  form.password = pass
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-kemenkum-navy-dark via-kemenkum-navy to-kemenkum-navy-light flex items-center justify-center p-4">
    <!-- Center Card -->
    <div class="w-full max-w-md animate-fade-in">
      <!-- Institutional Emblem & Branding -->
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center mb-2">
          <LogoPengayoman :size="72" :show-text="true" />
        </div>
        <h1 class="text-2xl font-black text-white tracking-wider mt-2">BAKAYUH</h1>
        <p class="text-xs font-semibold text-kemenkum-gold tracking-wide mt-0.5 uppercase">
          E-Performance &amp; E-RB Terpadu
        </p>
        <p class="text-xs text-kemenkum-silver/80 mt-1">
          Kantor Wilayah Kementerian Hukum Kalimantan Selatan
        </p>
      </div>

      <!-- Login Form Card -->
      <div class="card p-6 sm:p-8 bg-white/95 backdrop-blur shadow-2xl border-white/20">
        <h2 class="text-base font-bold text-slate-800 mb-1">Masuk ke Sistem</h2>
        <p class="text-xs text-slate-500 mb-5">Gunakan akun Kemenkum yang telah terdaftar</p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <!-- Username / Email Input -->
          <div>
            <label class="form-label text-xs">Username atau Email</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="form-input"
              placeholder="Contoh: admin atau ahu"
              autocomplete="username"
            />
            <p v-if="errors.username || errors.email" class="text-xs text-red-600 mt-1">
              {{ errors.username || errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div>
            <label class="form-label text-xs">Kata Sandi</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="form-input pr-16"
                placeholder="••••••••"
                autocomplete="current-password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-600 font-medium"
              >
                {{ showPassword ? 'Tutup' : 'Lihat' }}
              </button>
            </div>
            <p v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="btn-primary w-full justify-center py-2.5 mt-2 font-semibold shadow"
            :disabled="loading"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            <span>{{ loading ? 'Memverifikasi...' : 'Masuk Sistem' }}</span>
          </button>
        </form>

        <!-- Quick Demo Accounts for testing -->
        <div class="mt-6 pt-4 border-t border-slate-100">
          <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Akun Pengujian Sistem:</p>
          <div class="space-y-2">
            <!-- Admin -->
            <button
              type="button"
              @click="fillDemo('admin', 'Kanwil123')"
              class="w-full px-3 py-1.5 text-left bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200 text-slate-700 flex items-center justify-between text-xs"
            >
              <span class="font-bold text-slate-800">admin</span>
              <span class="text-[11px] text-slate-500 font-mono">Kanwil123</span>
            </button>

            <!-- 6 Unit Accounts -->
            <div class="grid grid-cols-3 gap-1.5 pt-1">
              <button
                v-for="u in ['ahu', 'ki', 'setjen', 'bsk', 'bhpn', 'pp']"
                :key="u"
                type="button"
                @click="fillDemo(u, 'Daduk@2026')"
                class="px-2 py-1 text-center bg-slate-50 hover:bg-slate-100 rounded border border-slate-200 text-xs text-slate-700 font-semibold uppercase hover:border-slate-300"
              >
                {{ u }}
              </button>
            </div>
            <p class="text-[10px] text-slate-400 text-center">Password unit: <code class="text-slate-600 font-mono">Daduk@2026</code></p>
          </div>
        </div>
      </div>

      <!-- Public Portal Link -->
      <div class="text-center mt-5">
        <RouterLink to="/" class="text-xs text-kemenkum-silver hover:text-white transition-colors underline">
          &larr; Lihat Portal Transparansi Publik (Tanpa Login)
        </RouterLink>
      </div>
    </div>
  </div>
</template>