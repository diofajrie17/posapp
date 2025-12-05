<template>
  <AppLayout title="Daftar Member">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Daftar Member"
        subtitle="Kelola data member dan informasi keanggotaan"
      >
        <template #actions>
          <Button
            variant="primary"
            href="/members/create"
          >
            + Tambah Member
          </Button>
        </template>
      </PageHeader>

      <!-- Statistics -->
      <div class="grid grid-cols-4 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total</h3>
            <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Aktif</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Segera Expired</h3>
            <p class="text-3xl font-bold text-orange-600">{{ stats.expiring_soon }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Expired</h3>
            <p class="text-3xl font-bold text-red-600">{{ stats.expired }}</p>
          </div>
        </Card>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
              Cari Member
            </label>
            <input
              id="search"
              v-model="filterForm.search"
              type="text"
              placeholder="Nama atau nomor HP"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              Status
            </label>
            <select
              id="status"
              v-model="filterForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua</option>
              <option value="active">Aktif</option>
              <option value="expiring_soon">Segera Expired</option>
              <option value="expired">Expired</option>
            </select>
          </div>

          <div class="flex items-end gap-2">
            <Button type="submit" variant="primary" class="flex-1">
              Filter
            </Button>
            <Button type="button" variant="secondary" @click="resetFilters">
              Reset
            </Button>
          </div>
        </form>
      </Card>

      <!-- Members List -->
      <Card title="Daftar Member">
        <DataTable v-if="members.length">
          <template #header>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Member
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Paket
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Berlaku Sampai
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
              Status
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
              Aksi
            </th>
          </template>
          <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-semibold text-sm">
                    {{ getInitials(member.full_name) }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900 whitespace-nowrap">
                    {{ member.full_name }}
                  </div>
                  <div class="text-sm text-gray-500 whitespace-nowrap">
                    {{ member.phone }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
              <span v-if="member.membership_package">{{ member.membership_package.name }}</span>
              <span v-else class="text-gray-400">-</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
              <div v-if="member.membership_end">
                <div class="font-medium">{{ formatDate(member.membership_end) }}</div>
                <div v-if="member.days_until_expiration !== null && member.days_until_expiration >= 0" class="text-xs text-gray-500">
                  {{ member.days_until_expiration }} hari
                </div>
              </div>
              <div v-else class="text-gray-400">-</div>
            </td>
            <td class="px-6 py-4 text-center whitespace-nowrap">
              <span v-if="member.status" :class="getStatusBadgeClass(member.status)">
                {{ getStatusLabel(member.status) }}
              </span>
              <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                -
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <div class="flex justify-center gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  :href="`/members/${member.id}/edit`"
                >
                  Edit
                </Button>
                <Button
                  variant="primary"
                  size="sm"
                  :href="`/members/${member.id}/renew`"
                >
                  Perpanjang
                </Button>
                <Button
                  variant="danger"
                  size="sm"
                  as="button"
                  @click="confirmDelete(member)"
                >
                  Hapus
                </Button>
              </div>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="👥"
          message="Tidak ada data member"
          subtitle="Belum ada member yang terdaftar atau sesuai dengan filter"
        >
          <template #actions>
            <Button
              variant="primary"
              href="/members/create"
            >
              + Tambah Member
            </Button>
          </template>
        </EmptyState>
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
  members: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      active: 0,
      expiring_soon: 0,
      expired: 0
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const filterForm = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || ''
})

function getInitials(name) {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

function getStatusBadgeClass(status) {
  const classes = 'px-2 py-1 text-xs font-semibold rounded-full'
  switch (status) {
    case 'active':
      return `${classes} bg-green-100 text-green-800`
    case 'expiring_soon':
      return `${classes} bg-orange-100 text-orange-800`
    case 'expired':
      return `${classes} bg-red-100 text-red-800`
    case 'inactive':
      return `${classes} bg-gray-100 text-gray-800`
    default:
      return `${classes} bg-gray-100 text-gray-800`
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'active':
      return 'Aktif'
    case 'expiring_soon':
      return 'Segera Expired'
    case 'expired':
      return 'Expired'
    case 'inactive':
      return 'Nonaktif'
    default:
      return '-'
  }
}

function applyFilters() {
  router.get('/members', filterForm.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function resetFilters() {
  filterForm.value = {
    search: '',
    status: ''
  }
  applyFilters()
}

function confirmDelete(member) {
  if (confirm(`Yakin ingin menghapus member "${member.full_name}"?`)) {
    router.delete(`/members/${member.id}`, {
      preserveScroll: true,
    })
  }
}
</script>
