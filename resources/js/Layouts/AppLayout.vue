<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Left Sidebar -->
    <div class="w-64 bg-white shadow-lg fixed inset-y-0 left-0 z-50 transform md:translate-x-0 transition-transform duration-300 ease-in-out" :class="{ '-translate-x-full': !menuOpen }">
      <!-- Brand -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="font-bold text-xl text-gray-800">POS App</div>
        <button
          class="md:hidden text-gray-600 focus:outline-none"
          @click="menuOpen = false"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex flex-col py-4">
        <Link
          href="/dashboard"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('dashboard') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Dashboard</span>
        </Link>
        
        <!-- Products Section -->
        <div>
          <button
            @click="toggleProductsMenu"
            class="w-full flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          >
            <span class="text-sm font-medium">Produk</span>
            <svg 
              class="w-4 h-4 transition-transform duration-200" 
              :class="{ 'rotate-180': productsMenuOpen }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          
          <!-- Collapsible submenu -->
          <div 
            v-show="productsMenuOpen" 
            class="transition-all duration-200 ease-in-out overflow-hidden"
          >
            <!-- Products List Link -->
            <Link
              href="/products"
              class="flex items-center px-8 py-2 text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-colors duration-200 text-sm"
              :class="{ 'bg-gray-100 text-gray-800': route().current('products.index') }"
              @click="menuOpen = false"
            >
              <span class="text-sm">Daftar Produk</span>
            </Link>
            
            <!-- Categories submenu -->
            <Link
              href="/categories"
              class="flex items-center px-8 py-2 text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-colors duration-200 text-sm"
              :class="{ 'bg-gray-100 text-gray-800': $page.url.startsWith('/categories') }"
              @click="menuOpen = false"
            >
              <span class="text-sm">Kategori</span>
            </Link>
            
            <!-- Units submenu -->
            <Link
              href="/units"
              class="flex items-center px-8 py-2 text-gray-600 hover:bg-gray-50 hover:text-gray-800 transition-colors duration-200 text-sm"
              :class="{ 'bg-gray-100 text-gray-800': $page.url.startsWith('/units') }"
              @click="menuOpen = false"
            >
              <span class="text-sm">Unit</span>
            </Link>
          </div>
        </div>
        
        <Link
          href="/transactions/create"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('transactions.create') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Transaksi</span>
        </Link>
        
        <Link
          href="/reports/daily-sales"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('reports.daily') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Laporan</span>
        </Link>
        
        <Link
          href="/expenses"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('expenses.index') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Pengeluaran</span>
        </Link>
        
        <Link
          href="/inventory/stock"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('stock.index') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Stok</span>
        </Link>
        
        <Link
          href="/members"
          class="flex items-center px-6 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors duration-200"
          :class="{ 'bg-indigo-100 text-indigo-700 border-r-2 border-indigo-600': route().current('members.index') }"
          @click="menuOpen = false"
        >
          <span class="text-sm font-medium">Member</span>
        </Link>
      </nav>

      <!-- Logout Button at Bottom -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
        <form @submit.prevent="logout">
          <button
            type="submit"
            class="w-full px-4 py-3 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors duration-200 text-sm font-medium"
          >
            Logout
          </button>
        </form>
      </div>
    </div>

    <!-- Mobile Overlay -->
    <div v-if="menuOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" @click="menuOpen = false"></div>

    <!-- Main Content Area -->
    <div class="flex-1 md:ml-64">
      <!-- Top Header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-6 py-4">
          <!-- Mobile Menu Button -->
          <button
            class="md:hidden text-gray-600 focus:outline-none"
            @click="menuOpen = !menuOpen"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>

          <!-- Page Title -->
          <h1 class="text-xl font-semibold text-gray-800">{{ title }}</h1>

          <!-- User Info -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ $page.props.auth?.user?.name }}</span>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="p-6 overflow-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref } from 'vue'

defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  }
})

const menuOpen = ref(false)
const productsMenuOpen = ref(true) // Start with products menu open

function toggleProductsMenu() {
  productsMenuOpen.value = !productsMenuOpen.value
}

function logout() {
  router.post(route('logout'))
  menuOpen.value = false
}
</script>
