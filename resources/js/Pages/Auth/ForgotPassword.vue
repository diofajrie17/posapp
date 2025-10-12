<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'

defineProps<{
  status?: string
}>()

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 p-6"
  >
    <Head title="Forgot Password" />

    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 animate-fade-in"
    >
      <!-- Judul -->
      <h1 class="text-3xl font-bold text-center text-gray-800">
        Forgot Password 🔑
      </h1>
      <p class="text-gray-500 text-center mt-2 text-sm">
        Don’t worry, enter your email and we’ll send you a reset link.
      </p>

      <!-- Status -->
      <div
        v-if="status"
        class="mt-4 mb-4 font-medium text-sm text-green-600 text-center"
      >
        {{ status }}
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="mt-6 space-y-5">
        <!-- Email -->
        <div>
          <label
            for="email"
            class="block text-sm font-medium text-gray-700"
            >Email</label
          >
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autofocus
            autocomplete="username"
            placeholder="you@example.com"
            class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none"
          />
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:bg-indigo-500 hover:scale-105 transform transition duration-300 disabled:opacity-50"
        >
          Send Reset Link
        </button>
      </form>

      <!-- Back to login -->
      <div class="mt-6 text-center">
        <a
          href="/login"
          class="text-sm text-indigo-600 hover:underline font-medium"
        >
          ← Back to Login
        </a>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
}
</style>
