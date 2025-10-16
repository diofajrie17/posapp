<template>
  <div class="bg-white rounded-lg shadow-sm p-6" :class="customClass">
    <div v-if="title || $slots.header" class="mb-4 pb-4 border-b border-gray-200">
      <slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ title }}</h2>
      </slot>
    </div>
    <slot></slot>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  customClass: {
    type: String,
    default: ''
  }
})
</script>
