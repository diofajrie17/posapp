<template>
  <AppLayout title="Daftar Produk">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <PageHeader title="Daftar Produk" subtitle="Kelola produk dan inventori Anda">
        <template #actions>
          <Button @click="showOpnameModal = true" variant="secondary">
            📊 Stock Opname
          </Button>
          <Button v-if="can.create" href="/products/create" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
          </Button>
        </template>
      </PageHeader>

      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Kode Produk</th>
            <th class="px-4 py-3 text-left font-semibold">Nama Produk</th>
            <th class="px-4 py-3 text-left font-semibold">Kategori</th>
            <th class="px-4 py-3 text-center font-semibold">Stok</th>
            <th class="px-4 py-3 text-right font-semibold">Harga Jual</th>
            <th class="px-4 py-3 text-right font-semibold">Avg Cost</th>
            <th class="px-4 py-3 text-left font-semibold">Unit</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </template>

          <tr v-if="products.length === 0">
            <td colspan="8" class="p-0">
              <EmptyState 
                icon="📦" 
                message="Belum ada produk" 
                subtitle="Tambahkan produk pertama Anda untuk memulai"
              />
            </td>
          </tr>

          <tr
            v-for="p in products"
            :key="p.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td class="px-4 py-3 text-gray-600 font-mono text-sm">
              {{ p.product_code }}
            </td>
            <td class="px-4 py-3 font-medium text-gray-800">
              {{ p.name }}
            </td>
            <td class="px-4 py-3">
              <span v-if="p.category" class="px-2 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-700">
                {{ p.category.name }}
              </span>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span
                class="px-2 py-1 text-xs rounded-full font-medium"
                :class="
                  p.stock > 10
                    ? 'bg-green-100 text-green-700'
                    : p.stock > 0
                    ? 'bg-yellow-100 text-yellow-700'
                    : 'bg-red-100 text-red-700'
                "
              >
                {{ formatNumber(p.stock) }}
              </span>
            </td>
            <td class="px-4 py-3 text-right font-semibold text-indigo-600">
              Rp {{ formatPrice(p.price) }}
            </td>
            <td class="px-4 py-3 text-right text-sm text-gray-600">
              {{ p.average_cost ? 'Rp ' + formatPrice(p.average_cost) : '-' }}
            </td>
            <td class="px-4 py-3 text-gray-600">
              <span v-if="getUnitDisplay(p)" class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                {{ getUnitDisplay(p) }}
              </span>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex gap-2 justify-center">
                <button
                  @click="viewMovements(p)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  title="Riwayat Pergerakan"
                >
                  📋
                </button>
                <Button
                  :href="`/products/${p.id}/edit`"
                  variant="ghost"
                  size="sm"
                >
                  Edit
                </Button>
                <Link
                  :href="`/products/${p.id}`"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:text-red-800 text-sm font-medium"
                  onclick="return confirm('Yakin ingin menghapus produk ini?')"
                >
                  Hapus
                </Link>
              </div>
            </td>
          </tr>
        </DataTable>
      </Card>

      <!-- Stock Opname Modal -->
      <Modal :show="showOpnameModal" @close="showOpnameModal = false">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Opname</h3>
          <form @submit.prevent="submitOpname">
            <div class="mb-4">
              <InputLabel for="opname_product" value="Produk *" />
              <select
                id="opname_product"
                v-model="opnameForm.product_id"
                class="w-full rounded-lg border-gray-300"
                required
              >
                <option value="">Pilih Produk</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }} (Stock: {{ formatNumber(product.stock) }} {{ product.unit?.name }})
                </option>
              </select>
              <InputError :message="opnameForm.errors.product_id" />
            </div>

            <div class="mb-4">
              <InputLabel for="actual_quantity" value="Jumlah Aktual *" />
              <TextInput
                id="actual_quantity"
                v-model="opnameForm.actual_quantity"
                type="number"
                step="0.01"
                min="0"
                class="w-full"
                required
              />
              <InputError :message="opnameForm.errors.actual_quantity" />
            </div>

            <div class="mb-4">
              <InputLabel for="reason" value="Alasan" />
              <textarea
                id="reason"
                v-model="opnameForm.reason"
                class="w-full rounded-lg border-gray-300"
                rows="3"
              ></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <Button type="button" @click="showOpnameModal = false" variant="secondary">
                Batal
              </Button>
              <Button type="submit" variant="primary" :disabled="opnameForm.processing">
                Simpan
              </Button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Movement History Modal -->
      <Modal :show="showMovementsModal" @close="showMovementsModal = false" max-width="4xl">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Riwayat Pergerakan: {{ selectedProduct?.name }}
          </h3>
          
          <div v-if="loadingMovements" class="text-center py-8">
            <p class="text-gray-600">Loading...</p>
          </div>

          <div v-else-if="movements.length === 0" class="text-center py-8">
            <p class="text-gray-600">Belum ada riwayat pergerakan</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Tanggal</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Arah</th>
                  <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700">Jumlah</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Sumber</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="movement in movements" :key="movement.id" class="hover:bg-gray-50">
                  <td class="px-4 py-2 text-sm">{{ formatDateTime(movement.moved_at) }}</td>
                  <td class="px-4 py-2 text-center">
                    <span
                      class="px-2 py-1 text-xs rounded-full font-medium"
                      :class="movement.direction === 'IN' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                    >
                      {{ movement.direction }}
                    </span>
                  </td>
                  <td class="px-4 py-2 text-center font-medium">{{ formatNumber(movement.quantity) }}</td>
                  <td class="px-4 py-2 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                      {{ movement.source }}
                    </span>
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-600">{{ movement.note }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-4 flex justify-end">
            <Button @click="showMovementsModal = false" variant="secondary">
              Tutup
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from "@inertiajs/vue3"
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  products: Array,
  can: Object,
})

const showOpnameModal = ref(false)
const showMovementsModal = ref(false)
const selectedProduct = ref(null)
const movements = ref([])
const loadingMovements = ref(false)

const opnameForm = useForm({
  product_id: '',
  actual_quantity: 0,
  reason: '',
})

const submitOpname = () => {
  opnameForm.post(route('products.stock-opname'), {
    onSuccess: () => {
      showOpnameModal.value = false
      opnameForm.reset()
    }
  })
}

const viewMovements = async (product) => {
  selectedProduct.value = product
  showMovementsModal.value = true
  loadingMovements.value = true
  movements.value = []
  
  try {
    const response = await axios.get(route('products.movements', product.id))
    movements.value = response.data.movements
  } catch (error) {
    console.error('Error loading movements:', error)
  } finally {
    loadingMovements.value = false
  }
}

function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value)
}

function getUnitDisplay(product) {
  if (!product.unit) return null
  
  if (product.base_unit && product.unit_quantity > 1) {
    const quantity = formatNumber(product.unit_quantity)
    return `${product.unit.name} (${quantity} ${product.base_unit.name})`
  }
  
  return product.unit.name
}

function formatNumber(value) {
  const num = parseFloat(value)
  return num % 1 === 0 ? num.toString() : num.toFixed(2).replace(/\.?0+$/, '')
}

function formatDateTime(datetime) {
  return new Date(datetime).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
