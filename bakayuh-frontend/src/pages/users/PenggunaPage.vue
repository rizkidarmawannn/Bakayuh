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
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Pengguna Sistem</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Kelola akun administrator, tim verifikator Kanwil, dan operator satuan kerja se-Kalsel
        </p>
      </div>

      <button
        v-if="isSuperAdmin"
        class="btn-primary text-xs inline-flex items-center gap-2"
        @click="openCreateModal"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        <span>Tambah Pengguna Baru</span>
      </button>
    </div>

    <!-- Filters Bar -->
    <div class="card p-4 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button
          v-for="r in ['ALL', 'super_admin', 'admin_kanwil', 'operator_satker', 'viewer']"
          :key="r"
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border whitespace-nowrap"
          :class="selectedRole === r
            ? 'bg-kemenkum-navy text-white border-kemenkum-navy'
            : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
          @click="selectedRole = r"
        >
          {{ r === 'ALL' ? 'Semua Role' : r.replace('_', ' ').toUpperCase() }}
        </button>
      </div>

      <div class="w-full sm:w-72 relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, atau satker..."
          class="input-text text-xs py-1.5 pl-8"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- User Table -->
    <div class="card overflow-hidden">
      <SkeletonTable v-if="loading" :rows="6" :cols="6" />

      <div v-else-if="filteredUsers.length === 0" class="p-12 text-center text-slate-400">
        Belum ada pengguna yang sesuai pencarian.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="w-12 text-center">No</th>
              <th>Nama Pegawai & Email</th>
              <th class="w-40 text-center">Peran (Role)</th>
              <th class="w-56">Satuan Kerja Penugasan</th>
              <th class="w-28 text-center">Status</th>
              <th class="w-36 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(u, idx) in paginatedUsers" :key="u.id" class="hover:bg-slate-50/80">
              <td class="text-center text-xs text-slate-400 font-semibold">{{ (currentPage - 1) * pageSize + idx + 1 }}</td>
              <td class="text-xs">
                <span class="font-bold text-slate-800">{{ u.name }}</span>
                <span class="block text-[11px] text-slate-400">{{ u.email }}</span>
              </td>
              <td class="text-center">
                <span
                  class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                  :class="{
                    'bg-slate-900 text-white': u.role === 'super_admin',
                    'bg-blue-100 text-blue-800': u.role === 'admin_kanwil',
                    'bg-gold text-slate-950': u.role === 'operator_satker',
                    'bg-slate-100 text-slate-600': u.role === 'viewer',
                  }"
                >
                  {{ u.role_label || u.role.replace('_', ' ') }}
                </span>
              </td>
              <td class="text-xs">
                <span v-if="u.satker" class="font-semibold text-slate-700">
                  {{ u.satker.nama }}
                </span>
                <span v-else class="text-slate-400 italic">
                  Seluruh Kanwil Kalsel
                </span>
              </td>
              <td class="text-center">
                <button
                  type="button"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold transition-opacity hover:opacity-80"
                  :class="u.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                  :disabled="u.id === currentUser?.id"
                  @click="handleToggleActive(u)"
                >
                  {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                </button>
              </td>
              <td class="text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    class="p-1 text-slate-400 hover:text-kemenkum-navy rounded transition-colors"
                    title="Ubah Data"
                    @click="openEditModal(u)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <button
                    class="p-1 text-slate-400 hover:text-amber-600 rounded transition-colors"
                    title="Reset Password"
                    @click="openResetModal(u)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                  </button>

                  <button
                    v-if="u.id !== currentUser?.id"
                    class="p-1 text-slate-400 hover:text-red-600 rounded transition-colors"
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