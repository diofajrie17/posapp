<template>
  <AppLayout title="Edit User">
    <div class="max-w-2xl">
      <!-- Header -->
      <div class="mb-6">
        <Link
          href="/admin/users"
          class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block"
        >
          ← Kembali
        </Link>
        <h1 class="text-3xl font-bold text-gray-800">✏️ Edit User</h1>
        <p class="text-gray-600 mt-1">Edit data user: {{ user.name }}</p>
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
          />
          <div v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email }}</div>
        </div>

        <!-- Password (optional) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Minimal 8 karakter"
          />
          <div v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password }}</div>
        </div>

        <!-- Password Confirmation (if password provided) -->
        <div v-if="form.password">
          <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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

        <!-- Current Role Display -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-sm text-blue-800">
            <span class="font-medium">Role saat ini:</span> 
            <span class="px-2 py-1 bg-blue-100 rounded text-blue-900 ml-2">
              {{ user.roles?.[0]?.name || 'No role' }}
            </span>
          </p>
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
            {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
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

const props = defineProps({
  user: Object,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
  role: props.user.roles?.[0]?.name || ''
})

const processing = ref(false)

function submit() {
  processing.value = true
  form.put(`/admin/users/${props.user.id}`, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

