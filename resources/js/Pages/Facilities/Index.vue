<template>
  <AppLayout title="Manajemen Fasilitas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Manajemen Fasilitas"
        subtitle="Kelola pendapatan dari fasilitas parkir, sewa alat, dan lainnya"
      >
        <template #actions>
          <Button
            variant="secondary"
            :href="route('facilities.export', localFilters)"
            as="a"
          >
            📊 Export CSV
          </Button>
          <Button
            v-if="can.create"
            variant="success"
            :href="route('facilities.create')"
          >
            Tambah Pendapatan Fasilitas
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Pendapatan Fasilitas</h3>
            <p class="text-3xl font-bold text-green-600">{{ rupiah(summary.total_income) }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Transaksi</h3>
            <p class="text-3xl font-bold text-blue-600">{{ facilities.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Jenis Fasilitas</h3>
            <p class="text-3xl font-bold text-gray-700">{{ summary.facility_types?.length || 0 }}</p>
          </div>
        </Card>
      </div>

      <!-- Filters -->
      <Card title="Filter Data" class="mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input
              v-model="localFilters.date_from"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input
              v-model="localFilters.date_to"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Fasilitas</label>
            <select
              v-model="localFilters.type"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Jenis</option>
              <option value="parking">Parkir</option>
              <option value="equipment_rental">Sewa Alat</option>
              <option value="space_rental">Sewa Ruang</option>
              <option value="locker">Locker</option>
              <option value="other">Lainnya</option>
            </select>
          </div>
          <Button @click="applyFilters">
            Filter
          </Button>
          <Button variant="secondary" @click="clearFilters">
            Reset
          </Button>
        </div>
      </Card>

      <!-- Facility Types Breakdown -->
      <Card v-if="summary.facility_types?.length" title="Breakdown per Jenis Fasilitas" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="type in summary.facility_types"
            :key="type.type"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <h4 class="font-medium text-gray-900 capitalize mb-1">{{ formatType(type.type) }}</h4>
            <p class="text-sm text-gray-600 mb-2">{{ type.count }} transaksi</p>
            <p class="text-xl font-bold text-green-600">{{ rupiah(type.total) }}</p>
          </div>
        </div>
      </Card>

      <!-- Facilities List -->
      <Card title="Daftar Transaksi Fasilitas">
        <DataTable v-if="facilities.data.length">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tanggal
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jenis
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Deskripsi
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Durasi
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Jumlah
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </template>
          <tr v-for="facility in facilities.data" :key="facility.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatDate(facility.date) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize"
                :class="getTypeColor(facility.type)"
              >
                {{ formatType(facility.type) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ facility.description }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ facility.customer_name || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
              {{ facility.duration ? facility.duration + 'h' : '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600 text-right">
              {{ rupiah(facility.amount) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <div class="flex justify-center gap-2">
                <Button
                  v-if="can.update"
                  variant="ghost"
                  size="sm"
                  :href="route('facilities.edit', facility.id)"
                >
                  Edit
                </Button>
                <Button
                  v-if="can.delete"
                  variant="danger"
                  size="sm"
                  @click="deleteFacility(facility)"
                >
                  Hapus
                </Button>
              </div>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="🏢"
          message="Tidak ada data fasilitas"
          subtitle="Belum ada transaksi fasilitas yang tercatat"
        >
          <template #actions>
            <Button
              v-if="can.create"
              variant="primary"
              :href="route('facilities.create')"
            >
              Tambah Pendapatan Fasilitas
            </Button>
          </template>
        </EmptyState>

        <!-- Pagination -->
        <div v-if="facilities.data.length && facilities.links" class="mt-6 border-t border-gray-200 pt-4">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Menampilkan {{ facilities.from }} - {{ facilities.to }} dari {{ facilities.total }} transaksi
            </div>
            <div class="flex gap-1">
              <span
                v-for="link in facilities.links"
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
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  facilities: Object,
  filters: Object,
  summary: Object,
  can: Object,
})

const localFilters = reactive({
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  type: props.filters.type || '',
})

function rupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function formatType(type) {
  return type.replace(/_/g, ' ')
}

function getTypeColor(type) {
  const colors = {
    parking: 'bg-blue-100 text-blue-800',
    equipment_rental: 'bg-purple-100 text-purple-800',
    space_rental: 'bg-indigo-100 text-indigo-800',
    locker: 'bg-yellow-100 text-yellow-800',
    other: 'bg-gray-100 text-gray-800',
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

function applyFilters() {
  router.get(route('facilities.index'), localFilters, {
    preserveState: true,
    preserveScroll: true,
  })
}

function clearFilters() {
  localFilters.date_from = ''
  localFilters.date_to = ''
  localFilters.type = ''
  applyFilters()
}

function deleteFacility(facility) {
  if (confirm(`Apakah Anda yakin ingin menghapus transaksi fasilitas "${facility.description}"?`)) {
    router.delete(route('facilities.destroy', facility.id), {
      preserveScroll: true,
    })
  }
}
</script>
