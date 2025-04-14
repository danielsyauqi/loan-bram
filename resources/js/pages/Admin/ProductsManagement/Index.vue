<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/solid';
import { type SharedData } from '@/types';
import { useForm } from '@inertiajs/vue3';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';


declare function route(name: string, params?: any): string;


const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');

const closeToast = () => {
    showToast.value = false;
};

const props = defineProps<{
    module: {
        id: number;
        slug: string;
        name: string;
        description: string;
        logo: string;
    };
    products: Array<{
        id: number;
        slug: string;
        name: string;
        minimum_loan: number;
        maximum_loan: number;
        rate: string;
        tenure: string[];
        created_at: string;
    }>;
    flash: {
        success: string;
        productName: string;
    };
}>();

const csrf_token = page.props.csrf_token;
console.log(props.products);

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
];

const showDeleteModal = ref(false);
const productToDelete = ref<{ id: number; name: string } | null>(null);
const toastMessage = ref('');
const showToast = ref(false);

const cancelDelete = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};// Add functions to handle delete operation

const confirmDelete = (product: {id: number, name: string}) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const performDelete = (id: number) => {
    if (!productToDelete.value) return;
    
    deleteForm.delete(route('products.destroy', { moduleSlug: props.module.slug, productId: productToDelete.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            productToDelete.value = null;
            
            // Show toast notification after successful deletion
            toastMessage.value = 'Product has been successfully deleted.';
            showToast.value = true;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                showToast.value = false;
            }, 5000);
        },
    });
};

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');
const closeSuccessModal = () => {
    showSuccessModal.value = false;
};
const productName = ref('');

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };


    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            successMessage.value = String(flash.success);

            if ('productName' in flash && flash.productName) {
                productName.value = String(flash.productName);
                successDetails.value = `Product Name: ${productName.value}`;
                showSuccessModal.value = true;
            } else {
                // If no reference ID, show toast notification
                toastMessage.value = successMessage.value;
                showToast.value = true;

                // Auto-dismiss toast after 5 seconds
                setTimeout(() => {
                    showToast.value = false;
                }, 5000);
            }
        }
    }
});



const searchQuery = ref('');

const filteredProducts = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return props.products.filter(product => 
        product.name.toLowerCase().includes(query)
    );
});

const deleteForm = useForm({});



// Format currency
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
    <Head :title="`Products - ${module.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Products - {{ module.name }}</h1>
                    <p class="text-gray-600 dark:text-gray-300">Manage loan products for this module</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                    <!-- Search Bar -->
                    <div class="relative flex-grow md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search products..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Add Product Button -->
                    <Link 
                        :href="route('products.create', { moduleSlug: module.slug })"
                        class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" />
                        <span>Add Product</span>
                    </Link>
                </div>
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
                                :href="route('modules.management.index')"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Back to Modules
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Mobile View (visible on small screens) -->
                <div class="block md:hidden">
                    <div v-for="product in filteredProducts" :key="product.id" class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ product.name }}</div>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('products.edit', { moduleSlug: module.slug, productSlug: product.slug })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Loan Range:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ formatCurrency(product.minimum_loan) }} - {{ formatCurrency(product.maximum_loan) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Rate:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ product.rate }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Tenure:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ product.tenure }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Created:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ product.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop View (visible on medium screens and up) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Loan Range
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Rate
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tenure
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Created At
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ formatCurrency(product.minimum_loan) }} - {{ formatCurrency(product.maximum_loan) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ Array.isArray(product.rate) ? product.rate.map(rate => rate + '%').join(', ') : product.rate }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ product.tenure }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ product.created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('products.edit', { moduleSlug: module.slug, productSlug: product.slug })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message -->
            <div v-if="filteredProducts.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="text-gray-500 dark:text-gray-400">No products found matching your search.</div>
                <button 
                    @click="searchQuery = ''" 
                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                >
                    Clear Search
                </button>
            </div>
        </div>
        <!-- Customer Actions Section -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
        </div>
    </AppLayout>

        <!-- Toast notification -->
        <ToastNotification
        :show="showToast"
        :message="toastMessage"
        type="success"
        :duration="5000"
        position="top-right"
        @close="closeToast"
    />
    

    <!-- Delete confirmation modal -->
    <DeleteConfirmationModal
        :show="showDeleteModal"
        message="Are you sure you want to delete this product?"
        :item-identifier="productToDelete?.name?.toString()"
        :is-processing="deleteForm.processing"
        @confirm="performDelete"
        @cancel="cancelDelete"
    />

    
    <!-- Success modal -->
    <SuccessModal
        :show="showSuccessModal"
        title="Application Submitted Successfully"
        :message="successMessage"
        :details="successDetails"
        @close="closeSuccessModal"
    />
    
</template> 

