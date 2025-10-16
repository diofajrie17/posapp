<template>
  <AppLayout title="Daftar Kategori">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Kategori" subtitle="Kelola kategori produk Anda">
        <template #actions>
          <Button v-if="can.create" href="/categories/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Nama Kategori</th>
            <th class="px-4 py-3 text-left font-semibold">Jumlah Produk</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="categories.length === 0">
            <td colspan="3" class="p-0">
              <EmptyState 
                icon="📁" 
                message="Belum ada kategori" 
                subtitle="Tambahkan kategori pertama untuk mengelompokkan produk"
              />
            </td>
          </tr>

          <tr
            v-for="category in categories"
            :key="category.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ category.name }}
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700">
                {{ category.products_count }} produk
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <Button
                  v-if="can.update"
                  :href="`/categories/${category.id}/edit`"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <button
                  v-if="can.delete"
                  @click="deleteCategory(category)"
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
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { router } from '@inertiajs/vue3'

defineProps({
  categories: Array,
  can: Object,
})

function deleteCategory(category) {
  if (confirm(`Yakin ingin menghapus kategori "${category.name}"?`)) {
    router.delete(`/categories/${category.id}`)
  }
}
</script>
