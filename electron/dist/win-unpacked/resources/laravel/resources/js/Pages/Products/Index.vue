<template>
  <div class="p-6 bg-white rounded shadow">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <h1 class="text-2xl font-bold text-gray-800">Daftar Produk</h1>
      <div class="flex gap-3">
        <Link href="/dashboard" class="btn-secondary">← Kembali ke Dashboard</Link>
        <Link
          v-if="can.create"
          href="/products/create"
          class="btn-primary"
        >
          + Tambah Produk
        </Link>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="px-4 py-2 text-left border-b border-gray-300">Nama</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Stok</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Harga</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Unit</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="p in products"
            :key="p.id"
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-2 border-b border-gray-100">{{ p.name }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ p.stock }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ formatRupiah(p.price) }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ p.unit }}</td>
            <td class="px-4 py-2 border-b border-gray-100">
              <Link :href="`/products/${p.id}/edit`" class="text-blue-600 hover:underline mr-3">Edit</Link>
              <Link
                :href="`/products/${p.id}`"
                method="delete"
                as="button"
                class="text-red-600 hover:underline"
                onclick="return confirm('Yakin ingin menghapus produk ini?')"
              >
                Hapus
              </Link>
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="5" class="text-center p-4 text-gray-500">Belum ada produk tersedia.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  products: Array,
  can: Object
})

function formatRupiah(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300 transition;
}
</style>
