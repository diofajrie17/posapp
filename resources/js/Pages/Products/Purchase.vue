<template>
  <AppLayout title="Tambah Pembelian Produk">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <h1 class="text-2xl font-bold mb-6">Tambah Pembelian Produk</h1>
      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>
          <select v-model="form.product_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
            <option disabled value="">Pilih Produk</option>
            <option v-for="p in products" :key="p.id" :value="p.id">
              {{ p.name }} (Stok: {{ p.stock }})
            </option>
          </select>
          <div v-if="form.errors.product_id" class="text-red-500 text-sm mt-1">{{ form.errors.product_id }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Dibeli</label>
          <input v-model.number="form.quantity" type="number" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Jumlah" />
          <div v-if="form.errors.quantity" class="text-red-500 text-sm mt-1">{{ form.errors.quantity }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Harga Beli per Unit</label>
          <input v-model.number="form.cost_price" type="number" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Harga Beli" />
          <div v-if="form.errors.cost_price" class="text-red-500 text-sm mt-1">{{ form.errors.cost_price }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
          <input v-model="form.notes" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Catatan" />
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white font-semibold px-6 py-4 rounded-lg hover:bg-blue-700 transition duration-200 text-lg">Simpan Pembelian</button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const products = ref([])
const form = useForm({ product_id: '', quantity: 1, cost_price: 0, notes: '' })

onMounted(async () => {
  // Fetch active products
  const res = await fetch('/api/products?fields=id,name,stock')
  products.value = await res.json()
})

function submit() {
  form.post('/products/purchase')
}
</script>
