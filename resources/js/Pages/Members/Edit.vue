<template>
  <AppLayout title="Edit Member">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Judul -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Member ✏️</h1>
        <p class="text-gray-600">Perbarui informasi member</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Nama Lengkap -->
        <div>
          <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
          <input
            id="full_name"
            v-model="form.full_name"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Masukkan nama lengkap"
          />
          <div v-if="form.errors.full_name" class="text-red-500 text-sm mt-1">
            {{ form.errors.full_name }}
          </div>
        </div>

        <!-- Nomor HP -->
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
          <input
            id="phone"
            v-model="form.phone"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Masukkan nomor HP"
          />
          <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
            {{ form.errors.phone }}
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Update Member</span>
          </button>
          
          <Link
            href="/members"
            class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition duration-200 font-medium text-center"
          >
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ 
  member: Object 
})

const form = useForm({
  full_name: props.member.full_name,
  phone: props.member.phone,
})

function submit() {
  form.put(`/members/${props.member.id}`)
}
</script>
