<template>
  <AppLayout title="Daftar Member">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header dan Tombol -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Daftar Member</h1>
        <div class="flex flex-wrap gap-3">
          <Link href="/members/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">+ Tambah Member</Link>
        </div>
      </div>

      <!-- Tabel Member -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Nama</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Phone</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="member in members"
              :key="member.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100">{{ member.full_name }}</td>
              <td class="px-4 py-3 border-b border-gray-100">{{ member.phone }}</td>
              <td class="px-4 py-3 border-b border-gray-100 flex gap-4">
                <Link
                  :href="`/members/${member.id}/edit`"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <Link
                  :href="`/members/${member.id}`"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:underline font-medium"
                  onclick="return confirm('Yakin ingin menghapus member ini?')"
                >
                  Hapus
                </Link>
              </td>
            </tr>
            <tr v-if="members.length === 0">
              <td colspan="3" class="text-center p-6 text-gray-500">
                Tidak ada data member.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  members: Array
})
</script>
