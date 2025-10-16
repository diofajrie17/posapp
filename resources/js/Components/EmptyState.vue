<template>
  <div class="text-center py-12 px-4 text-gray-500">
    <div class="text-5xl mb-4">{{ icon }}</div>
    <p class="text-lg font-medium text-gray-700">{{ message }}</p>
    <p v-if="subtitle" class="text-sm text-gray-500 mt-2">{{ subtitle }}</p>
    <slot name="actions"></slot>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: '📋'
  },
  message: {
    type: String,
    default: 'Belum ada data'
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>
