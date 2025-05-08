<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';
import { User } from 'lucide-vue-next';

// Define interface for authenticated user
interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: string;
    module_permissions: number[];
    status: string;
}

const props = defineProps<{
    modules: Array<{
        slug: string;
        id: number;
        title: string;
        description: string;
        banner: string;
        status: string;
        dateCreated: string;
        productCount: number;
        topProducts: string[];
        totalApplications: number;
    }>;
    authUser: AuthUser | null;
}>();

const modules = ref(props.modules || []);
const authUser = ref(props.authUser);

// Check if user is admin
const isAdmin = computed(() => {
    return authUser.value?.role === 'admin' || authUser.value?.role === 'superuser';
});


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Loan Providers',
        href: '/loan-modules',
    },
];

const searchQuery = ref('');

const filteredModules = computed(() => {
    const query = searchQuery.value.toLowerCase();
    
    // First filter by user permissions if user is not admin
    let permissionFiltered = modules.value;
    if (authUser.value && authUser.value.role !== 'admin' && authUser.value.role !== 'superuser') {
        const permissions = authUser.value.module_permissions || [];
        permissionFiltered = modules.value.filter(module => 
            permissions.includes(module.id)
        );
    }
    
    // Then filter by search query
    return permissionFiltered.filter(module => 
        module.title.toLowerCase().includes(query) ||
        module.description.toLowerCase().includes(query)
    );
});
const formatCurrency = (amount: number | string) => {
    return new Intl.NumberFormat('ms-MY', {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount));
};

</script>

<template>
    <Head title="Loan Providers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Loan Providers
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300">Browse and explore financial institutions offering loan products</p>
                </div>
                
                <!-- Search Bar -->
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search loan providers..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>

            <!-- Admin Actions Section -->
            <div v-if="isAdmin" class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-blue-800 dark:text-blue-300">Admin Controls</h2>
                        <p class="text-blue-600 dark:text-blue-400 text-sm">Manage loan modules and products</p>
                    </div>
                    <div class="flex gap-2">
                        <Link
                            href="/admin/modules"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-300"
                        >
                            Manage Modules
                        </Link>
                    </div>
                </div>
            </div>

        <!--
            <div v-if="authUser" class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4 rounded-lg border border-indigo-100 dark:border-indigo-800">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-100 dark:bg-indigo-800 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-indigo-800 dark:text-indigo-300">
                            Welcome, {{ authUser.name }}!
                        </h2>
                        <p class="text-indigo-600 dark:text-indigo-400 text-sm">
                            You are logged in as {{ authUser.role }}. Here you can browse through all loan providers you have access to.
                        </p>
                    </div>
                </div>
            </div> -->

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="module in filteredModules" 
                     :key="module.id" 
                     class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative">
                        <div class="w-full h-36 bg-white px-20">
                            <img :src="module.banner" 
                                 :alt="module.title"
                                 class="w-full h-full object-contain object-center bg-white"
                                 @error="(e: Event) => { 
                                    if (e.target) {
                                        // Try storage path first, then fallback to public path
                                        (e.target as HTMLImageElement).src = '/storage/images/loan-modules/default.png';
                                        (e.target as HTMLImageElement).onerror = () => {
                                            (e.target as HTMLImageElement).src = '/images/loan-modules/default.png';
                                        };
                                    }
                                 }"
                            />
                        </div>
                        <div class="absolute top-4 right-4">
                            <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 shadow-lg">
                                {{ module.status }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ module.title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 truncate max-w-xs mb-4" :title="module.description">
                            {{ module.description.length > 50 ? module.description.slice(0, 50) + '...' : module.description }}
                        </p>                           
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Established:</span>
                                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ module.dateCreated }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Products:</span>
                                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ module.productCount }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Applications:</span>
                                <span class="text-gray-800 dark:text-gray-200 font-medium">{{ module.totalApplications }}</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Popular Products:</div>
                            <div class="flex flex-wrap gap-2">
                                <div v-if="module.topProducts.length > 0">
                                <span v-for="product in module.topProducts" :key="product"
                                      class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100 rounded-full mr-1">
                                    {{ product }}
                                </span>
                                </div>
                                <div v-else>
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 rounded-full">
                                        No products available
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <Link :href="`/loan-modules/${module.slug}/applications`"
                                  class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-300 text-center">
                                View Module
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Actions Section -->
            <div v-if="authUser?.role === 'customer'" class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
            </div>

            <!-- No Results Message -->
            <div v-if="authUser?.module_permissions.length === 0" class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">Please contact your administrator to get access to the loan modules.</div>
            </div>

            <div v-else-if="filteredModules.length === 0 && authUser?.role !== 'customer'" class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">No loan providers found matching your search.</div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.grid {
    opacity: 0;
    animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grid > div {
    animation: slideIn 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Apply sequential delay to grid items */
.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }
.grid > div:nth-child(5) { animation-delay: 0.5s; }
.grid > div:nth-child(6) { animation-delay: 0.6s; }
.grid > div:nth-child(7) { animation-delay: 0.7s; }
.grid > div:nth-child(8) { animation-delay: 0.8s; }
.grid > div:nth-child(9) { animation-delay: 0.9s; }
.grid > div:nth-child(10) { animation-delay: 1s; }
</style> 