<template>
  <div class="p-6 bg-white rounded shadow">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <h1 class="text-2xl font-bold text-gray-800">Daftar Pengeluaran</h1>
      <div class="flex gap-3">
        <Link href="/dashboard" class="btn-secondary">← Kembali ke Dashboard</Link>
        <Link href="/expenses/create" class="btn-primary">+ Tambah Pengeluaran</Link>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse border border-gray-300 rounded">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="border border-gray-300 px-4 py-2 text-left">Deskripsi</th>
            <th class="border border-gray-300 px-4 py-2 text-right">Jumlah</th>
            <th class="border border-gray-300 px-4 py-2 text-left">Tanggal</th>
            <th class="border border-gray-300 px-4 py-2 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
            <td class="border border-gray-300 px-4 py-2">{{ expense.description }}</td>
            <td class="border border-gray-300 px-4 py-2 text-right">{{ formatRupiah(expense.amount) }}</td>
            <td class="border border-gray-300 px-4 py-2">{{ formatDate(expense.date) }}</td>
            <td class="border border-gray-300 px-4 py-2 text-center">
              <Link :href="route('expenses.edit', expense.id)" class="text-blue-600 hover:underline mr-3">Edit</Link>
              <button @click="destroy(expense.id)" class="text-red-600 hover:underline">Hapus</button>
            </td>
          </tr>
          <tr v-if="expenses.length === 0">
            <td colspan="4" class="text-center p-4 text-gray-500">Belum ada data pengeluaran.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  expenses: Array
})

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

function destroy(id) {
  if (confirm('Yakin ingin menghapus pengeluaran ini?')) {
    router.delete(route('expenses.destroy', id))
  }
}
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-indigo-600 text-white rounded font-semibold text-sm hover:bg-indigo-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300 transition;
}
</style>
