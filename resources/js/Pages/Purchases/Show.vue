<template>
  <AppLayout title="Detail Pembelian">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader 
        :title="`Detail Pembelian: ${purchase.purchase_number}`" 
        subtitle="Informasi lengkap pembelian"
      >
        <template #actions>
          <Button :href="route('purchases.index')" variant="secondary">
            Kembali
          </Button>
        </template>
      </PageHeader>

      <!-- Purchase Info -->
      <Card class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pembelian</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600">No. Pembelian</p>
            <p class="font-semibold text-indigo-600">{{ purchase.purchase_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tanggal Pembelian</p>
            <p class="font-semibold">{{ formatDate(purchase.purchase_date) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Supplier</p>
            <p class="font-semibold">{{ purchase.supplier_name }}</p>
            <p v-if="purchase.supplier_phone" class="text-sm text-gray-500">
              {{ purchase.supplier_phone }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Dibuat Oleh</p>
            <p class="font-semibold">{{ purchase.creator?.name }}</p>
            <p class="text-sm text-gray-500">{{ formatDateTime(purchase.created_at) }}</p>
          </div>
          <div v-if="purchase.supplier_address" class="md:col-span-2">
            <p class="text-sm text-gray-600">Alamat Supplier</p>
            <p class="font-medium">{{ purchase.supplier_address }}</p>
          </div>
          <div v-if="purchase.notes" class="md:col-span-2">
            <p class="text-sm text-gray-600">Catatan</p>
            <p class="font-medium">{{ purchase.notes }}</p>
          </div>
        </div>
      </Card>

      <!-- Items Table -->
      <Card>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Item Pembelian</h3>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Produk</th>
            <th class="px-4 py-3 text-center font-semibold">Unit & Konversi</th>
            <th class="px-4 py-3 text-center font-semibold">Qty Beli</th>
            <th class="px-4 py-3 text-center font-semibold">Qty Base</th>
            <th class="px-4 py-3 text-right font-semibold">Harga/Unit</th>
            <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
          </template>

          <tr 
            v-for="item in purchase.items" 
            :key="item.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium">
              {{ item.product?.name }}
            </td>
            <td class="px-4 py-3 text-center">
              <div class="font-semibold">{{ item.unit_name || item.unit?.name }}</div>
              <div v-if="item.unit_conversion && item.unit_conversion > 1" class="text-xs text-gray-500">
                @ {{ formatNumber(item.unit_conversion) }} pcs/unit
              </div>
            </td>
            <td class="px-4 py-3 text-center font-semibold">
              {{ formatNumber(item.quantity) }}
            </td>
            <td class="px-4 py-3 text-center text-blue-600 font-medium">
              {{ formatNumber(item.quantity_in_base_unit || item.base_quantity) }}
            </td>
            <td class="px-4 py-3 text-right">
              {{ formatRupiah(item.unit_cost) }}
              <div class="text-xs text-gray-500">
                ({{ formatRupiah(item.base_unit_cost) }}/pcs)
              </div>
            </td>
            <td class="px-4 py-3 text-right font-semibold text-green-600">
              {{ formatRupiah(item.subtotal) }}
            </td>
          </tr>

          <tr class="bg-gray-50 border-t-2 border-gray-300">
            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-800">
              Total:
            </td>
            <td class="px-4 py-3 text-right font-bold text-indigo-600 text-lg">
              {{ formatRupiah(purchase.total_amount) }}
            </td>
          </tr>
        </DataTable>
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

defineProps({
  purchase: Object,
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (value) => {
  const num = parseFloat(value)
  return num % 1 === 0 ? num.toString() : num.toFixed(2)
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}
</script>

