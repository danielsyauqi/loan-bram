<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { PencilIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';
declare const route: any;
const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth?.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');
const userStatus = computed(() => (userAny.value as any)?.status || 'not active');
const props = defineProps<{
    user: {
        id: number;
        name: string;
        username: string;
        email: string;
        phone_num: string;
        bank_name: string | null;
        bank_account: string | null;
        role: string;
        status: string;
        ic_num: string;
        user_photo: string | null;
        module_permissions: number[] | null;
        address: {
            id: number;
            address_line_1: string;
            address_line_2: string | null;
            city: string;
            state: string;
            zip: string;
            country: string;
        } | null;
    };
    loanModules: Array<{
        id: number;
        name: string;
        description: string;
        logo?: string;
        status?: string;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'User Management',
        href: '/admin/users',
    },
    {
        title: 'User Details',
        href: `/admin/users/${props.user.id}`,
    },
];

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'admin':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'agent':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'customer':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'sub agent':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'inactive':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const formatAddress = (address: typeof props.user.address) => {
    if (!address) return 'No address provided';
    
    let formattedAddress = address.address_line_1;
    if (address.address_line_2) formattedAddress += `, ${address.address_line_2}`;
    formattedAddress += `, ${address.city}, ${address.state} ${address.zip}, ${address.country}`;
    
    return formattedAddress;
};

// Helper function to check if a module is permitted
const isModulePermitted = (moduleId: number) => {
    return props.user.module_permissions?.includes(moduleId) || false;
};
</script>

<template>
    <Head title="User Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">User Details</h1>
                    <p class="text-gray-600 dark:text-gray-300">View detailed information about this user</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link :href="route('users.management.index')" class="flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600">
                        <ArrowLeftIcon class="h-4 w-4" />
                        <span>Back to List</span>
                    </Link>
                    <Link :href="route('users.management.edit', { id: user.id })" class="flex items-center justify-center gap-2 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <PencilIcon class="h-4 w-4" />
                        <span>Edit User</span>
                    </Link>
                </div>
            </div>
            
            <!-- User Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                        <div class="flex-shrink-0 h-24 w-24 rounded-full bg-blue-500 flex items-center justify-center text-white text-3xl font-bold overflow-hidden">
                            <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" :alt="user.name" class="h-full w-full object-cover" />
                            <template v-else>
                                {{ user && user.name ? user.name.charAt(0).toUpperCase() : '?' }}
                            </template>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ user.name || 'No Name Provided' }}</h2>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="px-3 py-1 text-sm rounded-full" :class="getRoleBadgeClass(user.role || '')">
                                    {{ user.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1, 4) + (user.role.length > 4 ? user.role.charAt(4).toUpperCase() + user.role.slice(5) : '')) : 'Unknown' }}
                                </span>
                                <span class="px-3 py-1 text-sm rounded-full" :class="getStatusBadgeClass(user.status || '')">
                                    {{ user.status ? (user.status.charAt(0).toUpperCase() + user.status.slice(1)) : 'Unknown' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
            <!-- User Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- Left Column - Basic Information -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Basic Information</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.username }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.email }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.phone_num }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">IC Number</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.ic_num }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Bank Information</h3>
                        <div class="space-y-3">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bank Name</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.bank_name || 'Not provided' }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Bank Account</div>
                                <div class="mt-1 text-gray-900 dark:text-white">{{ user.bank_account || 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Permissions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Module Permissions</h3>
                        <div v-if="user.module_permissions && user.module_permissions.length > 0" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="module in loanModules" :key="module.id" class="flex items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <span 
                                            class="inline-flex h-5 w-5 items-center justify-center rounded-full" 
                                            :class="isModulePermitted(module.id) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'"
                                        >
                                            <svg v-if="isModulePermitted(module.id)" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ module.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ module.description }}</p>
                                        <div class="mt-1 flex items-center">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100': module.status === 'Active',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100': module.status === 'Inactive',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100': module.status === 'Pending'
                                                    }">
                                                {{ module.status || 'Unknown' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 dark:text-gray-400 italic">No module permissions assigned</div>
                    </div>
                </div>
                
                <!-- Right Column - Address Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Address Information</h3>
                    <div v-if="user.address" class="space-y-3">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Address Line 1</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.address_line_1 }}</div>
                        </div>
                        <div v-if="user.address.address_line_2">
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Address Line 2</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.address_line_2 }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">City</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.city }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">State</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.state }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Zip/Postal Code</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.zip }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</div>
                            <div class="mt-1 text-gray-900 dark:text-white">{{ user.address.country }}</div>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400 italic">No address information provided</div>
                    
                    <!-- Full Address -->
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Full Address</div>
                        <div class="text-gray-900 dark:text-white">{{ formatAddress(user.address) }}</div>
                    </div>
                </div>
            </div>  
        </div>
        <!-- Customer Actions Section -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
        </div>
    </AppLayout>
</template> 