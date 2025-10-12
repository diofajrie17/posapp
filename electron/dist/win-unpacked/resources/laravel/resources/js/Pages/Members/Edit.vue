<template>
  <div>
    <h1 class="text-xl font-bold mb-4">Edit Member</h1>

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

      <div class="mt-6">
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
defineProps({ member: Object })

const form = useForm({
  full_name: member.full_name,
  phone: member.phone,
})

function submit() {
  form.put(`/members/${member.id}`)
}
</script>
