<template>
  <AppLayout title="Daftar Produk">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header & Tombol -->
      <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4"
      >
        <h1 class="text-3xl font-bold text-gray-800">Daftar Produk</h1>
        <div class="flex flex-wrap gap-3">
          <Link v-if="can.create" href="/products/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">
            + Tambah Produk
          </Link>
        </div>
      </div>

      <!-- Tabel Produk -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse text-sm text-gray-700">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Nama Produk</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Kategori</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Stok</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Harga</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Unit</th>
              <th class="px-4 py-3 text-center border-b border-gray-200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in products"
              :key="p.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100 font-medium text-gray-800">
                {{ p.name }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span v-if="p.category" class="px-2 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-700">
                  {{ p.category.name }}
                </span>
                <span v-else class="text-gray-400 text-xs">-</span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span
                  class="px-2 py-1 text-xs rounded-full font-medium"
                  :class="
                    p.stock > 10
                      ? 'bg-green-100 text-green-700'
                      : p.stock > 0
                      ? 'bg-yellow-100 text-yellow-700'
                      : 'bg-red-100 text-red-700'
                  "
                >
                  {{ p.stock }}
                </span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100 font-semibold text-indigo-600">
                {{ formatRupiah(p.price) }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-gray-600">
                <span v-if="getUnitDisplay(p)" class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                  {{ getUnitDisplay(p) }}
                </span>
                <span v-else class="text-gray-400 text-xs">-</span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-center flex gap-4 justify-center">
                <Link
                  :href="`/products/${p.id}/edit`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <Link
                  :href="`/products/${p.id}`"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:underline font-medium"
                  onclick="return confirm('Yakin ingin menghapus produk ini?')"
                >
                  Hapus
                </Link>
              </td>
            </tr>
            <tr v-if="products.length === 0">
              <td colspan="5" class="text-center p-6 text-gray-500">
                Belum ada produk tersedia.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  products: Array,
  can: Object,
});

function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
}

function getUnitDisplay(product) {
  if (!product.unit) return null;
  
  // If product has base unit configuration and quantity > 1, show detailed format
  if (product.base_unit && product.unit_quantity > 1) {
    const quantity = formatNumber(product.unit_quantity);
    return `${product.unit.name} (${quantity} ${product.base_unit.name})`;
  }
  
  // Otherwise just show unit name
  return product.unit.name;
}

function formatNumber(value) {
  const num = parseFloat(value);
  return num % 1 === 0 ? num.toString() : num.toFixed(4).replace(/\.?0+$/, '');
}
</script>
