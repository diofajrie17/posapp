<template>
  <AppLayout title="Daftar Produk">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Produk" subtitle="Kelola produk dan inventori Anda">
        <template #actions>
          <Button v-if="can.create" href="/products/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Nama Produk</th>
            <th class="px-4 py-3 text-left font-semibold">Kategori</th>
            <th class="px-4 py-3 text-left font-semibold">Stok</th>
            <th class="px-4 py-3 text-right font-semibold">Harga</th>
            <th class="px-4 py-3 text-left font-semibold">Unit</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="products.length === 0">
            <td colspan="6" class="p-0">
              <EmptyState 
                icon="📦" 
                message="Belum ada produk" 
                subtitle="Tambahkan produk pertama Anda untuk memulai"
              />
            </td>
          </tr>

          <tr
            v-for="p in products"
            :key="p.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ p.name }}
            </td>
            <td class="px-4 py-3">
              <span v-if="p.category" class="px-2 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-700">
                {{ p.category.name }}
              </span>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            <td class="px-4 py-3">
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
            <td class="px-4 py-3 text-right font-semibold text-indigo-600">
              {{ formatRupiah(p.price) }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              <span v-if="getUnitDisplay(p)" class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                {{ getUnitDisplay(p) }}
              </span>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <Button
                  :href="`/products/${p.id}/edit`"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <Link
                  :href="`/products/${p.id}`"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                  onclick="return confirm('Yakin ingin menghapus produk ini?')"
                >
                  Hapus
                </Link>
              </div>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

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
