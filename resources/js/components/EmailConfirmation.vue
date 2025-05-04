<script setup lang="ts">
import { computed, defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  verified: boolean;
  errorMessage?: string;
}>();

const title = computed(() => props.verified ? 'Email Confirmation Successful' : 'Email Confirmation Failed');
const message = computed(() => {
  if (props.verified) {
    return 'Your email address has been successfully verified and updated. You can now use your new email address to sign in to your account.';
  } else {
    return props.errorMessage || 'We couldn\'t verify your email address. The confirmation link may have expired or is invalid. Please try requesting a new verification email from your profile page.';
  }
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-xl">
      <div class="bg-white shadow-xl rounded-xl overflow-hidden">
        <div 
          class="p-6 text-center" 
          :class="verified ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'"
        >
          <div 
            class="w-16 h-16 mx-auto mb-5 rounded-full flex items-center justify-center text-2xl font-bold"
            :class="verified ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
          >
            {{ verified ? '✓' : '✗' }}
          </div>
        </div>
        
        <div class="p-8 text-center">
          <h1 class="text-xl font-bold mb-4 text-gray-800">{{ title }}</h1>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">{{ message }}</p>
          
          <div class="space-y-4">
            <Link 
              href="/login" 
              class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium text-sm uppercase tracking-wider transition transform hover:-translate-y-0.5 hover:shadow-md"
            >
              Go to Login
            </Link>
            
            <div class="text-sm text-gray-500 mt-6">
              Need assistance? 
              <Link href="/contact" class="text-blue-600 font-medium hover:underline">
                Contact our support team
              </Link>
            </div>
          </div>
        </div>
      </div>
      
      <div class="mt-8 text-center">
        <div class="text-xl font-semibold text-blue-600">{{ import.meta.env.VITE_APP_NAME || 'Laravel' }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add any additional styling if needed */
</style> 