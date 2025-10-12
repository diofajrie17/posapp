<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 p-6">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header dan Tombol -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Mutasi Stok</h1>
        <div class="flex flex-wrap gap-3">
          <Link href="/inventory/stock" class="btn-secondary">← Stock</Link>
        </div>
      </div>

      <!-- Filter -->
      <form @submit.prevent="apply" class="flex flex-wrap items-end gap-3 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Produk</label>
          <select v-model="f.product_id" class="border rounded-lg px-3 py-2">
            <option value="">Semua</option>
            <option v-for="p in products" :value="p.id" :key="p.id">{{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Sumber</label>
          <select v-model="f.source" class="border rounded-lg px-3 py-2">
            <option value="">Semua</option>
            <option>pos</option>
            <option>opname</option>
            <option>purchase</option>
            <option>return</option>
            <option>manual</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Arah</label>
          <select v-model="f.direction" class="border rounded-lg px-3 py-2">
            <option value="">Semua</option>
            <option>IN</option>
            <option>OUT</option>
          </select>
        </div>
        <button class="btn-primary">Terapkan</button>
      </form>

      <!-- Tabel -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Waktu</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Produk</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Arah</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Qty</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Sumber</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Catatan</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="m in movements.data"
              :key="m.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100">
                {{ new Date(m.moved_at).toLocaleString('id-ID',{hour12:false}) }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100">{{ m.product?.name }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ m.direction }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ m.quantity }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ m.source }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ m.note || '-' }}</td>
            </tr>
            <tr v-if="!movements.data.length">
              <td colspan="6" class="text-center p-6 text-gray-500">
                Tidak ada data mutasi stok.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex gap-2 flex-wrap">
        <Link
          v-for="link in movements.links"
          :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          :class="[
            'px-3 py-1 rounded border text-sm',
            {
              'bg-gray-800 text-white': link.active,
              'opacity-50 pointer-events-none': !link.url,
            },
          ]"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
  movements: Object,
  filters: Object,
  products: Array,
})

const f = reactive({ ...props.filters })

function apply() {
  router.get(route('stock.movements'), f, {
    preserveState: true,
    preserveScroll: true,
  })
}
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition;
}
.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300 transition;
}
</style>
