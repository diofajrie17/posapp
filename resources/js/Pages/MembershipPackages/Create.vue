<template>
  <AppLayout title="Tambah Paket Membership">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Card>
        <template #title>
          <h2 class="text-2xl font-bold text-gray-800">Tambah Paket Membership</h2>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Nama Paket -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Nama Paket <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Contoh: Bulanan, Tahunan, Harian"
            />
            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
              {{ form.errors.name }}
            </div>
          </div>

          <!-- Durasi (Hari) -->
          <div>
            <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-2">
              Durasi (Hari) <span class="text-red-500">*</span>
            </label>
            <input
              id="duration_days"
              v-model.number="form.duration_days"
              type="number"
              required
              min="1"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Contoh: 30, 365, 1"
            />
            <p class="text-xs text-gray-500 mt-1">30 hari = 1 bulan, 365 hari = 1 tahun</p>
            <div v-if="form.errors.duration_days" class="text-red-500 text-sm mt-1">
              {{ form.errors.duration_days }}
            </div>
          </div>

          <!-- Harga -->
          <div>
            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
              Harga (Rp) <span class="text-red-500">*</span>
            </label>
            <input
              id="price"
              v-model.number="form.price"
              type="number"
              required
              min="0"
              step="1000"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Contoh: 300000"
            />
            <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">
              {{ form.errors.price }}
            </div>
          </div>

          <!-- Deskripsi -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Deskripsi
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Deskripsi paket (opsional)"
            />
            <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
              {{ form.errors.description }}
            </div>
          </div>

          <!-- Status Aktif -->
          <div class="flex items-center">
            <input
              id="is_active"
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_active" class="ml-2 block text-sm text-gray-700">
              Paket Aktif
            </label>
          </div>

          <!-- Actions -->
          <div class="flex gap-4 pt-4">
            <Button
              type="submit"
              variant="primary"
              :disabled="form.processing"
              class="flex-1"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Paket</span>
            </Button>
            
            <Button
              type="button"
              variant="secondary"
              href="/packages"
              class="flex-1"
            >
              Batal
            </Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'

const form = useForm({
  name: '',
  duration_days: null,
  price: null,
  description: '',
  is_active: true,
})

function submit() {
  form.post('/packages')
}
</script>

