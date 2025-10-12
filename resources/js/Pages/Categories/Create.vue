<template>
  <AppLayout title="Tambah Kategori Baru">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Kategori Baru</h1>
        <p class="text-gray-600">Buat kategori baru untuk mengorganisir produk Anda</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nama Kategori
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Masukkan nama kategori"
            required
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <!-- Tombol -->
        <div class="flex gap-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan Kategori</span>
          </button>
          
          <Link
            href="/categories"
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
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: ''
})

const submit = () => {
  form.post('/categories')
}
</script>
