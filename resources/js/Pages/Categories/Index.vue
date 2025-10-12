<template>
  <AppLayout title="Daftar Kategori">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header & Tombol -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Daftar Kategori</h1>
        <div class="flex flex-wrap gap-3">
          <Link v-if="can.create" href="/categories/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">
            + Tambah Kategori
          </Link>
        </div>
      </div>

      <!-- Tabel Kategori -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse text-sm text-gray-700">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Nama Kategori</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Jumlah Produk</th>
              <th class="px-4 py-3 text-center border-b border-gray-200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="category in categories"
              :key="category.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100 font-medium text-gray-800">
                {{ category.name }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span class="px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700">
                  {{ category.products_count }} produk
                </span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-center flex gap-4 justify-center">
                <Link
                  v-if="can.update"
                  :href="`/categories/${category.id}/edit`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  v-if="can.delete"
                  @click="deleteCategory(category)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="3" class="text-center p-6 text-gray-500">
                Belum ada kategori yang tersedia.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'

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
