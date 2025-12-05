<template>
  <AppLayout title="Laporan Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Laporan Kelas" subtitle="Ringkasan pemasukan, pengeluaran, dan laba bersih">
        <template #actions>
          <Button href="/kelas/payments" variant="secondary">Tambah Pembayaran</Button>
        </template>
      </PageHeader>

      <Card class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Filter Laporan</h3>
        <form @submit.prevent="apply" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
            <select v-model.number="filters.kelas_id" class="input">
              <option value="">Semua Kelas</option>
              <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input v-model="filters.date_from" type="date" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input v-model="filters.date_to" type="date" class="input" />
          </div>
          <div class="flex items-end">
            <Button type="submit" variant="primary" class="w-full">Terapkan Filter</Button>
          </div>
        </form>
      </Card>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Pemasukan</h3>
            <p class="text-3xl font-bold text-green-600">{{ currency(summary.income) }}</p>
            <div class="text-xs text-gray-500 mt-2">
              <div>Dari Registrasi Member: {{ currency(summary.monthly_income) }}</div>
              <div>Dari Check-in Harian: {{ currency(summary.daily_income) }}</div>
            </div>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Pengeluaran</h3>
            <p class="text-3xl font-bold text-red-600">{{ currency(summary.expense) }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Laba Bersih</h3>
            <p class="text-3xl font-bold text-blue-600">{{ currency(summary.net) }}</p>
          </div>
        </Card>
      </div>

      <!-- Detailed Breakdown -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Income Breakdown -->
        <Card>
          <h3 class="text-lg font-semibold mb-4">Detail Pemasukan</h3>
          <div class="space-y-4">
            <div class="p-4 bg-green-50 rounded-lg">
              <div class="text-sm text-gray-600">Pendapatan dari Registrasi Member Bulanan</div>
              <div class="text-2xl font-bold text-green-700">{{ currency(summary.payment_income) }}</div>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg">
              <div class="text-sm text-gray-600">Pendapatan dari Check-in Member Harian</div>
              <div class="text-2xl font-bold text-blue-700">{{ currency(summary.attendance_income) }}</div>
            </div>
          </div>
        </Card>

        <!-- Expense Breakdown -->
        <Card>
          <h3 class="text-lg font-semibold mb-4">Detail Pengeluaran</h3>
          <div class="space-y-4">
            <div class="p-4 bg-red-50 rounded-lg">
              <div class="text-sm text-gray-600">Total Pengeluaran</div>
              <div class="text-2xl font-bold text-red-700">{{ currency(summary.expense) }}</div>
            </div>
          </div>
        </Card>
      </div>

      <!-- Transaction Lists -->
      <div class="grid grid-cols-1 gap-6">
        <!-- Payments List -->
        <Card v-if="payments && payments.length > 0">
          <h3 class="text-lg font-semibold mb-4">Daftar Pembayaran</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="p in payments" :key="p.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ p.payment_date }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ p.kelas?.name }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ p.member?.full_name || '-' }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-600 text-right">{{ currency(p.amount) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ p.payment_method || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>

        <!-- Expenses List -->
        <Card v-if="expenses && expenses.length > 0">
          <h3 class="text-lg font-semibold mb-4">Daftar Pengeluaran</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Pengeluaran</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="e in expenses" :key="e.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ e.expense_date }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ e.kelas?.name }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ e.description }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ e.category || '-' }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-red-600 text-right">{{ currency(e.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'

const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  summary: { type: Object, default: () => ({ income: 0, expense: 0, net: 0 }) },
  kelas: { type: Array, default: () => [] },
  payments: { type: Array, default: () => [] },
  attendances: { type: Array, default: () => [] },
  expenses: { type: Array, default: () => [] }
})

const filters = reactive({
  kelas_id: props.filters.kelas_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

function apply() {
  router.visit('/kelas/reports', { method: 'get', data: filters, preserveState: true, preserveScroll: true })
}

function currency(n) {
  return 'Rp ' + Number(n || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded; }
</style>


