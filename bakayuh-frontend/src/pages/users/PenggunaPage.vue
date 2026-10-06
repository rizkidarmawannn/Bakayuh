<script setup lang="ts">
import { ref, onMounted, reactive, computed } from 'vue'
import { apiClient } from '@/composables/useApi'
import { useAuth } from '@/composables/useAuth'
import { useToast } from '@/composables/useToast'
import type { User, SatuanKerja, UserRole } from '@/types'
import SkeletonTable from '@/components/common/SkeletonTable.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import { usePagination } from '@/composables/usePagination'

const { isSuperAdmin, user: currentUser } = useAuth()
const { success: toastSuccess, error: toastError } = useToast()

const loading = ref(true)
const users = ref<User[]>([])
const satkers = ref<SatuanKerja[]>([])
const searchQuery = ref('')
const selectedRole = ref<string>('ALL')

// Create / Edit Modal
const userModalOpen = ref(false)
const modalSubmitting = ref(false)
const editingUserId = ref<number | null>(null)

const userForm = reactive<{
  name: string
  email: string
  password: string
  role: UserRole
  satker_id: number | null
  is_active: boolean
}>({
  name: '',
  email: '',
  password: '',
  role: 'operator_satker',
  satker_id: null,
  is_active: true,
})

// Reset Password Modal
const resetModalOpen = ref(false)
const resetSubmitting = ref(false)
const targetResetUser = ref<User | null>(null)
const newPassword = ref('')

const filteredUsers = computed(() => {
  return users.value.filter((u) => {
    const matchesSearch =
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (u.satker?.nama && u.satker.nama.toLowerCase().includes(searchQuery.value.toLowerCase()))
    const matchesRole = selectedRole.value === 'ALL' || u.role === selectedRole.value
    return matchesSearch && matchesRole
  })
})

const {
  currentPage,
  pageSize,
  totalItems,
  paginatedItems: paginatedUsers,
} = usePagination(filteredUsers, { defaultPageSize: 10 })

async function loadInitialData() {
  try {
    const resSatker = await apiClient.get<{ data: SatuanKerja[] }>('/satker')
    satkers.value = resSatker.data.data
  } catch (err) {
    console.error(err)
  }
}

async function fetchUsers() {
  loading.value = true
  try {
    const res = await apiClient.get<any>('/users')
    users.value = res.data.data || res.data
  } catch (err: any) {
    toastError('Gagal memuat pengguna', err.response?.data?.message)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingUserId.value = null
  userForm.name = ''
  userForm.email = ''
  userForm.password = ''
  userForm.role = 'operator_satker'
  userForm.satker_id = satkers.value[0]?.id || null
  userForm.is_active = true
  userModalOpen.value = true
}

function openEditModal(u: User) {
  editingUserId.value = u.id
  userForm.name = u.name
  userForm.email = u.email
  userForm.password = ''
  userForm.role = u.role
  userForm.satker_id = u.satker_id
  userForm.is_active = u.is_active
  userModalOpen.value = true
}

function openResetModal(u: User) {
  targetResetUser.value = u
  newPassword.value = ''
  resetModalOpen.value = true
}

async function handleSaveUser() {
  if (!userForm.name.trim() || !userForm.email.trim()) {
    toastError('Validasi Gagal', 'Nama dan email pengguna wajib diisi.')
    return
  }

  if (!editingUserId.value && !userForm.password.trim()) {
    toastError('Validasi Gagal', 'Password wajib diisi untuk pengguna baru.')
    return
  }

  modalSubmitting.value = true
  try {
    if (editingUserId.value) {
      await apiClient.put(`/users/${editingUserId.value}`, {
        name: userForm.name,
        email: userForm.email,
        role: userForm.role,
        satker_id: userForm.role === 'operator_satker' ? userForm.satker_id : null,
        is_active: userForm.is_active,
      })
      toastSuccess('Berhasil', 'Pengguna berhasil diperbarui.')
    } else {
      await apiClient.post('/users', {
        name: userForm.name,
        email: userForm.email,
        password: userForm.password,
        role: userForm.role,
        satker_id: userForm.role === 'operator_satker' ? userForm.satker_id : null,
        is_active: userForm.is_active,
      })
      toastSuccess('Berhasil', 'Pengguna baru berhasil dibuat.')
    }
    userModalOpen.value = false
    await fetchUsers()
  } catch (err: any) {
    toastError('Gagal menyimpan', err.response?.data?.message)
  } finally {
    modalSubmitting.value = false
  }
}

async function handleToggleActive(u: User) {
  try {
    await apiClient.patch(`/users/${u.id}/toggle-active`)
    u.is_active = !u.is_active
    toastSuccess('Berhasil', `Status pengguna ${u.name} berhasil diubah.`)
  } catch (err: any) {
    toastError('Gagal mengubah status', err.response?.data?.message)
  }
}

async function handleResetPassword() {
  if (!targetResetUser.value || !newPassword.value.trim() || newPassword.value.length < 8) {
    toastError('Validasi Gagal', 'Password baru minimal 8 karakter.')
    return
  }

  resetSubmitting.value = true
  try {
    await apiClient.patch(`/users/${targetResetUser.value.id}/reset-password`, {
      password: newPassword.value,
    })
    toastSuccess('Berhasil', `Password pengguna ${targetResetUser.value.name} berhasil direset.`)
    resetModalOpen.value = false
  } catch (err: any) {
    toastError('Gagal reset password', err.response?.data?.message)
  } finally {
    resetSubmitting.value = false
  }
}

async function handleDeleteUser(u: User) {
  if (u.id === currentUser.value?.id) {
    toastError('Akses Ditolak', 'Anda tidak dapat menghapus akun Anda sendiri.')
    return
  }

  if (!confirm(`Hapus pengguna ${u.name}?`)) return

  try {
    await apiClient.delete(`/users/${u.id}`)
    toastSuccess('Berhasil', 'Pengguna berhasil dihapus.')
    users.value = users.value.filter((user) => user.id !== u.id)
  } catch (err: any) {
    toastError('Gagal menghapus', err.response?.data?.message)
  }
}

onMounted(async () => {
  await loadInitialData()
  await fetchUsers()
})
</script>

<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <!-- Official Kemenkumham Hero Banner -->
    <div class="rounded-2xl bg-gradient-to-r from-[#0C2B64] via-[#091F4A] to-[#163870] p-6 sm:p-7 text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-80 h-80 rounded-full bg-white/5 pointer-events-none blur-2xl" />
      <div class="relative z-10 space-y-2">
        <div class="flex items-center gap-2 text-xs text-slate-300 font-medium">
          <span>Beranda</span>
          <span>&gt;</span>
          <span>Administrasi</span>
          <span>&gt;</span>
          <span class="text-white font-semibold">Pengguna Sistem</span>
        </div>
        <div class="text-[11px] font-black uppercase tracking-widest text-amber-400">
          Administrasi & Master
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-slate-900 shadow-sm shrink-0">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide uppercase">
                Manajemen Pengguna Sistem
              </h1>
              <p class="text-xs sm:text-sm text-slate-200 mt-0.5 max-w-2xl leading-relaxed">
                Kelola akun administrator, tim verifikator Kanwil, dan operator satuan kerja se-Kalsel.
              </p>
            </div>
          </div>

          <!-- Action Button in Banner -->
          <div v-if="isSuperAdmin" class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
            <button
              type="button"
              @click="openCreateModal"
              class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all border border-amber-400/40 bg-amber-500 text-slate-950 hover:bg-amber-400 flex items-center gap-2 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              <span>Tambah Pengguna Baru</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Search Bar Card -->
    <div class="card p-5 bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button
          v-for="r in ['ALL', 'super_admin', 'admin_kanwil', 'operator_satker', 'viewer']"
          :key="r"
          type="button"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all border whitespace-nowrap"
          :class="selectedRole === r
            ? 'bg-[#091F4A] text-white border-[#091F4A] shadow-xs'
            : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
          @click="selectedRole = r"
        >
          {{ r === 'ALL' ? 'Semua Peran' : r.replace('_', ' ').toUpperCase() }}
        </button>
      </div>

      <div class="w-full sm:w-72 relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, satker..."
          class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#091F4A]"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- User Table Section -->
    <div class="card overflow-hidden bg-white border border-slate-200 shadow-sm rounded-xl">
      <!-- Card Title Bar -->
      <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="px-2.5 py-1 rounded bg-[#091F4A] text-white text-[10px] font-black uppercase tracking-wider">
            PENGGUNA
          </span>
          <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wide">
            Daftar Akun Pengguna Sistem
          </h2>
        </div>
        <div class="text-xs font-semibold text-slate-500">
          Menampilkan: <span class="font-bold text-slate-800">{{ filteredUsers.length }}</span> Pengguna
        </div>
      </div>

      <SkeletonTable v-if="loading" :rows="6" :cols="6" />

      <div v-else-if="filteredUsers.length === 0" class="p-16 text-center text-slate-400">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <p class="font-bold text-sm text-slate-700">Belum ada pengguna yang sesuai pencarian</p>
        <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci atau filter peran.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#091F4A] text-white text-[11px] font-black uppercase tracking-wider">
              <th class="py-4 px-4 text-center w-14 border-r border-white/10">No</th>
              <th class="py-4 px-5 min-w-[260px] border-r border-white/10">Nama Pegawai & Email</th>
              <th class="py-4 px-4 text-center w-44 border-r border-white/10">Peran (Role)</th>
              <th class="py-4 px-5 min-w-[240px] border-r border-white/10">Satuan Kerja Penugasan</th>
              <th class="py-4 px-3 text-center w-28 border-r border-white/10">Status</th>
              <th class="py-4 px-4 text-center w-36">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr
              v-for="(u, idx) in paginatedUsers"
              :key="u.id"
              class="hover:bg-blue-50/40 transition-colors"
            >
              <!-- No -->
              <td class="py-4 px-4 text-center font-bold text-slate-400">
                {{ (currentPage - 1) * pageSize + idx + 1 }}
              </td>

              <!-- Nama & Email -->
              <td class="py-4 px-5">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-[#091F4A]/10 text-[#091F4A] flex items-center justify-center font-black text-xs shrink-0 border border-[#091F4A]/20">
                    {{ u.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <span class="font-bold text-slate-900 block leading-snug">{{ u.name }}</span>
                    <span class="text-[11px] text-slate-400 block mt-0.5">{{ u.email }}</span>
                  </div>
                </div>
              </td>

              <!-- Peran -->
              <td class="py-4 px-4 text-center">
                <span
                  class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider shadow-xs"
                  :class="{
                    'bg-slate-900 text-white': u.role === 'super_admin',
                    'bg-[#091F4A] text-white': u.role === 'admin_kanwil',
                    'bg-amber-100 text-amber-900 border border-amber-300': u.role === 'operator_satker',
                    'bg-slate-100 text-slate-700 border border-slate-200': u.role === 'viewer',
                  }"
                >
                  {{ u.role_label || u.role.replace('_', ' ') }}
                </span>
              </td>

              <!-- Satker -->
              <td class="py-4 px-5">
                <span v-if="u.satker" class="font-bold text-slate-800 leading-snug block">
                  {{ u.satker.nama }}
                </span>
                <span v-else class="text-slate-400 italic">
                  Seluruh Kanwil Kalsel
                </span>
              </td>

              <!-- Status -->
              <td class="py-4 px-3 text-center">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border shadow-xs transition-opacity hover:opacity-80"
                  :class="u.is_active
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-slate-100 text-slate-500 border-slate-200'"
                  :disabled="u.id === currentUser?.id"
                  @click="handleToggleActive(u)"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="u.is_active ? 'bg-emerald-500' : 'bg-slate-400'"
                  />
                  <span>{{ u.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </button>
              </td>

              <!-- Aksi -->
              <td class="py-4 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    class="p-1.5 text-slate-500 hover:text-[#091F4A] hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100"
                    title="Ubah Data"
                    @click="openEditModal(u)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <button
                    class="p-1.5 text-slate-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition-colors border border-transparent hover:border-amber-100"
                    title="Reset Password"
                    @click="openResetModal(u)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                  </button>

                  <button
                    v-if="u.id !== currentUser?.id"
                    class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100"
                    title="Hapus Pengguna"
                    @click="handleDeleteUser(u)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AppPagination
        v-if="filteredUsers.length > 0"
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total-items="totalItems"
      />
    </div>

    <!-- Create / Edit User Modal -->
    <div
      v-if="userModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-800">
            {{ editingUserId ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru' }}
          </h2>
          <button class="text-slate-400 hover:text-slate-600 p-1" @click="userModalOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleSaveUser">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pegawai</label>
            <input
              v-model="userForm.name"
              type="text"
              placeholder="Contoh: Muhammad Ihsan, S.H."
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Resmi</label>
            <input
              v-model="userForm.email"
              type="email"
              placeholder="nama@kemenkum.go.id"
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div v-if="!editingUserId">
            <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (Min 8 Karakter)</label>
            <input
              v-model="userForm.password"
              type="password"
              placeholder="Minimal 8 karakter"
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Peran Hak Akses (Role)</label>
            <select v-model="userForm.role" class="input-select text-xs py-2" required>
              <option value="operator_satker">Operator Satker (Pelapor & Pengunggah)</option>
              <option value="admin_kanwil">Admin Kanwil (Tim Verifikator)</option>
              <option value="viewer">Viewer (Pimpinan Pemantau)</option>
              <option value="super_admin">Super Admin (Pengelola Penuh)</option>
            </select>
          </div>

          <div v-if="userForm.role === 'operator_satker'">
            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Kerja Penugasan</label>
            <select v-model="userForm.satker_id" class="input-select text-xs py-2" required>
              <option v-for="s in satkers" :key="s.id" :value="s.id">
                [{{ s.kode }}] {{ s.nama }}
              </option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" class="btn-secondary text-xs" @click="userModalOpen = false">
              Batal
            </button>
            <button type="submit" class="btn-primary text-xs" :disabled="modalSubmitting">
              <span>{{ modalSubmitting ? 'Menyimpan...' : 'Simpan Pengguna' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div
      v-if="resetModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in"
    >
      <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-800">Reset Password Pengguna</h2>
          <button class="text-slate-400 hover:text-slate-600 p-1" @click="resetModalOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form class="p-6 space-y-4" @submit.prevent="handleResetPassword">
          <p class="text-xs text-slate-600">
            Ubah kata sandi untuk akun <strong class="text-slate-900">{{ targetResetUser?.name }}</strong> ({{ targetResetUser?.email }}).
          </p>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru</label>
            <input
              v-model="newPassword"
              type="password"
              placeholder="Minimal 8 karakter..."
              class="input-text text-xs py-2"
              required
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button type="button" class="btn-secondary text-xs" @click="resetModalOpen = false">
              Batal
            </button>
            <button type="submit" class="btn-primary text-xs" :disabled="resetSubmitting">
              <span>{{ resetSubmitting ? 'Mereset...' : 'Simpan Password Baru' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>