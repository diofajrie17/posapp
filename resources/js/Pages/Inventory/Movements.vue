<template>
  <AppLayout title="Riwayat Mutasi Stok">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Riwayat Mutasi Stok"
        subtitle="Lihat riwayat pergerakan stok produk"
      >
        <template #actions>
          <Button
            variant="secondary"
            href="/products"
          >
            ← Kembali ke Produk
          </Button>
        </template>
      </PageHeader>

      <!-- Filters -->
      <Card title="Filter Data" class="mb-6">
        <form @submit.prevent="apply" class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
            <select 
              v-model="f.product_id" 
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Produk</option>
              <option v-for="p in products" :value="p.id" :key="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sumber</label>
            <select 
              v-model="f.source" 
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Sumber</option>
              <option value="pos">POS</option>
              <option value="opname">Opname</option>
              <option value="purchase">Purchase</option>
              <option value="return">Return</option>
              <option value="manual">Manual</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arah</label>
            <select 
              v-model="f.direction" 
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Arah</option>
              <option value="IN">IN (Masuk)</option>
              <option value="OUT">OUT (Keluar)</option>
            </select>
          </div>
          <Button type="submit">
            Filter
          </Button>
        </form>
      </Card>

      <!-- Movements List -->
      <Card title="Daftar Mutasi Stok">
        <DataTable v-if="movements.data.length">
          <template #header>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Waktu
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Produk
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Arah
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Quantity
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Sumber
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Catatan
            </th>
          </template>
          <tr v-for="m in movements.data" :key="m.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDateTime(m.moved_at) }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">
              {{ m.product?.name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span 
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="m.direction === 'IN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ m.direction }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-center"
                :class="m.direction === 'IN' ? 'text-green-600' : 'text-red-600'"
            >
              {{ m.direction === 'IN' ? '+' : '-' }}{{ m.quantity }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 uppercase">
                {{ m.source }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">
              {{ m.note || '-' }}
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="📋"
          message="Tidak ada data mutasi stok"
          subtitle="Belum ada riwayat pergerakan stok"
        />

        <!-- Pagination -->
        <div v-if="movements.data.length && movements.links" class="mt-6 border-t border-gray-200 pt-4">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Menampilkan {{ movements.from }} - {{ movements.to }} dari {{ movements.total }} mutasi
            </div>
            <div class="flex gap-1">
              <span
                v-for="link in movements.links"
                :key="link.label"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  link.active
                    ? 'bg-blue-600 text-white font-semibold'
                    : link.url
                    ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 cursor-pointer'
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                ]"
                @click="link.url && router.visit(link.url)"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  movements: Object,
  filters: Object,
  products: Array,
})

const f = reactive({ ...props.filters })

function formatDateTime(dateStr) {
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })
}

function apply() {
  router.get(route('stock.movements'), f, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>
