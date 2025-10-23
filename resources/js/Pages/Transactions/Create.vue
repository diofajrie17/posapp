<template>
  <AppLayout title="Transaksi Baru">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader 
        title="Transaksi Kasir" 
        subtitle="Catat transaksi penjualan baru"
      >
        <template #actions>
          <Button href="/dashboard" variant="secondary">
            Kembali
          </Button>
        </template>
      </PageHeader>

      <form @submit.prevent="submit">
        <!-- Customer Info -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="member_id" value="Pelanggan" />
              <select 
                id="member_id"
                v-model="form.member_id" 
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="null">Pengunjung Harian</option>
                <option v-for="m in members" :key="m.id" :value="m.id">
                  {{ m.full_name }}
                </option>
              </select>
              <InputError :message="form.errors.member_id" />
            </div>
            <div>
              <InputLabel for="payment_type" value="Metode Pembayaran *" />
              <select
                id="payment_type"
                v-model="form.payment_type"
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option>Cash</option>
                <option>QR</option>
                <option>Transfer</option>
              </select>
            </div>
          </div>
        </Card>

        <!-- Items -->
        <Card class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Item Produk</h3>
            <Button type="button" @click="addItem" variant="primary" size="sm">
              + Tambah Item
            </Button>
          </div>

          <div class="space-y-4">
            <div
              v-for="(item, idx) in form.items"
              :key="idx"
              class="border border-gray-200 rounded-lg p-4 relative"
            >
              <button
                v-if="form.items.length > 1"
                type="button"
                @click="removeItem(idx)"
                class="absolute top-2 right-2 text-red-600 hover:text-red-800"
              >
                ✕
              </button>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                  <InputLabel :for="`product_${idx}`" value="Produk *" />
                  <div class="relative">
                    <input
                      :id="`product_${idx}`"
                      v-model="item.productSearch"
                      @input="onProductSearch(idx)"
                      @focus="item.showProductDropdown = true"
                      @blur="() => setTimeout(() => item.showProductDropdown = false, 200)"
                      type="text"
                      class="w-full rounded-lg border-gray-300"
                      placeholder="Cari produk..."
                      autocomplete="off"
                    />
                    <div 
                      v-if="item.showProductDropdown && getFilteredProducts(item.productSearch).length > 0"
                      class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                    >
                      <div
                        v-for="product in getFilteredProducts(item.productSearch)"
                        :key="product.id"
                        @click="selectProduct(idx, product)"
                        class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                      >
                        <div class="font-medium">{{ product.name }}</div>
                        <div class="text-xs text-gray-500">
                          Stock: {{ product.stock }} | Harga: {{ formatRupiah(product.price) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div>
                  <InputLabel :for="`quantity_${idx}`" value="Jumlah *" />
                  <input
                    :id="`quantity_${idx}`"
                    :value="formatNumberInput(item.quantity)"
                    @input="e => updateQuantity(idx, e.target.value)"
                    type="text"
                    inputmode="numeric"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="0"
                  />
                </div>
                <div>
                  <InputLabel :for="`price_${idx}`" value="Harga Satuan *" />
                  <input
                    :id="`price_${idx}`"
                    :value="formatPriceInput(item.price_each)"
                    @input="e => updatePrice(idx, e.target.value)"
                    type="text"
                    inputmode="numeric"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="0"
                  />
                </div>
                <div>
                  <InputLabel value="Subtotal" />
                  <div class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-right font-semibold text-gray-700">
                    {{ formatRupiah((item.quantity || 0) * (item.price_each || 0)) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Discount & Payment -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Diskon & Pembayaran</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel for="discount_type" value="Tipe Diskon" />
              <select
                id="discount_type"
                v-model="form.discount_type"
                class="w-full rounded-lg border-gray-300"
              >
                <option :value="null">Tidak ada</option>
                <option value="fixed">Potongan (Rp)</option>
                <option value="percent">Persen (%)</option>
              </select>
            </div>
            <div>
              <InputLabel for="discount_value" value="Nilai Diskon" />
              <input
                id="discount_value"
                :value="formatPriceInput(form.discount_value)"
                @input="e => updateDiscountValue(e.target.value)"
                type="text"
                inputmode="numeric"
                class="w-full rounded-lg border-gray-300"
                placeholder="0"
              />
            </div>
            <div>
              <InputLabel value="Potongan" />
              <div class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded-lg border h-[42px]">
                <strong class="text-gray-800">{{ formatRupiah(discountAmount) }}</strong>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
              <InputLabel for="paid_amount" value="Dibayar (Cash)" />
              <input
                id="paid_amount"
                :value="formatPriceInput(form.paid_amount)"
                @input="e => updatePaidAmount(e.target.value)"
                type="text"
                inputmode="numeric"
                class="w-full rounded-lg border-gray-300"
                placeholder="0"
              />
            </div>
            <div>
              <InputLabel value="Kembalian" />
              <div class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded-lg border h-[42px]">
                <strong class="text-gray-800">{{ formatRupiah(changeAmount) }}</strong>
              </div>
            </div>
            <div>
              <InputLabel for="notes" value="Catatan" />
              <TextInput
                id="notes"
                v-model="form.notes"
                type="text"
                class="w-full"
                placeholder="Opsional"
              />
            </div>
          </div>
        </Card>

        <!-- Summary & Submit -->
        <Card class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
              <span class="text-blue-700 font-medium">Subtotal:</span>
              <strong class="text-blue-800 text-lg">{{ formatRupiah(subtotal) }}</strong>
            </div>
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
              <span class="text-green-700 font-medium">Total Bayar:</span>
              <strong class="text-green-800 text-xl">{{ formatRupiah(total) }}</strong>
            </div>
          </div>
        </Card>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
          <Button type="button" href="/dashboard" variant="secondary">
            Batal
          </Button>
          <Button type="submit" variant="primary" :disabled="form.processing">
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>💾 Simpan & Cetak</span>
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({ products: Array, members: Array })

const form = useForm({
  member_id: null,
  payment_type: 'Cash',
  items: [{ product_id: '', productSearch: '', showProductDropdown: false, quantity: 1, price_each: 0 }],
  discount_type: null,
  discount_value: 0,
  paid_amount: null,
  notes: ''
})

function addItem() {
  form.items.push({ product_id: '', productSearch: '', showProductDropdown: false, quantity: 1, price_each: 0 })
}

function removeItem(index) {
  form.items.splice(index, 1)
}

const getFilteredProducts = (searchTerm) => {
  if (!searchTerm || searchTerm.trim() === '') {
    return props.products
  }
  
  const term = searchTerm.toLowerCase()
  return props.products.filter(product => 
    product.name.toLowerCase().includes(term)
  )
}

const selectProduct = (index, product) => {
  const item = form.items[index]
  item.product_id = product.id
  item.productSearch = product.name
  item.showProductDropdown = false
  onProductSelect(index)
}

const onProductSearch = (index) => {
  const item = form.items[index]
  // If search is cleared, clear the product selection
  if (!item.productSearch || item.productSearch.trim() === '') {
    item.product_id = ''
    item.price_each = 0
  }
}

function onProductSelect(index) {
  const item = form.items[index]
  if (item.product_id) {
    const product = props.products.find(p => p.id === item.product_id)
    if (product) {
      // Set product name in search field
      item.productSearch = product.name
      // Set default price from product
      item.price_each = product.price
    }
  }
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

function formatPriceInput(value) {
  if (!value || value === 0) return ''
  const num = typeof value === 'string' ? parseFloat(value.replace(/\./g, '')) : value
  if (isNaN(num)) return ''
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

function parsePrice(value) {
  if (!value) return 0
  const cleaned = value.replace(/\./g, '')
  const num = parseFloat(cleaned)
  return isNaN(num) ? 0 : num
}

function updatePrice(index, value) {
  form.items[index].price_each = parsePrice(value)
}

function updateDiscountValue(value) {
  form.discount_value = parsePrice(value)
}

function updatePaidAmount(value) {
  form.paid_amount = parsePrice(value)
}

function formatNumberInput(value) {
  if (!value || value === 0) return ''
  const num = typeof value === 'string' ? parseFloat(value.replace(/\./g, '')) : value
  if (isNaN(num)) return ''
  return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

function parseNumber(value) {
  if (!value) return 0
  const cleaned = value.replace(/\./g, '')
  const num = parseFloat(cleaned)
  return isNaN(num) ? 0 : num
}

function updateQuantity(index, value) {
  form.items[index].quantity = parseNumber(value)
}

function submit() {
  form.post('/transactions')
}
</script>
