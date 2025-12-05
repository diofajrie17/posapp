<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps<{
  canResetPassword?: boolean
  status?: string
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 p-6"
  >
    <Head title="Login" />

    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 animate-fade-in"
    >
      <!-- Judul -->
      <h1 class="text-3xl font-bold text-center text-gray-800">
        Welcome Back 👋
      </h1>
      <p class="text-gray-500 text-center mt-2">Login to your POSAPP account</p>

      <!-- Status -->
      <div
        v-if="status"
        class="mt-4 mb-4 font-medium text-sm text-green-600 text-center"
      >
        {{ status }}
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="mt-6 space-y-5">
        <!-- Email or Name -->
        <div>
          <label
            for="email"
            class="block text-sm font-medium text-gray-700"
            >Email atau Nama</label
          >
          <input
            id="email"
            v-model="form.email"
            type="text"
            required
            autofocus
            autocomplete="username"
            placeholder="Masukkan email atau nama"
            class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none"
          />
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <!-- Password -->
        <div>
          <label
            for="password"
            class="block text-sm font-medium text-gray-700"
            >Password</label
          >
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
            class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none"
          />
          <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
          <input
            id="remember"
            type="checkbox"
            v-model="form.remember"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-600"
            >Remember me</label
          >
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <Link
            v-if="canResetPassword"
            :href="route('password.request')"
            class="text-sm text-indigo-600 hover:underline"
          >
            Forgot your password?
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold shadow-lg hover:bg-indigo-500 hover:scale-105 transform transition duration-300 disabled:opacity-50"
          >
            Log in
          </button>
        </div>
      </form>
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
