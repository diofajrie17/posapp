<template>
  <div class="p-6 bg-white rounded shadow max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Transaksi Kasir</h1>
      <Link href="/dashboard" class="btn-secondary">← Dashboard</Link>
    </div>

    <!-- Pelanggan -->
    <div class="mb-4">
      <label class="block text-sm font-medium">Pelanggan</label>
      <select v-model="form.member_id" class="input w-full">
        <option :value="null">Pengunjung Harian</option>
        <option v-for="m in members" :key="m.id" :value="m.id">{{ m.full_name }}</option>
      </select>
      <p class="input-error" v-if="form.errors.member_id">{{ form.errors.member_id }}</p>
    </div>

    <!-- Daftar Produk -->
    <div class="mb-4">
      <h2 class="font-semibold mb-2">Item Produk</h2>
      <div v-for="(item, idx) in form.items" :key="idx" class="mb-2 border p-3 rounded bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center">
          <select v-model="item.product_id" class="input">
            <option disabled value="">Pilih Produk</option>
            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <input v-model.number="item.quantity" type="number" min="1" class="input" placeholder="Qty" />
          <input v-model.number="item.price_each" type="number" class="input" placeholder="Harga" />
          <div class="text-right font-semibold text-gray-700">
            = {{ (item.quantity || 0) * (item.price_each || 0) }}
          </div>
        </div>
      </div>
      <button @click="addItem" class="btn-outline text-sm">+ Tambah Item</button>
    </div>

    <!-- Diskon -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
      <div>
        <label class="block text-sm font-medium">Tipe Diskon</label>
        <select v-model="form.discount_type" class="input w-full">
          <option :value="null">Tidak ada</option>
          <option value="fixed">Potongan (Rp)</option>
          <option value="percent">Persen (%)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Nilai Diskon</label>
        <input v-model.number="form.discount_value" type="number" class="input w-full" />
      </div>
      <div class="flex items-center justify-between p-3 bg-white rounded shadow">
        <span>Potongan:</span>
        <strong>{{ discountAmount.toLocaleString('id-ID') }}</strong>
      </div>
    </div>

    <!-- Pembayaran -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
      <div>
        <label class="block text-sm font-medium">Metode Pembayaran</label>
        <select v-model="form.payment_type" class="input w-full">
          <option>Cash</option>
          <option>QR</option>
          <option>Transfer</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Dibayar (Cash)</label>
        <input v-model.number="form.paid_amount" type="number" class="input w-full" />
      </div>
      <div class="flex items-center justify-between p-3 bg-white rounded shadow">
        <span>Kembalian:</span>
        <strong>{{ changeAmount.toLocaleString('id-ID') }}</strong>
      </div>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
      <div class="flex items-center justify-between p-3 bg-white rounded shadow">
        <span>Subtotal:</span>
        <strong>{{ subtotal.toLocaleString('id-ID') }}</strong>
      </div>
      <div class="flex items-center justify-between p-3 bg-white rounded shadow">
        <span>Total Bayar:</span>
        <strong>{{ total.toLocaleString('id-ID') }}</strong>
      </div>
      <div>
        <label class="block text-sm font-medium">Catatan</label>
        <input v-model="form.notes" type="text" class="input w-full" placeholder="Opsional" />
      </div>
    </div>

    <!-- Simpan -->
    <button @click="submit" class="btn-primary w-full mt-4">💾 Simpan & Cetak</button>

    <!-- Error -->
    <div v-if="Object.keys(form.errors).length" class="mt-4 text-red-600 text-sm space-y-1">
      <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

defineProps({ products: Array, members: Array })

const form = useForm({
  member_id: null,
  payment_type: 'Cash',
  items: [{ product_id: '', quantity: 1, price_each: 0 }],
  discount_type: null,
  discount_value: 0,
  paid_amount: null,
  notes: ''
})

function addItem() {
  form.items.push({ product_id: '', quantity: 1, price_each: 0 })
}

const subtotal = computed(() =>
  form.items.reduce((sum, item) => sum + (item.quantity || 0) * (item.price_each || 0), 0)
)

const discountAmount = computed(() => {
  if (!form.discount_type) return 0
  const value = Number(form.discount_value || 0)
  if (form.discount_type === 'fixed') return Math.min(value, subtotal.value)
  if (form.discount_type === 'percent') return subtotal.value * Math.min(Math.max(value, 0), 100) / 100
  return 0
})

const total = computed(() => Math.max(0, subtotal.value - discountAmount.value))

const changeAmount = computed(() => {
  if (form.paid_amount == null || form.paid_amount === '') return 0
  return Math.max(0, form.paid_amount - total.value)
})

function submit() {
  form.post('/transactions')
}
</script>

<style scoped>
.input {
  @apply border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500;
}
.input-error {
  @apply text-red-500 text-sm mt-1;
}
.btn-primary {
  @apply bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition;
}
.btn-secondary {
  @apply bg-gray-200 text-gray-800 font-medium px-4 py-2 rounded hover:bg-gray-300 transition;
}
.btn-outline {
  @apply border border-gray-400 text-gray-700 px-3 py-1 rounded hover:bg-gray-100 transition;
}
</style>
