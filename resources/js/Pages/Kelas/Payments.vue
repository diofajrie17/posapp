<template>
  <AppLayout title="Pembayaran Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Pembayaran Kelas" subtitle="Catat pemasukan dari kelas">
        <template #actions>
          <Button href="/kelas/reports" variant="secondary">Lihat Laporan</Button>
        </template>
      </PageHeader>

      <Card class="mb-6">
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-6 gap-4">
      <select v-model.number="form.kelas_id" class="input" required>
        <option value="" disabled>Pilih Kelas</option>
        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.name }}</option>
      </select>
      <select v-model.number="form.member_id" class="input">
        <option value="">(Opsional) Member</option>
        <option v-for="m in members" :key="m.id" :value="m.id">{{ m.full_name }}</option>
      </select>
      <input v-model="form.payment_date" type="date" class="input" required />
      <input v-model.number="form.amount" type="number" step="0.01" min="0" class="input" required placeholder="Jumlah" />
      <input v-model="form.payment_method" type="text" class="input" placeholder="Metode" />
          <Button type="submit" variant="primary">Catat</Button>
        </form>
      </Card>

      <Card>
        <DataTable>
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Tanggal</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Kelas</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Member</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Jumlah</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Metode</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="p in payments" :key="p.id">
            <td class="px-4 py-2">{{ p.payment_date }}</td>
            <td class="px-4 py-2">{{ p.kelas?.name }}</td>
            <td class="px-4 py-2">{{ p.member?.full_name || '-' }}</td>
            <td class="px-4 py-2">{{ currency(p.amount) }}</td>
            <td class="px-4 py-2">{{ p.payment_method }}</td>
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
  payments: { type: Array, default: () => [] },
  kelas: { type: Array, default: () => [] },
  members: { type: Array, default: () => [] }
})

const form = reactive({ kelas_id: '', member_id: '', payment_date: '', amount: 0, payment_method: '' })

function submit() {
  router.post('/kelas/payments', form)
}

function currency(n) {
  return 'Rp ' + Number(n || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded; }
</style>


