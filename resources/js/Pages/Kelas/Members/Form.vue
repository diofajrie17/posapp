<template>
  <AppLayout :title="title">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader :title="title" subtitle="Form member kelas">
        <template #actions>
          <Button href="/kelas/members" variant="secondary">Kembali</Button>
        </template>
      </PageHeader>

      <form v-if="mode !== 'show'" @submit.prevent="submit">
        <Card>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <InputLabel value="Pilih Kelas (Opsional)" />
              <select v-model.number="form.kelas_id" class="w-full px-3 py-2 border rounded">
                <option value="">Pilih Kelas</option>
                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.name }}</option>
              </select>
            </div>
            <div>
              <InputLabel value="Nama Lengkap *" />
              <TextInput v-model="form.full_name" type="text" class="w-full" required />
            </div>
            <div>
              <InputLabel value="Nomor WhatsApp *" />
              <TextInput v-model="form.phone" type="text" class="w-full" required />
            </div>
            <div>
               <InputLabel value="Jumlah Bayar (Rp)" />
               <TextInput v-model.number="form.amount" type="number" step="0.01" min="0" class="w-full" placeholder="Pilih kelas untuk auto-fill harga" />
               <p class="text-xs text-gray-500 mt-1">Harga otomatis terisi dari kelas yang dipilih</p>
             </div>
            <div>
              <InputLabel value="Metode Pembayaran" />
              <select v-model="form.payment_method" class="w-full px-3 py-2 border rounded">
                <option value="">Pilih Metode</option>
                <option value="Cash">Cash</option>
                <option value="QR">QR</option>
                <option value="Transfer">Transfer</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipe Membership: <span class="font-semibold text-indigo-600">Bulanan (30 Hari)</span>
              </label>
            </div>
            <div>
              <InputLabel value="Tanggal Mulai *" />
              <TextInput v-model="form.membership_start" @change="autoCalculateEndDate" type="date" class="w-full" required />
            </div>
            <div>
              <InputLabel value="Tanggal Berakhir (auto +30 hari)" />
              <TextInput v-model="form.membership_end" type="date" class="w-full bg-gray-100" readonly />
            </div>
            <div class="md:col-span-2">
              <InputLabel value="Catatan" />
              <textarea v-model="form.notes" class="w-full px-3 py-2 border rounded" rows="3"></textarea>
            </div>
            <div>
              <label class="flex items-center">
                <input type="checkbox" v-model="form.is_active" class="mr-2" />
                <span class="text-sm text-gray-600">Member Aktif</span>
              </label>
            </div>
          </div>
        </Card>

        <div class="mt-4 flex gap-2">
          <Button type="submit" variant="primary">Simpan</Button>
          <Button href="/kelas/members" variant="secondary">Batal</Button>
        </div>
      </form>

      <Card v-else>
        <div class="grid grid-cols-2 gap-4">
          <div><span class="font-medium">Nama</span>: {{ member.full_name }}</div>
          <div><span class="font-medium">WhatsApp</span>: {{ member.phone || '-' }}</div>
          <div><span class="font-medium">Kelas</span>: {{ member.kelas?.name || '-' }}</div>
          <div><span class="font-medium">Tipe</span>: Bulanan (30 Hari)</div>
          <div><span class="font-medium">Status</span>: {{ member.status }}</div>
          <div><span class="font-medium">Periode</span>: {{ formatPeriod(member.membership_start, member.membership_end) }}</div>
          <div><span class="font-medium">Jumlah Bayar</span>: {{ currency(member.amount) }}</div>
          <div><span class="font-medium">Metode Pembayaran</span>: {{ member.payment_method || '-' }}</div>
          <div class="col-span-2"><span class="font-medium">Catatan</span>: {{ member.notes || '-' }}</div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'

const props = defineProps({
  mode: { type: String, default: 'create' },
  member: { type: Object, default: null },
  kelas: { type: Array, default: () => [] }
})

const title = computed(() => props.mode === 'edit' ? 'Edit Member Kelas' : props.mode === 'show' ? 'Detail Member Kelas' : 'Tambah Member Kelas')

const form = reactive({
  kelas_id: props.member?.kelas_id ?? '',
  full_name: props.member?.full_name ?? '',
  phone: props.member?.phone ?? '',
  membership_type: 'monthly',
  membership_start: props.member?.membership_start ?? '',
  membership_end: props.member?.membership_end ?? '',
  amount: props.member?.amount ?? '',
  payment_method: props.member?.payment_method ?? '',
  is_active: props.member?.is_active ?? true,
  notes: props.member?.notes ?? ''
})

// Auto-fill price when kelas is selected
watch(() => form.kelas_id, (newKelasId) => {
  if (newKelasId && props.mode === 'create') {
    const selectedKelas = props.kelas.find(k => k.id === newKelasId)
    if (selectedKelas && selectedKelas.monthly_price) {
      form.amount = selectedKelas.monthly_price
    }
  }
})

function autoCalculateEndDate() {
  if (form.membership_start) {
    const startDate = new Date(form.membership_start)
    const endDate = new Date(startDate)
    endDate.setDate(endDate.getDate() + 30)
    form.membership_end = endDate.toISOString().split('T')[0]
  }
}

function submit() {
  if (props.mode === 'edit' && props.member) {
    router.put(`/kelas/members/${props.member.id}`, form)
  } else {
    router.post('/kelas/members', form)
  }
}

function formatPeriod(start, end) {
  if (start && end) {
    return `${new Date(start).toLocaleDateString('id-ID')} - ${new Date(end).toLocaleDateString('id-ID')}`
  }
  return '-'
}

function currency(n) {
  return 'Rp ' + Number(n || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}
</script>

