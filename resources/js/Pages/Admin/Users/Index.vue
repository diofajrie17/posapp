<template>
  <AppLayout title="Admin Panel - Kelola Users">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">👥 Kelola Users</h1>
          <p class="text-gray-600 mt-1">Manage admin dan kasir accounts</p>
        </div>
        <Link
          href="/admin/users/create"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition"
        >
          ➕ Tambah User
        </Link>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Permission</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="user.roles?.[0]?.name === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                >
                  {{ user.roles?.[0]?.name || 'No role' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-600">
                  {{ user.permissions_count || 0 }} permissions
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex justify-center gap-2">
                  <Link
                    :href="route('admin.users.permissions', user.id)"
                    class="text-orange-600 hover:text-orange-900 mr-3"
                    title="Kelola Permission"
                  >
                    🔐
                  </Link>
                  <Link
                    :href="route('admin.users.transactions', user.id)"
                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                    title="Lihat Transaksi"
                  >
                    📊
                  </Link>
                  <Link
                    :href="route('admin.users.edit', user.id)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                    title="Edit"
                  >
                    ✏️
                  </Link>
                  <button
                    @click="confirmDelete(user)"
                    class="text-red-600 hover:text-red-900"
                    title="Hapus"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data users</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="users.links && users.links.length > 3" class="bg-gray-50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              v-if="users.prev_page_url"
              :href="users.prev_page_url"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="users.next_page_url"
              :href="users.next_page_url"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

defineProps({
  users: Object
})

function confirmDelete(user) {
  if (confirm(`Yakin ingin menghapus user "${user.name}"?`)) {
    router.delete(route('admin.users.destroy', user.id))
  }
}
</script>
