<template>
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-800">{{ title }}</h1>
      <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
    </div>
    <div class="flex flex-wrap gap-3">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>
