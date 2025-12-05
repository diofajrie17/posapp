<template>
  <AppLayout title="Tambah User Baru">
    <div class="max-w-2xl">
      <!-- Header -->
      <div class="mb-6">
        <Link
          href="/admin/users"
          class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block"
        >
          ← Kembali
        </Link>
        <h1 class="text-3xl font-bold text-gray-800">➕ Tambah User Baru</h1>
        <p class="text-gray-600 mt-1">Tambahkan akun admin atau kasir baru</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Nama lengkap user"
          />
          <div v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="user@example.com"
          />
          <div v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email }}</div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Minimal 8 karakter"
          />
          <div v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password }}</div>
        </div>

        <!-- Password Confirmation -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Ulangi password"
          />
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select
            v-model="form.role"
            required
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Pilih Role</option>
            <option value="Kasir">Kasir</option>
            <option value="Admin">Admin</option>
          </select>
          <div v-if="errors.role" class="text-red-600 text-sm mt-1">{{ errors.role }}</div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <Link
            href="/admin/users"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
          >
            Batal
          </Link>
          <button
            type="submit"
            :disabled="processing"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
          >
            {{ processing ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
})

const processing = ref(false)

function submit() {
  processing.value = true
  form.post('/admin/users', {
    onFinish: () => {
      processing.value = false
    }
  })
}

defineProps({
  errors: {
    type: Object,
    default: () => ({})
  }
})
</script>

