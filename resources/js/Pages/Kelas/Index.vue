<template>
  <AppLayout title="Daftar Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Kelas" subtitle="Kelola kelas, jadwal, dan peserta">
        <template #actions>
          <Button v-if="can.manage" href="/kelas/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kelas
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Kode</th>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Instruktur</th>
            <th class="px-4 py-3 text-right font-semibold">Harga</th>
            <th class="px-4 py-3 text-center font-semibold">Status</th>
            <th class="px-4 py-3 text-right font-semibold">Aksi</th>
          </template>

          <tr v-if="list.length === 0">
            <td colspan="6" class="p-0">
              <EmptyState icon="🎓" message="Belum ada kelas" subtitle="Tambah kelas baru untuk memulai" />
            </td>
          </tr>

          <tr v-for="k in list" :key="k.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-700 font-mono text-sm">{{ k.code }}</td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ k.name }}</td>
            <td class="px-4 py-3 text-gray-700">{{ k.instructor || '-' }}</td>
            <td class="px-4 py-3 text-right">{{ formatCurrency(k.price) }}</td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-1 rounded text-xs" :class="badgeClass(k.status)">{{ k.status }}</span>
            </td>
            <td class="px-4 py-3 text-right">
              <Button :href="`/kelas/${k.id}`" variant="secondary" class="mr-2">Lihat</Button>
              <Button v-if="can.manage" :href="`/kelas/${k.id}/edit`" variant="primary">Edit</Button>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

const props = defineProps({
  list: { type: Array, default: () => [] },
  can: { type: Object, default: () => ({}) }
})

function formatCurrency(v) {
  return 'Rp ' + Number(v || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

function badgeClass(status) {
  if (status === 'active') return 'bg-green-100 text-green-700'
  if (status === 'completed') return 'bg-gray-100 text-gray-700'
  return 'bg-yellow-100 text-yellow-700'
}
</script>


