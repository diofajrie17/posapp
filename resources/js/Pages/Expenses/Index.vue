<template>
  <AppLayout title="Daftar Pengeluaran">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-lg p-6">
      <!-- Header dan Tombol -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Daftar Pengeluaran</h1>
        <div class="flex flex-wrap gap-3">
          <Link href="/expenses/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">+ Tambah Pengeluaran</Link>
        </div>
      </div>

      <!-- Tabel Pengeluaran -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="px-4 py-3 text-left border-b border-gray-200">Deskripsi</th>
              <th class="px-4 py-3 text-right border-b border-gray-200">Jumlah</th>
              <th class="px-4 py-3 text-left border-b border-gray-200">Tanggal</th>
              <th class="px-4 py-3 text-center border-b border-gray-200">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="expense in expenses"
              :key="expense.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 border-b border-gray-100 font-medium">
                {{ expense.description }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-right font-semibold text-gray-800">
                {{ formatRupiah(expense.amount) }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-gray-600">
                {{ formatDate(expense.date) }}
              </td>
              <td class="px-4 py-3 border-b border-gray-100 text-center flex gap-4 justify-center">
                <Link
                  :href="route('expenses.edit', expense.id)"
                  class="text-blue-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  @click="destroy(expense.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Hapus
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="expenses.length === 0">
              <td colspan="4" class="text-center p-6 text-gray-500">
                Belum ada data pengeluaran.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from '@/Layouts/AppLayout.vue'

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
