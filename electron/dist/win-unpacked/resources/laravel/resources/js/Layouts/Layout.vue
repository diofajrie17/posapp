<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md">
      <div class="p-4 border-b">
        <h2 class="text-xl font-bold text-indigo-600">MyApp</h2>
      </div>
      <nav class="p-4 space-y-2 text-gray-700">
        <Link href="/dashboard" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('dashboard') }">Dashboard</Link>
        <Link href="/products" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('products.index') }">Produk</Link>
        <Link href="/transactions/create" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('transactions.create') }">Transaksi Baru</Link>
        <Link href="/inventory/stock" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('stock.index') }">Inventory</Link>
        <Link href="/members" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('members.index') }">Member</Link>
        <Link href="/expenses" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('expenses.index') }">Pengeluaran</Link>
        <Link href="/reports/daily-sales" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('reports.daily') }">Laporan</Link>
        <Link href="/notifications" class="block px-3 py-2 rounded hover:bg-indigo-100" :class="{ 'bg-indigo-100 font-semibold': route().current('notifications.index') }">Notifikasi</Link>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, route } from '@inertiajs/vue3'
</script>

<style scoped>
/* bisa tambah styling sidebar khusus di sini */
</style>
