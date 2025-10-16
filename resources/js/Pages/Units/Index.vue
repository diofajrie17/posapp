<template>
  <AppLayout title="Daftar Unit">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Unit" subtitle="Kelola satuan pengukuran produk">
        <template #actions>
          <Button href="/units/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Unit
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Nama Unit</th>
            <th class="px-4 py-3 text-left font-semibold">Simbol</th>
            <th class="px-4 py-3 text-left font-semibold">Tipe</th>
            <th class="px-4 py-3 text-left font-semibold">Jumlah Produk</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="units.length === 0">
            <td colspan="5" class="p-0">
              <EmptyState 
                icon="⚖️" 
                message="Belum ada unit" 
                subtitle="Tambahkan unit pengukuran untuk produk Anda"
              />
            </td>
          </tr>

          <tr
            v-for="unit in units"
            :key="unit.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ unit.name }}
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                {{ unit.symbol || '-' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span 
                :class="unit.is_base_unit 
                  ? 'px-2 py-1 text-xs rounded-full font-medium bg-green-100 text-green-700' 
                  : 'px-2 py-1 text-xs rounded-full font-medium bg-orange-100 text-orange-700'"
              >
                {{ unit.is_base_unit ? 'Base Unit' : 'Derived Unit' }}
              </span>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700">
                {{ unit.products_count || 0 }} produk
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <Button
                  :href="`/units/${unit.id}/edit`"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <button
                  @click="deleteUnit(unit.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

defineProps({
  units: Array
})

function deleteUnit(id) {
  if (confirm('Yakin ingin menghapus unit ini?')) {
    router.delete(`/units/${id}`)
  }
}
</script>
