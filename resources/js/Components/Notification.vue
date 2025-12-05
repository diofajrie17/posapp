<template>
  <!-- Success Notification -->
  <div
    v-if="$page.props.flash?.success || $page.props.flash?.message"
    class="fixed top-4 right-4 z-50 max-w-md animate-fade-in"
  >
    <div class="bg-green-50 border border-green-200 rounded-lg shadow-lg p-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
        </div>
        <p class="ml-3 text-green-800 font-medium">{{ $page.props.flash.success || $page.props.flash.message }}</p>
      </div>
      <button
        @click="$page.props.flash.success = null; $page.props.flash.message = null"
        class="ml-4 text-green-400 hover:text-green-600 transition-colors"
      >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Error Notification -->
  <div
    v-if="$page.props.flash?.error"
    class="fixed top-4 right-4 z-50 max-w-md animate-fade-in"
  >
    <div class="bg-red-50 border border-red-200 rounded-lg shadow-lg p-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <p class="ml-3 text-red-800 font-medium">{{ $page.props.flash.error }}</p>
      </div>
      <button
        @click="$page.props.flash.error = null"
        class="ml-4 text-red-400 hover:text-red-600 transition-colors"
      >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Access Denied Notification -->
  <div
    v-if="$page.props.errors?.access_denied"
    class="fixed top-4 right-4 z-50 max-w-md animate-fade-in"
  >
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-lg p-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
        </div>
        <p class="ml-3 text-yellow-800 font-medium">{{ $page.props.errors.access_denied }}</p>
      </div>
      <button
        @click="$page.props.errors.access_denied = null"
        class="ml-4 text-yellow-400 hover:text-yellow-600 transition-colors"
      >
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

// Auto-hide notifications after 5 seconds
onMounted(() => {
  setTimeout(() => {
    if (window.$page?.props?.flash) {
      window.$page.props.flash.success = null
      window.$page.props.flash.error = null
      window.$page.props.flash.message = null
    }
  }, 5000)
})
</script>

<style>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
</style>

