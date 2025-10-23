<template>
  <AppLayout title="Dashboard">
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="p-5 rounded-xl shadow bg-gradient-to-r from-indigo-500 to-indigo-400 text-white">
        <div class="text-sm opacity-80">Omzet Hari Ini</div>
        <div class="text-2xl font-bold">{{ rupiah(summary.gross_total) }}</div>
      </div>
      <div class="p-5 rounded-xl shadow bg-gradient-to-r from-green-500 to-green-400 text-white">
        <div class="text-sm opacity-80">Transaksi</div>
        <div class="text-2xl font-bold">{{ summary.transaction_count }}</div>
      </div>
      <div class="p-5 rounded-xl shadow bg-gradient-to-r from-pink-500 to-pink-400 text-white">
        <div class="text-sm opacity-80">Pengeluaran</div>
        <div class="text-2xl font-bold">{{ rupiah(summary.expense) }}</div>
      </div>
      <div class="p-5 rounded-xl shadow bg-gradient-to-r from-yellow-500 to-yellow-400 text-white">
        <div class="text-sm opacity-80">Laba Bersih</div>
        <div class="text-2xl font-bold">{{ rupiah(summary.net_profit) }}</div>
      </div>
    </div>

    <!-- Members -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="p-5 rounded-xl shadow bg-white hover:shadow-lg transition">
        <div class="text-sm text-gray-500">Member Aktif</div>
        <div class="text-2xl font-semibold text-indigo-600">{{ members.active }}</div>
      </div>
      <div class="p-5 rounded-xl shadow bg-white hover:shadow-lg transition">
        <div class="text-sm text-gray-500">Expired H-5</div>
        <div class="text-2xl font-semibold text-red-500">{{ members.expiringH5 }}</div>
      </div>
    </div>

    <!-- Payment Breakdown & Top Products -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="p-5 rounded-xl shadow bg-white">
        <h2 class="font-semibold mb-3 text-gray-700">Metode Pembayaran (Hari Ini)</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-100 text-gray-600">
              <th class="px-3 py-2">Metode</th>
              <th class="px-3 py-2">Jumlah Trx</th>
              <th class="px-3 py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in paymentBreakdown" :key="p.payment_method">
              <td class="px-3 py-2 text-gray-700">{{ p.payment_method }}</td>
              <td class="px-3 py-2 text-center">{{ p.count }}</td>
              <td class="px-3 py-2 text-right">{{ rupiah(p.total) }}</td>
            </tr>
            <tr v-if="paymentBreakdown.length === 0">
              <td colspan="3" class="px-3 py-4 text-center text-gray-400">Tidak ada transaksi</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-5 rounded-xl shadow bg-white">
        <h2 class="font-semibold mb-3 text-gray-700">Produk Terlaris (Hari Ini)</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-100 text-gray-600">
              <th class="px-3 py-2">Produk</th>
              <th class="px-3 py-2">Qty</th>
              <th class="px-3 py-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in topProducts" :key="p.product_id">
              <td class="px-3 py-2 text-gray-700">{{ p.product_name }}</td>
              <td class="px-3 py-2 text-center">{{ p.total_quantity }}</td>
              <td class="px-3 py-2 text-right">{{ rupiah(p.total_revenue) }}</td>
            </tr>
            <tr v-if="topProducts.length === 0">
              <td colspan="3" class="px-3 py-4 text-center text-gray-400">Tidak ada transaksi</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="p-5 rounded-xl shadow bg-white">
      <h2 class="font-semibold mb-3 text-gray-700">Transaksi Terbaru</h2>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-100 text-gray-600">
            <th class="px-3 py-2">ID</th>
            <th class="px-3 py-2">Waktu</th>
            <th class="px-3 py-2">Member</th>
            <th class="px-3 py-2">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in recentTransactions" :key="t.id">
            <td class="px-3 py-2">#{{ t.id }}</td>
            <td class="px-3 py-2">{{ formatDateTime(t.created_at) }}</td>
            <td class="px-3 py-2">{{ t.member_name || '-' }}</td>
            <td class="px-3 py-2 text-right">{{ rupiah(t.gross_total) }}</td>
          </tr>
          <tr v-if="recentTransactions.length === 0">
            <td colspan="4" class="px-3 py-4 text-center text-gray-400">Tidak ada transaksi</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  today: String,
  summary: Object,
  members: Object,
  topProducts: Array,
  recentTransactions: Array,
  paymentBreakdown: Array,
  currentRoute: String,
})

function rupiah(n) {
  return 'Rp ' + formatPrice(n || 0)
}

function formatDateTime(s) {
  if (!s) return '-'
  return new Date(s).toLocaleString('id-ID', { hour12: false })
}

const todayFormatted = computed(() => {
  if (!props.today) return '-'
  return new Date(props.today).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})
</script>
