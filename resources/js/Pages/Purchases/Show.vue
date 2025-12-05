<template>
  <AppLayout title="Detail Pembelian">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader 
        :title="`Detail Pembelian: ${purchase.purchase_number}`" 
        subtitle="Informasi lengkap pembelian"
      >
        <template #actions>
          <div class="flex gap-2">
            <Button :href="route('purchases.index')" variant="secondary">
              Kembali
            </Button>
          </div>
        </template>
      </PageHeader>

      <!-- Payment Status Badge -->
      <div class="mb-6">
        <span 
          v-if="purchase.payment_status === 'paid'" 
          class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-green-100 text-green-800"
        >
          ✓ Lunas
        </span>
        <span 
          v-else-if="purchase.payment_status === 'partial'" 
          class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-yellow-100 text-yellow-800"
        >
          ⚠ Masih Ada Hutang
        </span>
        <span 
          v-else 
          class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-red-100 text-red-800"
        >
          ✕ Belum Dibayar
        </span>
      </div>

      <!-- Purchase Info -->
      <Card class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pembelian</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600">No. Pembelian</p>
            <p class="font-semibold text-indigo-600">{{ purchase.purchase_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Tanggal Pembelian</p>
            <p class="font-semibold">{{ formatDate(purchase.purchase_date) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Supplier</p>
            <p class="font-semibold">{{ purchase.supplier_name }}</p>
            <p v-if="purchase.supplier_phone" class="text-sm text-gray-500">
              {{ purchase.supplier_phone }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Dibuat Oleh</p>
            <p class="font-semibold">{{ purchase.creator?.name }}</p>
            <p class="text-sm text-gray-500">{{ formatDateTime(purchase.created_at) }}</p>
          </div>
          <div v-if="purchase.supplier_address" class="md:col-span-2">
            <p class="text-sm text-gray-600">Alamat Supplier</p>
            <p class="font-medium">{{ purchase.supplier_address }}</p>
          </div>
          <div v-if="purchase.notes" class="md:col-span-2">
            <p class="text-sm text-gray-600">Catatan</p>
            <p class="font-medium">{{ purchase.notes }}</p>
          </div>
        </div>
      </Card>

      <!-- Payment Summary -->
      <Card class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pembayaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-blue-600 mb-1">Total Pembelian</p>
            <p class="text-2xl font-bold text-blue-800">{{ formatRupiah(purchase.total_amount) }}</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-green-600 mb-1">Sudah Dibayar</p>
            <p class="text-2xl font-bold text-green-800">{{ formatRupiah(purchase.paid_amount || 0) }}</p>
          </div>
          <div :class="remainingAmount > 0 ? 'bg-red-50' : 'bg-gray-50'" class="p-4 rounded-lg">
            <p :class="remainingAmount > 0 ? 'text-red-600' : 'text-gray-600'" class="text-sm mb-1">Sisa Hutang</p>
            <p :class="remainingAmount > 0 ? 'text-red-800' : 'text-gray-800'" class="text-2xl font-bold">{{ formatRupiah(remainingAmount) }}</p>
          </div>
        </div>
      </Card>

      <!-- Items Table -->
      <Card class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Item Pembelian</h3>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Produk</th>
            <th class="px-4 py-3 text-center font-semibold">Unit & Konversi</th>
            <th class="px-4 py-3 text-center font-semibold">Qty Beli</th>
            <th class="px-4 py-3 text-center font-semibold">Qty Base</th>
            <th class="px-4 py-3 text-right font-semibold">Harga/Unit</th>
            <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
          </template>

          <tr 
            v-for="item in purchase.items" 
            :key="item.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 font-medium">
              {{ item.product?.name }}
            </td>
            <td class="px-4 py-3 text-center">
              <div class="font-semibold">{{ item.unit_name || item.unit?.name }}</div>
              <div v-if="item.unit_conversion && item.unit_conversion > 1" class="text-xs text-gray-500">
                @ {{ formatNumber(item.unit_conversion) }} pcs/unit
              </div>
            </td>
            <td class="px-4 py-3 text-center font-semibold">
              {{ formatNumber(item.quantity) }}
            </td>
            <td class="px-4 py-3 text-center text-blue-600 font-medium">
              {{ formatNumber(item.quantity_in_base_unit || item.base_quantity) }}
            </td>
            <td class="px-4 py-3 text-right">
              {{ formatRupiah(item.unit_cost) }}
              <div class="text-xs text-gray-500">
                ({{ formatRupiah(item.base_unit_cost) }}/pcs)
              </div>
            </td>
            <td class="px-4 py-3 text-right font-semibold text-green-600">
              {{ formatRupiah(item.subtotal) }}
            </td>
          </tr>

          <tr class="bg-gray-50 border-t-2 border-gray-300">
            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-800">
              Total:
            </td>
            <td class="px-4 py-3 text-right font-bold text-indigo-600 text-lg">
              {{ formatRupiah(purchase.total_amount) }}
            </td>
          </tr>
        </DataTable>
      </Card>

      <!-- Payment History -->
      <Card>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Riwayat Pembayaran</h3>
          <Button 
            v-if="remainingAmount > 0"
            @click="showPaymentModal = true"
            variant="primary"
            size="sm"
          >
            + Tambah Pembayaran
          </Button>
        </div>

        <div v-if="purchase.payments && purchase.payments.length > 0">
          <DataTable>
            <template #header>
              <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
              <th class="px-4 py-3 text-right font-semibold">Jumlah</th>
              <th class="px-4 py-3 text-center font-semibold">Metode</th>
              <th class="px-4 py-3 text-left font-semibold">Catatan</th>
              <th class="px-4 py-3 text-center font-semibold">Dibuat Oleh</th>
            </template>

            <tr 
              v-for="payment in purchase.payments" 
              :key="payment.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3">
                <div class="font-medium">{{ formatDate(payment.payment_date) }}</div>
                <div class="text-xs text-gray-500">{{ formatDateTime(payment.created_at) }}</div>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-green-600">
                {{ formatRupiah(payment.amount) }}
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ payment.payment_type }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">
                {{ payment.notes || '-' }}
              </td>
              <td class="px-4 py-3 text-center text-sm text-gray-600">
                {{ payment.creator?.name }}
              </td>
            </tr>
          </DataTable>
        </div>
        <div v-else class="p-8 text-center text-gray-500">
          <p class="text-lg">📄 Belum ada pembayaran</p>
          <p class="text-sm mt-2">Tambahkan pembayaran untuk mencatat transaksi</p>
        </div>
      </Card>

      <!-- Payment Modal -->
      <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Tambah Pembayaran</h3>
          </div>
          
          <form @submit.prevent="submitPayment" class="px-6 py-4">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran *</label>
                <input
                  :value="formatPriceInput(paymentForm.amount)"
                  @input="e => paymentForm.amount = parsePrice(e.target.value)"
                  type="text"
                  inputmode="numeric"
                  class="w-full rounded-lg border-gray-300"
                  placeholder="0"
                  required
                />
                <p class="text-xs text-gray-500 mt-1">
                  Sisa hutang: {{ formatRupiah(remainingAmount) }}
                </p>
                <p v-if="paymentForm.errors.amount" class="text-xs text-red-600 mt-1">
                  {{ paymentForm.errors.amount }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran *</label>
                <select
                  v-model="paymentForm.payment_type"
                  class="w-full rounded-lg border-gray-300"
                  required
                >
                  <option value="Cash">Cash</option>
                  <option value="QR">QR Code</option>
                  <option value="Transfer">Transfer</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembayaran *</label>
                <input
                  v-model="paymentForm.payment_date"
                  type="date"
                  class="w-full rounded-lg border-gray-300"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea
                  v-model="paymentForm.notes"
                  class="w-full rounded-lg border-gray-300"
                  rows="2"
                  placeholder="Catatan pembayaran..."
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <Button type="button" @click="closePaymentModal" variant="secondary">
                Batal
              </Button>
              <Button type="submit" variant="primary" :disabled="paymentForm.processing">
                {{ paymentForm.processing ? 'Menyimpan...' : 'Simpan Pembayaran' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  purchase: Object,
})

const showPaymentModal = ref(false)

const paymentForm = useForm({
  amount: 0,
  payment_type: 'Cash',
  payment_date: new Date().toISOString().split('T')[0],
  notes: '',
})

const remainingAmount = computed(() => {
  return props.purchase.total_amount - (props.purchase.paid_amount || 0)
})

const formatPriceInput = (value) => {
  if (!value || value === 0) return ''
  const num = typeof value === 'string' ? parseFloat(value.replace(/\./g, '')) : value
  if (isNaN(num)) return ''
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

const parsePrice = (value) => {
  if (!value) return 0
  const cleaned = value.replace(/\./g, '')
  const num = parseFloat(cleaned)
  return isNaN(num) ? 0 : num
}

const submitPayment = () => {
  paymentForm.post(route('purchases.payments.store', props.purchase.id), {
    onSuccess: () => {
      closePaymentModal()
    },
  })
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  paymentForm.reset()
  paymentForm.clearErrors()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (value) => {
  const num = parseFloat(value)
  return num % 1 === 0 ? num.toString() : num.toFixed(2)
}

const formatRupiah = (value) => {
  return 'Rp ' + formatPrice(value || 0)
}
</script>

