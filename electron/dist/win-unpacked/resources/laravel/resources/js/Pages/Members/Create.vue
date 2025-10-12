<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Tambah Member</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block">Nama Lengkap</label>
        <input v-model="form.full_name" type="text" class="w-full border rounded px-3 py-2" />
        <div v-if="form.errors.full_name" class="text-red-500 text-sm">{{ form.errors.full_name }}</div>
      </div>

      <div class="mb-4">
        <label class="block">No. HP</label>
        <input v-model="form.phone" type="text" class="w-full border rounded px-3 py-2" />
        <div v-if="form.errors.phone" class="text-red-500 text-sm">{{ form.errors.phone }}</div>
      </div>

      <!-- Tambahkan field lain sesuai kebutuhan -->
      <div class="mt-6">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  full_name: '',
  phone: '',
})

function submit() {
  form.post('/members')
}
</script>
