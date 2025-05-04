<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { type SharedData } from '@/types';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');

// Define the module interface
interface LoanModule {
  id: number;
  name: string;
  title: string;
  description: string;
  interestRate: string;
  minAmount: number;
  maxAmount: number;
  tenure: string;
  status: string;
  banner: string | null;
  slug: string;
}

const modules = ref<LoanModule[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Set up breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'New Application',
    href: '/new-application',
  }
];

// Format currency
const formatCurrency = (amount: number | string) => {
  return new Intl.NumberFormat('ms-MY', {
    style: 'currency',
    currency: 'MYR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount));
};

// Fetch the available modules when the component mounts
onMounted(async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/loan-modules');
    modules.value = response.data.modules;
  } catch (err) {
    error.value = 'Failed to load loan modules. Please try again later.';
    console.error('Error fetching loan modules:', err);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <Head title="New Application" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="userRole !== 'customer'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 animate__animated animate__fadeIn">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white">New Application</h1>
          <p class="text-gray-600 dark:text-gray-300">Select a loan module to start your application</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg animate__animated animate__fadeIn">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="modules.length === 0" class="text-center py-12 animate__animated animate__fadeIn">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No loan modules found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are currently no loan modules available. Please check back later.</p>
      </div>

      <!-- Module Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="(module, index) in modules" 
          :key="module.id" 
          class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden transform hover:scale-105 transition-transform duration-300 animate__animated animate__fadeInUp"
          :style="{ animationDelay: index * 100 + 'ms' }"
        >
          <!-- Card Banner -->
          <div class="h-40 overflow-hidden">
            <img 
              v-if="module.banner" 
              :src="module.banner" 
              :alt="module.title"
              class="w-full h-full object-contain" 
            />
            <div v-else class="w-full h-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
              <span class="text-white text-xl font-bold">{{ module.title }}</span>
            </div>
          </div>
          
          <!-- Card Content -->
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ module.title }}</h3>
            <p class="text-gray-500 dark:text-gray-400 truncate max-w-xs mb-4" :title="module.description">
                            {{ module.description.length > 50 ? module.description.slice(0, 50) + '...' : module.description }}
                        </p>               
            <div class="grid grid-cols-2 gap-2 mb-6">
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Interest Rate</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tenure</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ module.tenure }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Min Amount</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.minAmount) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Max Amount</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.maxAmount) }}</p>
              </div>
            </div>
            
            <Link 
              :href="module.slug ? `/loan-modules/${module.slug}/applications/create` : `/loan-modules/${module.id}/applications/create`" 
              class="block w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center transition-all duration-300 hover:bg-blue-800 hover:scale-105"
            >
              Start Application
            </Link>
          </div>
        </div>
      </div>
    </div>
     <!-- Customer Actions Section -->
     <div v-else class="text-center py-12 animate__animated animate__fadeIn">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
      </div>
  </AppLayout>
</template>

<style>
/* Import Animate.css */
@import 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.animate__fadeInUp {
  animation-duration: 0.6s;
}

/* Card hover effects */
.transform {
  transition: all 0.3s ease;
}

.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Custom animations */
@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.pulse {
  animation: pulse 2s infinite;
}
</style>
