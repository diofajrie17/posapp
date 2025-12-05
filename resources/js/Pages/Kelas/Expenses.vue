<template>
  <AppLayout title="Pengeluaran Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Pengeluaran Kelas" subtitle="Catat biaya operasional per kelas">
        <template #actions>
          <Button href="/kelas/reports" variant="secondary">Lihat Laporan</Button>
        </template>
      </PageHeader>

      <Card class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Tambah Pengeluaran Kelas</h3>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas *</label>
              <select v-model.number="form.kelas_id" class="input" required>
                <option value="" disabled>Pilih Kelas</option>
                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengeluaran *</label>
              <input v-model="form.expense_date" type="date" class="input" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengeluaran *</label>
              <input v-model="form.description" type="text" class="input" placeholder="Contoh: Bayar listrik, Beli peralatan" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
              <input v-model="form.category" type="text" class="input" placeholder="Opsional" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp) *</label>
              <input v-model.number="form.amount" type="number" step="0.01" min="0" class="input" placeholder="0" required />
            </div>
          </div>
          <div class="flex justify-end">
            <Button type="submit" variant="primary">Catat Pengeluaran</Button>
          </div>
        </form>
      </Card>

      <Card>
        <DataTable>
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Nama Kelas</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Nama Pengeluaran</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Kategori</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Jumlah</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="expenses.length === 0">
            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada pengeluaran</td>
          </tr>
          <tr v-for="e in expenses" :key="e.id" class="hover:bg-gray-50">
            <td class="px-4 py-2">{{ e.expense_date }}</td>
            <td class="px-4 py-2 font-medium">{{ e.kelas?.name || '-' }}</td>
            <td class="px-4 py-2">{{ e.description }}</td>
            <td class="px-4 py-2">{{ e.category || '-' }}</td>
            <td class="px-4 py-2 font-semibold text-red-600">{{ currency(e.amount) }}</td>
          </tr>
        </tbody>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'

const props = defineProps({
  expenses: { type: Array, default: () => [] },
  kelas: { type: Array, default: () => [] }
})

const form = reactive({ kelas_id: '', expense_date: '', category: '', description: '', amount: 0 })

function submit() {
  router.post('/kelas/expenses', form)
}

function currency(n) {
  return 'Rp ' + Number(n || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded; }
</style>


