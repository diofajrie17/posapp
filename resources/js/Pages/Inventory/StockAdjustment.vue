<template>
  <AppLayout title="Stock Adjustment">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8">
      <h1 class="text-2xl font-bold mb-6">Manual Stock Adjustment</h1>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Product</label>
          <select v-model="form.product_id" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Product</option>
            <option v-for="p in products" :key="p.id" :value="p.id">
              {{ p.name }}
              <span v-if="typeof p.is_active === 'boolean' && !p.is_active" class="text-gray-400"> [Nonaktif]</span>
              <span v-if="p.base_unit && p.unit_quantity > 1"> ({{ p.unit.name }} = {{ formatNumber(p.unit_quantity) }} {{ p.base_unit.name }})</span>
              <span v-else> ({{ p.unit?.name || '-' }})</span>
              — System: {{ formatNumber(p.stock) }}
              <span v-if="p.min_stock">, Min: {{ p.min_stock }}</span>
              <span v-if="p.max_stock">, Max: {{ p.max_stock }}</span>
            </option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Actual Stock (Physical Count)</label>
          <input v-model.number="form.qty_actual" type="number" min="0" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Reason/Notes</label>
          <input v-model="form.reason" type="text" class="w-full border rounded px-3 py-2" />
        </div>
        <div class="mb-4">
          <span class="text-sm">System Stock: <b>{{ systemStock }}</b></span><br>
          <span class="text-sm">Difference: <b :class="diffClass">{{ difference }}</b></span>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold">Submit Adjustment</button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const products = ref([])
const form = useForm({ product_id: '', qty_actual: 0, reason: '' })
const systemStock = computed(() => {
  const p = products.value.find(x => x.id === form.product_id)
  return p ? p.stock : 0
})
const difference = computed(() => form.qty_actual - systemStock.value)
const diffClass = computed(() => difference.value === 0 ? 'text-green-600' : (difference.value > 0 ? 'text-blue-600' : 'text-red-600'))
function formatNumber(val) {
  return parseFloat(val) % 1 === 0 ? val : parseFloat(val).toFixed(2)
}
function submit() {
  form.post('/inventory/stock-adjustments')
}
onMounted(async () => {
  // Fetch products with current stock
  const res = await fetch('/api/products?fields=id,name,stock')
  products.value = await res.json()
})
</script>
