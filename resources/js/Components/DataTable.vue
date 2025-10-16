<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="min-w-full border-collapse text-sm">
      <thead>
        <tr class="bg-gray-50 text-gray-700 border-b border-gray-200">
          <slot name="header"></slot>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <slot></slot>
      </tbody>
    </table>
  </div>
</template>

<script setup>
// No props needed, fully slotted for flexibility
</script>
