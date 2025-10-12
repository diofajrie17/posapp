<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-purple-100 to-pink-100 p-6">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">📦 Stock Opname</h1>
        <Link href="/inventory/stock" class="btn-secondary">← Stock</Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Catatan -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Alasan</label>
          <input
            v-model="reason"
            type="text"
            class="input-field"
            placeholder="Shift malam / audit bulanan"
          />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="bg-gray-100 text-gray-700">
                <th class="px-4 py-3 text-left border-b border-gray-200">Produk</th>
                <th class="px-4 py-3 text-left border-b border-gray-200">Stok Sistem</th>
                <th class="px-4 py-3 text-left border-b border-gray-200">Stok Aktual</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(p, i) in products"
                :key="p.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 border-b border-gray-100">{{ p.name }}</td>
                <td class="px-4 py-3 border-b border-gray-100">{{ p.stock }}</td>
                <td class="px-4 py-3 border-b border-gray-100">
                  <input
                    v-model.number="rows[i].qty_actual"
                    type="number"
                    min="0"
                    class="input-field w-32"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Action -->
        <div class="flex gap-3">
          <button type="submit" class="btn-primary">💾 Simpan Opname</button>
          <Link href="/inventory/stock" class="btn-secondary">Batal</Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const props = defineProps({ products: Array });

const reason = ref("");
const rows = reactive(
  props.products.map((p) => ({
    product_id: p.id,
    qty_actual: p.stock,
  }))
);

function submit() {
  router.post("/inventory/stock/opname", {
    items: rows,
    reason: reason.value,
  });
}
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300 transition;
}

.input-field {
  @apply w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none;
}
</style>
