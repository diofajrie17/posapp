<template>
  <AppLayout title="Daftar Unit">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header dan Tombol -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Daftar Unit</h1>
        <div class="flex flex-wrap gap-3">
          <Link href="/units/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">+ Tambah Unit</Link>
        </div>
      </div>

      <!-- Tabel Unit -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse text-sm text-gray-700">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Nama Unit</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Simbol</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Tipe</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Jumlah Produk</th>
              <th class="px-4 py-3 text-center border-b border-gray-200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="unit in units"
              :key="unit.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100 font-medium text-gray-800">
                {{ unit.name }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                  {{ unit.symbol || '-' }}
                </span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span 
                  :class="unit.is_base_unit 
                    ? 'px-2 py-1 text-xs rounded-full font-medium bg-green-100 text-green-700' 
                    : 'px-2 py-1 text-xs rounded-full font-medium bg-orange-100 text-orange-700'"
                >
                  {{ unit.is_base_unit ? 'Base Unit' : 'Derived Unit' }}
                </span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100">
                <span class="px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700">
                  {{ unit.products_count || 0 }} produk
                </span>
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-center flex gap-4 justify-center">
                <Link
                  :href="`/units/${unit.id}/edit`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  @click="deleteUnit(unit.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="units.length === 0">
              <td colspan="4" class="text-center p-6 text-gray-500">
                Belum ada data unit.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  units: Array
})

function deleteUnit(id) {
  if (confirm('Yakin ingin menghapus unit ini?')) {
    router.delete(`/units/${id}`)
  }
}

function formatNumber(value) {
  // Remove unnecessary decimal places
  const num = parseFloat(value)
  return num % 1 === 0 ? num.toString() : num.toFixed(4).replace(/\.?0+$/, '')
}
</script>
