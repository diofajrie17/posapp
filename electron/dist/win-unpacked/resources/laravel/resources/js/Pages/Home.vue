<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-500 via-indigo-600 to-purple-700 text-white relative overflow-hidden">
    <!-- Background glow -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-500/30 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-500/30 rounded-full blur-3xl animate-pulse delay-1000"></div>

    <!-- Content -->
    <div class="text-center z-10">
      <h1 class="text-5xl md:text-6xl font-extrabold drop-shadow-lg animate-fade-in">
        Welcome to <span class="text-yellow-300">POSAPP</span>
      </h1>
      <p class="mt-4 text-lg opacity-90 animate-fade-in-up delay-200">
        Solusi mudah untuk mengelola bisnis Anda 🚀
      </p>

      <!-- Login Button -->
      <a
        href="/login"
        class="mt-8 inline-block px-8 py-3 rounded-2xl bg-yellow-400 text-gray-900 font-semibold shadow-lg hover:bg-yellow-300 hover:scale-105 transition transform duration-300 animate-bounce-slow"
      >
        Login
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
// Tidak perlu script tambahan untuk sekarang
</script>

<style scoped>
/* Animasi custom */
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fade-in-up {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes bounce-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.animate-fade-in {
  animation: fade-in 1s ease-out forwards;
}
.animate-fade-in-up {
  animation: fade-in-up 1s ease-out forwards;
}
.animate-bounce-slow {
  animation: bounce-slow 2.5s infinite;
}
</style>
