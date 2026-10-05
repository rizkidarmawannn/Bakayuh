<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { apiClient, extractErrors } from '@/composables/useApi'
import { useToast } from '@/composables/useToast'

const { user } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const errors = reactive<Record<string, string>>({})
const loading = ref(false)

async function handleUpdatePassword() {
  loading.value = true
  Object.keys(errors).forEach((k) => delete errors[k])

  try {
    await apiClient.put('/auth/password', form)
    toastSuccess('Password Berhasil Diperbarui', 'Gunakan password baru pada login berikutnya.')
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
  } catch (err: any) {
    const extracted = extractErrors(err)
    if (Object.keys(extracted).length) {
      Object.assign(errors, extracted)
    } else {
      toastError('Gagal memperbarui password', err.response?.data?.message)
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="space-y-6 max-w-3xl">
    <div>
      <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Profil &amp; Keamanan Akun</h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Informasi akun pengguna dan perubahan kata sandi</p>
    </div>

    <!-- User Information Card -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Informasi Pengguna</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
          <span class="text-slate-400 block mb-0.5">Nama Lengkap</span>
          <span class="font-bold text-slate-800 text-sm">{{ user?.name }}</span>
        </div>
        <div>
          <span class="text-slate-400 block mb-0.5">Alamat Email</span>
          <span class="font-bold text-slate-800 text-sm">{{ user?.email }}</span>
        </div>
        <div>
          <span class="text-slate-400 block mb-0.5">Peran / Hak Akses</span>
          <span class="badge-blue font-bold text-xs">{{ user?.role_label }}</span>
        </div>
        <div>
          <span class="text-slate-400 block mb-0.5">Satuan Kerja</span>
          <span class="font-semibold text-slate-700 text-xs">{{ user?.satker?.nama ?? 'Kantor Wilayah' }}</span>
        </div>
      </div>
    </div>

    <!-- Password Change Form -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Ubah Kata Sandi</h2>

      <form @submit.prevent="handleUpdatePassword" class="space-y-4 max-w-md">
        <div>
          <label class="form-label text-xs">Kata Sandi Saat Ini</label>
          <input
            v-model="form.current_password"
            type="password"
            required
            class="form-input text-xs"
            placeholder="••••••••"
          />
          <p v-if="errors.current_password" class="text-xs text-red-600 mt-1">{{ errors.current_password }}</p>
        </div>

        <div>
          <label class="form-label text-xs">Kata Sandi Baru</label>
          <input
            v-model="form.new_password"
            type="password"
            required
            minlength="8"
            class="form-input text-xs"
            placeholder="Minimal 8 karakter"
          />
          <p v-if="errors.new_password" class="text-xs text-red-600 mt-1">{{ errors.new_password }}</p>
        </div>

        <div>
          <label class="form-label text-xs">Konfirmasi Kata Sandi Baru</label>
          <input
            v-model="form.new_password_confirmation"
            type="password"
            required
            minlength="8"
            class="form-input text-xs"
            placeholder="Ulangi kata sandi baru"
          />
        </div>

        <button
          type="submit"
          class="btn-primary text-xs py-2 px-4 shadow"
          :disabled="loading"
        >
          {{ loading ? 'Memproses...' : 'Perbarui Kata Sandi' }}
        </button>
      </form>
    </div>
  </div>
</template>