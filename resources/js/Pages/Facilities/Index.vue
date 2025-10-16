<template>
  <AppLayout title="Manajemen Fasilitas">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Manajemen Fasilitas</h2>
          <div class="flex gap-3">
            <a
              :href="route('facilities.export', localFilters)"
              class="px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition"
            >
              📊 Export CSV
            </a>
            <Link
              v-if="can.create"
              :href="route('facilities.create')"
              class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
            >
              Tambah Pendapatan Fasilitas
            </Link>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-green-600 mb-2">Total Pendapatan Fasilitas</h3>
              <p class="text-2xl font-bold text-green-700">{{ rupiah(summary.total_income) }}</p>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-blue-600 mb-2">Total Transaksi</h3>
              <p class="text-2xl font-bold text-blue-700">{{ facilities.total }}</p>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-gray-600 mb-2">Jenis Fasilitas</h3>
              <p class="text-2xl font-bold text-gray-700">{{ summary.facility_types?.length || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex flex-wrap gap-4 items-end">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input
                  v-model="localFilters.date_from"
                  type="date"
                  class="border border-gray-300 rounded px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input
                  v-model="localFilters.date_to"
                  type="date"
                  class="border border-gray-300 rounded px-3 py-2 text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Fasilitas</label>
                <select
                  v-model="localFilters.type"
                  class="border border-gray-300 rounded px-3 py-2 text-sm"
                >
                  <option value="">Semua Jenis</option>
                  <option value="parking">Parkir</option>
                  <option value="equipment_rental">Sewa Alat</option>
                  <option value="space_rental">Sewa Ruang</option>
                  <option value="locker">Locker</option>
                  <option value="other">Lainnya</option>
                </select>
              </div>
              <button
                @click="applyFilters"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Filter
              </button>
              <button
                @click="clearFilters"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Facility Types Breakdown -->
        <div v-if="summary.facility_types?.length" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Breakdown per Jenis Fasilitas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="type in summary.facility_types"
                :key="type.type"
                class="border border-gray-200 rounded p-4"
              >
                <h4 class="font-medium capitalize mb-2">{{ type.type.replace('_', ' ') }}</h4>
                <p class="text-sm text-gray-600">{{ type.count }} transaksi</p>
                <p class="text-lg font-bold text-green-600">{{ rupiah(type.total) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Facilities List -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
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
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="facility in facilities.data" :key="facility.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(facility.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 capitalize">
                      {{ facility.type.replace('_', ' ') }}
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">
                    {{ rupiah(facility.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <Link
                        v-if="can.update"
                        :href="route('facilities.edit', facility.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </Link>
                      <button
                        v-if="can.delete"
                        @click="deleteFacility(facility)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="facilities.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ facilities.from }} to {{ facilities.to }} of {{ facilities.total }} results
              </div>
              <div class="flex space-x-1">
                <span
                  v-for="link in facilities.links"
                  :key="link.label"
                  :class="[
                    'px-3 py-2 text-sm rounded',
                    link.active
                      ? 'bg-blue-600 text-white'
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
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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
  return new Date(dateStr).toLocaleDateString('id-ID')
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