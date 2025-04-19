<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/solid';
import { PlusIcon } from 'lucide-vue-next';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';

import { useForm } from '@inertiajs/vue3';
declare function route(name: string, params?: any): string;

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => (user.value as any)?.role || 'user');

const loading = ref(true);
const applicationToDelete = ref<number | null>(null);
const showDeleteModal = ref(false);
const showSuccessModal = ref(false);
const successReferenceId = ref('');
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const props = defineProps<{
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
  permissions: {
    create: boolean;
    edit: boolean;
    delete: boolean;
  };
  moduleSlug: string; 
  flash?: {
    success?: string;
    referenceId?: string;
    error?: string;
  };
}>();

// Use flash messages if available
onMounted(() => {
  loading.value = false;
  
  if (props.flash?.success && props.flash?.referenceId) {
    successReferenceId.value = props.flash.referenceId;
    showSuccessModal.value = true;
  } else if (props.flash?.success) {
    toastMessage.value = props.flash.success;
    toastType.value = 'success';
    showToast.value = true;
  } else if (props.flash?.error) {
    toastMessage.value = props.flash.error;
    toastType.value = 'error';
    showToast.value = true;
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Loan Applications',
    href: '/loan-applications',
  },
];

const searchQuery = ref('');
const statusFilter = ref('all');
const sortBy = ref('latest');
const itemsPerPage = ref(10);
const currentPage = ref(1);

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

// Reset to first page when filters change
watch([searchQuery, statusFilter, sortBy], () => {
  currentPage.value = 1;
});

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

// Pagination logic
const totalPages = computed(() => Math.ceil(filteredApplications.value.length / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => startIndex.value + itemsPerPage.value);

const paginatedApplications = computed(() => {
  return filteredApplications.value.slice(startIndex.value, endIndex.value);
});

const paginationRange = computed(() => {
  const range: (number | string)[] = [];
  const maxVisiblePages = 5;
  
  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) {
      range.push(i);
    }
  } else {
    range.push(1);
    
    if (currentPage.value > 3) {
      range.push('...');
    }
    
    const startPage = Math.max(2, currentPage.value - 1);
    const endPage = Math.min(totalPages.value - 1, currentPage.value + 1);
    
    for (let i = startPage; i <= endPage; i++) {
      range.push(i);
    }
    
    if (currentPage.value < totalPages.value - 2) {
      range.push('...');
    }
    
    range.push(totalPages.value);
  }
  
  return range;
});

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const goToPage = (page: number) => {
  currentPage.value = page;
};

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
    case 'Processing':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
    case 'Pending@Agency':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
    case 'Pending@Bank':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
    case 'Delete Request':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100';
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

// Permissions check helpers
const canCreateApplication = computed(() => {
  return props.isAdmin || props.permissions.create;
});

const canEdit = (application: any) => {
  return props.isAdmin || props.permissions.edit;
};

const canDelete = (application: any) => {
  return props.isAdmin || props.permissions.delete;
};

// Delete application logic
const deleteForm = useForm({});

const confirmDelete = () => {
  if (applicationToDelete.value) {
    deleteForm.delete(route('loan-applications.destroySecond', { moduleSlug: props.moduleSlug, applicationId: applicationToDelete.value }), {
      onSuccess: () => {
        showDeleteModal.value = false;
        applicationToDelete.value = null;
        toastMessage.value = 'Loan application deleted successfully.';
        toastType.value = 'success';
        showToast.value = true;
      },
      onError: (error) => {
        showDeleteModal.value = false;
        applicationToDelete.value = null;
        toastMessage.value = error.message || 'An error occurred while deleting the application.';
        toastType.value = 'error';
        showToast.value = true;
      }
    });
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  applicationToDelete.value = null;
};

const deleteApplication = (id: number) => {
  applicationToDelete.value = id;
  showDeleteModal.value = true;
};

// Show toast for flash messages
onMounted(() => {
    const pageProps = usePage().props.value as SharedData;
    
    // Add null check before accessing flash property
    if (pageProps && pageProps.flash) {
      const flash = pageProps.flash as { success?: string; error?: string };
      
      // Check for flash messages from the server
      if (flash.success) {
        toastMessage.value = flash.success;
        toastType.value = 'success';
        showToast.value = true;
      } else if (flash.error) {
        toastMessage.value = flash.error;
        toastType.value = 'error';
        showToast.value = true;
      }
    }
});

const goToReferenceId = (referenceId: string) => {
  if(props.moduleSlug){
    router.visit(route('loan-modules.applications.show', { moduleSlug: props.moduleSlug, referenceId: referenceId }));
  }else{
    router.visit(route('choose-module', {   referenceId: referenceId }));
  }
};

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Loan Applications" />

    <div class="p-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Loan Applications</h1>
        
        <div class="flex flex-col sm:flex-row gap-3">
          <Link
            v-if="canCreateApplication"
            :href="'/new-application/'"
            class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <PlusIcon class="w-4 h-4" />
            New Application
          </Link>
        </div>
      </div>

      <!-- Filters and search -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="relative flex-grow">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="searchQuery"
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Search by customer name or reference ID"
            />
          </div>

          <div class="flex flex-col sm:flex-row gap-4">
            <div>
              <label for="statusFilter" class="sr-only">Filter by Status</label>
              <div class="relative">
                <select
                  id="statusFilter"
                  v-model="statusFilter"
                  class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 dark:text-white"
                >
                  <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                </div>
              </div>
            </div>

            <div>
              <label for="sortBy" class="sr-only">Sort By</label>
              <select
                id="sortBy"
                v-model="sortBy"
                class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md bg-white dark:bg-gray-700 dark:text-white"
              >
                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Applications list -->
      <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div v-if="loading" class="p-10 flex justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>

        <div v-else-if="filteredApplications.length === 0" class="p-10 text-center">
          <p class="text-gray-500 dark:text-gray-400">No loan applications found.</p>
        </div>

        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Reference ID
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Customer
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Amount
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Date Created
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Last Updated
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="application in paginatedApplications" :key="application.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    {{ application.reference_id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ application.customer_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ formatCurrency(application.amount_applied) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="[getStatusColor(application.status), 'px-2 py-1 text-xs rounded-full']">
                      {{ application.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ formatDate(application.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                    {{ formatDate(application.updated_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Button v-if="props.isAdmin"
                      @click="goToReferenceId(application.reference_id)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-4"
                    >
                      View
                    </Button>
                    <Link v-else
                      :href="route('customer.application.show', { referenceNumber: application.reference_id })"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-4"
                    >
                      View
                    </Link>
                    <button
                      v-if="canDelete(application)"
                      @click="deleteApplication(application.id)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="prevPage"
                :disabled="currentPage === 1"
                :class="[
                  currentPage === 1 ? 'opacity-50 cursor-not-allowed' : '',
                  'relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Previous
              </button>
              <button
                @click="nextPage"
                :disabled="currentPage >= totalPages"
                :class="[
                  currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : '',
                  'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  Showing
                  <span class="font-medium">{{ startIndex + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(endIndex, filteredApplications.length) }}</span>
                  of
                  <span class="font-medium">{{ filteredApplications.length }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="prevPage"
                    :disabled="currentPage === 1"
                    :class="[
                      currentPage === 1 ? 'opacity-50 cursor-not-allowed' : '',
                      'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <template v-for="page in paginationRange" :key="page">
                    <button
                      v-if="page !== '...'"
                      @click="goToPage(Number(page))"
                      :class="[
                        currentPage === page
                          ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-500 text-blue-600 dark:text-blue-200'
                          : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <span
                      v-else
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                      ...
                    </span>
                  </template>
                  <button
                    @click="nextPage"
                    :disabled="currentPage >= totalPages"
                    :class="[
                      currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : '',
                      'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      v-if="showSuccessModal"
      :show="showSuccessModal"
      :message="'Your loan application has been processed successfully.'"
      :reference-id="successReferenceId"
      @close="showSuccessModal = false"
    />

    <!-- Toast Notification -->
    <ToastNotification
      v-if="showToast"
      :show="showToast"
      :message="toastMessage"
      :type="toastType as 'success' | 'error' | 'info' | 'warning'"
      @close="showToast = false"
    />

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmationModal
      v-if="showDeleteModal"
      :show="showDeleteModal"
      :title="'Delete Loan Application'"
      :message="'Are you sure you want to delete this loan application? This action cannot be undone.'"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </AppLayout>
</template>

