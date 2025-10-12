<template>
  <AppLayout title="Inventori">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Inventori</h1>
        <div class="flex flex-wrap gap-3">
          <Link href="/inventory/stock/opname" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">Stock Opname</Link>
          <Link href="/inventory/movements" class="px-4 py-2 border border-gray-400 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-100 transition">Riwayat Mutasi</Link>
        </div>
      </div>

      <!-- Table Section -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Produk</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Stok</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Unit</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in products"
              :key="p.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100">{{ p.name }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ p.stock }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ p.unit }}</td>
            </tr>
            <tr v-if="products.length === 0">
              <td colspan="3" class="text-center p-6 text-gray-500">
                Tidak ada data produk.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ products: Array })
</script>
