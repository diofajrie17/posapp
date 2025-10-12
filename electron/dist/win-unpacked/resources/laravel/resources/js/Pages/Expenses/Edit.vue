<template>
  <div class="p-6 bg-white rounded shadow max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Pengeluaran</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label for="description" class="block font-semibold mb-1">Deskripsi</label>
        <input
          type="text"
          id="description"
          v-model="form.description"
          class="w-full border rounded px-3 py-2"
          :class="{ 'border-red-500': errors.description }"
        />
        <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</p>
      </div>

      <div class="mb-4">
        <label for="amount" class="block font-semibold mb-1">Jumlah (Rp)</label>
        <input
          type="number"
          id="amount"
          v-model.number="form.amount"
          class="w-full border rounded px-3 py-2"
          :class="{ 'border-red-500': errors.amount }"
          min="0"
          step="1000"
        />
        <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
      </div>

      <div class="mb-4">
        <label for="date" class="block font-semibold mb-1">Tanggal</label>
        <input
          type="date"
          id="date"
          v-model="form.date"
          class="w-full border rounded px-3 py-2"
          :class="{ 'border-red-500': errors.date }"
        />
        <p v-if="errors.date" class="text-red-500 text-sm mt-1">{{ errors.date }}</p>
      </div>

      <div class="flex gap-3">
        <button
          type="submit"
          class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
          :disabled="processing"
        >
          Update
        </button>
        <Link href="/expenses" class="px-4 py-2 border rounded hover:bg-gray-100">Batal</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, watchEffect } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

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
