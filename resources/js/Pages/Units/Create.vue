<template>
  <AppLayout title="Tambah Unit">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Judul -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Unit Baru 📏</h1>
        <p class="text-gray-600">Buat unit baru untuk produk Anda</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Nama Unit -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Unit</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Contoh: pcs, box, kg, liter"
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <!-- Simbol -->
        <div>
          <label for="symbol" class="block text-sm font-medium text-gray-700 mb-2">Simbol (Opsional)</label>
          <input
            id="symbol"
            v-model="form.symbol"
            type="text"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Contoh: pcs, box, kg, L"
          />
          <div v-if="form.errors.symbol" class="text-red-500 text-sm mt-1">
            {{ form.errors.symbol }}
          </div>
        </div>

        <!-- Tipe Unit -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Unit</label>
          <div class="space-y-3">
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.is_base_unit"
                :value="true"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-gray-700">Base Unit (Unit dasar seperti pcs, gram, ml)</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.is_base_unit"
                :value="false"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-gray-700">Derived Unit (Unit turunan seperti box, dozen, kg)</span>
            </label>
          </div>
          <div class="text-sm text-gray-600 mt-2">
            <strong>Base Unit:</strong> Unit dasar yang tidak memerlukan konversi (seperti pcs, gram)<br>
            <strong>Derived Unit:</strong> Unit yang memerlukan konfigurasi konversi saat membuat produk (seperti box, dozen)
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
            <span v-else>Simpan Unit</span>
          </button>
          
          <Link
            href="/units"
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

const form = useForm({
  name: '',
  symbol: '',
  is_base_unit: true
})

function submit() {
  form.post('/units')
}
</script>
