<template>
  <AppLayout title="Riwayat Check-in Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Riwayat Check-in Kelas" subtitle="Data riwayat kehadiran member">
        <template #actions>
          <Button href="/kelas/checkin" variant="secondary">Kembali ke Check-in</Button>
        </template>
      </PageHeader>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ stats.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Member Bulanan</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.monthly }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Member Harian</h3>
            <p class="text-3xl font-bold text-purple-600">{{ stats.daily }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Pendapatan</h3>
            <p class="text-3xl font-bold text-green-600">{{ currency(stats.total_amount) }}</p>
          </div>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Filter Riwayat</h3>
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
            <select v-model.number="filters.kelas_id" class="input">
              <option value="">Semua Kelas</option>
              <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input v-model="filters.start_date" type="date" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input v-model="filters.end_date" type="date" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Member</label>
            <select v-model="filters.type" class="input">
              <option value="">Semua Tipe</option>
              <option value="monthly">Bulanan</option>
              <option value="daily">Harian</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama/HP</label>
            <input v-model="filters.search" type="text" class="input" placeholder="Search..." />
          </div>
          <div class="col-span-5 flex justify-end gap-2">
            <Button type="button" @click="resetFilters" variant="secondary">Reset</Button>
            <Button type="submit" variant="primary">Terapkan Filter</Button>
          </div>
        </form>
      </Card>

      <!-- Attendance List -->
      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-4 py-3 text-left font-semibold">Kelas</th>
            <th class="px-4 py-3 text-left font-semibold">Nama Member</th>
            <th class="px-4 py-3 text-left font-semibold">Tipe</th>
            <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
            <th class="px-4 py-3 text-left font-semibold">Metode</th>
            <th class="px-4 py-3 text-left font-semibold">Status</th>
          </template>

          <tr v-if="!attendances.data || attendances.data.length === 0">
            <td colspan="7" class="p-0">
              <EmptyState icon="📝" message="Belum ada riwayat check-in" subtitle="Check-in pertama akan muncul di sini" />
            </td>
          </tr>

          <tr v-for="attendance in attendances.data || attendances" :key="attendance.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm">
              <div>{{ formatDate(attendance.date) }}</div>
              <div class="text-xs text-gray-500">{{ formatTime(attendance.check_in_time) }}</div>
            </td>
            <td class="px-4 py-3 font-medium">{{ attendance.kelas?.name }}</td>
            <td class="px-4 py-3">
              <div>{{ getDisplayName(attendance) }}</div>
              <div class="text-xs text-gray-500">{{ getDisplayPhone(attendance) }}</div>
            </td>
            <td class="px-4 py-3">
              <span :class="attendance.type === 'monthly' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'" 
                    class="px-2 py-1 rounded text-xs font-semibold">
                {{ attendance.type === 'monthly' ? 'Bulanan' : 'Harian' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right font-semibold text-green-600">{{ currency(attendance.amount) }}</td>
            <td class="px-4 py-3 text-sm">{{ attendance.payment_method || '-' }}</td>
            <td class="px-4 py-3">
              <span v-if="attendance.check_out_time" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                Selesai
              </span>
              <span v-else class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">
                Masuk
              </span>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  attendances: { type: [Object, Array], default: () => ({ data: [] }) },
  stats: { type: Object, default: () => ({}) },
  kelasList: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) }
})

const filters = reactive({
  kelas_id: props.filters.kelas_id || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  type: props.filters.type || '',
  search: props.filters.search || ''
})

function applyFilters() {
  router.visit('/kelas/checkin/history', { 
    method: 'get', 
    data: filters, 
    preserveState: true, 
    preserveScroll: true 
  })
}

function resetFilters() {
  filters.kelas_id = ''
  filters.start_date = ''
  filters.end_date = ''
  filters.type = ''
  filters.search = ''
  applyFilters()
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function formatTime(datetime) {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

function currency(value) {
  return 'Rp ' + Number(value || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

function getDisplayName(attendance) {
  if (attendance.type === 'monthly' && attendance.member) {
    return attendance.member.full_name
  }
  return attendance.customer_name
}

function getDisplayPhone(attendance) {
  if (attendance.type === 'monthly' && attendance.member) {
    return attendance.member.phone
  }
  return attendance.customer_phone
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded; }
</style>

