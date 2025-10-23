<template>
  <AppLayout title="Daftar Pengeluaran">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Pengeluaran" subtitle="Kelola pengeluaran operasional">
        <template #actions>
          <Button
            :href="route('expenses.export')"
            variant="secondary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export CSV
          </Button>
          <Button href="/expenses/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pengeluaran
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
            <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="expenses.length === 0">
            <td colspan="4" class="p-0">
              <EmptyState 
                icon="💸" 
                message="Belum ada pengeluaran" 
                subtitle="Catat pengeluaran operasional Anda"
              />
            </td>
          </tr>

          <tr
            v-for="expense in expenses"
            :key="expense.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ expense.description }}
            </td>
            <td class="px-4 py-3 text-right font-semibold text-red-600">
              Rp {{ formatPrice(expense.amount) }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              {{ formatDate(expense.date) }}
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <Button
                  :href="route('expenses.edit', expense.id)"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <button
                  @click="destroy(expense.id)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  expenses: Array,
});

function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
  }).format(value);
}

function formatDate(date) {
  return new Date(date).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function destroy(id) {
  if (confirm("Yakin ingin menghapus pengeluaran ini?")) {
    router.delete(route("expenses.destroy", id));
  }
}
</script>
