<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/solid';
import { PlusIcon } from 'lucide-vue-next';

const props = defineProps<{
    moduleId: number;
    module: {
        title: string;
        description: string;
        banner: string;
        interestRate: string;
        minAmount: number;
        maxAmount: number;
        processingTime: string;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Loan Modules',
        href: '/loan-modules',
    },
    {
        title: props.module.title,
        href: `/loan-modules/${props.moduleId}/applications`,
    },
];

const searchQuery = ref('');
const statusFilter = ref('all');
const sortBy = ref('latest');

// Sample application data
const applications = [
    {
        id: 1,
        applicantName: 'John Smith',
        applicationDate: '2024-03-04',
        amount: 25000,
        status: 'approved',
        email: 'john.smith@example.com',
        phone: '+1234567890',
        creditScore: 750,
        monthlyIncome: 5000,
        documents: ['ID.pdf', 'PaySlip.pdf', 'BankStatement.pdf'],
        notes: 'All documents verified',
        lastUpdated: '2024-03-06'
    },
    {
        id: 2,
        applicantName: 'Sarah Johnson',
        applicationDate: '2024-03-03',
        amount: 15000,
        status: 'pending',
        email: 'sarah.j@example.com',
        phone: '+1234567891',
        creditScore: 680,
        monthlyIncome: 4200,
        documents: ['ID.pdf', 'PaySlip.pdf'],
        notes: 'Awaiting additional documentation',
        lastUpdated: '2024-03-05'
    },
    {
        id: 3,
        applicantName: 'Michael Brown',
        applicationDate: '2024-03-02',
        amount: 30000,
        status: 'rejected',
        email: 'michael.b@example.com',
        phone: '+1234567892',
        creditScore: 580,
        monthlyIncome: 3800,
        documents: ['ID.pdf', 'PaySlip.pdf', 'BankStatement.pdf'],
        notes: 'Credit score below minimum requirement',
        lastUpdated: '2024-03-04'
    },
];

const statusOptions = [
    { value: 'all', label: 'All Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' }
];

const sortOptions = [
    { value: 'latest', label: 'Latest First' },
    { value: 'oldest', label: 'Oldest First' },
    { value: 'amount-high', label: 'Amount (High to Low)' },
    { value: 'amount-low', label: 'Amount (Low to High)' }
];

const filteredApplications = computed(() => {
    let filtered = [...applications];
    
    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(app => 
            app.applicantName.toLowerCase().includes(query) ||
            app.email.toLowerCase().includes(query) ||
            app.phone.includes(query)
        );
    }
    
    // Apply status filter
    if (statusFilter.value !== 'all') {
        filtered = filtered.filter(app => app.status === statusFilter.value);
    }
    
    // Apply sorting
    switch (sortBy.value) {
        case 'latest':
            filtered.sort((a, b) => new Date(b.applicationDate).getTime() - new Date(a.applicationDate).getTime());
            break;
        case 'oldest':
            filtered.sort((a, b) => new Date(a.applicationDate).getTime() - new Date(b.applicationDate).getTime());
            break;
        case 'amount-high':
            filtered.sort((a, b) => b.amount - a.amount);
            break;
        case 'amount-low':
            filtered.sort((a, b) => a.amount - b.amount);
            break;
    }
    
    return filtered;
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
        case 'rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const formatCurrency = (amount: number | string) => {
    return new Intl.NumberFormat('ms-MY', {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount));
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head :title="`${module.title} Applications`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Module Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="relative h-48">
                    <img :src="module.banner" 
                         :alt="module.title"
                         class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        <h1 class="text-3xl font-bold">{{ module.title }}</h1>
                        <p class="text-gray-200 mt-2">{{ module.description }}</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Interest Rate</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Loan Range</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(module.minAmount) }} - {{ formatCurrency(module.maxAmount) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Processing Time</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.processingTime }}</div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between bg-white dark:bg-gray-800 rounded-xl p-4">
                <div class="flex flex-col md:flex-row gap-4 flex-grow">
                    <!-- Search -->
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search applications..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="relative min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <FunnelIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Sort -->
                    <div class="relative min-w-[200px]">
                        <select
                            v-model="sortBy"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- New Application Button -->
                    <Link 
                        :href="`/loan-modules/${moduleId}/applications/create`"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        <PlusIcon class="h-4 w-4" />
                        <span>New Application</span>
                    </Link>
                </div>
            </div>

            <!-- Applications List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Applicant
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Application Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Updated
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="application in filteredApplications" 
                                :key="application.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ application.applicantName }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ application.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(application.applicationDate) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(application.amount) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          :class="getStatusColor(application.status)">
                                        {{ application.status.charAt(0).toUpperCase() + application.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(application.lastUpdated) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message -->
            <div v-if="filteredApplications.length === 0" class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">No applications found matching your criteria.</div>
            </div>
        </div>
    </AppLayout>
</template> 