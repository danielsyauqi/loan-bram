<script setup lang="ts">
import StatusBadge from '@/components/ui/StatusBadge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
declare function route(name: string, params?: any): string;

// Get page props using usePage with proper typing
const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userStatus = computed(() => (user.value as any)?.status || 'not active');

// Define props
const props = defineProps<{
    applications: Array<any>,
    metrics: any,
    user: any,
    error: any
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const currentTime = ref('');
const greeting = ref('');

const updateGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) greeting.value = 'Good Morning';
    else if (hour < 17) greeting.value = 'Good Afternoon';
    else greeting.value = 'Good Evening';
};

// Helper function to format currency
const formatCurrency = (value: string) => {
    if (!value) return 'RM 0.00';
    return `RM ${parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')}`;
};

// Computed property for status colors
const statusColors = computed(() => {
    return {
        new: { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'bg-blue-200' },
        processing: { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: 'bg-yellow-200' },
        approved: { bg: 'bg-green-100', text: 'text-green-800', icon: 'bg-green-200' },
        rejected: { bg: 'bg-red-100', text: 'text-red-800', icon: 'bg-red-200' },
        disbursed: { bg: 'bg-purple-100', text: 'text-purple-800', icon: 'bg-purple-200' },
        total: { bg: 'bg-gray-100', text: 'text-gray-800', icon: 'bg-gray-200' }
    };
});

// Filter applications by status
const selectedStatus = ref('all');
const filteredApplications = computed(() => {
    if (selectedStatus.value === 'all') {
        return props.applications;
    }
    
    if (selectedStatus.value === 'processing') {
        return props.applications?.filter(app => 
            ['Processing', 'Pending', 'Ready to Submit'].includes(app.status)
        );
    }

    if (selectedStatus.value === 'rejected') {
        return props.applications?.filter(app => 
            ['Rejected', 'Delete Request'].includes(app.status)
        );
    }
    
    return props.applications?.filter(app => 
        app.status.toLowerCase() === selectedStatus.value.toLowerCase()
    );
});

// Get appropriate icon for each status
const getStatusIcon = (status: string) => {
    switch(status.toLowerCase()) {
        case 'new':
            return 'M12 4v16m8-8H4';
        case 'processing':
            return 'M19 8l-7 7-7-7';
        case 'approved':
            return 'M5 13l4 4L19 7';
        case 'rejected':
            return 'M6 18L18 6M6 6l12 12';
        case 'disbursed':
            return 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z';
        case 'total':
            return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
        default:
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
};

onMounted(() => {
    updateGreeting();
    setInterval(() => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }, 1000);
});

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl p-3 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- User Profile and Welcome Message -->
            <div class="p-3 sm:p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 transition-all duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="flex-shrink-0 h-10 w-10 sm:h-16 sm:w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                            <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" class="h-10 w-10 sm:h-16 sm:w-16 rounded-full object-cover" />
                            <span v-else class="text-lg sm:text-2xl">{{ user.name.charAt(0) }}</span>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-bold text-gray-800 dark:text-white">{{ greeting }}, {{ user.name }}!</h2>
                            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">Welcome to your Loan Management Dashboard</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right mt-2 sm:mt-0">
                        <div class="text-lg sm:text-2xl font-bold text-gray-800 dark:text-white">
                            <StatusBadge :status="props.user.status" class="first-badge mr-2" />
                            <span class="text-base sm:text-xl">{{ currentTime }}</span>
                            <StatusBadge :status="props.user.status" class="second-badge mr-2 hidden" />
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ new Date().toLocaleDateString() }}</div>
                    </div>
                </div>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-4 mb-3 sm:mb-6">
                    <!-- Total Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-gray-500': selectedStatus === 'all' }"
                        @click="selectedStatus = 'all'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.total.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.total.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('total')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics?.total }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- New Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-blue-500': selectedStatus === 'new' }"
                        @click="selectedStatus = 'new'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.new.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.new.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('new')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">New</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics?.new }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Processing Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-yellow-500': selectedStatus === 'processing' }"
                        @click="selectedStatus = 'processing'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.processing.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.processing.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('processing')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Processing</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics.processing }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Approved Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-green-500': selectedStatus === 'approved' }"
                        @click="selectedStatus = 'approved'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.approved.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.approved.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('approved')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Approved</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics.approved }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rejected Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-red-500': selectedStatus === 'rejected' }"
                        @click="selectedStatus = 'rejected'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.rejected.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.rejected.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('rejected')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Rejected</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics.rejected }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Disbursed Applications -->
                    <div 
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-300"
                        :class="{ 'ring-2 ring-purple-500': selectedStatus === 'disbursed' }"
                        @click="selectedStatus = 'disbursed'"
                    >
                        <div class="flex items-center">
                            <div class="p-2 sm:p-3 rounded-full" :class="statusColors.disbursed.icon">
                                <svg class="h-4 w-4 sm:h-6 sm:w-6" :class="statusColors.disbursed.text" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getStatusIcon('disbursed')" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-4">
                                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">Disbursed</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-700 dark:text-white">{{ metrics.disbursed }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-3 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-white mb-3 sm:mb-4">
                        {{ selectedStatus === 'all' ? 'All Applications' : `${selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1)} Applications` }}
                        <span class="text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">({{ filteredApplications.length }})</span>
                    </h2>
                    
                    <div v-if="filteredApplications.length === 0" class="text-center py-6 sm:py-8">
                        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No applications found</h3>
                        <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            {{ selectedStatus === 'all' ? 'You have not submitted any loan applications yet.' : `You don't have any ${selectedStatus} applications.` }}
                        </p>
                    </div>

                    <div v-else class="overflow-x-auto -mx-3 sm:mx-0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ref ID</th>
                                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Loan Type</th>
                                    <th scope="col" class="hidden sm:table-cell px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="hidden md:table-cell px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="application in filteredApplications" :key="application.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-white">
                                        {{ application.reference_id }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-300">
                                        {{ application.module?.name ?? 'Not Assigned' }}
                                    </td>
                                    <td class="hidden sm:table-cell px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-300">
                                        {{ application.product?.name ?? 'Not Assigned' }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-300">
                                        {{ formatCurrency(application.amount_applied) }}
                                    </td>
                                    <td class="hidden md:table-cell px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-300">
                                        {{ application.date_received ?? 'Not Assigned' }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <StatusBadge :status="application.status" />
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                        <Link 
                                            :href="route('customer.application.show', { 
                                                referenceNumber: application.reference_id 
                                            })" 
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            View
                                        </Link> 
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Add a create application button for mobile -->
                <div class="sm:hidden fixed bottom-6 right-6">
                    <Link 
                        :href="route('customer.applications.new')"
                        class="inline-flex items-center justify-center p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </Link>
                </div>
        </div>
        <div v-else>    
            <div class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
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

/* Apply animation delay to grid items */
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

@media (max-width: 640px) {
    .first-badge {
        display: none;
    }
    .second-badge {
        display: inline-block;
        margin-left: 0.5rem;
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
