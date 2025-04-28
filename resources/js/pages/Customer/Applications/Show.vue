<script setup lang="ts">
import { dateUtils, formatCurrency as formatCurrencyUtil, arrayUtils } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ArrowLeftIcon, DocumentTextIcon, UserGroupIcon, CalendarIcon, BanknotesIcon, UserCircleIcon, BriefcaseIcon } from '@heroicons/vue/24/solid';
import { reactive, onMounted, ref, onUnmounted, watch, computed } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import EditWorkflowModal from '@/components/Modals/EditWorkflowModal.vue';
import ConfirmationModal from '@/components/Modals/ConfirmationModalTemplate.vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import axios from 'axios';
import { WorkflowIcon } from 'lucide-vue-next'; 

declare function route(name: string, params?: any): string;


// Ensure correct typing for usePage - simplified
const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');
const userStatus = computed(() => (user.value as any)?.status || 'not active');

// Register components
const components = {
  QuillEditor,
  ErrorModal,
  SuccessModal,
  ConfirmationModal,
  EditWorkflowModal,
  ToastNotification
};

// Add default values to the props
const props = withDefaults(defineProps<{
    application?: any;
    module?: {
        title: string;
        description: string;
        interestRate: string;
        minAmount: number;
        maxAmount: number;
        processingTime: string;
        status: string;
        banner: string;
        tenure: string;
    };
    product?: any;
    agents?: any;
    customer?: any;
    address?: any;
    employment?: any;
    salary?: any;
    sub_agent?: any;
    redemption?: any;
    workflow_remarks?: any;
    currentSubAgent?: any;
    companyAddress?: any;
}>(), {
    moduleId: 0,
    module: () => ({
        title: 'Loan',
        description: '',
        banner: '',
        interestRate: '0%',
        minAmount: 1000,
        maxAmount: 10000,
        processingTime: '',
        status: '',
        tenure: '',
    }),
    flash: () => ({
        success: '',
        referenceId: '',
        error: '',
    }),
    autosaveNotifications: false,
});


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/customer-dashboard',
    },
    {
        title: 'Application Details',
        href: '/customer-dashboard',
    },
    {
        title: props.application.reference_id,
        href: '/customer-dashboard',
    },
];

// Format currency
const formatCurrency = (amount: number | string) => {
    return new Intl.NumberFormat('ms-MY', {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount));
};

// Add a safe sorting function for workflow items
const getSortedWorkflowRemarks = () => {
  if (!props.workflow_remarks || !Array.isArray(props.workflow_remarks)) {
    return [];
  }
  
  return [...props.workflow_remarks].sort((a, b) => {
    const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
    const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
    return dateB - dateA; // Sort by most recent first
  });
};

// Add tabs for better organization
const activeTab = ref('details');

// Convert application status to class for styling the progress bar
const getStatusClass = (status: string): string => {
  switch(status.toLowerCase()) {
    case 'new': return 'bg-blue-500';
    case 'processing': return 'bg-yellow-500';
    case 'approved': return 'bg-green-500';
    case 'disbursed': return 'bg-purple-500';
    case 'rejected': return 'bg-red-500';
    case 'delete request': return 'bg-red-500';
    default: return 'bg-gray-500';
  }
};

// Calculate the progress based on status
const getProgressPercentage = (status: string): string => {
  switch(status.toLowerCase()) {
    case 'new': return '20%';
    case 'processing': return '40%';
    case 'approved': return '75%';
    case 'disbursed': return '100%';
    case 'rejected': return '100%';
    case 'delete request': return '100%';
    default: return '0%';
  }
};

// Helper functions to compute totals safely
const getTotalIncome = (userData: any): string => {
  const incomeData = arrayUtils.safeJsonParse(userData?.income, []);
  return arrayUtils.sumAmounts(incomeData).toFixed(2);
};

const getTotalDeduction = (userData: any): string => {
  const deductionData = arrayUtils.safeJsonParse(userData?.deduction, []);
  return arrayUtils.sumAmounts(deductionData).toFixed(2);
};

const getNettIncome = (userData: any): string => {
  const totalIncome = parseFloat(getTotalIncome(userData));
  const totalDeduction = parseFloat(getTotalDeduction(userData));
  return (totalIncome - totalDeduction).toFixed(2);
};

// Group documents for better organization
const documentGroups = {
  personal: ['Original Payslip', 'Copy Payslip', 'IC Copy'],
  employment: ['HRMIS Report', 'ANM Password', 'QR Code Payslip', 'Confirmation Letter']
};

// Assign sub-agent
console.log(props.sub_agent);
const selectedSubAgent = ref('');
const subAgentsList = ref(props.sub_agent);

const assignSubAgent = () => {
  if (!selectedSubAgent.value) {
    console.error('No sub-agent selected');
    toastMessage.value = 'Please select a sub-agent before assigning';
    showToast.value = true;
    return;
  }
  
  axios.post(route('assign-sub-agent', {
    referenceId: props.application.reference_id,
    subagentId: selectedSubAgent.value
  }))
  .then(response => {
    if (response.data.success) {
      // Handle success
      console.log('Sub-agent assigned successfully');
      toastMessage.value = 'Sub-agent assigned successfully';
      showToast.value = true;
      router.reload();
    } else {
      // Handle error
      const errorMsg = response.data.message || 'Unknown error occurred';
      console.error('Error assigning sub-agent:', errorMsg);
      toastMessage.value = `Failed to assign sub-agent: ${errorMsg}`;
      showToast.value = true;
    }
  })
  .catch(error => {
    // Detailed error logging
    const errorDetails = {
      message: error.response?.data?.message || error.message || 'Network error',
      status: error.response?.status,
      statusText: error.response?.statusText,
      url: error.config?.url,
      method: error.config?.method,
      data: error.config?.data,
      referenceId: props.application.reference_id,
      subAgentId: selectedSubAgent.value
    };
    
    console.error('Error assigning sub-agent:', errorDetails);
    toastMessage.value = `Failed to assign sub-agent: ${errorDetails.message}`;
    showToast.value = true;
  });
};

// Add toast notification state
const showToast = ref(false);
const toastMessage = ref('');

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };


    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            toastMessage.value = String(flash.success);
            showToast.value = true;
        }
    }
});


</script>

<template>
    <Head :title="'Application Details'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'customer' || userRole === 'agent' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header with Application Status Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 transition-all duration-300">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <Link :href="`/customer-dashboard`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                            <ArrowLeftIcon class="h-4 w-4" />
                            <span>Back to Dashboard</span>
                        </Link>
                    </div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-2 flex items-center gap-2">
                            Application #{{ application.reference_id }} 
                            <StatusBadge :status="application.status" />
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300">{{ module.title }} - {{ formatCurrency(application.amount_applied) }}</p>
            </div>

                    <div class="text-right">
                        <div class="md:text-lg font-medium text-gray-800 dark:text-white">
                            Submitted: {{ application.date_received ? dateUtils.formatDate(application.date_received) : 'Pending' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            Product: {{ product.name ? product.name : 'Pending' }}
                        </div>
                    </div>
                            </div>
                            
                <!-- Progress Bar -->
                <div class="mb-2 mt-8">
                    <div class="flex justify-between mb-1 text-xs font-medium">
                        <span>Application Progress</span>
                        <span>{{ application.status }}</span>
                            </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="h-2.5 rounded-full transition-all duration-500" 
                             :class="getStatusClass(application.status)"
                             :style="{ width: getProgressPercentage(application.status) }"></div>
                            </div>
                            </div>
                            
                <!-- Progress Steps -->
                <div class="flex items-center justify-between w-full mt-4">
                    <div class="relative flex flex-col items-center">
                        <div class="rounded-full h-8 w-8 flex items-center justify-center z-10 border-2 text-xs font-semibold"
                             :class="application.status ? 'bg-blue-500 border-blue-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-700'">
                            1
                        </div>
                        <div class="text-xs mt-1 text-center">New</div>
                    </div>
                    <div class="flex-1 h-1 mx-2 bg-gray-200" :class="['processing', 'approved', 'disbursed'].includes(application.status.toLowerCase()) ? 'bg-blue-500' : ''"></div>
                    
                    <div class="relative flex flex-col items-center">
                        <div class="rounded-full h-8 w-8 flex items-center justify-center z-10 border-2 text-xs font-semibold"
                            :class="['processing', 'approved', 'disbursed'].includes(application.status.toLowerCase()) ? 'bg-yellow-500 border-yellow-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-700'">
                            2
                        </div>
                        <div class="text-xs mt-1 text-center">Processing</div>
                    </div>
                    <div class="flex-1 h-1 mx-2 bg-gray-200" :class="['approved', 'disbursed'].includes(application.status.toLowerCase()) ? 'bg-blue-500' : ''"></div>
                    
                    <div class="relative flex flex-col items-center">
                        <div class="rounded-full h-8 w-8 flex items-center justify-center z-10 border-2 text-xs font-semibold"
                            :class="['approved', 'disbursed'].includes(application.status.toLowerCase()) ? 'bg-green-500 border-green-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-700'">
                            3
                        </div>
                        <div class="text-xs mt-1 text-center">Approved</div>
                                    </div>
                    <div class="flex-1 h-1 mx-2 bg-gray-200" :class="application.status.toLowerCase() === 'disbursed' ? 'bg-blue-500' : ''"></div>
                    
                    <div class="relative flex flex-col items-center">
                        <div class="rounded-full h-8 w-8 flex items-center justify-center z-10 border-2 text-xs font-semibold"
                            :class="application.status.toLowerCase() === 'disbursed' ? 'bg-purple-500 border-purple-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-700'">
                            4
                        </div>
                        <div class="text-xs mt-1 text-center">Disbursed</div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button v-if="userRole === 'agent'"
                        @click="activeTab = 'customer'" 
                        :class="[
                            'px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'customer' 
                                ? 'text-blue-600 border-b-2 border-blue-500' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Customer Details
                    </button>
                    <button 
                        @click="activeTab = 'details'" 
                        :class="[
                            'px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'details' 
                                ? 'text-blue-600 border-b-2 border-blue-500' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Application Details
                    </button>
                    <button v-if="userRole === 'agent'"
                        @click="activeTab = 'workflow'" 
                        :class="[
                            'px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'workflow' 
                                ? 'text-blue-600 border-b-2 border-blue-500' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Workflow History
                    </button>
                    <button 
                        @click="activeTab = 'documents'" 
                        :class="[
                            'px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'documents' 
                                ? 'text-blue-600 border-b-2 border-blue-500' 
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Documents
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Customer Details Tab -->
                    <div v-if="activeTab === 'customer'" class="space-y-8">
                        <!-- Customer Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <UserCircleIcon class="h-7 w-7 text-blue-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Customer Details</h3>
                                </div>
                                <ul class="mt-3 space-y-2">
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Name:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ customer.name }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Email:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ customer.email }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Phone:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ customer.phone_num }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Address:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ address.address_line_1 }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Postcode:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ address.zip }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">State:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ address.state }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Country:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ address.country }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Bank:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ customer.bank_name }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Account No:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ customer.bank_account }}</span>
                                    </li>
                                </ul>
                                </div>
                                
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <BriefcaseIcon class="h-7 w-7 text-orange-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Employment Details</h3>
                                </div>
                                <ul class="mt-3 space-y-2">
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Company Name:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.company_name }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Job Title:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.job_title }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Employment Type:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.emp_type }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Company Phone Number:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.phone_num }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Company Bank:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.bank }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Company Bank Account Number:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.account_num }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Pension:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ employment.pension }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Address:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ companyAddress.address_line_1 + ', ' + companyAddress.address_line_2 }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Postcode:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ companyAddress.zip }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">State:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ companyAddress.state }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="customer-details-item text-gray-500 dark:text-gray-400">Country:</span>
                                        <span class="customer-details-item font-medium text-gray-900 dark:text-white px-2">{{ companyAddress.country }}</span>
                                    </li>
                                    
                                </ul>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <BanknotesIcon class="h-7 w-7 text-green-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Salary Details</h3>
                                </div>
                            <div class="mt-8 space-y-6">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Month</p>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ dateUtils.getMonthName((salary)?.month || '') }}</p>
                                            </div>
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Year</p>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ (salary)?.year }}</p>
                                            </div>
                                        <div class="col-span-1 md:col-span-2">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Income & Deduction Summary</p>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                    <thead class="bg-gray-100 dark:bg-gray-800">
                                                        <tr>
                                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Income</th>
                                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deduction</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                        <tr class="border border-gray-200 dark:border-gray-600">
                                                            <td class="px-3 text-sm font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                                                                <div v-if="(salary)?.income">
                                                                    <div v-for="(item, index) in arrayUtils.safeJsonParse((salary)?.income)" :key="index" class="border-b border-gray-100 dark:border-gray-600 last:border-0 py-1">
                                                                        {{ item.label }}: RM {{ item.amount }}
                                        </div>
                                            </div>
                                                            </td>
                                                            <td class="px-3 text-sm font-medium text-gray-900 dark:text-white">
                                                                <div v-if="(salary)?.deduction">
                                                                    <div v-for="(item, index) in arrayUtils.safeJsonParse((salary)?.deduction)" :key="index" class="border-b border-gray-100 dark:border-gray-600 last:border-0 py-1">
                                                                        {{ item.label }}: RM {{ item.amount }}
                                        </div>
                                            </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot class="bg-gray-100 dark:bg-gray-800">
                                                        <tr>
                                                            <td class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Income: RM {{ getTotalIncome((salary) || {}) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Deduction: RM {{ getTotalDeduction((salary) || {}) }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                        </div>
                                </div>
                                <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Nett Income</p>
                                            <p class="font-medium text-gray-900 dark:text-white">RM {{ getNettIncome((salary) || {}) }}</p>
                                </div>

                                <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Attachments</p>
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                    <a v-if="(salary)?.attachements" 
                                                :href="(salary)?.attachements" 
                                                target="_blank" 
                                                class="text-blue-500 hover:underline">
                                                    View Attachment
                                                </a>
                                                <span v-else>No attachment available</span>
                                            </p>
                                        </div>
                                        
                                    </div>
                                </div>

                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                    <div class="flex items-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank h-5 w-5 mr-3 text-purple-500" viewBox="0 0 16 16">
                                        <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Redemption Details</h3>
                                </div>

                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bank/Coop</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expiry Date</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Redemption Amount</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Installment</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remark</th>
                                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attachments</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                            <template v-if="application?.redemptions?.length > 0">
                                                <tr v-for="(redemption, index) in application.redemptions" :key="index">
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.bank_name || 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.expiry_date || 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.redemption_amount ? 'RM ' + redemption.redemption_amount : 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.monthly_installment ? 'RM ' + redemption.monthly_installment : 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.remark || 'N/A' }}</td>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                                        <a v-if="redemption?.redemption_attachment" :href="redemption?.redemption_attachment" target="_blank" class="text-blue-500 hover:underline">View Attachment</a>
                                                        <span v-else>No attachment available</span>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr v-else-if="application">
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ application?.bank_name || 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ application?.expiry_date || 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ application?.redemption_amount ? 'RM ' + application.redemption_amount : 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ application?.monthly_installment ? 'RM ' + application.monthly_installment : 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ application?.remark || 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                                    <a v-if="application?.redemption_attachment" :href="application?.redemption_attachment" target="_blank" class="text-blue-500 hover:underline">View Attachment</a>
                                                    <span v-else>No attachment available</span>
                                                </td>
                                            </tr>
                                            <tr v-if="!application">
                                                <td colspan="6" class="px-3 py-2 text-center text-sm text-gray-500 dark:text-gray-400">No redemption information available</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                </div>

                            </div>
                            
                            
                        </div>
                    <!-- Application Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-8">
                        <!-- Application Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Loan Details Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <DocumentTextIcon class="h-5 w-5 text-blue-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Loan Details</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Module:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ module.title ?? 'Pending' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Product:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ product.name ?? 'Pending' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Rate:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.rates ?? '0' }}%</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Tenure:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.tenure_applied ?? '0'}} years</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Biro:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.biro ?? 'Pending' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Banca:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.banca ?? 'Pending' }}</span>
                                    </li>
                                </ul>
                                </div>

                            <!-- Financial Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <BanknotesIcon class="h-5 w-5 text-green-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Financial</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Amount Applied:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.amount_applied ? formatCurrency(application.amount_applied) : 'Pending' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Amount Approved:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ application.amount_approved ? formatCurrency(application.amount_approved) : 'Pending' }}
                                        </span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Amount Disbursed:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ application.amount_disbursed ? formatCurrency(application.amount_disbursed) : 'Pending' }}
                                        </span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Tenure Approved:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ application.tenure_approved ? application.tenure_approved + ' years' : 'Pending' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Timeline Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                                <div class="flex items-center mb-4">
                                    <CalendarIcon class="h-5 w-5 text-purple-500 mr-2" />
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Timeline</h3>
                                </div>
                                <ul class="space-y-3">
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Received:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ application.date_received || 'Pending' }}</span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Approved:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ application.date_approved || 'Pending' }}
                                        </span>
                                    </li>
                                    <li class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Disbursed:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ application.date_disbursed || 'Pending' }}
                                        </span>
                                    </li>
                                    <li class="flex justify-between" v-if="application.date_rejected">
                                        <span class="text-gray-500 dark:text-gray-400">Rejected:</span>
                                        <span class="font-medium text-red-600 dark:text-red-400">
                                            {{ application.date_rejected }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                                </div>

                        <!-- Agent Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                            <div class="flex items-center mb-4">
                                <UserGroupIcon class="h-5 w-5 text-indigo-500 mr-2" />
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Assigned Agent</h3>
                            </div>
                            <div class="flex items-center">
                                <div class="flex items-center mr-10">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500">
                                        {{ agents?.name?.charAt(0) || 'A' }} 
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ agents?.name || 'Not assigned' }} (Master Agent)</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ agents?.email }}</div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500">
                                        {{ agents?.name?.charAt(0) || 'A' }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ currentSubAgent?.name || 'Not assigned' }} (Sub Agent)</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ currentSubAgent?.email }}</div>

                                        <div v-if="!application.agent_id || !application.sub_agent_id && userRole === 'agent'" class="mt-2">
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                                <select 
                                                    v-model="selectedSubAgent" 
                                                    class="px-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm"
                                                >
                                                    <option value="" disabled selected>Select a sub-agent</option>
                                                    <option v-for="agent in subAgentsList" :key="agent.id " :value="agent.id">
                                                        {{ agent.name }}
                                                    </option>
                                                </select>
                                                <button 
                                                    type="button" 
                                                    @click="assignSubAgent" 
                                                    class="mt-2 sm:mt-0 sm:ml-2 px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto"
                                                    :disabled="!selectedSubAgent"
                                                >
                                                    Assign
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <!-- Workflow History Tab -->
                    <div v-if="activeTab === 'workflow'" class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Workflow Timeline</h3>
                            
                            <div v-if="getSortedWorkflowRemarks().length === 0" class="text-center py-6 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                <div class="text-gray-500 dark:text-gray-400">No workflow remarks available</div>
                            </div>
                            
                            <div v-else class="relative">
                                                <!-- Timeline line -->
                                                <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-blue-200 dark:bg-blue-700"></div>
                                                
                                                    <!-- Timeline items -->
                                                    <div v-for="(item, index) in getSortedWorkflowRemarks()" 
                                                     :key="index" 
                                    class="relative pl-10 pb-6 last:pb-0">
                                                    <!-- Timeline dot -->
                                    <div class="absolute left-3 top-1 h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center -ml-2.5 -mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    
                                                    <!-- Timeline content -->
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4 border border-gray-200 dark:border-gray-600 shadow-sm">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ item.user || 'System' }}
                                                </span>
                                                <StatusBadge :status="item.status" size="sm" />
                                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ item.created_at ? dateUtils.formatDate(item.created_at, 'DD MMMM YYYY, HH:mm') : 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="prose dark:prose-invert max-w-none ql-editor text-sm border-t border-gray-200 dark:border-gray-600 pt-2" v-html="item.remarks"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <!-- Documents Tab -->
                    <div v-if="activeTab === 'documents'" class="space-y-6">
                        <!-- Document Checklist -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Document Checklist</h3>
                            
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Personal Documents</h4>
                                <ul class="space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <li v-for="doc in documentGroups.personal" :key="doc" class="flex items-center">
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                :checked="application.document_checklist && application.document_checklist.includes(doc)"
                                                disabled
                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded mr-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                            />
                                            <span :class="[
                                                'ml-2', 
                                                application.document_checklist && application.document_checklist.includes(doc) 
                                                    ? 'text-gray-900 dark:text-white' 
                                                    : 'text-gray-500 dark:text-gray-400'
                                            ]">{{ doc }}</span>
                    </div>
                                        <span v-if="application.document_checklist && application.document_checklist.includes(doc)" 
                                              class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                            Received
                                        </span>
                                    </li>
                                </ul>
                        </div>
                        
                        <div>
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Employment Documents</h4>
                                <ul class="space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <li v-for="doc in documentGroups.employment" :key="doc" class="flex items-center">
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                :checked="application.document_checklist && application.document_checklist.includes(doc)"
                                                disabled
                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded mr-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                            />
                                            <span :class="[
                                                'ml-2', 
                                                application.document_checklist && application.document_checklist.includes(doc) 
                                                    ? 'text-gray-900 dark:text-white' 
                                                    : 'text-gray-500 dark:text-gray-400'
                                            ]">{{ doc }}</span>
                        </div>
                                        <span v-if="application.document_checklist && application.document_checklist.includes(doc)" 
                                              class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                            Received
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                    </div> 
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
</template>

<style scoped>
/* Smooth transitions for all interactive elements */
button, a, .tab-content {
    transition: all 0.3s ease;
}

/* Clean up timeline styling */
.timeline-line {
    transition: height 0.5s ease;
}

/* Apply fancy hover effects to cards */
.bg-white {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Dark mode adjustments */
.dark .bg-white:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

/* Media queries for responsiveness */
@media (max-width: 768px) {
    .tab-content {
        padding: 1rem;
    }

    .customer-details-item {
        font-size: 14px;
    }

}
</style>


