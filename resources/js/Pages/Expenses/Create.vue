<template>
  <AppLayout title="Tambah Pengeluaran">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">➕ Tambah Pengeluaran Baru</h1>
        <p class="text-gray-600">Catat detail pengeluaran harian Anda di sini</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Deskripsi -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
          <input
            type="text"
            id="description"
            v-model="form.description"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            :class="{ 'border-red-500': errors.description }"
            placeholder="Contoh: Beli ATK"
          />
          <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
        </div>

        <!-- Jumlah -->
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp)</label>
          <input
            type="number"
            id="amount"
            v-model.number="form.amount"
            min="0"
            step="1000"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            :class="{ 'border-red-500': errors.amount }"
            placeholder="Contoh: 150000"
          />
          <div v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</div>
        </div>

        <!-- Tanggal -->
        <div>
          <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
          <input
            type="date"
            id="date"
            v-model="form.date"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            :class="{ 'border-red-500': errors.date }"
          />
          <div v-if="errors.date" class="text-red-500 text-sm mt-1">{{ errors.date }}</div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <button
            type="submit"
            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
            :disabled="processing"
          >
            <span v-if="processing">Menyimpan...</span>
            <span v-else>💾 Simpan</span>
          </button>
          <Link
            href="/expenses"
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
import { reactive, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = reactive({
  description: '',
  amount: '',
  date: ''
})

const errors = reactive({})
const processing = ref(false)

function submit() {
  processing.value = true
  router.post(route('expenses.store'), form, {
    onError: (errs) => {
      Object.assign(errors, errs)
      processing.value = false
    },
    onSuccess: () => {
      processing.value = false
    }
  })
}
</script>
