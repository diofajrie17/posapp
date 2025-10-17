<template>
  <AppLayout title="Edit Produk">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Judul -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Produk ✏️</h1>
        <p class="text-gray-600">Perbarui informasi produk sesuai kebutuhan</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Nama Produk -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Produk</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Masukkan nama produk"
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <!-- Kategori -->
        <div>
          <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
          <select
            id="category_id"
            v-model="form.category_id"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
          >
            <option value="">Pilih Kategori (Opsional)</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.category_id }}
          </div>
        </div>

        <!-- Stok -->
        <div>
          <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
          <input
            id="stock"
            v-model="form.stock"
            type="number"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="0"
          />
          <div v-if="form.errors.stock" class="text-red-500 text-sm mt-1">
            {{ form.errors.stock }}
          </div>
        </div>

        <!-- Harga Modal -->
        <div>
          <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Beli</label>
          <input
            id="cost_price"
            v-model="form.cost_price"
            type="number"
            step="0.01"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="0"
          />
          <div v-if="form.errors.cost_price" class="text-red-500 text-sm mt-1">
            {{ form.errors.cost_price }}
          </div>
          <!-- Base Unit Cost Price Calculator -->
          <div v-if="showBaseUnitCostPrice" class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center justify-between">
              <span class="text-sm text-amber-800 font-medium">
                Harga Beli per {{ selectedBaseUnit?.name }}:
              </span>
              <span class="text-lg font-bold text-amber-900">
                {{ formatRupiah(baseUnitCostPrice) }}
              </span>
            </div>
            <div class="text-xs text-amber-700 mt-1">
              {{ formatRupiah(form.cost_price) }} ÷ {{ form.unit_quantity }} {{ selectedBaseUnit?.name }} = {{ formatRupiah(baseUnitCostPrice) }}/{{ selectedBaseUnit?.name }}
            </div>
          </div>
        </div>

        <!-- Harga -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual</label>
          <input
            id="price"
            v-model="form.price"
            type="number"
            step="0.01"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="0"
          />
          <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">
            {{ form.errors.price }}
          </div>
          <!-- Base Unit Selling Price Calculator -->
          <div v-if="showBaseUnitPrice" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center justify-between">
              <span class="text-sm text-green-800 font-medium">
                Harga Jual per {{ selectedBaseUnit?.name }}:
              </span>
              <span class="text-lg font-bold text-green-900">
                {{ formatRupiah(baseUnitPrice) }}
              </span>
            </div>
            <div class="text-xs text-green-700 mt-1">
              {{ formatRupiah(form.price) }} ÷ {{ form.unit_quantity }} {{ selectedBaseUnit?.name }} = {{ formatRupiah(baseUnitPrice) }}/{{ selectedBaseUnit?.name }}
            </div>
          </div>
          <!-- Profit Margin Info -->
          <div v-if="showProfitMargin" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm text-blue-800 font-medium">
                Profit per {{ selectedBaseUnit?.name }}:
              </span>
              <span class="text-lg font-bold text-blue-900">
                {{ formatRupiah(profitPerBaseUnit) }}
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-xs text-blue-700">Margin:</span>
              <span class="text-sm font-semibold text-blue-900">
                {{ profitMarginPercentage }}%
              </span>
            </div>
          </div>
            <!-- Separate Pricing Fields -->
            <div v-if="form.unit_id && form.base_unit_id && form.unit_quantity > 1" class="mt-4">
              <label for="base_unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual per {{ selectedBaseUnit?.name }} (khusus)</label>
              <input
                id="base_unit_price"
                v-model="form.base_unit_price"
                type="number"
                step="0.01"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                placeholder="Contoh: 3200"
              />
              <div v-if="form.errors.base_unit_price" class="text-red-500 text-sm mt-1">
                {{ form.errors.base_unit_price }}
              </div>
              <div class="text-xs text-gray-500 mt-1">Isi jika ingin harga khusus per unit dasar (misal: harga eceran lebih mahal dari harga grosir)</div>
            </div>
            <div v-if="form.unit_id && form.base_unit_id && form.unit_quantity > 1" class="mt-2">
              <label for="derived_unit_price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual per {{ selectedUnit?.name }} (khusus)</label>
              <input
                id="derived_unit_price"
                v-model="form.derived_unit_price"
                type="number"
                step="0.01"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                placeholder="Contoh: 72000"
              />
              <div v-if="form.errors.derived_unit_price" class="text-red-500 text-sm mt-1">
                {{ form.errors.derived_unit_price }}
              </div>
              <div class="text-xs text-gray-500 mt-1">Isi jika ingin harga khusus per unit turunan (misal: harga grosir lebih murah dari harga eceran)</div>
            </div>
        </div>

        <!-- Unit -->
        <div>
          <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
          <select
            id="unit_id"
            v-model="form.unit_id"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            @change="onUnitChange"
          >
            <option value="">Pilih Unit</option>
            <option 
              v-for="unit in units" 
              :key="unit.id" 
              :value="unit.id"
            >
              {{ unit.name }} {{ unit.symbol ? '(' + unit.symbol + ')' : '' }}
            </option>
          </select>
          <div v-if="form.errors.unit_id" class="text-red-500 text-sm mt-1">
            {{ form.errors.unit_id }}
          </div>
        </div>

        <!-- Unit Quantity Configuration (only for non-base units) -->
                <!-- Unit Quantity Configuration (for non-base units) -->
        <div v-if="form.unit_id && selectedUnit && !selectedUnit.is_base_unit" class="bg-blue-50 p-4 rounded-lg border border-blue-200">
          <h3 class="font-medium text-blue-800 mb-3">Konfigurasi Unit</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Base Unit -->
            <div>
              <label for="base_unit_id" class="block text-sm font-medium text-gray-700 mb-2">Unit Dasar</label>
              <select
                id="base_unit_id"
                v-model="form.base_unit_id"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              >
                <option value="">Pilih unit dasar</option>
                <option 
                  v-for="unit in baseUnits" 
                  :key="unit.id" 
                  :value="unit.id"
                >
                  {{ unit.name }} {{ unit.symbol ? '(' + unit.symbol + ')' : '' }}
                </option>
              </select>
              <div v-if="form.errors.base_unit_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.base_unit_id }}
              </div>
              <div class="text-xs text-gray-500 mt-1">
                Unit dasar yang akan digunakan untuk perhitungan stok
              </div>
            </div>

            <!-- Unit Quantity -->
            <div>
              <label for="unit_quantity" class="block text-sm font-medium text-gray-700 mb-2">Jumlah per Unit</label>
              <input
                id="unit_quantity"
                v-model.number="form.unit_quantity"
                type="number"
                step="0.0001"
                min="0.0001"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                placeholder="Contoh: 12 (untuk 1 box = 12 pcs)"
              />
              <div v-if="form.errors.unit_quantity" class="text-red-500 text-sm mt-1">
                {{ form.errors.unit_quantity }}
              </div>
              <div class="text-xs text-gray-500 mt-1">
                Berapa banyak unit dasar dalam 1 {{ selectedUnit.name }}
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div v-if="form.unit_id && form.base_unit_id && form.unit_quantity && selectedUnit && selectedBaseUnit" class="mt-3 bg-green-50 p-3 rounded border border-green-200">
            <div class="text-sm text-green-800">
              <strong>Preview:</strong> 1 {{ selectedUnit.name }} = {{ form.unit_quantity }} {{ selectedBaseUnit.name }}
            </div>
          </div>
        </div>

        <div class="text-sm text-gray-600">
          <Link href="/units" class="text-blue-600 hover:underline">
            Kelola unit di sini
          </Link>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <div class="flex-1">
            <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-2">Stok Minimum</label>
            <input
              id="min_stock"
              v-model="form.min_stock"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Contoh: 10"
            />
            <div v-if="form.errors.min_stock" class="text-red-500 text-sm mt-1">
              {{ form.errors.min_stock }}
            </div>
          </div>
          <div class="flex-1">
            <label for="max_stock" class="block text-sm font-medium text-gray-700 mb-2">Stok Maksimum</label>
            <input
              id="max_stock"
              v-model="form.max_stock"
              type="number"
              step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
              placeholder="Contoh: 100"
            />
            <div v-if="form.errors.max_stock" class="text-red-500 text-sm mt-1">
              {{ form.errors.max_stock }}
            </div>
          </div>
          <div class="flex-1 flex items-center mt-6">
            <label for="is_active" class="block text-sm font-medium text-gray-700 mr-2">Aktif?</label>
            <input type="checkbox" id="is_active" v-model="form.is_active" class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
          </div>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Update Produk</span>
          </button>
          <Link
            href="/products"
            class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition duration-200 font-medium text-center"
          >
            Batal
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  product: Object,
  categories: Array,
  units: Array,
  baseUnits: Array
})

const selectedUnit = computed(() => {
  if (!form.unit_id) return null
  return props.units.find(unit => unit.id == form.unit_id)
})

const selectedBaseUnit = computed(() => {
  if (!form.base_unit_id) return null
  return props.baseUnits.find(unit => unit.id == form.base_unit_id)
})

const isBaseUnit = computed(() => {
  return selectedUnit.value?.is_base_unit || false
})

// Computed property to show base unit cost price
const showBaseUnitCostPrice = computed(() => {
  return form.unit_id && 
         form.base_unit_id && 
         form.unit_quantity > 1 && 
         form.cost_price > 0 &&
         !isBaseUnit.value
})

// Computed property to calculate base unit cost price
const baseUnitCostPrice = computed(() => {
  if (!showBaseUnitCostPrice.value) return 0
  return form.cost_price / form.unit_quantity
})

// Computed property to show base unit selling price
const showBaseUnitPrice = computed(() => {
  return form.unit_id && 
         form.base_unit_id && 
         form.unit_quantity > 1 && 
         form.price > 0 &&
         !isBaseUnit.value
})

// Computed property to calculate base unit selling price
const baseUnitPrice = computed(() => {
  if (!showBaseUnitPrice.value) return 0
  return form.price / form.unit_quantity
})

// Computed property to show profit margin
const showProfitMargin = computed(() => {
  return showBaseUnitPrice.value && 
         showBaseUnitCostPrice.value && 
         form.cost_price > 0
})

// Computed property to calculate profit per base unit
const profitPerBaseUnit = computed(() => {
  if (!showProfitMargin.value) return 0
  return baseUnitPrice.value - baseUnitCostPrice.value
})

// Computed property to calculate profit margin percentage
const profitMarginPercentage = computed(() => {
  if (!showProfitMargin.value || baseUnitCostPrice.value === 0) return 0
  return ((profitPerBaseUnit.value / baseUnitCostPrice.value) * 100).toFixed(2)
})

// Format currency
function formatRupiah(value) {
  if (!value && value !== 0) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

const form = useForm({
  name: props.product.name,
  stock: props.product.stock,
  price: props.product.price,
  cost_price: props.product.cost_price || 0,
  unit_id: props.product.unit_id || '',
  unit_quantity: props.product.unit_quantity || 1,
  base_unit_id: props.product.base_unit_id || '',
  category_id: props.product.category_id || '',
  base_unit_price: props.product.base_unit_price || '',
  derived_unit_price: props.product.derived_unit_price || '',
  min_stock: props.product.min_stock || '',
  max_stock: props.product.max_stock || '',
  is_active: typeof props.product.is_active === 'boolean' ? props.product.is_active : true
})

function onUnitChange() {
  // Reset unit configuration when unit changes
  if (isBaseUnit.value) {
    form.base_unit_id = ''
    form.unit_quantity = 1
  }
}

function submit() {
  form.put(`/products/${props.product.id}`)
}
</script>
