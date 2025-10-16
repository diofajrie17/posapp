<template>
  <AppLayout title="Daftar Member">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <PageHeader
        title="Daftar Member"
        subtitle="Kelola data member dan informasi keanggotaan"
      >
        <template #actions>
          <Button
            variant="primary"
            href="/members/create"
          >
            + Tambah Member
          </Button>
        </template>
      </PageHeader>

      <!-- Summary Card -->
      <Card class="mb-6">
        <div class="text-center">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Total Member</h3>
          <p class="text-3xl font-bold text-blue-600">{{ members.length }}</p>
        </div>
      </Card>

      <!-- Members List -->
      <Card title="Daftar Member">
        <DataTable v-if="members.length">
          <template #header>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nama Lengkap
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                No. Telepon
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                Aksi
              </th>
            </tr>
          </template>
          <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-semibold text-sm">
                    {{ getInitials(member.full_name) }}
                  </span>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ member.full_name }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ member.phone || '-' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
              <div class="flex justify-center gap-2">
                <Button
                  variant="ghost"
                  size="sm"
                  :href="`/members/${member.id}/edit`"
                >
                  Edit
                </Button>
                <Button
                  variant="danger"
                  size="sm"
                  :href="`/members/${member.id}`"
                  method="delete"
                  as="button"
                  @click="confirmDelete(member)"
                >
                  Hapus
                </Button>
              </div>
            </td>
          </tr>
        </DataTable>

        <EmptyState
          v-else
          icon="👥"
          message="Tidak ada data member"
          subtitle="Belum ada member yang terdaftar"
        >
          <template #actions>
            <Button
              variant="primary"
              href="/members/create"
            >
              + Tambah Member
            </Button>
          </template>
        </EmptyState>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

defineProps({
  members: Array
})

function getInitials(name) {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

function confirmDelete(member) {
  if (confirm(`Yakin ingin menghapus member "${member.full_name}"?`)) {
    router.delete(`/members/${member.id}`, {
      preserveScroll: true,
    })
  }
}
</script>
