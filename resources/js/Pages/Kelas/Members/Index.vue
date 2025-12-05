<template>
  <AppLayout title="Member Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Member Kelas" subtitle="Kelola member untuk kelas (bulanan/harian)">
        <template #actions>
          <Button href="/kelas/members/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Member
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Total Member</h3>
            <p class="text-3xl font-bold text-indigo-600">{{ stats.total }}</p>
          </div>
        </Card>
        <Card>
          <div class="text-center">
            <h3 class="text-sm font-medium text-gray-600 mb-1">Member Aktif</h3>
            <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
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
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <InputLabel value="Cari Nama/Telepon" />
            <TextInput v-model="filters.search" placeholder="Cari..." class="w-full" />
          </div>
          <div>
            <InputLabel value="Tipe Membership" />
            <select v-model="filters.type" class="w-full px-3 py-2 border rounded">
              <option value="">Semua Tipe</option>
              <option value="monthly">Bulanan</option>
              <option value="daily">Harian</option>
            </select>
          </div>
          <div>
            <InputLabel value="Status" />
            <select v-model="filters.status" class="w-full px-3 py-2 border rounded">
              <option value="">Semua Status</option>
              <option value="active">Aktif</option>
              <option value="expired">Expired</option>
            </select>
          </div>
          <div class="flex items-end">
            <Button type="submit" variant="primary" class="w-full">Terapkan Filter</Button>
          </div>
        </form>
      </Card>

      <!-- Table -->
      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Telepon</th>
            <th class="px-4 py-3 text-left font-semibold">Tipe</th>
            <th class="px-4 py-3 text-left font-semibold">Periode</th>
            <th class="px-4 py-3 text-center font-semibold">Status</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="members.length === 0">
            <td colspan="6" class="p-0">
              <EmptyState icon="👥" message="Belum ada member" subtitle="Tambahkan member baru untuk memulai" />
            </td>
          </tr>

          <tr v-for="m in members" :key="m.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium text-gray-800">{{ m.full_name }}</td>
            <td class="px-4 py-3 text-gray-700">{{ m.phone || '-' }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded text-xs" :class="m.membership_type === 'monthly' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'">
                {{ m.membership_type === 'monthly' ? 'Bulanan' : 'Harian' }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-700 text-sm">
              <div v-if="m.membership_start && m.membership_end">
                {{ formatDate(m.membership_start) }} - {{ formatDate(m.membership_end) }}
              </div>
              <div v-else class="text-gray-400">-</div>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-1 rounded text-xs" :class="badgeClass(m)">
                {{ m.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <Button :href="`/kelas/members/${m.id}`" variant="secondary" class="mr-2">Lihat</Button>
              <Button :href="`/kelas/members/${m.id}/edit`" variant="primary">Edit</Button>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({
  members: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) }
})

const filters = reactive(props.filters || {})

function applyFilters() {
  router.visit('/kelas/members', { method: 'get', data: filters, preserveState: true, preserveScroll: true })
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

function badgeClass(m) {
  if (m.status === 'active') return 'bg-green-100 text-green-700'
  if (m.status === 'expired') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-700'
}
</script>

