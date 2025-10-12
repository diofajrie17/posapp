<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Stock Opname</h1>

    <div class="mb-3">
      <label class="block text-sm">Catatan/Alasan</label>
      <input v-model="reason" class="border rounded p-2 w-full" placeholder="Shift malam / audit bulanan" />
    </div>

    <form @submit.prevent="submit" class="p-4 bg-white rounded shadow">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="px-3 py-2">Produk</th>
            <th class="px-3 py-2">Stok Sistem</th>
            <th class="px-3 py-2">Stok Aktual</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, i) in products" :key="p.id" class="border-b">
            <td class="px-3 py-2">{{ p.name }}</td>
            <td class="px-3 py-2">{{ p.stock }}</td>
            <td class="px-3 py-2">
              <input v-model.number="rows[i].qty_actual" type="number" min="0" class="border rounded p-2 w-32" />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Simpan Opname</button>
        <Link href="/inventory/stock" class="px-4 py-2 rounded border ml-2">Batal</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive, ref } from 'vue'  // <-- pastikan import ref dan reactive

const props = defineProps({ products: Array })

const reason = ref('')
const rows = reactive(props.products.map(p => ({
  product_id: p.id,
  qty_actual: p.stock
})))

function submit() {
  router.post('/inventory/stock/opname', {
    items: rows,
    reason: reason.value,
  })
}
</script>
