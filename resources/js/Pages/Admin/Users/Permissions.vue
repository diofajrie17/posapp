<template>
  <AppLayout title="Kelola Permission">
    <div class="max-w-4xl">
      <!-- Header -->
      <div class="mb-6">
        <Link
          href="/admin/users"
          class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block"
        >
          ← Kembali
        </Link>
        <h1 class="text-3xl font-bold text-gray-800">🔐 Kelola Permission - {{ user.name }}</h1>
        <p class="text-gray-600 mt-1">Atur akses yang boleh digunakan oleh kasir ini</p>
      </div>

      <!-- Success Message -->
      <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-green-800">{{ $page.props.flash.success }}</p>
      </div>

      <!-- Current Role Info -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Info User</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm text-gray-600">Nama</label>
            <p class="font-medium text-gray-900">{{ user.name }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-600">Email</label>
            <p class="font-medium text-gray-900">{{ user.email }}</p>
          </div>
          <div>
            <label class="text-sm text-gray-600">Role</label>
            <p>
              <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                :class="user.roles?.[0]?.name === 'Admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                {{ user.roles?.[0]?.name || 'No role' }}
              </span>
            </p>
          </div>
        </div>
      </div>

      <!-- Permission Groups -->
      <div class="space-y-6">
        <!-- Current Permissions -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Permissions</h3>
          <div class="flex flex-wrap gap-2">
            <span 
              v-for="permission in userPermissions" 
              :key="permission"
              class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full"
            >
              {{ permission }}
            </span>
            <span v-if="userPermissions.length === 0" class="text-gray-500 text-sm">Tidak ada permission</span>
          </div>
        </div>

        <!-- Permission Groups -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Members -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              👥 Member Management
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="members.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Members</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="members.create"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Create Members</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="members.update"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Update Members</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="members.delete"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Delete Members</span>
              </label>
            </div>
          </div>

          <!-- Packages -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              💳 Membership Packages
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="packages.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Packages</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="packages.create"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Create Packages</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="packages.update"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Update Packages</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="packages.delete"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Delete Packages</span>
              </label>
            </div>
          </div>

          <!-- Attendance -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              📋 Attendance
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="attendance.checkin"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Check-in Member</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="attendance.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Attendance</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="attendance.reports"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Attendance Reports</span>
              </label>
            </div>
          </div>

          <!-- Products -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              📦 Products
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="products.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Products</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="products.create"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Create Products</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="products.update"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Update Products</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="products.delete"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Delete Products</span>
              </label>
            </div>
          </div>

          <!-- Transactions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              💰 Transactions
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="transactions.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Transactions</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="transactions.create"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Create Transaction</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="transactions.reprint"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Reprint Receipt</span>
              </label>
            </div>
          </div>

          <!-- Reports -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              📊 Reports
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="reports.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Reports</span>
              </label>
            </div>
          </div>

          <!-- Expenses -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
              💸 Expenses
            </h3>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="expenses.view"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">View Expenses</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="expenses.create"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Create Expenses</span>
              </label>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  value="expenses.update"
                  v-model="form.permissions"
                  class="mr-3 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span class="text-sm text-gray-700">Update Expenses</span>
              </label>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t">
            <Link
              href="/admin/users"
              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
            >
              {{ processing ? 'Menyimpan...' : 'Simpan Permission' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  user: Object,
  allPermissions: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
})

const processing = ref(false)

const userPermissions = computed(() => {
  return props.user.permissions?.map(p => p.name) || []
})

// Initialize form with user's current permissions
const form = useForm({
  permissions: props.user.permissions?.map(p => p.name) || []
})

function submit() {
  processing.value = true
  form.post(`/admin/users/${props.user.id}/permissions`, {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>

