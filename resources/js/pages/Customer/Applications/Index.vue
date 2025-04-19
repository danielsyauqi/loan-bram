<script setup lang="ts">
// Add global route function declaration for TypeScript
declare function route(name: string, params?: any): string;

import { dateUtils, formatCurrency as formatCurrencyUtil } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
  MagnifyingGlassIcon, 
  FunnelIcon,
  PlusCircleIcon, 
  ChevronRightIcon,
  EyeIcon,
  DocumentTextIcon,
  ClockIcon
} from '@heroicons/vue/24/solid';
import ToastNotification from '@/components/Modals/ToastNotification.vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);

// Props
const props = defineProps<{
  applications: Array<{
    id: number;
    reference_id: string;
    amount_applied: number;
    amount_approved?: number;
    date_received?: string;
    date_submitted?: string;
    date_approved?: string;
    date_rejected?: string;
    date_disbursed?: string;
    status?: string;
    created_at: string;
    updated_at: string;
    module: any;
    product: any;
  }>;
  flash?: {
    success?: string;
    error?: string;
  };

}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Applications',
    href: '/customer/applications',
  },
];

const searchQuery = ref('');
const statusFilter = ref('all');
const sortBy = ref('latest');

const statusOptions = [
    { value: 'all', label: 'All Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'ready_to_submit', label: 'Ready to Submit' },
    { value: 'process', label: 'Process' },
    { value: 'pending_agency', label: 'Pending@Agency' },
    { value: 'pending_bank', label: 'Pending@Bank' },
    { value: 'delete_request', label: 'Delete Request' },
];

const sortOptions = [
    { value: 'latest', label: 'Latest First' },
    { value: 'oldest', label: 'Oldest First' },
    { value: 'amount-high', label: 'Amount (High to Low)' },
    { value: 'amount-low', label: 'Amount (Low to High)' }
];

const filteredApplications = computed(() => {
    let filtered = [...props.applications];
    
    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(app => 
            app.reference_id.toLowerCase().includes(query) ||
            (app.product?.name && app.product.name.toLowerCase().includes(query))
        );
    }
    
    // Apply status filter
    if (statusFilter.value !== 'all') {
        filtered = filtered.filter(app => {
            if (statusFilter.value === 'new' && app.status === 'New') return true;
            if (statusFilter.value === 'pending' && app.status === 'Pending') return true;
            if (statusFilter.value === 'approved' && app.status === 'Approved') return true;
            if (statusFilter.value === 'disbursed' && app.status === 'Disbursed') return true;
            if (statusFilter.value === 'rejected' && app.status === 'Rejected') return true;
            if (statusFilter.value === 'ready_to_submit' && app.status === 'Ready to Submit') return true;
            if (statusFilter.value === 'processing' && app.status === 'Processing') return true;
            if (statusFilter.value === 'delete_request' && app.status === 'Delete Request') return true;
            if (statusFilter.value === 'pending_agency' && app.status === 'Pending@Agency') return true;
            if (statusFilter.value === 'pending_bank' && app.status === 'Pending@Bank') return true;
            
            return false;
        });
    }
    
    // Apply sorting
    if (sortBy.value === 'latest') {
        filtered.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
    } else if (sortBy.value === 'oldest') {
        filtered.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
    } else if (sortBy.value === 'amount-high') {
        filtered.sort((a, b) => b.amount_applied - a.amount_applied);
    } else if (sortBy.value === 'amount-low') {
        filtered.sort((a, b) => a.amount_applied - b.amount_applied);
    }
    
    return filtered;
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'New':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';
        case 'Approved':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'Disbursed':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'Rejected':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        case 'Ready to Submit': //
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100'; 
        case 'Processing':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'Pending@Agency':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'Pending@Bank':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'Delete Request':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
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

// Helper function to determine application status
const getApplicationStatus = (application: any): string => {
    if (application.status === 'New') return 'New';
    if (application.status === 'Pending') return 'Pending';
    if (application.status === 'Approved') return 'Approved';
    if (application.status === 'Disbursed') return 'Disbursed';
    if (application.status === 'Rejected') return 'Rejected';
    if (application.status === 'Ready to Submit') return 'Ready to Submit';
    if (application.status === 'Processing') return 'Processing';
    if (application.status === 'Pending@Agency') return 'Pending@Agency';
    if (application.status === 'Pending@Bank') return 'Pending@Bank';
    if (application.status === 'Delete Request') return 'Delete Request';
    return 'pending';
};

// Add state variables for delete confirmation modal
const showDeleteModal = ref(false);
const applicationToDelete = ref<{id: number, reference_id: string} | null>(null);
const deleteForm = useForm({
  reason: '',
});
const deletionInProgress = ref(false);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');
const referenceId = ref('');

// Add state variables for delete success modal
const showDeleteSuccessModal = ref(false);
const deleteSuccessReferenceId = ref('');

// Add functions to handle delete operation
const confirmDelete = (application: {id: number, reference_id: string}) => {
    applicationToDelete.value = application;
    deleteForm.reset();
    showDeleteModal.value = true;
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    applicationToDelete.value = null;
};

// Function to close delete success modal
const closeDeleteSuccessModal = () => {
    showDeleteSuccessModal.value = false;
};

// Add toast notification state
const showToast = ref(false);
const toastMessage = ref('');

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

// Function to close success modal
const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };


    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            successMessage.value = String(flash.success);

            if ('referenceId' in flash && flash.referenceId) {
                referenceId.value = String(flash.referenceId);
                successDetails.value = `Reference ID: ${referenceId.value}`;
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


// Update the performDelete function to send the reason for deletion
const performDelete = () => {
    if (!applicationToDelete.value) return;
    
    // Validate reason field
    if (!deleteForm.reason) {
        deleteForm.setError('reason', 'Please provide a reason for deletion');
        return;
    }
    
    deletionInProgress.value = true;
    
    deleteForm.delete(route('customer.application.deleteRequest', { referenceNumber: applicationToDelete.value.reference_id, remark: deleteForm.reason }), {
        onSuccess: () => {
            // Store reference ID for success modal
            deleteSuccessReferenceId.value = applicationToDelete.value?.reference_id || '';
            
            // Reset and close delete modal
            showDeleteModal.value = false;
            applicationToDelete.value = null;
            deletionInProgress.value = false;
            
            // Show delete success modal
            showDeleteSuccessModal.value = true;
        },
        onError: () => {
            deletionInProgress.value = false;
        }
    });
};
</script>

<template>
  <Head title="My Applications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-6 bg-gray-50 dark:bg-gray-900 rounded-xl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white">My Loan Applications</h1>
          <p class="text-gray-600 dark:text-gray-300">Track and manage your loan applications</p>
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
                    <Link :href="route('customer.applications.new')" class="inline-flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      <PlusCircleIcon class="h-5 w-5 mr-2" />
                      New Application
                    </Link>
                </div>
            </div>

            <!-- Applications List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Desktop Table (hidden on mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Application ID
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
                                              #{{ application.reference_id }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                              {{ application.module?.name ?? 'Pending' }} - {{ application.product?.name ?? 'Pending' }} 
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ formatDate(application.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(application.amount_applied) }}</div>
                                    <div v-if="application.amount_approved" class="text-xs text-green-600 dark:text-green-400">
                                        Approved: {{ formatCurrency(application.amount_approved) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                          :class="getStatusColor(application.status || 'New')">
                                        {{ (application.status || 'New').charAt(0).toUpperCase() + (application.status || 'New').slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(application.updated_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-3">
                                        <Link :href="route('customer.application.show', { referenceNumber: application.reference_id })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            View Details
                                        </Link>
                                        <button 
                                            v-if="application.status === 'New'"
                                            @click="confirmDelete(application)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                          >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Layout (shown only on mobile) -->
                <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                    <div 
                        v-for="application in filteredApplications" 
                        :key="application.id" 
                        class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">#{{ application.reference_id }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                  {{ application.module?.name ?? 'Pending' }} - {{ application.product?.name ?? 'Pending' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Applied {{ formatDate(application.created_at) }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full" 
                                :class="getStatusColor(application.status || 'New')">
                                {{ (application.status || 'New').charAt(0).toUpperCase() + (application.status || 'New').slice(1) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Amount</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(application.amount_applied) }}</p>
                                <p v-if="application.amount_approved" class="text-xs text-green-600 dark:text-green-400">
                                    Approved: {{ formatCurrency(application.amount_approved) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Last Updated</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formatDate(application.updated_at) }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex space-x-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                            <Link :href="route('customer.application.show', { referenceNumber: application.reference_id })" class="flex-1 text-center py-2 px-2 text-sm rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                <span class="flex items-center justify-center">
                                    <EyeIcon class="h-4 w-4 mr-1" />
                                    View Details
                                </span>
                            </Link>
                            <button 
                                v-if="application.status === 'New'"
                                @click="confirmDelete(application)"
                                class="flex-1 text-center py-2 px-2 text-sm rounded-md bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                            >
                                <span class="flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Delete
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Results Message -->
                <div v-if="filteredApplications.length === 0" class="text-center py-12">
                    <div class="flex flex-col items-center">
                        <DocumentTextIcon class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-4" />
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-1">No applications found</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ searchQuery || statusFilter !== 'all' 
                                ? 'Try adjusting your filters or search terms.' 
                                : 'Start by creating your first loan application.' }}
                        </p>
                        <div v-if="!searchQuery && statusFilter === 'all'" class="mt-4">
                            <Link :href="route('customer.applications.new')" class="inline-flex items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <PlusCircleIcon class="h-5 w-5 mr-2" />
                                New Application
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
      
    </div>
  </AppLayout>
  
  <!-- Toast Notification -->
  <ToastNotification
    :show="showToast"
    :message="toastMessage"
    @close="closeToast"
  />
  
  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteModal" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="cancelDelete"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl transform transition-all p-6">
      <div class="flex flex-col items-center text-center mb-4">
        <div class="rounded-full bg-red-100 p-3 mb-4">
          <svg class="h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Confirm Deletion Request</h3>
        <p class="mt-2 text-base text-gray-600 dark:text-gray-300">
          Are you sure you want to request deletion of this application?
        </p>
        <p v-if="applicationToDelete" class="mt-2 text-sm text-gray-600 dark:text-gray-300">
          Reference #{{ applicationToDelete.reference_id }}
        </p>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Your request will be reviewed by our team within 1-3 working days.
        </p>
      </div>
      
      <div class="mb-4">
        <label for="delete-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Reason for deletion <span class="text-red-500">*</span>
        </label>
        <textarea
          id="delete-reason"
          v-model="deleteForm.reason"
          rows="3"
          placeholder="Please provide a reason for deleting this application..."
          class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          :class="{'border-red-500 focus:ring-red-500': deleteForm.errors.reason}"
        ></textarea>
        <p v-if="deleteForm.errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400">
          {{ deleteForm.errors.reason }}
        </p>
      </div>
      
      <div class="flex space-x-3">
        <button 
          @click="performDelete"
          class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          :disabled="deletionInProgress"
        >
          <svg v-if="deletionInProgress" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ deletionInProgress ? 'Submitting...' : 'Submit Delete Request' }}
        </button>
        <button
          @click="cancelDelete"
          class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :disabled="deletionInProgress"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
  
  <!-- Delete Success Modal -->
  <div v-if="showDeleteSuccessModal" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl transform transition-all p-6">
      <div class="flex flex-col items-center text-center mb-6">
        <div class="rounded-full bg-green-100 p-3 mb-4">
          <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delete Request Submitted</h3>
        <p class="mt-2 text-base text-gray-600 dark:text-gray-300">
          Your application deletion request has been received.
        </p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Reference #{{ deleteSuccessReferenceId }}
        </p>
        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-900/30">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-700 dark:text-blue-300">
                Your request will be processed within <span class="font-semibold">1-3 working days</span>. The application status has been updated to "Delete Request".
              </p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="flex justify-center">
        <button 
          @click="closeDeleteSuccessModal" 
          class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Got it
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.progress-bar-wrapper {
  width: 100%;
  background-color: #edf2f7;
  height: 0.5rem;
  border-radius: 0.25rem;
  overflow: hidden;
}

.progress-bar {
  height: 0.5rem;
  transition: width 0.5s ease-in-out;
}

@media (max-width: 640px) {
  .filters-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
}
</style>
