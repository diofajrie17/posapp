<template>
  <AppLayout title="Daily Sales Report">
    <div class="bg-white">
      <!-- Header -->
      <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 no-print"
      >
        <h1 class="text-3xl font-bold text-gray-800">📊 Daily Sales Report</h1>
        <div class="flex gap-3">
          <a
            :href="route('reports.daily.export', { date: localDate })"
            class="px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition"
          >📊 Export CSV</a>
          <button @click="print" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition">🖨 Print</button>
        </div>
      </div>

      <!-- Filter -->
      <div
        class="flex flex-wrap items-end gap-3 mb-6 no-print bg-gray-50 p-4 rounded-lg border"
      >
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
          <input
            type="date"
            v-model="localDate"
            class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @change="reload"
          />
        </div>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card-summary bg-gradient-to-r from-blue-500 to-blue-600">
          <div class="text-sm text-white/80">Total Transaksi</div>
          <div class="text-2xl font-bold text-white">{{ summary.trx_count }}</div>
        </div>
        <div class="card-summary bg-gradient-to-r from-green-500 to-green-600">
          <div class="text-sm text-white/80">Omzet Kotor</div>
          <div class="text-2xl font-bold text-white">{{ rupiah(summary.gross_total) }}</div>
        </div>
        <div class="card-summary bg-gradient-to-r from-purple-500 to-purple-600">
          <div class="text-sm text-white/80">Rata-rata / Trx</div>
          <div class="text-2xl font-bold text-white">{{ rupiah(avgPerTrx) }}</div>
        </div>
      </div>

      <!-- Payment Methods -->
      <div class="card">
        <h2 class="card-title">Metode Pembayaran</h2>
        <table class="table">
          <thead>
            <tr>
              <th>Metode</th>
              <th>Jumlah Trx</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in byPayment" :key="m.payment_type">
              <td>{{ m.payment_type }}</td>
              <td>{{ m.count }}</td>
              <td>{{ rupiah(m.total) }}</td>
            </tr>
            <tr v-if="byPayment.length === 0">
              <td colspan="3" class="text-center text-gray-500 py-4">Tidak ada data</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Top Produk -->
      <div class="card mt-6">
        <h2 class="card-title">Top 10 Produk</h2>
        <table class="table">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Qty</th>
              <th>Omzet</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in topProducts" :key="p.product_id">
              <td>
                {{ p.product?.name }}
                <span class="text-xs text-gray-500">({{ p.product?.unit }})</span>
              </td>
              <td>{{ p.qty }}</td>
              <td>{{ rupiah(p.gross) }}</td>
            </tr>
            <tr v-if="topProducts.length === 0">
              <td colspan="3" class="text-center text-gray-500 py-4">Tidak ada data</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Detail Transaksi -->
      <div class="card mt-6">
        <h2 class="card-title">Rincian Transaksi Harian</h2>
        <div class="overflow-x-auto">
          <table class="table">
            <thead>
              <tr>
                <th class="w-12"></th>
                <th>ID</th>
                <th>Waktu</th>
                <th>Member</th>
                <th>Metode</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
                <th class="text-right">HPP</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="t in transactions" :key="t.id">
                <!-- Main Transaction Row -->
                <tr class="hover:bg-gray-50 cursor-pointer" @click="toggleTransaction(t.id)">
                  <td class="p-2">
                    <svg 
                      class="w-4 h-4 transition-transform" 
                      :class="{ 'rotate-90': expandedTransactions.includes(t.id) }"
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </td>
                  <td class="font-medium">{{ t.id }}</td>
                  <td>{{ dt(t.date_time) }}</td>
                  <td>
                    <span v-if="t.member">{{ t.member.full_name }}</span>
                    <span v-else class="text-gray-500">Pengunjung Harian</span>
                  </td>
                  <td>{{ t.payment_type }}</td>
                  <td class="text-right">{{ rupiah(t.subtotal_amount) }}</td>
                  <td class="text-right text-red-600">- {{ rupiah(t.discount_amount) }}</td>
                  <td class="text-right font-semibold">{{ rupiah(t.total_amount) }}</td>
                  <td class="text-right text-blue-600">{{ rupiah(t.cogs_amount) }}</td>
                </tr>
                
                <!-- Expanded Items Row -->
                <tr v-if="expandedTransactions.includes(t.id)" class="bg-gray-50">
                  <td colspan="9" class="p-0">
                    <div class="px-4 py-3">
                      <h4 class="text-sm font-semibold text-gray-700 mb-2">Detail Item:</h4>
                      <table class="w-full text-xs">
                        <thead>
                          <tr class="bg-white">
                            <th class="text-left p-2 border-b">Produk</th>
                            <th class="text-right p-2 border-b">Qty</th>
                            <th class="text-right p-2 border-b">Harga Satuan</th>
                            <th class="text-right p-2 border-b">HPP Satuan</th>
                            <th class="text-right p-2 border-b">Subtotal</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="item in t.items" :key="item.id" class="border-b">
                            <td class="p-2">
                              <span class="font-medium">{{ item.product?.name }}</span>
                              <span class="text-gray-500 text-xs ml-1">({{ item.product?.unit }})</span>
                            </td>
                            <td class="text-right p-2">{{ item.quantity }}</td>
                            <td class="text-right p-2">{{ rupiah(item.price_each) }}</td>
                            <td class="text-right p-2 text-blue-600">{{ rupiah(item.unit_cogs) }}</td>
                            <td class="text-right p-2 font-medium">{{ rupiah(item.quantity * item.price_each) }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-if="transactions.length === 0">
                <td colspan="9" class="text-center text-gray-500 py-4">Tidak ada transaksi</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Summary Footer -->
        <div class="mt-4 pt-4 border-t grid grid-cols-2 gap-4">
          <div class="text-sm">
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Subtotal:</span>
              <strong class="text-gray-800">{{ rupiah(sumSubtotal) }}</strong>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Total Diskon:</span>
              <strong class="text-red-600">- {{ rupiah(sumDiscount) }}</strong>
            </div>
            <div class="flex justify-between pt-2 border-t">
              <span class="font-semibold">Omzet Kotor:</span>
              <strong class="text-green-600 text-lg">{{ rupiah(summary.gross_total) }}</strong>
            </div>
          </div>
          <div class="text-sm">
            <div class="flex justify-between pt-2">
              <span class="text-gray-600">Total HPP:</span>
              <strong class="text-blue-600">{{ rupiah(totalHPP) }}</strong>
            </div>
            <div class="flex justify-between pt-2 border-t">
              <span class="font-semibold">Laba Kotor:</span>
              <strong class="text-green-600 text-lg">{{ rupiah(grossProfit) }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  date: String,
  summary: Object,
  byPayment: Array,
  topProducts: Array,
  transactions: Array,
  sumSubtotal: Number,
  sumDiscount: Number,
})

const localDate = ref(props.date)
const expandedTransactions = ref([])

// Toggle expand transaction
function toggleTransaction(id) {
  const index = expandedTransactions.value.indexOf(id)
  if (index > -1) {
    expandedTransactions.value.splice(index, 1)
  } else {
    expandedTransactions.value.push(id)
  }
}

// Calculate total HPP
const totalHPP = computed(() => {
  return props.transactions.reduce((sum, t) => sum + (t.cogs_amount || 0), 0)
})

// Calculate gross profit
const grossProfit = computed(() => {
  return (props.summary.gross_total || 0) - totalHPP.value
})

const avgPerTrx = computed(() => {
  if (!props.summary.trx_count) return 0
  return Number(props.summary.gross_total || 0) / props.summary.trx_count
})

function rupiah(n) {
  return 'Rp ' + formatPrice(n || 0)
}

function dt(s) {
  return new Date(s).toLocaleString('id-ID', { hour12: false })
}

function reload() {
  router.get(route('reports.daily'), { date: localDate.value }, {
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
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
.card-title {
  font-weight: 600;
  font-size: 1.125rem;
  margin-bottom: 0.75rem;
  color: rgb(55 65 81);
}
.card-summary {
  padding: 1.25rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.table {
  width: 100%;
  font-size: 0.875rem;
}
.table th {
  padding: 0.5rem 0.75rem;
  background-color: rgb(243 244 246);
  text-align: left;
  color: rgb(55 65 81);
}
.table td {
  padding: 0.5rem 0.75rem;
  border-bottom: 1px solid rgb(229 231 235);
}
@media print {
  .no-print {
    display: none !important;
  }
  @page {
    margin: 10mm;
  }
}
</style>
