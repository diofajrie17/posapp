<template>
  <AppLayout title="Transaksi Kasir">
    <div class="space-y-6">
      <!-- Header -->
      <div class="mb-6">
        <Link
          href="/admin/users"
          class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block"
        >
          ← Kembali
        </Link>
        <h1 class="text-3xl font-bold text-gray-800">📊 Transaksi {{ user.name }}</h1>
        <p class="text-gray-600 mt-1">Riwayat semua transaksi yang dibuat oleh kasir ini</p>
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-3 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input
              v-model="localDateFrom"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input
              v-model="localDateTo"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
          </div>
          <button
            @click="reload"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
          >
            Filter
          </button>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ transaction.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDateTime(transaction.date_time) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <span v-if="transaction.member">{{ transaction.member.full_name }}</span>
                <span v-else class="text-gray-500">Guest</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ transaction.payment_type }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right">{{ rupiah(transaction.total_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                <Link
                  :href="route('transactions.show', transaction.id)"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Lihat
                </Link>
              </td>
            </tr>
            <tr v-if="transactions.data.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada transaksi</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="transactions.links && transactions.links.length > 3" class="bg-gray-50 px-4 py-3">
          <!-- Pagination controls -->
        </div>
      </div>

      <!-- Summary -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Summary</h3>
        <div class="grid grid-cols-3 gap-4">
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="text-2xl font-bold text-blue-600">{{ transactions.total || 0 }}</div>
            <div class="text-sm text-gray-600">Total Transaksi</div>
          </div>
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="text-2xl font-bold text-green-600">{{ rupiah(summary.total_sales) }}</div>
            <div class="text-sm text-gray-600">Total Omzet</div>
          </div>
          <div class="text-center p-4 bg-emerald-50 rounded-lg">
            <div class="text-2xl font-bold text-emerald-600">{{ rupiah(summary.avg_transaction) }}</div>
            <div class="text-sm text-gray-600">Rata-rata per Transaksi</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  user: Object,
  transactions: Object,
  filters: Object
})

const localDateFrom = ref(props.filters.date_from)
const localDateTo = ref(props.filters.date_to)

const summary = computed(() => {
  const total = props.transactions.total || 0
  const totalSales = props.transactions.data?.reduce((sum, t) => sum + (t.total_amount || 0), 0) || 0
  return {
    total_sales: totalSales,
    avg_transaction: total > 0 ? totalSales / total : 0
  }
})

function formatDateTime(dateTime) {
  return new Date(dateTime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function rupiah(n) {
  return 'Rp ' + formatPrice(n || 0)
}

function reload() {
  router.get(route('admin.users.transactions', props.user.id), {
    date_from: localDateFrom.value,
    date_to: localDateTo.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}
</script>

