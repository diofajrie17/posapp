<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Global Notifications -->
    <Notification />
    
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
      <!-- Mobile Menu Overlay -->
      <div 
        v-if="menuOpen" 
        class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" 
        @click="menuOpen = false"
      ></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Left: Logo -->
          <div class="flex-shrink-0">
            <h1 class="text-xl font-bold text-gray-800">One O One POS APP</h1>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden lg:flex lg:items-center lg:space-x-1">
            <!-- Dashboard -->
            <Link
              href="/dashboard"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="route().current('dashboard') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Dashboard</span>
              <span v-if="route().current('dashboard')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Produk Dropdown -->
            <div class="relative">
              <button
                @click="toggleProductsMenu"
                class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
              >
                <span :class="(route().current('products.index') || route().current('categories.index') || route().current('units.index') || route().current('stock.movements')) ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Produk</span>
                <svg 
                  class="ml-1 h-3.5 w-3.5 transition-transform duration-200" 
                  :class="{ 'rotate-180': productsMenuOpen }"
                  :style="{ marginTop: '2px' }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <span v-if="(route().current('products.index') || route().current('categories.index') || route().current('units.index') || route().current('stock.movements'))" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
              </button>
              
              <!-- Dropdown Menu -->
              <div 
                v-show="productsMenuOpen"
                class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl py-1 z-50 border border-gray-100"
              >
                <Link
                  href="/products"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('products.index') }"
                  @click="productsMenuOpen = false"
                >Daftar Produk</Link>
                <Link
                  href="/categories"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('categories.index') }"
                  @click="productsMenuOpen = false"
                >Kategori</Link>
                <Link
                  href="/units"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('units.index') }"
                  @click="productsMenuOpen = false"
                >Unit</Link>
                <Link
                  href="/inventory/movements"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('stock.movements') }"
                  @click="productsMenuOpen = false"
                >Riwayat Pergerakan</Link>
              </div>
            </div>

            <!-- Transaksi -->
            <Link
              href="/transactions/create"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="route().current('transactions.create') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Transaksi</span>
              <span v-if="route().current('transactions.create')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Pembelian -->
            <Link
              v-if="!isCashier"
              href="/purchases"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="(route().current('purchases.index') || route().current('purchases.create') || route().current('purchases.show')) ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Pembelian</span>
              <span v-if="(route().current('purchases.index') || route().current('purchases.create') || route().current('purchases.show'))" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Laporan -->
            <Link
              href="/reports/comprehensive"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="(route().current('reports.comprehensive') || route().current('reports.daily')) ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Laporan</span>
              <span v-if="(route().current('reports.comprehensive') || route().current('reports.daily'))" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Pengeluaran -->
            <Link
              href="/expenses"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="route().current('expenses.index') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Pengeluaran</span>
              <span v-if="route().current('expenses.index')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Iklan -->
            <Link
              v-if="!isCashier"
              href="/ads"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="route().current('ads.index') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Iklan</span>
              <span v-if="route().current('ads.index')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Fasilitas -->
            <Link
              v-if="!isCashier"
              href="/facilities"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
            >
              <span :class="route().current('facilities.index') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Fasilitas</span>
              <span v-if="route().current('facilities.index')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>

            <!-- Member Gym & Kehadiran Dropdown -->
            <div class="relative">
              <button
                @click="toggleMembersMenu"
                class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
              >
                <span :class="(route().current('members.index') || route().current('members.create') || route().current('packages.index') || route().current('attendance.checkin') || route().current('attendance.history') || route().current('reports.attendance')) ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Member Gym</span>
                <svg 
                  class="ml-1 h-3.5 w-3.5 transition-transform duration-200" 
                  :class="{ 'rotate-180': membersMenuOpen }"
                  :style="{ marginTop: '2px' }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <span v-if="(route().current('members.index') || route().current('members.create') || route().current('packages.index') || route().current('attendance.checkin') || route().current('attendance.history') || route().current('reports.attendance'))" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
              </button>
              
              <!-- Dropdown Menu -->
              <div 
                v-show="membersMenuOpen"
                class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl py-1 z-50 border border-gray-100"
              >
                <Link
                  href="/members"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': (route().current('members.index') || route().current('members.create') || route().current('members.edit')) }"
                  @click="membersMenuOpen = false"
                >Daftar Member</Link>
                <Link
                  href="/packages"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': (route().current('packages.index') || route().current('packages.create') || route().current('packages.edit')) }"
                  @click="membersMenuOpen = false"
                >Paket Membership</Link>
                <Link
                  href="/attendance/checkin"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('attendance.checkin') }"
                  @click="membersMenuOpen = false"
                >Check-in</Link>
                <Link
                  href="/attendance/history"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('attendance.history') }"
                  @click="membersMenuOpen = false"
                >Riwayat Kehadiran</Link>
                <Link
                  href="/reports/attendance"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('reports.attendance') }"
                  @click="membersMenuOpen = false"
                >Laporan Kehadiran</Link>
              </div>
            </div>

            <!-- Kelas & Pelatihan Dropdown -->
            <div class="relative">
              <button
                @click="toggleKelasMenu"
                class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
              >
                <span :class="(route().current('kelas.*') || route().current('kelas.checkin.*') || route().current('kelas.expenses.*') || route().current('kelas.reports.*')) ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'">Kelas</span>
                <svg 
                  class="ml-1 h-3.5 w-3.5 transition-transform duration-200" 
                  :class="{ 'rotate-180': kelasMenuOpen }"
                  :style="{ marginTop: '2px' }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <span v-if="(route().current('kelas.*') || route().current('kelas.checkin.*') || route().current('kelas.expenses.*') || route().current('kelas.reports.*'))" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
              </button>
              
              <!-- Dropdown Menu -->
              <div 
                v-show="kelasMenuOpen"
                class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl py-1 z-50 border border-gray-100"
              >
                <Link
                  href="/kelas"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.index') || route().current('kelas.create') || route().current('kelas.show') || route().current('kelas.edit') }"
                  @click="kelasMenuOpen = false"
                >Daftar Kelas</Link>
                <Link
                  href="/kelas/members"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.members.*') }"
                  @click="kelasMenuOpen = false"
                >Member Kelas</Link>
                <Link
                  href="/kelas/checkin"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.checkin.*') }"
                  @click="kelasMenuOpen = false"
                >Check-in</Link>
                <Link
                  href="/kelas/checkin/history"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.checkin.history') }"
                  @click="kelasMenuOpen = false"
                >Riwayat Check-in</Link>
                <Link
                  href="/kelas/expenses"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.expenses.*') }"
                  @click="kelasMenuOpen = false"
                >Pengeluaran</Link>
                <Link
                  href="/kelas/reports"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                  :class="{ 'bg-indigo-50 text-indigo-600': route().current('kelas.reports.*') }"
                  @click="kelasMenuOpen = false"
                >Laporan</Link>
              </div>
            </div>

            <!-- Admin Panel (only for Admin role) -->
            <Link
              v-if="$page.props.auth.roles.includes('Admin')"
              href="/admin/users"
              class="inline-flex items-center px-3 py-2 text-sm font-medium transition-colors relative"
              :class="route().current('admin.users.*') ? 'text-indigo-600' : 'text-gray-700 hover:text-indigo-600'"
            >
              <span>Admin Panel</span>
              <span v-if="route().current('admin.users.*')" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600"></span>
            </Link>
          </div>

          <!-- Right: User Menu & Mobile Toggle -->
          <div class="flex items-center">
            <!-- User Info -->
            <div class="hidden sm:flex items-center mr-3">
              <span class="text-sm text-gray-700 font-medium">{{ $page.props.auth?.user?.name }}</span>
            </div>

            <!-- User Dropdown -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center justify-center w-9 h-9 rounded-full hover:bg-gray-100 transition-colors"
              >
                <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </button>

              <!-- User Menu Dropdown -->
              <div 
                v-show="userMenuOpen"
                class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-xl py-1 z-50 border border-gray-100"
              >
                <button
                  @click="logout"
                  class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors rounded mx-1"
                >
                  Logout
                </button>
              </div>
            </div>

            <!-- Mobile Menu Button -->
            <button
              @click="menuOpen = !menuOpen"
              class="lg:hidden ml-4 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path 
                  v-if="!menuOpen"
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path 
                  v-else
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div 
        class="lg:hidden"
        :class="{ 'hidden': !menuOpen }"
      >
        <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl z-50 overflow-y-auto">
          <div class="p-4 space-y-1">
            <Link
              href="/dashboard"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="route().current('dashboard') ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Dashboard</Link>

            <button
              @click="toggleProductsMenu"
              class="w-full flex items-center justify-between px-4 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            >
              Produk
              <svg 
                class="h-5 w-5 transition-transform" 
                :class="{ 'rotate-180': productsMenuOpen }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-show="productsMenuOpen" class="ml-4 space-y-1">
              <Link href="/products" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Daftar Produk</Link>
              <Link href="/categories" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Kategori</Link>
              <Link href="/units" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Unit</Link>
              <Link href="/inventory/movements" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Riwayat Pergerakan</Link>
            </div>

            <Link
              href="/transactions/create"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="route().current('transactions.create') ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Transaksi</Link>

            <Link
              v-if="!isCashier"
              href="/purchases"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="(route().current('purchases.index') || route().current('purchases.create')) ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Pembelian</Link>

            <Link
              href="/reports/comprehensive"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="(route().current('reports.comprehensive') || route().current('reports.daily')) ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Laporan</Link>

            <!-- Kelas Menu -->
            <button
              @click="toggleKelasMenu"
              class="w-full flex items-center justify-between px-4 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            >
              Kelas
              <svg 
                class="h-5 w-5 transition-transform" 
                :class="{ 'rotate-180': kelasMenuOpen }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-show="kelasMenuOpen" class="ml-4 space-y-1">
              <Link href="/kelas" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Daftar Kelas</Link>
              <Link href="/kelas/members" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Member Kelas</Link>
              <Link href="/kelas/checkin" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Check-in</Link>
              <Link href="/kelas/checkin/history" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Riwayat Check-in</Link>
              <Link href="/kelas/expenses" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Pengeluaran</Link>
              <Link href="/kelas/reports" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Laporan</Link>
            </div>

            <Link
              href="/expenses"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="route().current('expenses.index') ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Pengeluaran</Link>

            <Link
              v-if="!isCashier"
              href="/ads"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="route().current('ads.index') ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Iklan</Link>

            <Link
              v-if="!isCashier"
              href="/facilities"
              class="block px-4 py-2 rounded-md text-base font-medium"
              :class="route().current('facilities.index') ? 'bg-indigo-100 text-indigo-900' : 'text-gray-700 hover:bg-gray-100'"
              @click="menuOpen = false"
            >Fasilitas</Link>

            <button
              @click="toggleMembersMenu"
              class="w-full flex items-center justify-between px-4 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
            >
              Member Gym
              <svg 
                class="h-5 w-5 transition-transform" 
                :class="{ 'rotate-180': membersMenuOpen }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-show="membersMenuOpen" class="ml-4 space-y-1">
              <Link href="/members" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Daftar Member</Link>
              <Link href="/packages" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Paket Membership</Link>
              <Link href="/attendance/checkin" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Check-in</Link>
              <Link href="/attendance/history" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Riwayat Kehadiran</Link>
              <Link href="/reports/attendance" @click="menuOpen = false" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Laporan Kehadiran</Link>
            </div>

            <!-- Mobile Logout -->
            <button
              @click="logout"
              class="w-full mt-4 px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 font-medium"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref, computed } from 'vue'
import Notification from '@/Components/Notification.vue'

const page = usePage()

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  auth: {
    type: Object,
    default: () => ({})
  }
})

const isAdmin = computed(() => {
  return page.props.auth.roles?.includes('Admin') || false
})

const isCashier = computed(() => {
  return page.props.auth.roles?.includes('Kasir') || false
})

const menuOpen = ref(false)
const userMenuOpen = ref(false)
const productsMenuOpen = ref(false)
const membersMenuOpen = ref(false)
const kelasMenuOpen = ref(false)

function toggleProductsMenu() {
  productsMenuOpen.value = !productsMenuOpen.value
}

function toggleMembersMenu() {
  membersMenuOpen.value = !membersMenuOpen.value
}

function toggleKelasMenu() {
  kelasMenuOpen.value = !kelasMenuOpen.value
}

function logout() {
  router.post(route('logout'))
  menuOpen.value = false
  userMenuOpen.value = false
}

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    userMenuOpen.value = false
  }
})
</script>

<style scoped>
/* bisa tambah styling sidebar khusus di sini */
</style>
