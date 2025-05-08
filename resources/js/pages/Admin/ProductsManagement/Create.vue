<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { XCircleIcon, PlusCircleIcon, MinusCircleIcon } from '@heroicons/vue/24/solid';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
declare function route(name: string, params?: any): string;

const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');
const userStatus = computed(() => (userAny.value as any)?.status || 'not active');



const props = defineProps<{
    module: {
        id: number;
        slug: string;
        name: string;
        description: string;
        logo: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Management',
        href: '#',
    },
    {
        title: 'Modules Management',
        href: '/admin/modules',
    },
    {
        title: props.module.name,
        href: `/admin/modules/${props.module.slug}/products`,
    },
    {
        title: 'Create Product',
        href: `/admin/modules/${props.module.slug}/products/create`,
    },
];

const form = useForm({
    name: '',
    minimum_loan: 1000,
    maximum_loan: 50000,
    rate: [''] as string[],
    tenure_value: '',
    tenure_unit: 'days',
    tenure: '',
});


const addRate = () => {
    form.rate.push('');
};

const removeRate = (index: number) => {
    form.rate.splice(index, 1);
};

const capitalize = (str: string) => str.charAt(0).toUpperCase() + str.slice(1);

const submit = () => {
    // Filter out empty rates
    const validRates = form.rate.filter(rate => rate !== '');
    
    // Update the form with filtered rates
    form.rate = validRates;
    
    // Combine tenure_value and tenure_unit into tenure
    form.tenure = `${form.tenure_value} ${capitalize(form.tenure_unit)}`;
    
    // Submit the form
    form.post(route('products.store', { moduleSlug: props.module.slug }));
};
</script>

<template>
    <Head :title="`Create Product - ${module.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Create Product - {{ module.name }}</h1>
                <p class="text-gray-600 dark:text-gray-300">Add a new loan product to this module</p>
            </div>

            <!-- Module Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="flex flex-col md:flex-row">
                    <div class="md:w-1/4 p-4 flex justify-center items-center bg-gray-50 dark:bg-gray-700 bg-white">
                        <img :src="module.logo" :alt="module.name" class="h-32 w-full object-contain rounded-lg bg-white"
                             @error="(e: Event) => { 
                                if (e.target) {
                                    (e.target as HTMLImageElement).src = '/storage/images/loan-modules/default.png';
                                }
                             }"
                        />
                    </div>
                    <div class="md:w-3/4 p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ module.name }}</h2>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">{{ module.description }}</p>
                        <div class="mt-4">
                            <Link 
                                    :href="route('products.index', { moduleSlug: module.slug })"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Back to Products
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Form -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    v-model="form.name" 
                                    class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    required
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loan Details -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Loan Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Minimum Loan -->
                            <div>
                                <label for="minimum_loan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Minimum Loan Amount</label>
                                <div class=" mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        id="minimum_loan" 
                                        v-model="form.minimum_loan" 
                                        class="px-2 py-2 pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        min="0"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.minimum_loan" class="text-red-500 text-sm mt-1">{{ form.errors.minimum_loan }}</div>
                            </div>
                            
                            <!-- Maximum Loan -->
                            <div>
                                <label for="maximum_loan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maximum Loan Amount</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        id="maximum_loan" 
                                        v-model="form.maximum_loan" 
                                        class=" px-2 py-2 pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        min="0"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.maximum_loan" class="text-red-500 text-sm mt-1">{{ form.errors.maximum_loan }}</div>
                            </div>
                        

                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="block text-sm font-medium text-gray-700 dark:text-gray-300">Interest Rate (%)</h3>
                                    <button 
                                        type="button" 
                                        @click="addRate" 
                                        class="px-2 py-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <PlusCircleIcon class="h-4 w-4 mr-1" />
                                        Add Rate
                                    </button>
                                </div>
                                
                                <div v-for="(rate, index) in form.rate" :key="`rate-${index}`" class="flex items-center gap-2 mb-2">
                                    <input 
                                        type="number" 
                                        step="0.01"
                                        v-model="form.rate[index]" 
                                        class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Enter interest rate"
                                    />
                                    <button 
                                        type="button" 
                                        @click="removeRate(index)" 
                                        class="px-2 py-2 inline-flex items-center p-1 border border-transparent rounded-full text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        :disabled="form.rate.length <= 1"
                                    >
                                        <XCircleIcon class="h-5 w-5" />
                                    </button>
                                </div>
                                <div v-if="form.errors.rate" class="text-red-500 text-sm mt-1">{{ form.errors.rate }}</div>
                            </div>
                            
                            <!-- Tenure -->
                            <div>
                                <label for="tenure" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tenure</label>
                                <div class="flex gap-2 mt-1">
                                    <input 
                                        type="text" 
                                        id="tenure_value" 
                                        v-model="form.tenure_value" 
                                        placeholder="e.g. 2 or 3-4"
                                        class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        required
                                    />
                                    <select
                                        v-model="form.tenure_unit"
                                        class="px-2 py-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        required
                                    >
                                        <option value="days">Days</option>
                                        <option value="weeks">Weeks</option>
                                        <option value="months">Months</option>
                                        <option value="years">Years</option>
                                    </select>
                                </div>
                                <div v-if="form.errors.tenure" class="text-red-500 text-sm mt-1">{{ form.errors.tenure }}</div>
                            </div>
                        </div>
                    </div>
                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link 
                            :href="route('products.index', { moduleSlug: module.slug })"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="form.processing"
                        >
                            Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Customer Actions Section -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
        </div>
    </AppLayout>
</template> 