<template>
  <div class="p-6 bg-white rounded shadow">
    <!-- Header dan Aksi -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 no-print">
      <h1 class="text-2xl font-bold text-gray-800">Laporan Harian Omzet</h1>
      <div class="flex gap-3">
        <Link href="/dashboard" class="btn-secondary">← Kembali ke Dashboard</Link>
        <a
          :href="route('reports.daily.export', { date: localDate })"
          class="btn-outline"
        >Export CSV</a>
        <button @click="print" class="btn-primary">Print</button>
      </div>
    </div>

    <!-- Filter Tanggal -->
    <div class="flex flex-wrap items-end gap-3 mb-6 no-print">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
        <input type="date" v-model="localDate" class="border rounded px-3 py-2" @change="reload" />
      </div>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
      <div class="p-4 rounded-lg shadow bg-white">
        <div class="text-sm text-gray-500">Total Transaksi</div>
        <div class="text-2xl font-semibold">{{ summary.trx_count }}</div>
      </div>
      <div class="p-4 rounded-lg shadow bg-white">
        <div class="text-sm text-gray-500">Omzet Kotor</div>
        <div class="text-2xl font-semibold">{{ rupiah(summary.gross_total) }}</div>
      </div>
      <div class="p-4 rounded-lg shadow bg-white">
        <div class="text-sm text-gray-500">Rata-rata / Trx</div>
        <div class="text-2xl font-semibold">{{ rupiah(avgPerTrx) }}</div>
      </div>
    </div>

    <!-- Metode Pembayaran -->
    <div class="p-4 rounded-lg shadow bg-white mb-6">
      <div class="font-semibold mb-2">Metode Pembayaran</div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-100">
            <th class="px-3 py-2">Metode</th>
            <th class="px-3 py-2">Jumlah Trx</th>
            <th class="px-3 py-2">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in byPayment" :key="m.payment_type" class="border-b hover:bg-gray-50">
            <td class="px-3 py-2">{{ m.payment_type }}</td>
            <td class="px-3 py-2">{{ m.count }}</td>
            <td class="px-3 py-2">{{ rupiah(m.total) }}</td>
          </tr>
          <tr v-if="byPayment.length === 0">
            <td colspan="3" class="text-center text-gray-500 px-3 py-4">Tidak ada data</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Top Produk -->
    <div class="p-4 rounded-lg shadow bg-white mb-6">
      <div class="font-semibold mb-2">Top 10 Produk</div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-100">
            <th class="px-3 py-2">Produk</th>
            <th class="px-3 py-2">Qty</th>
            <th class="px-3 py-2">Omzet</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in topProducts" :key="p.product_id" class="border-b hover:bg-gray-50">
            <td class="px-3 py-2">
              {{ p.product?.name }}
              <span class="text-xs text-gray-500">({{ p.product?.unit }})</span>
            </td>
            <td class="px-3 py-2">{{ p.qty }}</td>
            <td class="px-3 py-2">{{ rupiah(p.gross) }}</td>
          </tr>
          <tr v-if="topProducts.length === 0">
            <td colspan="3" class="text-center text-gray-500 px-3 py-4">Tidak ada data</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Detail Transaksi -->
    <div class="p-4 rounded-lg shadow bg-white mb-4">
      <div class="font-semibold mb-2">Detail Transaksi</div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-100">
            <th class="px-3 py-2">Waktu</th>
            <th class="px-3 py-2">Member</th>
            <th class="px-3 py-2">Metode</th>
            <th class="px-3 py-2">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in transactions" :key="t.id" class="border-b hover:bg-gray-50">
            <td class="px-3 py-2">{{ dt(t.date_time) }}</td>
            <td class="px-3 py-2">
              <span v-if="t.member">{{ t.member.full_name }}</span>
              <span v-else class="text-gray-500">Pengunjung Harian</span>
            </td>
            <td class="px-3 py-2">{{ t.payment_type }}</td>
            <td class="px-3 py-2">{{ rupiah(t.total_amount) }}</td>
          </tr>
          <tr v-if="transactions.length === 0">
            <td colspan="4" class="text-center text-gray-500 px-3 py-4">Tidak ada transaksi</td>
          </tr>
        </tbody>
      </table>

      <!-- Ringkasan Subtotal -->
      <div class="mt-4 text-sm">
        <div class="flex justify-between">
          <span>Subtotal (hari itu)</span>
          <strong>{{ rupiah(sumSubtotal) }}</strong>
        </div>
        <div class="flex justify-between">
          <span>Total Diskon</span>
          <strong>- {{ rupiah(sumDiscount) }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

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

const avgPerTrx = computed(() => {
  if (!props.summary.trx_count) return 0
  return Number(props.summary.gross_total || 0) / props.summary.trx_count
})

function rupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
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

<style>
/* Tombol Styling */
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300 transition;
}

.btn-outline {
  @apply px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition;
}

/* Print Mode */
@media print {
  .no-print {
    display: none !important;
  }
  @page {
    margin: 10mm;
  }
}
</style>
