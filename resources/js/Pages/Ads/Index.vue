<template>
  <AppLayout title="Manajemen Iklan">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Manajemen Iklan</h2>
          <div class="flex gap-3">
            <a
              :href="route('ads.export', localFilters)"
              class="px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition"
            >
              📊 Export CSV
            </a>
            <Link
              v-if="can.create"
              :href="route('ads.create')"
              class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            >
              Tambah Iklan
            </Link>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-red-600 mb-2">Total Pengeluaran Iklan</h3>
              <p class="text-2xl font-bold text-red-700">{{ rupiah(summary.total_spent) }}</p>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-blue-600 mb-2">Total Iklan</h3>
              <p class="text-2xl font-bold text-blue-700">{{ ads.total }}</p>
            </div>
          </div>
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-center">
              <h3 class="text-lg font-semibold text-gray-600 mb-2">Jenis Iklan</h3>
              <p class="text-2xl font-bold text-gray-700">{{ summary.ad_types?.length || 0 }}</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Iklan</label>
                <select
                  v-model="localFilters.type"
                  class="border border-gray-300 rounded px-3 py-2 text-sm"
                >
                  <option value="">Semua Jenis</option>
                  <option value="social_media">Social Media</option>
                  <option value="billboard">Billboard</option>
                  <option value="print">Print Media</option>
                  <option value="online">Online</option>
                  <option value="radio">Radio</option>
                  <option value="tv">TV</option>
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

        <!-- Ad Types Breakdown -->
        <div v-if="summary.ad_types?.length" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Breakdown per Jenis Iklan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="type in summary.ad_types"
                :key="type.type"
                class="border border-gray-200 rounded p-4"
              >
                <h4 class="font-medium capitalize mb-2">{{ type.type.replace('_', ' ') }}</h4>
                <p class="text-sm text-gray-600">{{ type.count }} iklan</p>
                <p class="text-lg font-bold text-red-600">{{ rupiah(type.total) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Ads List -->
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
                    Vendor
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
                <tr v-for="ad in ads.data" :key="ad.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(ad.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                      {{ ad.type.replace('_', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ ad.description }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ ad.vendor || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 text-right">
                    {{ rupiah(ad.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex justify-center space-x-2">
                      <Link
                        v-if="can.update"
                        :href="route('ads.edit', ad.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </Link>
                      <button
                        v-if="can.delete"
                        @click="deleteAd(ad)"
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
          <div v-if="ads.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ ads.from }} to {{ ads.to }} of {{ ads.total }} results
              </div>
              <div class="flex space-x-1">
                <span
                  v-for="link in ads.links"
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
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  ads: Object,
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
  router.get(route('ads.index'), localFilters, {
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

function deleteAd(ad) {
  if (confirm(`Apakah Anda yakin ingin menghapus iklan "${ad.description}"?`)) {
    router.delete(route('ads.destroy', ad.id), {
      preserveScroll: true,
    })
  }
}
</script>