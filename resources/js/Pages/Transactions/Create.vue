<template>
  <AppLayout title="Transaksi Baru">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">🛒 Transaksi Kasir</h1>
      </div>

      <!-- Pelanggan -->
      <div class="mb-6">
        <label class="block text-sm font-medium mb-2 text-gray-700">Pelanggan</label>
        <select v-model="form.member_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
          <option :value="null">Pengunjung Harian</option>
          <option v-for="m in members" :key="m.id" :value="m.id">
            {{ m.full_name }}
          </option>
        </select>
        <div class="text-red-500 text-sm mt-1" v-if="form.errors.member_id">{{ form.errors.member_id }}</div>
      </div>

      <!-- Daftar Produk -->
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Item Produk</h2>
        <div
          v-for="(item, idx) in form.items"
          :key="idx"
          class="mb-3 border border-gray-200 p-4 rounded-lg bg-gray-50"
        >
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Produk</label>
              <select v-model="item.product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                <option disabled value="">Pilih Produk</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Jumlah</label>
              <input v-model.number="item.quantity" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Qty" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Harga Satuan</label>
              <input v-model.number="item.price_each" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Harga" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Subtotal</label>
              <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-right font-semibold text-gray-700">
                {{ formatRupiah((item.quantity || 0) * (item.price_each || 0)) }}
              </div>
            </div>
          </div>
          <div class="mt-3 flex justify-end" v-if="form.items.length > 1">
            <button @click="form.items.splice(idx, 1)" type="button" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded text-sm">
              🗑️ Hapus
            </button>
          </div>
        </div>
        <button @click="addItem" type="button" class="px-3 py-2 border border-gray-400 text-gray-700 rounded-lg hover:bg-gray-100 transition text-sm">
          + Tambah Item
        </button>
      </div>

      <!-- Diskon -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Tipe Diskon</label>
          <select v-model="form.discount_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
            <option :value="null">Tidak ada</option>
            <option value="fixed">Potongan (Rp)</option>
            <option value="percent">Persen (%)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Nilai Diskon</label>
          <input v-model.number="form.discount_value" type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" />
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <span class="text-gray-700">Potongan:</span>
          <strong class="text-gray-800">{{ formatRupiah(discountAmount) }}</strong>
        </div>
      </div>

      <!-- Pembayaran -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Metode Pembayaran</label>
          <select v-model="form.payment_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
            <option>Cash</option>
            <option>QR</option>
            <option>Transfer</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Dibayar (Cash)</label>
          <input v-model.number="form.paid_amount" type="number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" />
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
          <span class="text-gray-700">Kembalian:</span>
          <strong class="text-gray-800">{{ formatRupiah(changeAmount) }}</strong>
        </div>
      </div>

      <!-- Ringkasan -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
          <span class="text-blue-700">Subtotal:</span>
          <strong class="text-blue-800">{{ formatRupiah(subtotal) }}</strong>
        </div>
        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
          <span class="text-green-700">Total Bayar:</span>
          <strong class="text-green-800 text-lg">{{ formatRupiah(total) }}</strong>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Catatan</label>
          <input v-model="form.notes" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" placeholder="Opsional" />
        </div>
      </div>

      <!-- Simpan -->
      <button @click="submit" :disabled="form.processing" class="w-full bg-blue-600 text-white font-semibold px-6 py-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 text-lg">
        <span v-if="form.processing">Menyimpan...</span>
        <span v-else>💾 Simpan & Cetak</span>
      </button>

      <!-- Error -->
      <div
        v-if="Object.keys(form.errors).length"
        class="mt-4 text-red-600 text-sm space-y-1 bg-red-50 p-3 rounded-lg border border-red-200"
      >
        <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

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
  if (form.discount_type === 'percent')
    return subtotal.value * Math.min(Math.max(value, 0), 100) / 100
  return 0
})

const total = computed(() => Math.max(0, subtotal.value - discountAmount.value))

const changeAmount = computed(() => {
  if (form.paid_amount == null || form.paid_amount === '') return 0
  return Math.max(0, form.paid_amount - total.value)
})

function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
}

function submit() {
  form.post('/transactions')
}
</script>
