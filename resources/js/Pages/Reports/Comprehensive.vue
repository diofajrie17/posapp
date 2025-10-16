<template>
  <AppLayout title="Laporan Komprehensif">
    <div class="bg-white">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 no-print">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Komprehensif</h1>
        <div class="flex gap-3">
          <a
            :href="route('reports.comprehensive.export', { date_from: filters.date_from, date_to: filters.date_to })"
            class="px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition"
          >📊 Export CSV</a>
          <button @click="print" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition">🖨 Print</button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-end gap-3 mb-6 no-print bg-gray-50 p-4 rounded-lg border">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
          <input
            v-model="localDateFrom"
            type="date"
            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
          <input
            v-model="localDateTo"
            type="date"
            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button
          @click="reload"
          class="px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition"
        >
          Filter
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card text-center">
          <h3 class="text-lg font-semibold text-green-600 mb-2">Total Pendapatan</h3>
          <p class="text-2xl font-bold text-green-700">{{ rupiah(totals.income) }}</p>
          <p class="text-sm text-gray-600">Penjualan + Fasilitas</p>
        </div>
        <div class="card text-center">
          <h3 class="text-lg font-semibold text-red-600 mb-2">Total Pengeluaran</h3>
          <p class="text-2xl font-bold text-red-700">{{ rupiah(totals.expenses) }}</p>
          <p class="text-sm text-gray-600">Operasional + Iklan</p>
        </div>
        <div class="card text-center">
          <h3 class="text-lg font-semibold mb-2" :class="totals.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">Laba Bersih</h3>
          <p class="text-2xl font-bold" :class="totals.net_profit >= 0 ? 'text-green-700' : 'text-red-700'">{{ rupiah(totals.net_profit) }}</p>
          <p class="text-sm text-gray-600">{{ totals.net_profit >= 0 ? 'Profit' : 'Loss' }}</p>
        </div>
        <div class="card text-center">
          <h3 class="text-lg font-semibold text-blue-600 mb-2">Total Transaksi</h3>
          <p class="text-2xl font-bold text-blue-700">{{ sales.summary?.trx_count || 0 }}</p>
          <p class="text-sm text-gray-600">Penjualan</p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Breakdown -->
        <div class="card">
          <h3 class="card-title">💰 Penjualan (Sales)</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(sales.summary?.gross_total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ sales.summary?.trx_count || 0 }} transaksi</p>
          </div>
          <div v-if="sales.by_payment?.length">
            <h4 class="font-medium mb-2">Berdasarkan Metode Pembayaran:</h4>
            <div v-for="payment in sales.by_payment" :key="payment.payment_type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ payment.payment_type }}</span>
              <span class="font-medium">{{ rupiah(payment.total) }} ({{ payment.count }}x)</span>
            </div>
          </div>
        </div>

        <!-- Expenses Breakdown -->
        <div class="card">
          <h3 class="card-title">💸 Pengeluaran Operasional</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(expenses.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ expenses.summary?.count || 0 }} item</p>
          </div>
        </div>

        <!-- Ads Expenses -->
        <div class="card">
          <h3 class="card-title">📢 Pengeluaran Iklan</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(ads.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ ads.summary?.count || 0 }} iklan</p>
          </div>
          <div v-if="ads.by_type?.length">
            <h4 class="font-medium mb-2">Berdasarkan Jenis:</h4>
            <div v-for="type in ads.by_type" :key="type.type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ type.type }}</span>
              <span class="font-medium">{{ rupiah(type.total) }} ({{ type.count }}x)</span>
            </div>
          </div>
        </div>

        <!-- Facility Income -->
        <div class="card">
          <h3 class="card-title">🏢 Pendapatan Fasilitas</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(facilities.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ facilities.summary?.count || 0 }} item</p>
          </div>
          <div v-if="facilities.by_type?.length">
            <h4 class="font-medium mb-2">Berdasarkan Jenis:</h4>
            <div v-for="type in facilities.by_type" :key="type.type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ type.type }}</span>
              <span class="font-medium">{{ rupiah(type.total) }} ({{ type.count }}x)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div v-if="top_products?.length" class="card mb-8">
        <h3 class="card-title">🏆 Produk Terlaris</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left p-3 font-medium">Produk</th>
                <th class="text-right p-3 font-medium">Qty</th>
                <th class="text-right p-3 font-medium">Omzet</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in top_products" :key="product.product_id" class="border-t">
                <td class="p-3">
                  <span class="font-medium">{{ product.product?.name || 'N/A' }}</span>
                  <span class="text-xs text-gray-500 ml-2">{{ product.product?.unit || '' }}</span>
                </td>
                <td class="p-3 text-right">{{ Number(product.qty || 0).toLocaleString('id-ID') }}</td>
                <td class="p-3 text-right font-medium">{{ rupiah(product.gross) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sales Trend Chart -->
      <div v-if="sales.by_date?.length" class="card">
        <h3 class="card-title">📈 Tren Penjualan Harian</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left p-3 font-medium">Tanggal</th>
                <th class="text-right p-3 font-medium">Transaksi</th>
                <th class="text-right p-3 font-medium">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="day in sales.by_date" :key="day.date" class="border-t">
                <td class="p-3">{{ formatDate(day.date) }}</td>
                <td class="p-3 text-right">{{ day.trx_count }}</td>
                <td class="p-3 text-right font-medium">{{ rupiah(day.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  filters: Object,
  sales: Object,
  expenses: Object,
  ads: Object,
  facilities: Object,
  top_products: Array,
  totals: Object,
})

const localDateFrom = ref(props.filters.date_from)
const localDateTo = ref(props.filters.date_to)

function rupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function reload() {
  router.get(route('reports.comprehensive'), {
    date_from: localDateFrom.value,
    date_to: localDateTo.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function print() {
  window.print()
}
</script>

<style scoped>
.card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.card-title {
  font-weight: 600;
  font-size: 1.125rem;
  margin-bottom: 1rem;
  color: #374151;
}

@media print {
  .no-print {
    display: none !important;
  }
  
  .card {
    box-shadow: none;
    border: 1px solid #e5e7eb;
    break-inside: avoid;
    margin-bottom: 1rem;
  }
  
  body {
    font-size: 12px;
  }
  
  .grid {
    display: block;
  }
  
  .grid > * {
    margin-bottom: 1rem;
  }
}
</style>