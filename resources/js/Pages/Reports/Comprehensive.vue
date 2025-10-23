<template>
  <AppLayout title="Laporan Komprehensif">
    <div class="bg-white">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 no-print">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Komprehensif</h1>
        <div class="flex gap-3">
          <a
            :href="route('reports.comprehensive.export', { date_from: filters.date_from, date_to: filters.date_to })"
            class="px-4 py-2 border border-gray-400 text-gray-700 rounded font-semibold text-sm hover:bg-gray-100 transition"
          >📊 Export CSV</a>
          <button @click="print" class="px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition">🖨 Print</button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-end gap-3 mb-6 no-print bg-gray-50 p-4 rounded-lg border">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
          <input
            v-model="localDateFrom"
            type="date"
            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
          <input
            v-model="localDateTo"
            type="date"
            class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button
          @click="reload"
          class="px-4 py-2 bg-blue-600 text-white rounded font-semibold text-sm hover:bg-blue-700 transition"
        >
          Filter
        </button>
        
        <!-- Section Filter Dropdown -->
        <div id="filter-dropdown">
          <button
            @click="toggleDropdown"
            class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded font-semibold text-sm hover:bg-gray-50 transition flex items-center gap-2"
          >
            <span>📊 Tampilkan Bagian</span>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ selectedCount }}/{{ totalSections }}</span>
            <svg class="w-4 h-4" :class="{ 'rotate-180': isDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
        </div>
        
        <!-- Dropdown Menu (Portal to body level for proper positioning) -->
        <Teleport to="body">
          <div
            v-show="isDropdownOpen"
            id="filter-dropdown-menu"
            class="fixed bg-white border border-gray-200 rounded-lg shadow-xl z-[9999] min-w-[280px]"
            :style="{ top: dropdownPosition.top + 'px', left: dropdownPosition.left + 'px' }"
          >
            <!-- Quick Actions -->
            <div class="flex gap-2 p-3 border-b border-gray-200">
              <button
                @click="selectAll"
                class="flex-1 px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition font-medium"
              >
                Pilih Semua
              </button>
              <button
                @click="deselectAll"
                class="flex-1 px-3 py-1.5 text-xs bg-gray-50 text-gray-700 rounded hover:bg-gray-100 transition font-medium"
              >
                Hapus Semua
              </button>
            </div>
            
            <!-- Checkbox List -->
            <div class="p-2 max-h-96 overflow-y-auto">
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.summary"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">Ringkasan Total</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.sales"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">💰 Penjualan (Sales)</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.membership"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">👥 Pendapatan Membership</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.expenses"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">💸 Pengeluaran Operasional</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.ads"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">📢 Pengeluaran Iklan</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.facilities"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">🏢 Pendapatan Fasilitas</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.topProducts"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">🏆 Produk Terlaris</span>
              </label>
              
              <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 rounded cursor-pointer">
                <input
                  type="checkbox"
                  v-model="visibleSections.salesTrend"
                  class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                />
                <span class="text-sm text-gray-700">📈 Tren Penjualan Harian</span>
              </label>
            </div>
          </div>
        </Teleport>
      </div>

      <!-- Summary Cards -->
      <div v-if="visibleSections.summary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-blue-600 mb-2">Total Pendapatan</h3>
          <p class="text-xl font-bold text-blue-700">{{ rupiah(totals.revenue) }}</p>
          <p class="text-xs text-gray-600">Revenue</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-orange-600 mb-2">HPP</h3>
          <p class="text-xl font-bold text-orange-700">{{ rupiah(totals.cogs) }}</p>
          <p class="text-xs text-gray-600">Cost of Goods</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-green-600 mb-2">Laba Kotor</h3>
          <p class="text-xl font-bold text-green-700">{{ rupiah(totals.gross_profit) }}</p>
          <p class="text-xs text-gray-600">Gross Profit</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-red-600 mb-2">Pengeluaran</h3>
          <p class="text-xl font-bold text-red-700">{{ rupiah(totals.expenses) }}</p>
          <p class="text-xs text-gray-600">Operasional + Iklan</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold mb-2" :class="totals.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">Laba Bersih</h3>
          <p class="text-xl font-bold" :class="totals.net_profit >= 0 ? 'text-green-700' : 'text-red-700'">{{ rupiah(totals.net_profit) }}</p>
          <p class="text-xs text-gray-600">{{ totals.net_profit >= 0 ? 'Profit' : 'Loss' }}</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-purple-600 mb-2">Transaksi</h3>
          <p class="text-xl font-bold text-purple-700">{{ sales.summary?.trx_count || 0 }}</p>
          <p class="text-xs text-gray-600">Total Sales</p>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Breakdown -->
        <div v-if="visibleSections.sales" class="card">
          <h3 class="card-title">💰 Penjualan (Sales)</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(sales.summary?.gross_total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ sales.summary?.trx_count || 0 }} transaksi</p>
          </div>
          <div v-if="sales.by_payment?.length">
            <h4 class="font-medium mb-2">Berdasarkan Metode Pembayaran:</h4>
            <div v-for="payment in sales.by_payment" :key="payment.payment_type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ payment.payment_type }}</span>
              <span class="font-medium">{{ rupiah(payment.total) }} ({{ payment.count }}x)</span>
            </div>
          </div>
        </div>

        <!-- Membership Income -->
        <div v-if="visibleSections.membership" class="card">
          <h3 class="card-title">👥 Pendapatan Membership</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(memberships?.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ memberships?.summary?.count || 0 }} pembayaran</p>
          </div>
          <div v-if="memberships?.by_type?.length">
            <h4 class="font-medium mb-2">Berdasarkan Tipe:</h4>
            <div v-for="type in memberships.by_type" :key="type.type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ type.type === 'registration' ? 'Registrasi Member' : 'Pelanggan Harian' }}</span>
              <span class="font-medium">{{ rupiah(type.total) }} ({{ type.count }}x)</span>
            </div>
          </div>
          <div v-if="memberships?.by_payment?.length" class="mt-3">
            <h4 class="font-medium mb-2">Berdasarkan Metode Pembayaran:</h4>
            <div v-for="payment in memberships.by_payment" :key="payment.payment_type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ payment.payment_type }}</span>
              <span class="font-medium">{{ rupiah(payment.total) }} ({{ payment.count }}x)</span>
            </div>
          </div>
        </div>

        <!-- Expenses Breakdown -->
        <div v-if="visibleSections.expenses" class="card">
          <h3 class="card-title">💸 Pengeluaran Operasional</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(expenses.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ expenses.summary?.count || 0 }} item</p>
          </div>
        </div>

        <!-- Ads Expenses -->
        <div v-if="visibleSections.ads" class="card">
          <h3 class="card-title">📢 Pengeluaran Iklan</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(ads.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ ads.summary?.count || 0 }} iklan</p>
          </div>
          <div v-if="ads.by_type?.length">
            <h4 class="font-medium mb-2">Berdasarkan Jenis:</h4>
            <div v-for="type in ads.by_type" :key="type.type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ type.type }}</span>
              <span class="font-medium">{{ rupiah(type.total) }} ({{ type.count }}x)</span>
            </div>
          </div>
        </div>

        <!-- Facility Income -->
        <div v-if="visibleSections.facilities" class="card">
          <h3 class="card-title">🏢 Pendapatan Fasilitas</h3>
          <div class="mb-4">
            <p class="text-lg font-semibold">Total: {{ rupiah(facilities.summary?.total || 0) }}</p>
            <p class="text-sm text-gray-600">{{ facilities.summary?.count || 0 }} item</p>
          </div>
          <div v-if="facilities.by_type?.length">
            <h4 class="font-medium mb-2">Berdasarkan Jenis:</h4>
            <div v-for="type in facilities.by_type" :key="type.type" class="flex justify-between items-center py-1">
              <span class="capitalize">{{ type.type }}</span>
              <span class="font-medium">{{ rupiah(type.total) }} ({{ type.count }}x)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div v-if="visibleSections.topProducts && top_products?.length" class="card mb-8">
        <h3 class="card-title">🏆 Produk Terlaris</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left p-3 font-medium">Produk</th>
                <th class="text-right p-3 font-medium">Qty</th>
                <th class="text-right p-3 font-medium">Omzet</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in top_products" :key="product.product_id" class="border-t">
                <td class="p-3">
                  <span class="font-medium">{{ product.product?.name || 'N/A' }}</span>
                  <span class="text-xs text-gray-500 ml-2">{{ product.product?.unit || '' }}</span>
                </td>
                <td class="p-3 text-right">{{ Number(product.qty || 0).toLocaleString('id-ID') }}</td>
                <td class="p-3 text-right font-medium">{{ rupiah(product.gross) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sales Trend Chart -->
      <div v-if="visibleSections.salesTrend && sales.by_date?.length" class="card">
        <h3 class="card-title">📈 Tren Penjualan Harian</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left p-3 font-medium">Tanggal</th>
                <th class="text-right p-3 font-medium">Transaksi</th>
                <th class="text-right p-3 font-medium">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="day in sales.by_date" :key="day.date" class="border-t">
                <td class="p-3">{{ formatDate(day.date) }}</td>
                <td class="p-3 text-right">{{ day.trx_count }}</td>
                <td class="p-3 text-right font-medium">{{ rupiah(day.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatPrice } from '@/composables/usePriceFormatter'

const props = defineProps({
  filters: Object,
  sales: Object,
  expenses: Object,
  ads: Object,
  facilities: Object,
  memberships: Object,
  top_products: Array,
  totals: Object,
})

const localDateFrom = ref(props.filters.date_from)
const localDateTo = ref(props.filters.date_to)

// Section visibility state
const visibleSections = ref({
  summary: true,
  sales: true,
  membership: true,
  expenses: true,
  ads: true,
  facilities: true,
  topProducts: true,
  salesTrend: true
})

// Dropdown state
const isDropdownOpen = ref(false)
const dropdownPosition = ref({ top: 0, left: 0 })

// Calculate dropdown position
function toggleDropdown(event) {
  if (!isDropdownOpen.value) {
    const button = event.currentTarget
    const rect = button.getBoundingClientRect()
    dropdownPosition.value = {
      top: rect.bottom + window.scrollY + 8,
      left: rect.left + window.scrollX
    }
  }
  isDropdownOpen.value = !isDropdownOpen.value
}

// Load saved filters from localStorage
onMounted(() => {
  const saved = localStorage.getItem('report_comprehensive_filters')
  if (saved) {
    try {
      Object.assign(visibleSections.value, JSON.parse(saved))
    } catch (e) {
      console.error('Failed to load filter preferences:', e)
    }
  }
})

// Save filters to localStorage
watch(visibleSections, (newVal) => {
  localStorage.setItem('report_comprehensive_filters', JSON.stringify(newVal))
}, { deep: true })

// Count selected sections
const selectedCount = computed(() => {
  return Object.values(visibleSections.value).filter(v => v).length
})

const totalSections = computed(() => {
  return Object.keys(visibleSections.value).length
})

// Toggle all sections
function selectAll() {
  Object.keys(visibleSections.value).forEach(key => {
    visibleSections.value[key] = true
  })
}

function deselectAll() {
  Object.keys(visibleSections.value).forEach(key => {
    visibleSections.value[key] = false
  })
}

// Close dropdown when clicking outside
function handleClickOutside(event) {
  if (!isDropdownOpen.value) return
  
  const button = document.getElementById('filter-dropdown')
  const dropdownMenu = document.getElementById('filter-dropdown-menu')
  
  // Check if click is outside both the button and dropdown menu
  if (button && !button.contains(event.target) && 
      dropdownMenu && !dropdownMenu.contains(event.target)) {
    isDropdownOpen.value = false
  }
}

// Close dropdown on scroll to prevent misalignment
function handleScroll() {
  if (isDropdownOpen.value) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('scroll', handleScroll, true)
})

// Clean up event listeners
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', handleScroll, true)
})

function rupiah(n) {
  return 'Rp ' + formatPrice(n || 0)
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function reload() {
  router.get(route('reports.comprehensive'), {
    date_from: localDateFrom.value,
    date_to: localDateTo.value
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function print() {
  window.print()
}
</script>

<style scoped>
.card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.card-title {
  font-weight: 600;
  font-size: 1.125rem;
  margin-bottom: 1rem;
  color: #374151;
}

svg {
  transition: transform 0.2s ease-in-out;
}

.rotate-180 {
  transform: rotate(180deg);
}

@media print {
  .no-print {
    display: none !important;
  }
  
  .card {
    box-shadow: none;
    border: 1px solid #e5e7eb;
    break-inside: avoid;
    margin-bottom: 1rem;
  }
  
  body {
    font-size: 12px;
  }
  
  .grid {
    display: block;
  }
  
  .grid > * {
    margin-bottom: 1rem;
  }
}
</style>