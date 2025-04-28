<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/solid';
import { PlusIcon } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
declare function route(name: string, params?: any): string;

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');
const userStatus = computed(() => (user.value as any)?.status || 'not active');

const props = defineProps<{
    moduleId: number;
    module: {
        title: string;
        slug: string;
        description: string;
        banner: string;
        interestRate: string;
        minAmount: number;
        maxAmount: number;
        tenure: string;
        status: string;
    };
    applications: Array<{
        id: number;
        customer_id: number;
        product_id: number;
        agent_id: number;
        biro: string;
        banca: string;
        rates: number;
        document_checklist: any[];
        date_received: string | null;
        date_rejected: string | null;
        date_approved: string | null;
        date_disbursed: string | null;
        date_submitted: string | null;
        tenure_applied: number;
        tenure_approved: number;
        amount_applied: number;
        amount_approved: number;
        amount_disbursed: number;
        created_at: string;
        updated_at: string;
        reference_id: string;
        customer_name: string;
        status: string;
    }>;
    isAdmin: boolean;
    modulePermissionValue: boolean;
    flash: {
        success: string;
        referenceId: string;
    };
}>();
console.log(props.modulePermissionValue);
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
        href: `/loan-modules/${props.module.slug}/applications`,
    },
];

const searchQuery = ref('');
const statusFilter = ref('all');
const sortBy = ref('latest');

const statusOptions = [
    { value: 'all', label: 'All Status' },
    { value: 'new', label: 'New' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'ready_to_submit', label: 'Ready to Submit' },
    { value: 'process', label: 'Process' },
    { value: 'pending_agency', label: 'Pending@Agency' },
    { value: 'pending_bank', label: 'Pending@Bank' },
    { value: 'disbursed', label: 'Disbursed' },
    { value: 'delete_request', label: 'Delete Request' }
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
            app.customer_name.toLowerCase().includes(query) ||
            app.reference_id.toLowerCase().includes(query)
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
            if (statusFilter.value === 'process' && app.status === 'Process') return true;
            if (statusFilter.value === 'pending_agency' && app.status === 'Pending@Agency') return true;
            if (statusFilter.value === 'pending_bank' && app.status === 'Pending@Bank') return true;
            if (statusFilter.value === 'delete_request' && app.status === 'Delete Request') return true;
            
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
        case 'Ready to Submit':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100'; 
        case 'Process':
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
    if (application.status === 'Process') return 'Process';
    if (application.status === 'Pending@Agency') return 'Pending@Agency';
    if (application.status === 'Pending@Bank') return 'Pending@Bank';
    if (application.status === 'Delete Request') return 'Delete Request';
    return 'pending';
};

// Add state variables for delete confirmation modal
const showDeleteModal = ref(false);
const applicationToDelete = ref<{id: number, reference_id: string} | null>(null);
const deleteForm = useForm({});

// Add functions to handle delete operation
const confirmDelete = (application: {id: number, reference_id: string}) => {
    applicationToDelete.value = application;
    showDeleteModal.value = true;
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    applicationToDelete.value = null;
};

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');
const referenceId = ref('');

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


// Update the performDelete function to show toast instead of modal for deletion success
const performDelete = () => {
    if (!applicationToDelete.value) return;
    
    deleteForm.delete(route('loan-applications.destroy', { moduleSlug: props.module.slug, applicationId: applicationToDelete.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            applicationToDelete.value = null;
            
            // Show toast notification after successful deletion
            toastMessage.value = 'Application has been successfully deleted.';
            showToast.value = true;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                showToast.value = false;
            }, 5000);
        },
    });
};
</script>

<template>
    <Head :title="`${module.title} Applications`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole !== 'customer' && props.modulePermissionValue === true && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl p-3 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Module Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="relative h-32 sm:h-48">
                    <img :src="module.banner" 
                         :alt="module.title"
                         class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-6 text-white">
                        <h1 class="text-xl sm:text-3xl font-bold">{{ module.title }}</h1>
                        <p class="text-sm sm:text-base text-gray-200 mt-1 sm:mt-2 line-clamp-2">{{ module.description }}</p>
                    </div>
                </div>
                <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Interest Rate</div>
                        <div class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Loan Range</div>
                        <div class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(module.minAmount) }} - {{ formatCurrency(module.maxAmount) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tenure</div>
                        <div class="text-sm sm:text-lg font-semibold text-gray-900 dark:text-white">{{ module.tenure }}</div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="flex flex-col gap-3 sm:flex-row sm:gap-4 sm:items-center justify-between bg-white dark:bg-gray-800 rounded-xl p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 w-full sm:w-auto">
                    <!-- Search -->
                    <div class="relative w-full sm:max-w-xs">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search applications..."
                            class="block w-full pl-9 sm:pl-10 pr-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="relative w-full sm:w-auto sm:min-w-[180px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <FunnelIcon class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="block w-full pl-9 sm:pl-10 pr-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4">
                    <!-- Sort -->
                    <div class="relative w-full sm:w-auto sm:min-w-[160px]">
                        <select
                            v-model="sortBy"
                            class="block w-full px-3 py-1.5 sm:py-2 text-sm border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- New Application Button -->
                    <Link v-if="props.isAdmin"
                        :href="`/loan-modules/${module.slug}/applications/create`"
                        class="flex items-center justify-center w-full sm:w-auto gap-1 sm:gap-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
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
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Applicant
                                </th>
                                <th scope="col" class="hidden md:table-cell px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="hidden sm:table-cell px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Updated
                                </th>
                                <th scope="col" class="relative px-3 sm:px-6 py-2 sm:py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="application in filteredApplications" 
                                :key="application.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white">
                                                {{ application.customer_name }}
                                            </div>
                                            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                                #{{ application.reference_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                    <div class="text-xs sm:text-sm text-gray-900 dark:text-white">{{ formatDate(application.created_at) }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white">{{ formatCurrency(application.amount_applied) }}</div>
                                </td>
                                <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                    <StatusBadge :status="application.status" />
                                </td>
                                <td class="hidden sm:table-cell px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(application.updated_at) }}
                                </td>
                                <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-right text-xs sm:text-sm font-medium">
                                    <div class="flex justify-end space-x-2 sm:space-x-3">
                                        <Link v-if="props.isAdmin" :href="`/loan-modules/${module.slug}/applications/${application.reference_id}`" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            View
                                        </Link>
                                        <Link v-else :href="`/show-application/${application.reference_id}`" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            View
                                        </Link>
                                        <button 
                                            v-if="props.isAdmin" 
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
            </div>

            <!-- No Results Message -->
            <div v-if="filteredApplications.length === 0" class="text-center py-8 sm:py-12">
                <div class="text-sm sm:text-base text-gray-500 dark:text-gray-400">No applications found matching your criteria.</div>
            </div>
            
            <!-- Mobile fixed action button -->
            <div class="sm:hidden fixed bottom-6 right-6">
                <Link 
                    :href="`/loan-modules/${moduleId}/applications/create`"
                    class="flex items-center justify-center p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <PlusIcon class="h-6 w-6" />
                </Link>
            </div>
        </div>

        <!-- Customer Actions Section -->
        <div v-if="userRole === 'customer' || props.modulePermissionValue === false" class="text-center py-12">
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
    
    <!-- Success modal -->
    <SuccessModal
        :show="showSuccessModal"
        title="Application Submitted Successfully"
        :message="successMessage"
        :details="successDetails"
        @close="closeSuccessModal"
    />
    
    <!-- Delete confirmation modal -->
    <DeleteConfirmationModal
        :show="showDeleteModal"
        message="Are you sure you want to delete this application?"
        :item-identifier="applicationToDelete?.reference_id"
        :is-processing="deleteForm.processing"
        @confirm="performDelete"
        @cancel="cancelDelete"
    />
</template>

<style scoped>
/* Responsive styles */
@media (max-width: 640px) {
    .status-badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
    }
}

/* Animation styles from dashboard */
.bg-white {
    opacity: 0;
    animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Apply sequential delay to grid items */
.bg-white:nth-child(1) { animation-delay: 0.1s; }
.bg-white:nth-child(2) { animation-delay: 0.2s; }
.bg-white:nth-child(3) { animation-delay: 0.3s; }
.bg-white:nth-child(4) { animation-delay: 0.4s; }
</style> 