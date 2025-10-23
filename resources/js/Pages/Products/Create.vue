<template>
  <AppLayout title="Tambah Produk">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg p-8">
      <!-- Judul -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Tambah Produk 🛒</h1>
        <p class="text-gray-600">Lengkapi form berikut untuk menambahkan produk baru</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
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

        <!-- Kode Produk -->
        <div>
          <label for="product_code" class="block text-sm font-medium text-gray-700 mb-2">Kode Produk *</label>
          <input
            id="product_code"
            v-model="form.product_code"
            type="text"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="Masukkan kode produk"
          />
          <div v-if="form.errors.product_code" class="text-red-500 text-sm mt-1">
            {{ form.errors.product_code }}
          </div>
        </div>

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

        <!-- Stok -->
        <div>
          <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">Stok</label>
          <input
            id="stock"
            v-model="stockFormatter.displayValue.value"
            @input="stockFormatter.handleInput"
            type="text"
            inputmode="numeric"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="0"
          />
          <div v-if="form.errors.stock" class="text-red-500 text-sm mt-1">
            {{ form.errors.stock }}
          </div>
        </div>

        <!-- Harga -->
        <div>
          <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Harga Jual</label>
          <input
            id="price"
            v-model="priceFormatter.displayValue.value"
            @input="priceFormatter.handleInput"
            type="text"
            inputmode="numeric"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            placeholder="0"
          />
          <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">
            {{ form.errors.price }}
          </div>
        </div>

        <!-- Unit -->
        <div>
          <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-2">
            Unit (Base Unit)
          </label>
          <select
            id="unit_id"
            v-model="form.unit_id"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            @change="onUnitChange"
          >
            <option value="">Pilih Base Unit</option>
            <option 
              v-for="unit in units" 
              :key="unit.id" 
              :value="unit.id"
            >
              {{ unit.name }} {{ unit.symbol ? '(' + unit.symbol + ')' : '' }}
            </option>
          </select>
          <p class="text-xs text-gray-500 mt-1">
            Produk harus menggunakan base unit. Derived unit hanya untuk pembelian.
          </p>
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
          <button
            type="submit"
            :disabled="form.processing"
            class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-medium"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan Produk</span>
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
import { computed, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePriceFormatter, useNumberFormatter } from '@/composables/usePriceFormatter'

const props = defineProps({
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

const form = useForm({
  name: '',
  product_code: '',
  stock: 0,
  price: 0,
  unit_id: '',
  unit_quantity: 1,
  base_unit_id: '',
  category_id: ''
})

// Price formatter
const priceFormatter = usePriceFormatter(form.price)

// Stock formatter (integer only)
const stockFormatter = useNumberFormatter(form.stock, false)

// Sync price value
watch(() => priceFormatter.numericValue.value, (newValue) => {
  form.price = newValue
})

// Sync stock value
watch(() => stockFormatter.numericValue.value, (newValue) => {
  form.stock = newValue
})

function onUnitChange() {
  // Reset unit configuration when unit changes
  if (isBaseUnit.value) {
    // For base units, set base_unit_id to the same as unit_id
    form.base_unit_id = form.unit_id
    form.unit_quantity = 1
  } else {
    // For derived units, clear base_unit_id so user can select
    form.base_unit_id = ''
  }
}

function submit() {
  form.post('/products')
}
</script>
