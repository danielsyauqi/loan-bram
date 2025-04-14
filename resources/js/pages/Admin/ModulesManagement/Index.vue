<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { MagnifyingGlassIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/solid';
import { ListBulletIcon, PlusCircleIcon } from '@heroicons/vue/24/outline';
import { type SharedData } from '@/types';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { useForm } from '@inertiajs/vue3';

declare function route(name: string, params?: any): string;


const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');


const props = defineProps<{
    modules: Array<{
        id: number;
        name: string;
        description: string;
        logo: string;
        status: string;
        created_at: string;
        products_count: number;
        slug: string;
    }>;
    flash: {
        success: string;
        moduleName: string;
    };
}>();

const csrf_token = page.props.csrf_token;

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
];

const searchQuery = ref('');

const filteredModules = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return props.modules.filter(module => 
        module.name.toLowerCase().includes(query) ||
        module.description.toLowerCase().includes(query)
    );
});

const confirmDelete = (id: number, name: string) => {
    moduleToDelete.value = { id, name };
    showDeleteModal.value = true;
};

const showDeleteModal = ref(false);
const moduleToDelete = ref<{ id: number; name: string } | null>(null);
const toastMessage = ref('');
const showToast = ref(false);
const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');
const deleteForm = useForm({});

const closeToast = () => {
    showToast.value = false;
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

const moduleName = ref('');

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };

    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            successMessage.value = String(flash.success);

            if ('moduleName' in flash && flash.moduleName) {
                moduleName.value = String(flash.moduleName);
                successDetails.value = `Module Name: ${moduleName.value}`;
                showSuccessModal.value = true;
            } else {
                toastMessage.value = successMessage.value;
                showToast.value = true;

                setTimeout(() => {
                    showToast.value = false;
                }, 5000);
            }
        }
    }
});



const performDelete = (id: number) => {
    if (!moduleToDelete.value) return;
    
    deleteForm.delete(route('modules.management.destroy', { id: moduleToDelete.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            moduleToDelete.value = null;

            toastMessage.value = 'Module has been successfully deleted.';
            showToast.value = true;

            setTimeout(() => {
                showToast.value = false;    
            }, 5000);
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;  
    moduleToDelete.value = null;
};









</script>

<template>
    <Head title="Modules Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Modules Management</h1>
                    <p class="text-gray-600 dark:text-gray-300">Manage loan modules and their settings</p>
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
                            placeholder="Search modules..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Add Module Button -->
                    <Link 
                        :href="route('modules.management.create')"
                        class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" />
                        <span>Add Module</span>
                    </Link>
                </div>
            </div>

            <!-- Modules Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Mobile View (visible on small screens) -->
                <div class="block md:hidden">
                    <div v-for="module in filteredModules" :key="module.id" class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img :src="module.logo" :alt="module.name" class="h-12 w-12 rounded-full object-cover" 
                                     @error="(e: Event) => { 
                                        if (e.target) {
                                            (e.target as HTMLImageElement).src = '/storage/images/loan-modules/default.png';
                                        }
                                     }"
                                />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ module.name }}</div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          :class="module.status === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'">
                                        {{ module.status }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('products.index', { moduleSlug: module.slug })" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="View Products">
                                    <ListBulletIcon class="h-5 w-5" />
                                </Link>
                                <Link :href="route('products.create', { moduleSlug: module.slug })" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Add Product">
                                    <PlusCircleIcon class="h-5 w-5" />
                                </Link>
                                <Link :href="route('modules.management.edit', { moduleSlug: module.slug })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Edit Module">
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button @click="confirmDelete(module.id, module.name)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete Module">
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-300 mb-3 line-clamp-2">{{ module.description }}</div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Products:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ module.products_count }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Created:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">{{ module.created_at }}</span>
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
                                    Logo
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Products
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
                            <tr v-for="module in filteredModules" :key="module.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img :src="module.logo" :alt="module.name" class="h-10 w-10 rounded-full object-cover" 
                                         @error="(e: Event) => { 
                                            if (e.target) {
                                                (e.target as HTMLImageElement).src = '/storage/images/loan-modules/default.png';
                                            }
                                         }"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ module.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-300 truncate max-w-xs">{{ module.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          :class="module.status === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'">
                                        {{ module.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ module.products_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ module.created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('products.index', { moduleSlug: module.slug })" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="View Products" >
                                            <ListBulletIcon class="h-5 w-5" />
                                        </Link>
                                        <Link :href="route('products.create', { moduleSlug: module.slug })" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Add Product">
                                            <PlusCircleIcon class="h-5 w-5" />
                                        </Link>
                                        <Link :href="route('modules.management.edit', { moduleSlug: module.slug})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button @click="confirmDelete(module.id, module.name)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                        <form :id="`delete-form-${module.id}`" :action="route('modules.management.destroy', { id: module.id })" method="POST" class="hidden">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="_token" :value="csrf_token">
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Results Message -->
            <div v-if="filteredModules.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
                <div class="text-gray-500 dark:text-gray-400">No modules found matching your search.</div>
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
        :item-identifier="moduleToDelete?.name?.toString()"
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