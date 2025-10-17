<template>
  <AppLayout title="Riwayat Kehadiran">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader
        title="Riwayat Kehadiran"
        subtitle="Lihat dan kelola riwayat kehadiran"
      >
        <template #actions>
          <Button
            variant="primary"
            href="/attendance/checkin"
          >
            ✓ Check-in
          </Button>
        </template>
      </PageHeader>

      <!-- Statistics -->
      <div class="grid grid-cols-3 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Kehadiran</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Member</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.members }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Harian</h3>
            <p class="text-3xl font-bold text-purple-600">{{ stats.daily }}</p>
          </div>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
              Dari Tanggal
            </label>
            <input
              id="start_date"
              v-model="filterForm.start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
              Sampai Tanggal
            </label>
            <input
              id="end_date"
              v-model="filterForm.end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
              Tipe
            </label>
            <select
              id="type"
              v-model="filterForm.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua</option>
              <option value="member">Member</option>
              <option value="daily">Harian</option>
            </select>
          </div>

          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
              Cari
            </label>
            <input
              id="search"
              v-model="filterForm.search"
              type="text"
              placeholder="Nama atau HP"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div class="md:col-span-4 flex gap-2">
            <Button type="submit" variant="primary">
              Filter
            </Button>
            <Button type="button" variant="secondary" @click="resetFilters">
              Reset
            </Button>
          </div>
        </form>
      </Card>

      <!-- Attendance List -->
      <Card title="Daftar Kehadiran">
        <DataTable v-if="attendances.data.length">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tanggal & Waktu
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                No. HP
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipe
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Check-out
              </th>
            </tr>
          </template>
          <tr v-for="attendance in attendances.data" :key="attendance.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <div>{{ formatDate(attendance.date) }}</div>
              <div class="text-xs text-gray-500">{{ formatTime(attendance.check_in_time) }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ attendance.type === 'member' ? attendance.member?.full_name : attendance.customer_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ attendance.type === 'member' ? attendance.member?.phone : attendance.customer_phone }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                attendance.type === 'member' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
              ]">
                {{ attendance.type === 'member' ? 'Member' : 'Harian' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <span v-if="attendance.check_out_time" class="text-green-600">
                {{ formatTime(attendance.check_out_time) }}
              </span>
              <span v-else class="text-gray-400">-</span>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="📊"
          message="Tidak ada data kehadiran"
          subtitle="Belum ada kehadiran yang tercatat untuk periode ini"
        />

        <!-- Pagination -->
        <div v-if="attendances.data.length > 0" class="mt-6 flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Menampilkan {{ attendances.from }} - {{ attendances.to }} dari {{ attendances.total }} data
          </div>
          <div class="flex gap-2">
            <Button
              v-for="link in attendances.links"
              :key="link.label"
              :href="link.url"
              :disabled="!link.url"
              variant="ghost"
              size="sm"
              v-html="link.label"
            />
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  attendances: Object,
  stats: Object,
  filters: Object
})

const filterForm = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  type: props.filters.type || '',
  search: props.filters.search || ''
})

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

function applyFilters() {
  router.get('/attendance/history', filterForm.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function resetFilters() {
  filterForm.value = {
    start_date: '',
    end_date: '',
    type: '',
    search: ''
  }
  applyFilters()
}
</script>

