<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md">
      <div class="p-6 font-bold text-xl border-b border-gray-200">My App</div>
      <nav class="mt-6">
        <Link
          v-for="item in navItems"
          :key="item.name"
          :href="route(item.route)"
          class="block px-6 py-3 hover:bg-indigo-500 hover:text-white transition"
          :class="{ 'bg-indigo-600 text-white': isActive(item.route) }"
        >
          {{ item.name }}
        </Link>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="flex justify-between items-center p-4 bg-white shadow">
        <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
        <form @submit.prevent="logout">
          <button
            type="submit"
            class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition"
          >
            Logout
          </button>
        </form>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6 overflow-auto">
        <!-- Date -->
        <div class="mb-6 text-gray-600">
          📅 Tanggal: <span class="font-medium">{{ todayFormatted }}</span>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="p-5 rounded-xl shadow bg-gradient-to-r from-indigo-500 to-indigo-400 text-white">
            <div class="text-sm opacity-80">Omzet Hari Ini</div>
            <div class="text-2xl font-bold">{{ rupiah(summary.gross_total) }}</div>
          </div>
          <div class="p-5 rounded-xl shadow bg-gradient-to-r from-green-500 to-green-400 text-white">
            <div class="text-sm opacity-80">Transaksi</div>
            <div class="text-2xl font-bold">{{ summary.trx_count }}</div>
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
                <tr v-for="m in paymentBreakdown" :key="m.payment_type" class="border-b hover:bg-gray-50">
                  <td class="px-3 py-2">{{ m.payment_type }}</td>
                  <td class="px-3 py-2">{{ m.count }}</td>
                  <td class="px-3 py-2 font-medium">{{ rupiah(m.total) }}</td>
                </tr>
                <tr v-if="paymentBreakdown.length === 0">
                  <td colspan="3" class="px-3 py-4 text-center text-gray-400">Tidak ada data</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-5 rounded-xl shadow bg-white">
            <h2 class="font-semibold mb-3 text-gray-700">5 Produk Terlaris (Hari Ini)</h2>
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-100 text-gray-600">
                  <th class="px-3 py-2">Produk</th>
                  <th class="px-3 py-2">Qty</th>
                  <th class="px-3 py-2">Omzet</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in topProducts" :key="p.product_id" class="border-b hover:bg-gray-50">
                  <td class="px-3 py-2">
                    {{ p.product?.name || '-' }}
                    <span class="text-xs text-gray-500" v-if="p.product?.unit">({{ p.product.unit }})</span>
                  </td>
                  <td class="px-3 py-2">{{ p.qty }}</td>
                  <td class="px-3 py-2 font-medium">{{ rupiah(p.gross) }}</td>
                </tr>
                <tr v-if="topProducts.length === 0">
                  <td colspan="3" class="px-3 py-4 text-center text-gray-400">Tidak ada data</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="p-5 rounded-xl shadow bg-white">
          <h2 class="font-semibold mb-3 text-gray-700">10 Transaksi Terakhir (Hari Ini)</h2>
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-100 text-gray-600">
                <th class="px-3 py-2">Waktu</th>
                <th class="px-3 py-2">Pelanggan</th>
                <th class="px-3 py-2">Metode</th>
                <th class="px-3 py-2">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in recentTransactions" :key="t.id" class="border-b hover:bg-gray-50">
                <td class="px-3 py-2">{{ formatDateTime(t.date_time) }}</td>
                <td class="px-3 py-2">
                  <span v-if="t.member" class="font-medium">{{ t.member.full_name }}</span>
                  <span v-else class="text-gray-500">Pengunjung Harian</span>
                </td>
                <td class="px-3 py-2">{{ t.payment_type }}</td>
                <td class="px-3 py-2 font-medium text-indigo-600">{{ rupiah(t.total_amount) }}</td>
              </tr>
              <tr v-if="recentTransactions.length === 0">
                <td colspan="4" class="px-3 py-4 text-center text-gray-400">Tidak ada transaksi</td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

const props = defineProps({
  today: String,
  summary: Object,
  members: Object,
  topProducts: Array,
  recentTransactions: Array,
  paymentBreakdown: Array,
  currentRoute: String,
})

const navItems = [
  { name: 'Dashboard', route: 'dashboard' },
  { name: 'Produk', route: 'products.index' },
  { name: 'Transaksi', route: 'transactions.create' },
  { name: 'Laporan', route: 'reports.daily' },
  { name: 'Pengeluaran', route: 'expenses.index' },
  { name: 'Stok', route: 'stock.index' },
  { name: 'Member', route: 'members.index' },
]

function rupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
}

function formatDateTime(s) {
  if (!s) return '-'
  return new Date(s).toLocaleString('id-ID', { hour12: false })
}

function logout() {
  router.post(route('logout'))
}

function isActive(routeName) {
  return props.currentRoute === routeName
}

const todayFormatted = computed(() => {
  if (!props.today) return '-'
  // Format tanggal yyyy-mm-dd jadi dd MMM yyyy
  const d = new Date(props.today)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
})
</script>

<style scoped>
main::-webkit-scrollbar {
  width: 8px;
}
main::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}
</style>
