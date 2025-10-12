<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Tambah Produk</h1>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label>Nama Produk</label>
        <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
      </div>
      <div class="mb-4">
        <label>Stok</label>
        <input v-model="form.stock" type="number" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-4">
        <label>Harga Jual</label>
        <input v-model="form.price" type="number" step="0.01" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="mb-4">
        <label>Satuan</label>
        <input v-model="form.unit" type="text" class="w-full border rounded px-3 py-2" />
      </div>

      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({ product: Object })

const form = useForm({
  name: product.name,
  stock: product.stock,
  price: product.price,
  unit: product.unit
});

function submit() {
  form.put(`/products/${product.id}`);
}
</script>
