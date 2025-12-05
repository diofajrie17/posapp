<template>
  <AppLayout title="Paket Membership">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Paket Membership"
        subtitle="Kelola paket membership dan harga"
      >
        <template #actions>
          <Button
            variant="primary"
            href="/packages/create"
          >
            + Tambah Paket
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Card -->
      <Card class="mb-6">
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Paket</h3>
            <p class="text-3xl font-bold text-blue-600">{{ packages.length }}</p>
          </div>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Paket Aktif</h3>
            <p class="text-3xl font-bold text-green-600">{{ activePackages }}</p>
          </div>
        </div>
      </Card>

      <!-- Packages List -->
      <Card title="Daftar Paket">
        <DataTable v-if="packages.length">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama Paket
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Durasi
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Harga
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </template>
          <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                {{ pkg.name }}
              </div>
              <div v-if="pkg.description" class="text-sm text-gray-500">
                {{ pkg.description }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ pkg.duration_days }} hari
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              Rp {{ formatPrice(pkg.price) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span :class="[
                'px-2 py-1 text-xs font-semibold rounded-full',
                pkg.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
              ]">
                {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <div class="flex justify-center gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  :href="`/packages/${pkg.id}/edit`"
                >
                  Edit
                </Button>
                <Button
                  variant="danger"
                  size="sm"
                  as="button"
                  @click="confirmDelete(pkg)"
                >
                  Hapus
                </Button>
              </div>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="📦"
          message="Tidak ada paket membership"
          subtitle="Belum ada paket membership yang terdaftar"
        >
          <template #actions>
            <Button
              variant="primary"
              href="/packages/create"
            >
              + Tambah Paket
            </Button>
          </template>
        </EmptyState>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  packages: Array
})

const activePackages = computed(() => {
  return props.packages.filter(pkg => pkg.is_active).length
})

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

function confirmDelete(pkg) {
  if (confirm(`Yakin ingin menghapus paket "${pkg.name}"?`)) {
    router.delete(`/packages/${pkg.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

