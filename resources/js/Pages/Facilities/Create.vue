<template>
  <AppLayout title="Tambah Pendapatan Fasilitas">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">🏢 Tambah Pendapatan Fasilitas</h2>
              <Link
                :href="route('facilities.index')"
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
                    Harga <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="amountFormatter.displayValue.value"
                    @input="amountFormatter.handleInput"
                    type="text"
                    inputmode="numeric"
                    required
                    placeholder="0"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.amount }"
                  />
                  <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jenis Fasilitas <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.type"
                    required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.type }"
                  >
                    <option value="">Pilih Jenis Fasilitas</option>
                    <option value="parking">Parkir</option>
                    <option value="equipment_rental">Sewa Alat</option>
                    <option value="space_rental">Sewa Ruang</option>
                    <option value="locker">Locker</option>
                    <option value="other">Lainnya</option>
                  </select>
                  <p v-if="errors.type" class="text-red-500 text-sm mt-1">{{ errors.type }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Customer
                  </label>
                  <input
                    v-model="form.customer_name"
                    type="text"
                    placeholder="Nama customer (opsional)"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.customer_name }"
                  />
                  <p v-if="errors.customer_name" class="text-red-500 text-sm mt-1">{{ errors.customer_name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Durasi (jam)
                  </label>
                  <input
                    v-model="form.duration"
                    type="number"
                    step="0.5"
                    min="0"
                    placeholder="Durasi dalam jam (opsional)"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.duration }"
                  />
                  <p v-if="errors.duration" class="text-red-500 text-sm mt-1">{{ errors.duration }}</p>
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
                  placeholder="Deskripsi detail tentang pendapatan fasilitas ini"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Catatan
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  placeholder="Catatan tambahan (opsional)"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.notes }"
                ></textarea>
                <p v-if="errors.notes" class="text-red-500 text-sm mt-1">{{ errors.notes }}</p>
              </div>

              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('facilities.index')"
                  class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded"
                >
                  Batal
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50"
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
import { reactive, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePriceFormatter } from '@/composables/usePriceFormatter'

const props = defineProps({
  errors: Object,
})

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  amount: '',
  description: '',
  type: '',
  customer_name: '',
  duration: '',
  notes: '',
})

// Amount formatter
const amountFormatter = usePriceFormatter(0)

// Sync amount value
watch(() => amountFormatter.numericValue.value, (newValue) => {
  form.amount = newValue
})

const processing = ref(false)

function submit() {
  processing.value = true
  router.post(route('facilities.store'), form, {
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>