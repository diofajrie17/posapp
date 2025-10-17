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
          <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Produk</label>
              <select 
                v-model="item.product_id" 
                @change="onProductChange(idx)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              >
                <option disabled value="">Pilih Produk</option>
                <option v-for="p in products" :key="p.id" :value="p.id">
                  {{ p.name }}
                  <span v-if="typeof p.is_active === 'boolean' && !p.is_active" class="text-gray-400"> [Nonaktif]</span>
                  <span v-if="p.base_unit && p.unit_quantity > 1"> ({{ p.unit.name }} = {{ formatNumber(p.unit_quantity) }} {{ p.base_unit.name }})</span>
                  <span v-else> ({{ p.unit?.name || '-' }})</span>
                  — Stock: {{ formatNumber(p.stock) }}
                  <span v-if="p.min_stock">, Min: {{ p.min_stock }}</span>
                  <span v-if="p.max_stock">, Max: {{ p.max_stock }}</span>
                </option>
              </select>
              <div v-if="item.product_id" class="text-xs mt-1" :class="getStockAlertClass(item.product_id)">
                {{ getStockInfo(item.product_id) }}
              </div>
            </div>
            
            <!-- Unit Selection: Show if product has derived unit -->
            <div v-if="canSelectUnit(item.product_id)">
              <label class="block text-sm font-medium mb-1 text-gray-700">Jual Dalam</label>
              <select 
                v-model="item.sell_in_base_unit"
                @change="onUnitTypeChange(idx)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              >
                <option :value="false">{{ getDerivedUnitName(item.product_id) }}</option>
                <option :value="true">{{ getBaseUnitName(item.product_id) }}</option>
              </select>
              <div class="text-xs text-gray-500 mt-1">
                {{ getPriceInfo(item) }}
                <span v-if="products.find(p => p.id === item.product_id)?.min_stock"> | Min: {{ products.find(p => p.id === item.product_id)?.min_stock }}</span>
                <span v-if="products.find(p => p.id === item.product_id)?.max_stock"> | Max: {{ products.find(p => p.id === item.product_id)?.max_stock }}</span>
                <span v-if="typeof products.find(p => p.id === item.product_id)?.is_active === 'boolean' && !products.find(p => p.id === item.product_id)?.is_active" class="text-red-500 font-semibold"> | Nonaktif</span>
              </div>
            </div>
            <div v-else>
              <label class="block text-sm font-medium mb-1 text-gray-700">Unit</label>
              <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                {{ getProductUnit(item.product_id) }}
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Jumlah</label>
              <div class="flex items-center gap-2">
                <button 
                  @click="decrementQuantity(idx)"
                  type="button" 
                  class="px-2 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition text-gray-700 font-semibold"
                  :disabled="item.quantity <= 0.001"
                >
                  −
                </button>
                <input 
                  v-model.number="item.quantity" 
                  type="number" 
                  min="0.001"
                  step="0.001"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200" 
                  placeholder="Qty" 
                />
                <button 
                  @click="incrementQuantity(idx)"
                  type="button" 
                  class="px-2 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition text-gray-700 font-semibold"
                >
                  +
                </button>
              </div>
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
import { computed, toRef } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ 
  products: Array, 
  members: Array 
})

// Make products reactive so we can access it in functions
const products = toRef(props, 'products')

const form = useForm({
  member_id: null,
  payment_type: 'Cash',
  items: [{ product_id: '', quantity: 1, price_each: 0, sell_in_base_unit: false }],
  discount_type: null,
  discount_value: 0,
  paid_amount: null,
  notes: ''
})

function addItem() {
  form.items.push({ product_id: '', quantity: 1, price_each: 0, sell_in_base_unit: false })
}

function incrementQuantity(itemIndex) {
  form.items[itemIndex].quantity += 1;
}

function decrementQuantity(itemIndex) {
  if (form.items[itemIndex].quantity > 0.001) {
    form.items[itemIndex].quantity -= 1;
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

function onProductChange(itemIndex) {
  const item = form.items[itemIndex];
  const product = products.value.find(p => p.id === item.product_id);
  
  if (product) {
    // Determine default unit type
    const defaultSellInBaseUnit = product.base_unit_id && product.unit_quantity > 1;
    
    // Check if this product with same unit type already exists in cart
    const existingItemIndex = form.items.findIndex((cartItem, idx) => {
      return idx !== itemIndex && 
             cartItem.product_id === item.product_id && 
             cartItem.sell_in_base_unit === defaultSellInBaseUnit;
    });
    
    if (existingItemIndex !== -1) {
      // Product already exists, increase quantity and remove current empty row
      form.items[existingItemIndex].quantity += 1;
      form.items.splice(itemIndex, 1);
      return;
    }
    
    // Default to base unit selling if product has derived unit
    if (defaultSellInBaseUnit) {
      item.sell_in_base_unit = true;
      // Use explicit base_unit_price if available, otherwise calculate
      item.price_each = product.base_unit_price || (product.price / product.unit_quantity);
    } else {
      item.sell_in_base_unit = false;
      item.price_each = product.price;
    }
    item.quantity = 1;
  }
}

function onUnitTypeChange(itemIndex) {
  const item = form.items[itemIndex];
  const product = products.value.find(p => p.id === item.product_id);
  
  if (product) {
    // Check if this product with same unit type already exists in cart
    const existingItemIndex = form.items.findIndex((cartItem, idx) => {
      return idx !== itemIndex && 
             cartItem.product_id === item.product_id && 
             cartItem.sell_in_base_unit === item.sell_in_base_unit;
    });
    
    if (existingItemIndex !== -1) {
      // Product with same unit type already exists, merge quantities and remove current row
      const currentQty = item.quantity || 1;
      form.items[existingItemIndex].quantity += currentQty;
      form.items.splice(itemIndex, 1);
      return;
    }
    
    if (item.sell_in_base_unit) {
      // Selling in base unit - use explicit base_unit_price if available
      item.price_each = product.base_unit_price || (product.price / product.unit_quantity);
    } else {
      // Selling in derived unit - use explicit derived_unit_price if available
      item.price_each = product.derived_unit_price || product.price;
    }
    item.quantity = 1;
  }
}

function getProductUnitDisplay(product) {
  if (!product.unit && !product.unit_id) return '';
  
  // If product has base unit configuration
  if (product.base_unit && product.unit_quantity > 1) {
    return `(${formatNumber(product.unit_quantity)} ${product.base_unit.name})`;
  }
  
  // Show unit name if available
  if (product.unit) {
    return product.unit.name ? `(${product.unit.name})` : '';
  }
  
  return '';
}

function canSelectUnit(productId) {
  if (!productId) return false;
  const product = products.value.find(p => p.id === productId);
  return product && product.base_unit_id && product.unit_quantity > 1;
}

function getDerivedUnitName(productId) {
  if (!productId) return '';
  const product = products.value.find(p => p.id === productId);
  return product && product.unit ? product.unit.name : '';
}

function getBaseUnitName(productId) {
  if (!productId) return '';
  const product = products.value.find(p => p.id === productId);
  return product && product.base_unit ? product.base_unit.name : '';
}

function getProductUnit(productId) {
  if (!productId) return '-';
  const product = products.value.find(p => p.id === productId);
  return product && product.unit ? product.unit.name : '-';
}

function getPriceInfo(item) {
  if (!item.product_id) return '';
  const product = products.value.find(p => p.id === item.product_id);
  if (!product) return '';
  
  if (item.sell_in_base_unit) {
    const basePrice = product.base_unit_price || (product.price / product.unit_quantity);
    return `${formatRupiah(basePrice)} / ${product.base_unit.name}`;
  } else {
    const derivedPrice = product.derived_unit_price || product.price;
    return `${formatRupiah(derivedPrice)} / ${product.unit.name}`;
  }
}

function formatNumber(value) {
  const num = parseFloat(value);
  return num % 1 === 0 ? num.toString() : num.toFixed(4).replace(/\.?0+$/, '');
}

function getStockInfo(productId) {
  if (!productId) return '';
  const product = products.value.find(p => p.id === productId);
  if (!product) return '';
  
  const unitName = product.base_unit ? product.base_unit.name : (product.unit ? product.unit.name : 'unit');
  const stockText = `Stok: ${formatNumber(product.stock)} ${unitName}`;
  
  if (product.stock <= product.min_stock) {
    return `⚠️ ${stockText} (Low Stock)`;
  }
  
  return `✓ ${stockText}`;
}

function getStockAlertClass(productId) {
  if (!productId) return '';
  const product = products.value.find(p => p.id === productId);
  if (!product) return '';
  
  if (product.stock <= 0) {
    return 'text-red-600 font-semibold';
  } else if (product.stock <= product.min_stock) {
    return 'text-orange-600 font-semibold';
  }
  
  return 'text-green-600';
}

function submit() {
  form.post('/transactions')
}
</script>
