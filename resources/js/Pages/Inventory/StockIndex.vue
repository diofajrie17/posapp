<template>
  <AppLayout title="Inventori">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Inventori"
        subtitle="Monitor stok produk dan kelola inventori"
      >
        <template #actions>
          <Button
            variant="secondary"
            href="/inventory/movements"
          >
            📜 Riwayat Mutasi
          </Button>
          <Button
            variant="primary"
            href="/inventory/stock/opname"
          >
            📊 Stock Opname
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Card -->
      <Card class="mb-6">
        <div class="text-center">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Total Produk</h3>
          <p class="text-3xl font-bold text-blue-600">{{ products.length }}</p>
        </div>
      </Card>

      <!-- Stock List -->
      <Card title="Daftar Stok Produk">
        <DataTable v-if="products.length">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konversi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min/Max Stok</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </template>
          <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">
              {{ p.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span 
                class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                :class="getStockClass(p.stock)"
              >
                {{ p.stock }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span v-if="p.unit">{{ p.unit.name || p.unit }}</span>
              <span v-else>-</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span v-if="p.base_unit && p.unit_quantity > 1">{{ p.unit.name }} = {{ p.unit_quantity }} {{ p.base_unit.name }}</span>
              <span v-else>-</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span>{{ p.min_stock || '-' }} / {{ p.max_stock || '-' }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span v-if="typeof p.is_active === 'boolean'" :class="p.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'" class="px-2 py-1 text-xs rounded-full font-medium">
                {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="📦"
          message="Tidak ada data stok"
          subtitle="Belum ada produk yang terdaftar dalam inventori"
        >
          <template #actions>
            <Button
              variant="primary"
              href="/products/create"
            >
              Tambah Produk
            </Button>
          </template>
        </EmptyState>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

defineProps({ 
  products: Array 
})

function getStockClass(stock) {
  const numStock = Number(stock) || 0
  if (numStock === 0) return 'bg-red-100 text-red-800'
  if (numStock <= 10) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}
</script>
