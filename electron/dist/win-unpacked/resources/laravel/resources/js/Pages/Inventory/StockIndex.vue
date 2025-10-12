<template>
  <div class="p-6 bg-white rounded shadow">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <h1 class="text-2xl font-bold text-gray-800">Inventori</h1>
      <div class="flex gap-3">
        <Link href="/dashboard" class="btn-secondary">← Kembali ke Dashboard</Link>
        <Link href="/inventory/stock/opname" class="btn-primary">Stock Opname</Link>
        <Link href="/inventory/movements" class="btn-outline">Riwayat Mutasi</Link>
      </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="px-4 py-2 text-left border-b border-gray-200">Produk</th>
            <th class="px-4 py-2 text-left border-b border-gray-200">Stok</th>
            <th class="px-4 py-2 text-left border-b border-gray-200">Unit</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 border-b border-gray-100">{{ p.name }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ p.stock }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ p.unit }}</td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="3" class="text-center p-4 text-gray-500">Tidak ada data produk.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ products: Array })
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300 transition;
}

.btn-outline {
  @apply px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition;
}
</style>
