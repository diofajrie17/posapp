<template>
  <AppLayout title="Tambah Iklan">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">📢 Tambah Iklan Baru</h2>
              <Link
                :href="route('ads.index')"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
              >
                Kembali
              </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.date"
                    type="date"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.date }"
                  />
                  <p v-if="errors.date" class="text-red-500 text-sm mt-1">{{ errors.date }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Biaya <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    placeholder="0"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.amount }"
                  />
                  <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Iklan <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.type"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.type }"
                  >
                    <option value="">Pilih Jenis Iklan</option>
                    <option value="social_media">Social Media</option>
                    <option value="billboard">Billboard</option>
                    <option value="print">Print Media</option>
                    <option value="online">Online</option>
                    <option value="radio">Radio</option>
                    <option value="tv">TV</option>
                    <option value="other">Lainnya</option>
                  </select>
                  <p v-if="errors.type" class="text-red-500 text-sm mt-1">{{ errors.type }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Vendor/Platform
                  </label>
                  <input
                    v-model="form.vendor"
                    type="text"
                    placeholder="Nama vendor atau platform iklan"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.vendor }"
                  />
                  <p v-if="errors.vendor" class="text-red-500 text-sm mt-1">{{ errors.vendor }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  required
                  rows="4"
                  placeholder="Deskripsi detail tentang iklan ini"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
              </div>

              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('ads.index')"
                  class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded"
                >
                  Batal
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50"
                >
                  {{ processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  errors: Object,
})

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  amount: '',
  description: '',
  type: '',
  vendor: '',
})

const processing = ref(false)

function submit() {
  processing.value = true
  router.post(route('ads.store'), form, {
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>