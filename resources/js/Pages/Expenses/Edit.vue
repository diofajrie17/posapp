<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 px-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Pengeluaran</h1>

      <form @submit.prevent="submit" class="space-y-5">
        <!-- Deskripsi -->
        <div>
          <label for="description" class="block font-semibold mb-1">Deskripsi</label>
          <input
            type="text"
            id="description"
            v-model="form.description"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
            :class="{ 'border-red-500': errors.description }"
          />
          <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
        </div>

        <!-- Jumlah -->
        <div>
          <label for="amount" class="block font-semibold mb-1">Jumlah (Rp)</label>
          <input
            type="number"
            id="amount"
            v-model.number="form.amount"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
            :class="{ 'border-red-500': errors.amount }"
            min="0"
            step="1000"
          />
          <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
        </div>

        <!-- Tanggal -->
        <div>
          <label for="date" class="block font-semibold mb-1">Tanggal</label>
          <input
            type="date"
            id="date"
            v-model="form.date"
            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
            :class="{ 'border-red-500': errors.date }"
          />
          <p v-if="errors.date" class="text-red-500 text-sm mt-1">{{ errors.date }}</p>
        </div>

        <!-- Tombol -->
        <div class="flex gap-3">
          <button
            type="submit"
            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
            :disabled="processing"
          >
            Update
          </button>
          <Link href="/expenses" class="flex-1 px-4 py-2 border rounded-lg hover:bg-gray-100 text-center">
            Batal
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  expense: Object
})

const form = reactive({
  description: props.expense.description,
  amount: props.expense.amount,
  date: props.expense.date
})

const errors = reactive({})
const processing = ref(false)

function submit() {
  processing.value = true
  router.put(route('expenses.update', props.expense.id), form, {
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
