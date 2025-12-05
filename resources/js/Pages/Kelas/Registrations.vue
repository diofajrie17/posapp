<template>
  <AppLayout title="Registrasi Kelas">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Registrasi Kelas" subtitle="Daftarkan member ke kelas">
        <template #actions>
          <Button href="/kelas" variant="secondary">Daftar Kelas</Button>
        </template>
      </PageHeader>

      <Card class="mb-6">
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
            <select v-model.number="form.kelas_id" class="input" required>
              <option value="" disabled>Pilih Kelas</option>
              <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Member</label>
            <select v-model.number="form.member_id" class="input" required>
              <option value="" disabled>Pilih Member</option>
              <option v-for="m in members" :key="m.id" :value="m.id">{{ m.full_name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai*</label>
            <input v-model="form.start_date" @change="autoCalculateEndDate" type="date" class="input" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir (auto 30 hari)</label>
            <input v-model="form.end_date" type="date" class="input bg-gray-100" readonly />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Registrasi</label>
            <input v-model="form.registration_date" type="date" class="input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar</label>
            <input v-model.number="form.amount" type="number" step="0.01" min="0" class="input" placeholder="0" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
            <select v-model="form.payment_method" class="input">
              <option value="">Pilih Metode</option>
              <option value="Cash">Cash</option>
              <option value="QR">QR</option>
              <option value="Transfer">Transfer</option>
            </select>
          </div>
          <div class="md:col-span-2 lg:col-span-3">
            <Button type="submit" variant="primary" class="w-full">Tambah Registrasi & Pembayaran</Button>
          </div>
        </form>
      </Card>

      <Card>
        <DataTable>
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Kelas</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Member</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Periode</th>
            <th class="px-4 py-2 text-left text-xs text-gray-500 uppercase">Tanggal Registrasi</th>
            <th></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="r in registrations" :key="r.id">
            <td class="px-4 py-2">{{ r.kelas?.name }}</td>
            <td class="px-4 py-2">{{ r.member?.full_name }}</td>
            <td class="px-4 py-2">{{ r.start_date }} - {{ r.end_date }}</td>
            <td class="px-4 py-2">{{ r.registration_date }}</td>
            <td class="px-4 py-2 text-right">
                <Button @click="remove(r.id)" variant="danger" as="button">Hapus</Button>
            </td>
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
  registrations: { type: Array, default: () => [] },
  kelas: { type: Array, default: () => [] },
  members: { type: Array, default: () => [] }
})

const form = reactive({ 
  kelas_id: '', 
  member_id: '', 
  start_date: '',
  end_date: '',
  registration_date: '',
  amount: '',
  payment_method: ''
})

function autoCalculateEndDate() {
  if (form.start_date) {
    const startDate = new Date(form.start_date)
    const endDate = new Date(startDate)
    endDate.setDate(endDate.getDate() + 30)
    form.end_date = endDate.toISOString().split('T')[0]
    
    // Also set registration_date if not set
    if (!form.registration_date) {
      form.registration_date = form.start_date
    }
  }
}

function submit() {
  router.post('/kelas/registrations', form)
}

function remove(id) {
  router.delete(`/kelas/registrations/${id}`)
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500; }
</style>


