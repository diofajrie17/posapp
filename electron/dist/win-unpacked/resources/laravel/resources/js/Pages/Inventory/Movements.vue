<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Riwayat Mutasi Stok</h1>

    <form @submit.prevent="apply" class="flex flex-wrap items-end gap-2 mb-3">
      <div>
        <label class="block text-sm">Produk</label>
        <select v-model="f.product_id" class="border rounded p-2">
          <option value="">Semua</option>
          <option v-for="p in products" :value="p.id" :key="p.id">{{ p.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm">Sumber</label>
        <select v-model="f.source" class="border rounded p-2">
          <option value="">Semua</option>
          <option>pos</option><option>opname</option><option>purchase</option><option>return</option><option>manual</option>
        </select>
      </div>
      <div>
        <label class="block text-sm">Arah</label>
        <select v-model="f.direction" class="border rounded p-2">
          <option value="">Semua</option>
          <option>IN</option><option>OUT</option>
        </select>
      </div>
      <button class="px-3 py-2 rounded bg-gray-800 text-white">Terapkan</button>
    </form>

    <div class="p-4 bg-white rounded shadow">
      <table class="w-full">
        <thead><tr class="bg-gray-100 text-left">
          <th class="px-3 py-2">Waktu</th><th class="px-3 py-2">Produk</th><th class="px-3 py-2">Arah</th>
          <th class="px-3 py-2">Qty</th><th class="px-3 py-2">Sumber</th><th class="px-3 py-2">Catatan</th>
        </tr></thead>
        <tbody>
          <tr v-for="m in movements.data" :key="m.id" class="border-b">
            <td class="px-3 py-2">{{ new Date(m.moved_at).toLocaleString('id-ID',{hour12:false}) }}</td>
            <td class="px-3 py-2">{{ m.product?.name }}</td>
            <td class="px-3 py-2">{{ m.direction }}</td>
            <td class="px-3 py-2">{{ m.quantity }}</td>
            <td class="px-3 py-2">{{ m.source }}</td>
            <td class="px-3 py-2">{{ m.note || '-' }}</td>
          </tr>
        </tbody>
      </table>

      <div class="mt-3 flex gap-2">
        <Link v-for="link in movements.links" :key="link.label" :href="link.url || '#'"
              v-html="link.label"
              :class="['px-3 py-1 rounded border', { 'bg-gray-800 text-white': link.active, 'opacity-50 pointer-events-none': !link.url }]"/>
      </div>
    </div>
  </div>
</template>
<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive } from 'vue'  // <-- jangan lupa import reactive

const props = defineProps({ movements: Object, filters: Object, products: Array })
const f = reactive({ ...props.filters })

function apply() {
  router.get(route('stock.movements'), f, { preserveState: true, preserveScroll: true })
}
</script>

