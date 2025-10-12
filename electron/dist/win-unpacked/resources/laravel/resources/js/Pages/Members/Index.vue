<template>
  <div class="p-6 bg-white rounded shadow">
    <!-- Header dan Tombol -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
      <h1 class="text-2xl font-bold text-gray-800">Daftar Member</h1>
      <div class="flex gap-3">
        <Link href="/dashboard" class="btn-secondary">← Kembali ke Dashboard</Link>
        <Link href="/members/create" class="btn-primary">+ Tambah Member</Link>
      </div>
    </div>

    <!-- Tabel Member -->
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
            <th class="px-4 py-2 text-left border-b border-gray-300">Nama</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Phone</th>
            <th class="px-4 py-2 text-left border-b border-gray-300">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50">
            <td class="px-4 py-2 border-b border-gray-100">{{ member.full_name }}</td>
            <td class="px-4 py-2 border-b border-gray-100">{{ member.phone }}</td>
            <td class="px-4 py-2 border-b border-gray-100">
              <Link :href="`/members/${member.id}/edit`" class="text-blue-600 hover:underline">Edit</Link>
              <Link
                :href="`/members/${member.id}`"
                method="delete"
                as="button"
                class="text-red-600 hover:underline ml-4"
                onclick="return confirm('Yakin ingin menghapus member ini?')"
              >
                Hapus
              </Link>
            </td>
          </tr>
          <tr v-if="members.length === 0">
            <td colspan="3" class="text-center p-4 text-gray-500">Tidak ada data member.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  members: Array
})
</script>

<style>
.btn-primary {
  @apply px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition;
}

.btn-secondary {
  @apply px-4 py-2 bg-gray-200 text-gray-800 rounded font-semibold text-sm hover:bg-gray-300 transition;
}
</style>
