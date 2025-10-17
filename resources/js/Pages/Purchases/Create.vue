<template>
  <AppLayout title="Tambah Pembelian">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader 
        title="Tambah Pembelian" 
        subtitle="Catat pembelian stok baru"
      >
        <template #actions>
          <Button :href="route('purchases.index')" variant="secondary">
            Kembali
          </Button>
        </template>
      </PageHeader>

      <form @submit.prevent="submitForm">
        <!-- Supplier Info -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Supplier</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="supplier_name" value="Nama Supplier *" />
              <TextInput
                id="supplier_name"
                v-model="form.supplier_name"
                type="text"
                class="w-full"
                required
              />
              <InputError :message="form.errors.supplier_name" />
            </div>
            <div>
              <InputLabel for="supplier_phone" value="No. Telepon" />
              <TextInput
                id="supplier_phone"
                v-model="form.supplier_phone"
                type="text"
                class="w-full"
              />
            </div>
            <div class="md:col-span-2">
              <InputLabel for="supplier_address" value="Alamat" />
              <textarea
                id="supplier_address"
                v-model="form.supplier_address"
                class="w-full rounded-lg border-gray-300"
                rows="2"
              ></textarea>
            </div>
            <div>
              <InputLabel for="purchase_date" value="Tanggal Pembelian *" />
              <TextInput
                id="purchase_date"
                v-model="form.purchase_date"
                type="date"
                class="w-full"
                required
              />
              <InputError :message="form.errors.purchase_date" />
            </div>
          </div>
        </Card>

        <!-- Items -->
        <Card class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Item Pembelian</h3>
            <Button type="button" @click="addItem" variant="primary" size="sm">
              + Tambah Item
            </Button>
          </div>

          <div class="space-y-4">
            <div
              v-for="(item, index) in form.items"
              :key="index"
              class="border border-gray-200 rounded-lg p-4 relative"
            >
              <button
                v-if="form.items.length > 1"
                type="button"
                @click="removeItem(index)"
                class="absolute top-2 right-2 text-red-600 hover:text-red-800"
              >
                ✕
              </button>

              <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div class="md:col-span-2 relative">
                  <InputLabel :for="`product_${index}`" value="Produk *" />
                  <div class="relative">
                    <input
                      :id="`product_${index}`"
                      v-model="item.productSearch"
                      @input="onProductSearch(index)"
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
                        @click="selectProduct(index, product)"
                        class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                      >
                        <div class="font-medium">{{ product.name }}</div>
                        <div class="text-xs text-gray-500">Stock: {{ product.stock }} {{ product.unit?.name }}</div>
                      </div>
                    </div>
                  </div>
                  <InputError :message="form.errors[`items.${index}.product_id`]" />
                </div>

                <div>
                  <InputLabel :for="`unit_name_${index}`" value="Unit *" />
                  <select
                    :id="`unit_name_${index}`"
                    v-model="item.unit_name"
                    @change="onUnitChange(index)"
                    class="w-full rounded-lg border-gray-300"
                    :disabled="!item.product_id"
                    required
                  >
                    <option value="">Pilih Unit</option>
                    <option v-for="unit in getCompatibleUnits(item.product_id)" :key="unit.id" :value="unit.name">
                      {{ unit.name }}
                    </option>
                  </select>
                  <InputError :message="form.errors[`items.${index}.unit_name`]" />
                </div>

                <div v-if="!isBaseUnit(item)">
                  <InputLabel :for="`unit_conversion_${index}`" value="Jumlah/Unit *" />
                  <TextInput
                    :id="`unit_conversion_${index}`"
                    v-model="item.unit_conversion"
                    type="number"
                    step="0.0001"
                    min="0.0001"
                    class="w-full"
                    :disabled="!item.product_id"
                    @input="onUnitChange(index)"
                    placeholder="12"
                    required
                  />
                  <InputError :message="form.errors[`items.${index}.unit_conversion`]" />
                  <p v-if="item.baseUnitName" class="text-xs text-gray-500 mt-1">
                    per {{ item.baseUnitName }}
                  </p>
                </div>

                <div>
                  <InputLabel :for="`quantity_${index}`" value="Qty *" />
                  <TextInput
                    :id="`quantity_${index}`"
                    v-model="item.quantity"
                    type="number"
                    step="0.01"
                    min="0.01"
                    class="w-full"
                    @input="calculateItemTotal(index)"
                    required
                  />
                  <InputError :message="form.errors[`items.${index}.quantity`]" />
                  <p v-if="item.baseQuantity && item.unit_conversion > 1" class="text-xs text-blue-600 mt-1">
                    = {{ item.baseQuantity }} {{ item.baseUnitName }}
                  </p>
                </div>

                <div>
                  <InputLabel :for="`unit_cost_${index}`" value="Harga/Unit *" />
                  <TextInput
                    :id="`unit_cost_${index}`"
                    v-model="item.unit_cost"
                    type="number"
                    step="1"
                    min="0"
                    class="w-full"
                    @input="calculateItemTotal(index)"
                    required
                  />
                  <InputError :message="form.errors[`items.${index}.unit_cost`]" />
                </div>
              </div>

              <!-- Conversion Preview -->
              <div v-if="item.unit_name && item.baseUnitName && item.unit_conversion > 1" class="mt-2 p-2 bg-blue-50 rounded text-sm text-blue-700">
                💡 1 {{ item.unit_name }} = {{ item.unit_conversion }} {{ item.baseUnitName }}
              </div>

              <div class="mt-3 flex justify-between items-center border-t pt-3">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-lg font-semibold text-gray-800">
                  {{ formatRupiah(item.subtotal || 0) }}
                </span>
              </div>
            </div>
          </div>

          <InputError :message="form.errors.items" class="mt-2" />
        </Card>

        <!-- Notes & Total -->
        <Card class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <InputLabel for="notes" value="Catatan" />
              <textarea
                id="notes"
                v-model="form.notes"
                class="w-full rounded-lg border-gray-300"
                rows="3"
              ></textarea>
            </div>
            <div class="flex flex-col justify-end">
              <div class="bg-indigo-50 p-4 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-gray-700">Total Items:</span>
                  <span class="font-medium">{{ form.items.length }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                  <span class="text-lg font-semibold text-gray-800">Total:</span>
                  <span class="text-2xl font-bold text-indigo-600">
                    {{ formatRupiah(totalAmount) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
          <Button type="button" :href="route('purchases.index')" variant="secondary">
            Batal
          </Button>
          <Button type="submit" variant="primary" :disabled="form.processing">
            {{ form.processing ? 'Menyimpan...' : 'Simpan Pembelian' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  products: Array,
  units: Array,
  purchaseNumber: String,
})

const form = useForm({
  supplier_name: '',
  supplier_phone: '',
  supplier_address: '',
  purchase_date: new Date().toISOString().split('T')[0],
  items: [createEmptyItem()],
  notes: '',
})

function createEmptyItem() {
  return {
    product_id: '',
    productSearch: '',     // NEW: Search input value
    showProductDropdown: false, // NEW: Toggle dropdown visibility
    unit_name: '',         // NEW: Custom unit name
    unit_conversion: 1,    // NEW: Custom conversion factor
    quantity: 0,
    unit_cost: 0,
    subtotal: 0,
    baseQuantity: 0,
    baseUnitName: '',
  }
}

const totalAmount = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0)
})

const addItem = () => {
  form.items.push(createEmptyItem())
}

const removeItem = (index) => {
  form.items.splice(index, 1)
}

const getCompatibleUnits = (productId) => {
  if (!productId) return []
  
  const product = props.products.find(p => p.id === productId)
  if (!product) return []
  
  // If product doesn't have base_unit configured, show all units
  if (!product.base_unit || !product.base_unit.type) {
    return props.units
  }
  
  const baseUnitType = product.base_unit.type
  return props.units.filter(unit => unit.type === baseUnitType)
}

const getProductBaseUnit = (productId) => {
  const product = props.products.find(p => p.id === productId)
  return product?.base_unit?.name || ''
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
  onProductChange(index)
}

const onProductSearch = (index) => {
  const item = form.items[index]
  // If search is cleared, clear the product selection
  if (!item.productSearch || item.productSearch.trim() === '') {
    item.product_id = ''
    item.unit_name = ''
    item.baseUnitName = ''
  }
}

const isBaseUnit = (item) => {
  if (!item.product_id || !item.unit_name) return false
  
  const product = props.products.find(p => p.id === item.product_id)
  if (!product || !product.base_unit) return false
  
  return item.unit_name === product.base_unit.name
}

const onProductChange = (index) => {
  const item = form.items[index]
  const product = props.products.find(p => p.id === item.product_id)
  
  if (product && product.base_unit) {
    // Set product name in search field
    item.productSearch = product.name
    
    // Set base unit name for preview
    item.baseUnitName = product.base_unit.name
    
    // Get compatible units and set default
    const compatibleUnits = getCompatibleUnits(item.product_id)
    if (compatibleUnits.length > 0) {
      item.unit_name = compatibleUnits[0].name
      item.unit_conversion = 1  // Default to 1:1 conversion
    }
    
    calculateItemTotal(index)
  }
}

const onUnitChange = (index) => {
  const item = form.items[index]
  
  // If base unit is selected, set conversion to 1
  if (isBaseUnit(item)) {
    item.unit_conversion = 1
  }
  
  // Recalculate when unit name or conversion changes
  calculateItemTotal(index)
}

const calculateItemTotal = (index) => {
  const item = form.items[index]
  const quantity = parseFloat(item.quantity) || 0
  const unitCost = parseFloat(item.unit_cost) || 0
  const conversion = parseFloat(item.unit_conversion) || 1
  
  item.subtotal = quantity * unitCost
  item.baseQuantity = quantity * conversion
}

const submitForm = () => {
  form.post(route('purchases.store'))
}

const formatRupiah = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}
</script>

