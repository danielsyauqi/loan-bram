<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const goToLogin = () => {
    router.visit('/login');
};
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full text-center">
      <div class="flex flex-col items-center mb-6">
        <div class="rounded-full bg-green-100 p-4 mb-4">
          <svg class="h-10 w-10 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Registration Successful!</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Your account has been created. You can now log in to your account.</p>
      </div>
      <button
        @click="goToLogin"
        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Go to Login
      </button>
    </div>
  </div>
</template>
