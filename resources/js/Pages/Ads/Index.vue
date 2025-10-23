<template>
  <AppLayout title="Manajemen Iklan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Manajemen Iklan" subtitle="Kelola pengeluaran iklan dan promosi">
        <template #actions>
          <Button
            :href="route('ads.export', localFilters)"
            variant="secondary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
          </Button>
          <Button
            v-if="can.create"
            :href="route('ads.create')"
            variant="primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Iklan
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-lg font-semibold text-red-600 mb-2">Total Pengeluaran Iklan</h3>
            <p class="text-3xl font-bold text-red-700">{{ rupiah(summary.total_spent) }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-lg font-semibold text-blue-600 mb-2">Total Iklan</h3>
            <p class="text-3xl font-bold text-blue-700">{{ ads.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Jenis Iklan</h3>
            <p class="text-3xl font-bold text-gray-700">{{ summary.ad_types?.length || 0 }}</p>
          </div>
        </Card>
      </div>

      <!-- Filters -->
      <Card title="Filter" customClass="mb-6">
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input
              v-model="localFilters.date_from"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input
              v-model="localFilters.date_to"
              type="date"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Iklan</label>
            <select
              v-model="localFilters.type"
              class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
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
          <Button @click="applyFilters" variant="primary">
            Filter
          </Button>
          <Button @click="clearFilters" variant="secondary">
            Reset
          </Button>
        </div>
      </Card>

      <!-- Ad Types Breakdown -->
      <Card v-if="summary.ad_types?.length" title="Breakdown per Jenis Iklan" customClass="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="type in summary.ad_types"
            :key="type.type"
            class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
          >
            <h4 class="font-medium capitalize mb-2">{{ type.type.replace('_', ' ') }}</h4>
            <p class="text-sm text-gray-600">{{ type.count }} iklan</p>
            <p class="text-lg font-bold text-red-600">{{ rupiah(type.total) }}</p>
          </div>
        </div>
      </Card>

      <!-- Ads List -->
      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-4 py-3 text-left font-semibold">Jenis</th>
            <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
            <th class="px-4 py-3 text-left font-semibold">Vendor</th>
            <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="ads.data && ads.data.length === 0">
            <td colspan="6" class="p-0">
              <EmptyState 
                icon="📢" 
                message="Belum ada iklan" 
                subtitle="Catat pengeluaran iklan dan promosi Anda"
              />
            </td>
          </tr>

          <tr v-for="ad in ads.data" :key="ad.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-gray-900">
              {{ formatDate(ad.date) }}
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                {{ ad.type.replace('_', ' ') }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-900">
              {{ ad.description }}
            </td>
            <td class="px-4 py-3 text-gray-900">
              {{ ad.vendor || '-' }}
            </td>
            <td class="px-4 py-3 text-right font-semibold text-red-600">
              {{ rupiah(ad.amount) }}
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <Button
                  v-if="can.update"
                  :href="route('ads.edit', ad.id)"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <button
                  v-if="can.delete"
                  @click="deleteAd(ad)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </DataTable>

        <!-- Pagination -->
        <div v-if="ads.links" class="mt-4 flex justify-between items-center">
          <div class="text-sm text-gray-700">
            Menampilkan {{ ads.from }} sampai {{ ads.to }} dari {{ ads.total }} hasil
          </div>
          <div class="flex space-x-1">
            <span
              v-for="link in ads.links"
              :key="link.label"
              :class="[
                'px-3 py-2 text-sm rounded-lg',
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
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

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
  return 'Rp ' + formatPrice(n || 0)
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
