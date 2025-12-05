<template>
  <AppLayout title="Laporan Komprehensif">
    <div class="bg-white">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 no-print">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Komprehensif</h1>
        <div class="flex gap-3">
          <button 
            @click="showDetailedReport = !showDetailedReport"
            class="px-4 py-2 border border-green-600 text-green-700 rounded font-semibold text-sm hover:bg-green-50 transition"
          >🔍 Cek Laporan</button>
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
          <h3 class="text-sm font-semibold text-emerald-600 mb-2">Pendapatan Cash</h3>
          <p class="text-xl font-bold text-emerald-700">{{ rupiah(totalCash) }}</p>
          <p class="text-xs text-gray-600">Cash</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-purple-600 mb-2">Pendapatan QR</h3>
          <p class="text-xl font-bold text-purple-700">{{ rupiah(totalQr) }}</p>
          <p class="text-xs text-gray-600">QR</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-indigo-600 mb-2">Pendapatan Transfer</h3>
          <p class="text-xl font-bold text-indigo-700">{{ rupiah(totalTransfer) }}</p>
          <p class="text-xs text-gray-600">Transfer</p>
        </div>
        <div v-if="isCashier" class="card text-center">
          <h3 class="text-sm font-semibold text-teal-600 mb-2">Total Cash</h3>
          <p class="text-xl font-bold text-teal-700">{{ rupiah(netCashForCashier) }}</p>
          <p class="text-xs text-gray-600">Pendapatan Cash - Pengeluaran</p>
        </div>
        <div v-if="!isCashier" class="card text-center">
          <h3 class="text-sm font-semibold text-orange-600 mb-2">HPP</h3>
          <p class="text-xl font-bold text-orange-700">{{ rupiah(totals.cogs) }}</p>
          <p class="text-xs text-gray-600">Cost of Goods</p>
        </div>
        <div v-if="!isCashier" class="card text-center">
          <h3 class="text-sm font-semibold text-green-600 mb-2">Laba Kotor</h3>
          <p class="text-xl font-bold text-green-700">{{ rupiah(totals.gross_profit) }}</p>
          <p class="text-xs text-gray-600">Gross Profit</p>
        </div>
        <div class="card text-center">
          <h3 class="text-sm font-semibold text-red-600 mb-2">Pengeluaran</h3>
          <p class="text-xl font-bold text-red-700">{{ rupiah(totals.expenses) }}</p>
          <p class="text-xs text-gray-600">Operasional + Iklan</p>
        </div>
        <div v-if="!isCashier" class="card text-center">
          <h3 class="text-sm font-semibold mb-2" :class="totals.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">Laba Bersih</h3>
          <p class="text-xl font-bold" :class="totals.net_profit >= 0 ? 'text-green-700' : 'text-red-700'">{{ rupiah(totals.net_profit) }}</p>
          <p class="text-xs text-gray-600">{{ totals.net_profit >= 0 ? 'Profit' : 'Loss' }}</p>
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
                <th v-if="!isCashier" class="text-right p-3 font-medium text-blue-600">HPP</th>
                <th v-if="!isCashier" class="text-right p-3 font-medium text-green-600">Laba Kotor</th>
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
                <td v-if="!isCashier" class="p-3 text-right text-blue-600">{{ rupiah(product.total_cogs || 0) }}</td>
                <td v-if="!isCashier" class="p-3 text-right text-green-600 font-semibold">{{ rupiah((product.gross || 0) - (product.total_cogs || 0)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sales Trend Chart -->
      <div v-if="visibleSections.salesTrend && sales.by_date?.length" class="card">
        <h3 class="card-title">📈 Daftar Produk yang Terjual per Hari</h3>
        <div class="overflow-x-auto">
          <div v-for="day in sales.by_date" :key="day.date" class="mb-6 border-b pb-6 last:border-b-0 last:pb-0">
            <!-- Date Header -->
            <div class="flex items-center justify-between mb-3 pb-2 border-b-2 border-blue-200">
              <h4 class="text-base font-semibold text-gray-800">{{ formatDate(day.date) }}</h4>
              <div class="flex items-center gap-4 text-xs">
                <span class="text-gray-600">{{ day.trx_count }} transaksi</span>
                <template v-if="!isCashier">
                  <span class="px-2 py-1 bg-green-50 text-green-700 rounded font-medium">Omzet: {{ rupiah(day.total) }}</span>
                  <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded font-medium">HPP: {{ rupiah(day.total_cogs || 0) }}</span>
                  <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded font-semibold">Laba: {{ rupiah((day.total || 0) - (day.total_cogs || 0)) }}</span>
                </template>
              </div>
            </div>
            
            <!-- Product List -->
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2 font-medium">Produk</th>
                  <th class="text-left p-2 font-medium">Kategori</th>
                  <th class="text-right p-2 font-medium">Qty</th>
                  <th class="text-right p-2 font-medium">Omzet</th>
                  <th v-if="!isCashier" class="text-right p-2 font-medium text-blue-600">HPP</th>
                  <th v-if="!isCashier" class="text-right p-2 font-medium text-green-600">Laba Kotor</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="product in getProductsByDate(day.date)" 
                  :key="product.product_id"
                  class="border-t hover:bg-gray-50"
                >
                  <td class="p-2">
                    <span class="font-medium">{{ product.product?.name || 'N/A' }}</span>
                    <span class="text-xs text-gray-500 ml-1">({{ product.product?.unit || '' }})</span>
                  </td>
                  <td class="p-2">{{ product.product?.category?.name || '-' }}</td>
                  <td class="text-right p-2">{{ Number(product.total_qty || 0).toLocaleString('id-ID') }}</td>
                  <td class="text-right p-2 font-medium">{{ rupiah(product.total_revenue || 0) }}</td>
                  <td v-if="!isCashier" class="text-right p-2 text-blue-600">{{ rupiah(product.total_cogs || 0) }}</td>
                  <td v-if="!isCashier" class="text-right p-2 text-green-600 font-semibold">
                    {{ rupiah((product.total_revenue || 0) - (product.total_cogs || 0)) }}
                  </td>
                </tr>
                <tr v-if="!getProductsByDate(day.date).length">
                  <td :colspan="isCashier ? 4 : 6" class="text-center text-gray-500 py-3">Tidak ada produk terjual</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Detailed Report (CSV Format View) -->
      <div v-if="showDetailedReport" class="card mt-8">
        <div class="flex justify-between items-center mb-4">
          <h3 class="card-title">📋 Detail Laporan (Format CSV)</h3>
          <button 
            @click="showDetailedReport = false" 
            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800"
          >
            ✕ Tutup
          </button>
        </div>

        <!-- Sales Transactions -->
        <div v-if="detailed_data?.sales?.length" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b">💰 Transaksi Penjualan</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2">Tanggal</th>
                  <th class="text-left p-2">Metode</th>
                  <th class="text-left p-2">Member</th>
                  <th class="text-right p-2">Subtotal</th>
                  <th class="text-right p-2">Diskon</th>
                  <th v-if="!isCashier" class="text-right p-2">HPP</th>
                  <th class="text-right p-2 font-medium">Total</th>
                  <th class="text-right p-2">Persentase</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in detailed_data.sales" :key="sale.id" class="border-t hover:bg-gray-50">
                  <td class="p-2">{{ formatDateTime(sale.date_time) }}</td>
                  <td class="p-2">{{ sale.payment_type }}</td>
                  <td class="p-2">{{ sale.member?.full_name || 'Guest' }}</td>
                  <td class="text-right p-2">{{ rupiah(sale.subtotal_amount) }}</td>
                  <td class="text-right p-2 text-red-600">- {{ rupiah(sale.discount_amount) }}</td>
                  <td v-if="!isCashier" class="text-right p-2 text-blue-600">{{ rupiah(sale.cogs_amount) }}</td>
                  <td class="text-right p-2 font-semibold">{{ rupiah(sale.total_amount) }}</td>
                  <td class="text-right p-2 text-gray-600">{{ getPercentage(sale.total_amount, totals.revenue) }}%</td>
                </tr>
                <tr class="bg-gray-100 font-semibold">
                  <td :colspan="isCashier ? 5 : 6" class="p-2">Total Penjualan</td>
                  <td class="text-right p-2">{{ rupiah(totals.revenue) }}</td>
                  <td class="text-right p-2">100%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Expenses -->
        <div v-if="detailed_data?.expenses?.length" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b">💸 Pengeluaran Operasional</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2">Tanggal</th>
                  <th class="text-left p-2">Deskripsi</th>
                  <th class="text-right p-2">Jumlah</th>
                  <th class="text-right p-2">Persentase</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="expense in detailed_data.expenses" :key="expense.id" class="border-t hover:bg-gray-50">
                  <td class="p-2">{{ formatDate(expense.date) }}</td>
                  <td class="p-2">{{ expense.description }}</td>
                  <td class="text-right p-2 font-medium text-red-600">{{ rupiah(expense.amount) }}</td>
                  <td class="text-right p-2 text-gray-600">{{ getPercentage(expense.amount, totals.expenses) }}%</td>
                </tr>
                <tr class="bg-gray-100 font-semibold">
                  <td colspan="2" class="p-2">Total Pengeluaran</td>
                  <td class="text-right p-2 text-red-600">{{ rupiah(totals.expenses) }}</td>
                  <td class="text-right p-2">100%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Ads -->
        <div v-if="detailed_data?.ads?.length" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b">📢 Pengeluaran Iklan</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2">Tanggal</th>
                  <th class="text-left p-2">Jenis</th>
                  <th class="text-left p-2">Deskripsi</th>
                  <th class="text-right p-2">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ad in detailed_data.ads" :key="ad.id" class="border-t hover:bg-gray-50">
                  <td class="p-2">{{ formatDate(ad.date) }}</td>
                  <td class="p-2">{{ ad.type }}</td>
                  <td class="p-2">{{ ad.description }}</td>
                  <td class="text-right p-2 font-medium text-red-600">{{ rupiah(ad.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Facilities -->
        <div v-if="detailed_data?.facilities?.length" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b">🏢 Pendapatan Fasilitas</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2">Tanggal</th>
                  <th class="text-left p-2">Jenis</th>
                  <th class="text-left p-2">Deskripsi</th>
                  <th class="text-right p-2">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="facility in detailed_data.facilities" :key="facility.id" class="border-t hover:bg-gray-50">
                  <td class="p-2">{{ formatDate(facility.date) }}</td>
                  <td class="p-2">{{ facility.type }}</td>
                  <td class="p-2">{{ facility.description }}</td>
                  <td class="text-right p-2 font-medium text-green-600">{{ rupiah(facility.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Memberships -->
        <div v-if="detailed_data?.memberships?.length" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-3 pb-2 border-b">👥 Pendapatan Membership</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left p-2">Tanggal</th>
                  <th class="text-left p-2">Member</th>
                  <th class="text-left p-2">Tipe</th>
                  <th class="text-left p-2">Metode</th>
                  <th class="text-right p-2">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="membership in detailed_data.memberships" :key="membership.id" class="border-t hover:bg-gray-50">
                  <td class="p-2">{{ formatDate(membership.date) }}</td>
                  <td class="p-2">{{ membership.member_name }}</td>
                  <td class="p-2">{{ membership.type }}</td>
                  <td class="p-2">{{ membership.payment_type }}</td>
                  <td class="text-right p-2 font-medium text-green-600">{{ rupiah(membership.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
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
  detailed_data: Object,
  totals: Object,
})

const localDateFrom = ref(props.filters.date_from)
const localDateTo = ref(props.filters.date_to)
const showDetailedReport = ref(false)

// Role-based guard & payment breakdown
const page = usePage()
const isCashier = computed(() => page.props.auth?.roles?.includes('Kasir'))

const totalCash = computed(() => {
  const salesCash = (props.sales?.by_payment || []).find(p => p.payment_type === 'Cash')?.total || 0
  const membershipCash = (props.memberships?.by_payment || []).find(p => p.payment_type === 'Cash')?.total || 0
  return (salesCash || 0) + (membershipCash || 0)
})

const totalQr = computed(() => {
  const salesQr = (props.sales?.by_payment || []).find(p => p.payment_type === 'QR')?.total || 0
  const membershipQr = (props.memberships?.by_payment || []).find(p => p.payment_type === 'QR')?.total || 0
  return (salesQr || 0) + (membershipQr || 0)
})

const totalTransfer = computed(() => {
  const salesTransfer = (props.sales?.by_payment || []).find(p => p.payment_type === 'Transfer')?.total || 0
  const membershipTransfer = (props.memberships?.by_payment || []).find(p => p.payment_type === 'Transfer')?.total || 0
  return (salesTransfer || 0) + (membershipTransfer || 0)
})

// Net cash for cashier view: Pendapatan Cash - Pengeluaran
const netCashForCashier = computed(() => {
  const expenses = props.totals?.expenses || 0
  return (totalCash.value || 0) - (expenses || 0)
})

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

function formatDateTime(dateTime) {
  return new Date(dateTime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getPercentage(value, total) {
  if (!total || total === 0) return 0
  return ((value / total) * 100).toFixed(2)
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

// Helper function to get products by date
function getProductsByDate(date) {
  if (!props.sales.product_by_date) return []
  const items = props.sales.product_by_date
    .filter(item => item.date === date)
    .slice()
  // Sort by category name (asc), then product name (asc)
  items.sort((a, b) => {
    const catA = (a.product?.category?.name || '').toLowerCase()
    const catB = (b.product?.category?.name || '').toLowerCase()
    if (catA < catB) return -1
    if (catA > catB) return 1
    const nameA = (a.product?.name || '').toLowerCase()
    const nameB = (b.product?.name || '').toLowerCase()
    if (nameA < nameB) return -1
    if (nameA > nameB) return 1
    return 0
  })
  return items
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