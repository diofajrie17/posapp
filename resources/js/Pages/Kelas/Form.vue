<template>
  <AppLayout :title="title">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader :title="title" subtitle="Form kelas">
        <template #actions>
          <Button href="/kelas" variant="secondary">Kembali</Button>
        </template>
      </PageHeader>

      <form v-if="mode !== 'show'" @submit.prevent="submit">
        <Card>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-gray-600">Kode</label>
          <input v-model="form.code" type="text" class="input" required />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Nama</label>
          <input v-model="form.name" type="text" class="input" required />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Instruktur</label>
          <input v-model="form.instructor" type="text" class="input" />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Kapasitas</label>
          <input v-model.number="form.capacity" type="number" min="0" class="input" />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Harga Bulanan (Monthly) *</label>
          <input v-model.number="form.monthly_price" type="number" step="0.01" min="0" class="input" required />
          <p class="text-xs text-gray-500 mt-1">Untuk registrasi member bulanan</p>
        </div>
        <div>
          <label class="block text-sm text-gray-600">Harga Harian (Daily) *</label>
          <input v-model.number="form.daily_price" type="number" step="0.01" min="0" class="input" required />
          <p class="text-xs text-gray-500 mt-1">Untuk check-in member harian</p>
        </div>
        <div>
          <label class="block text-sm text-gray-600">Status</label>
          <select v-model="form.status" class="input">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600">Mulai</label>
          <input v-model="form.start_date" type="date" class="input" />
        </div>
        <div>
          <label class="block text-sm text-gray-600">Selesai</label>
          <input v-model="form.end_date" type="date" class="input" />
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-600">Deskripsi</label>
          <textarea v-model="form.description" class="input"></textarea>
        </div>
          </div>
        </Card>
        <div class="mt-4 flex gap-2">
          <Button class="px-3 py-2" type="submit" variant="primary">Simpan</Button>
          <Button href="/kelas" variant="secondary">Batal</Button>
        </div>
      </form>

      <Card v-else>
        <div class="grid grid-cols-2 gap-4">
          <div><span class="font-medium">Kode</span>: {{ kelas.code }}</div>
          <div><span class="font-medium">Nama</span>: {{ kelas.name }}</div>
          <div><span class="font-medium">Instruktur</span>: {{ kelas.instructor }}</div>
          <div><span class="font-medium">Harga</span>: {{ formatCurrency(kelas.price) }}</div>
          <div><span class="font-medium">Harga Bulanan</span>: {{ formatCurrency(kelas.monthly_price) }}</div>
          <div><span class="font-medium">Harga Harian</span>: {{ formatCurrency(kelas.daily_price) }}</div>
          <div class="col-span-2"><span class="font-medium">Deskripsi</span>: {{ kelas.description }}</div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'

const props = defineProps({
  mode: { type: String, default: 'create' },
  kelas: { type: Object, default: null }
})

const title = computed(() => props.mode === 'edit' ? 'Edit Kelas' : props.mode === 'show' ? 'Detail Kelas' : 'Tambah Kelas')

const form = reactive({
  code: props.kelas?.code ?? '',
  name: props.kelas?.name ?? '',
  instructor: props.kelas?.instructor ?? '',
  capacity: props.kelas?.capacity ?? 0,
  price: props.kelas?.price ?? 0,
  monthly_price: props.kelas?.monthly_price ?? 0,
  daily_price: props.kelas?.daily_price ?? 0,
  status: props.kelas?.status ?? 'active',
  start_date: props.kelas?.start_date ?? '',
  end_date: props.kelas?.end_date ?? '',
  description: props.kelas?.description ?? ''
})

function formatCurrency(v) {
  return 'Rp ' + Number(v || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

function submit() {
  if (props.mode === 'edit' && props.kelas) {
    router.put(`/kelas/${props.kelas.id}`, form)
  } else {
    router.post('/kelas', form)
  }
}
</script>

<style scoped>
.input { @apply w-full px-3 py-2 border rounded outline-none focus:ring focus:border-indigo-400; }
</style>


