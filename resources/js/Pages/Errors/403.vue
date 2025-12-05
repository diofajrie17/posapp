<template>
  <AppLayout title="Akses Ditolak">
    <div class="max-w-xl mx-auto mt-16">
      <div class="bg-white border border-red-200 rounded-2xl p-8 shadow">
        <div class="flex items-start">
          <div class="flex-shrink-0 mr-4">
            <svg class="h-10 w-10 text-red-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <h1 class="text-2xl font-bold text-red-700 mb-2">Akses Ditolak (403)</h1>
            <p class="text-gray-700 mb-4">{{ message || 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
            <div class="flex gap-3">
              <Link href="/dashboard" class="px-4 py-2 rounded bg-gray-800 text-white">Kembali ke Dashboard</Link>
              <button @click="close" class="px-4 py-2 rounded border">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({ message: String })

function close() {
  router.visit('/dashboard')
}
</script>
