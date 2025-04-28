<script setup lang="ts">
// Add global route function declaration for TypeScript
declare function route(name: string, params?: any): string;

import { dateUtils, formatCurrency as formatCurrencyUtil } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeftIcon, CheckCircleIcon, UserCircleIcon, BriefcaseIcon, CreditCardIcon } from '@heroicons/vue/24/solid';
import { reactive, ref, computed, watch } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { User } from '@/types';
// Use proper Inertia page props typing with index signature
interface PageProps {
  flash?: {
    success?: string;
    [key: string]: any;
  };
  auth?: {
    user: User;
  };
  [key: string]: any;
}

// Type the page object
const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user);
const userStatus = computed(() => (user.value as any)?.status || 'not active');

// Props
const props = withDefaults(defineProps<{
  customer?: any;
  address?: any;
  loanStatus?: any;
  redemption?: any;
  salary?: any;
  employment?: any;
  employment_address?: any;
  flash?: {
    success?: string;
    error?: string;
  };    
}>(), {});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'New Application',
    href: `/customer/applications/create`,
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

// Define the form object
const form = useForm({
  request_details: '',
  information_correct: false,
  terms_agreed: false,
});



// Update expandedSections object to include all section types
const expandedSections = reactive({
  personalInfo: true,
  addressInfo: true,
  employmentInfo: true,
  userInfo: true,
  salaryInfo: true,
  redemptionInfo: true
});



// Add utility functions
const arrayUtils = {
  safeJsonParse(jsonString?: string) {
    if (!jsonString) return [];
    try {
      return JSON.parse(jsonString);
    } catch (e) {
      console.error('Error parsing JSON:', e);
      return [];
    }
  }
};

// Define missing calculation functions
const getTotalIncome = (salary: any) => {
  if (!salary.income) return '0.00';
  try {
    const incomeItems = arrayUtils.safeJsonParse(salary.income);
    return incomeItems.reduce((total: number, item: any) => {
      return total + parseFloat(item.amount.toString() || '0');
    }, 0).toFixed(2);
  } catch (e) {
    return '0.00';
  }
};

const getTotalDeduction = (salary: any) => {
  if (!salary.deduction) return '0.00';
  try {
    const deductionItems = arrayUtils.safeJsonParse(salary.deduction);
    return deductionItems.reduce((total: number, item: any) => {
      return total + parseFloat(item.amount.toString() || '0');
    }, 0).toFixed(2);
  } catch (e) {
    return '0.00';
  }
};

const getNettIncome = (salary: any) => {
  const totalIncome = parseFloat(getTotalIncome(salary));
  const totalDeduction = parseFloat(getTotalDeduction(salary));
  return (totalIncome - totalDeduction).toFixed(2);
};

// Update toggle section function to handle all section types
const toggleSection = (section: 'personalInfo' | 'addressInfo' | 'employmentInfo' | 'userInfo' | 'salaryInfo' | 'redemptionInfo') => {
  expandedSections[section as keyof typeof expandedSections] = !expandedSections[section as keyof typeof expandedSections];
};

// Modals
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);
const showSuccessModal = ref(false);
const successMessage = ref('Your application has been submitted successfully.');
const referenceId = ref('');

// Submit form
const submitForm = () => {
  // Reset error messages
  errorMessages.value = [];
  
  // Validate form
  let hasErrors = false;
  if(props.loanStatus === false){
    errorMessages.value.push('You need to wait for your latest application to be approved/disbursed by the admin before you can submit your new loan application.');
    hasErrors = true;
  }

  if (!form.request_details) {
    errorMessages.value.push('Please describe your loan request');
    hasErrors = true;
  }
  
  if (!form.information_correct) {
    errorMessages.value.push('Please confirm your information is correct');
    hasErrors = true;
  }
  
  if (!form.terms_agreed) {
    errorMessages.value.push('Please agree to the terms and conditions');
    hasErrors = true;
  }
  
  if (hasErrors) {
    showErrorModal.value = true;
    return;
  }
  
  // Submit the form (backend will be implemented later)
  form.post(route('customer.applications.store'), {
    onSuccess: () => {
      // Show success message
      referenceId.value = page.props.flash?.success?.toString() || '';
      showSuccessModal.value = true;
    },
    onError: (errors) => {
      errorMessages.value = Object.values(errors).flat();
      showErrorModal.value = true;
    }
  });
};

// Close error modal
const closeErrorModal = () => {
  showErrorModal.value = false;
};

// Redirect after success
const redirectToDashboard = () => {
  window.location.href = route('customer.dashboard');
};

const redirectToIdentityForm = () => {
  window.location.href = route('customer.identityForm');
};

const unlockSubmitButtonValue = ref(false);


watch(() => form.information_correct && form.terms_agreed, (newValue) => {
  unlockSubmitButtonValue.value = newValue;
});



</script>

<template>
  <Head :title="`New Loan Application`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-2">New Loan Application</h1>
          <p class="text-gray-600 dark:text-gray-300">Please review your information and submit your application request</p>
        </div>
      </div>

      

      <!-- Application Form -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Form Fields (2/3 width) -->
        <div class="md:col-span-2 space-y-6">

            <div v-if="customer.status === 'verified' || customer.status === 'inactive' || customer.status === 'not verified' || customer.status === 'active'" class="transition-all duration-500 ease-in-out ">
            <div v-if="customer.status === 'verified' || customer.status === 'active'" class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 mb-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 dark:text-green-300">Congratulations! You are eligible for submit loan application.</p>
                    </div>
                </div>
            </div>
            <div v-if="customer.status === 'inactive' || customer.status === 'not verified'" class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 mb-4 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300">You are not eligible for submit loan application. Please complete your <span class="font-bold cursor-pointer underline" @click="redirectToIdentityForm">identity form</span> first.</p>
                    </div>
                </div>
            </div>
        </div>
          <!-- Personal Information -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="space-y-6">
                <h3 @click="toggleSection('userInfo')" class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    User Information
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform transition-transform" :class="{ 'rotate-180': !expandedSections.userInfo }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </h3>
                <div v-show="expandedSections.userInfo" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (user)?.name }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">IC Number</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (user as any)?.ic_num }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (user)?.email }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (user as any)?.phone_num }}</p>
                        </div>
                    <div class="w-full md:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Address</p>
                        <p class="font-medium text-gray-900 dark:text-white break-words">{{ address }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 space-y-6">
                <h3 @click="toggleSection('employmentInfo')" class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-building h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                        <path d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
                        <path d="M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1zm11 0H3v14h3v-2.5a.5.5 0 0 1 .5-.5h3z"/>
                    </svg>
                    Employment Information
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform transition-transform" :class="{ 'rotate-180': !expandedSections.employmentInfo }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </h3>
                <div v-show="expandedSections.employmentInfo" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Company Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.company_name }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Job Title</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.job_title }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Phone Number Employment</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.phone_num }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bank</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.bank }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Account Number</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.account_num }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pension</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ (employment)?.pension }}</p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date Joined</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ (employment)?.date_joined ? 
                                dateUtils.formatDate(String((employment)?.date_joined || ''), 'DD MMMM YYYY') : '' }}
                        </p>
                        </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Employment Type</p>
                        <p class="font-medium text-gray-900 dark:text-white break-words">{{ (employment)?.emp_type }}</p>
                    </div>

                    <div class="w-full md:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Employment Address</p>
                        <p class="font-medium text-gray-900 dark:text-white break-words">{{ employment_address }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 space-y-6">
                    <h3 @click="toggleSection('salaryInfo')" class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                        <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                        <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                        <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
                    </svg>
                    Salary Information
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform transition-transform" :class="{ 'rotate-180': !expandedSections.salaryInfo }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </h3> 
                <div v-show="expandedSections.salaryInfo" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
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
                                <a v-if="(salary)?.attachments" 
                                :href="'/storage/' + (salary)?.attachments" 
                                target="_blank" 
                                class="text-blue-500 hover:underline">
                                View Attachment
                            </a>
                            <span v-else class="font-medium text-gray-900 dark:text-white">No attachments</span>
                        </p>
                    </div>
                    
                </div>
            </div>

            <div class="mt-8 space-y-6">
                    <h3 @click="toggleSection('redemptionInfo')" class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                        <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
                    </svg>
                    Redemption Information
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transform transition-transform" :class="{ 'rotate-180': !expandedSections.redemptionInfo }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </h3> 
                <div v-show="expandedSections.redemptionInfo" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bank/Coop</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expiry Date</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Redemption Amount</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Installment</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remark</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attachments</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                            <template v-if="redemption && redemption.length > 0">
                                <tr v-for="(redemption, index) in redemption" :key="index">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.bank_name || 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.expiry_date || 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.redemption_amount ? 'RM ' + redemption.redemption_amount : 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.monthly_installment ? 'RM ' + redemption.monthly_installment : 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.remark || 'N/A' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        <a v-if="redemption?.redemption_attachment" 
                                            :href="redemption?.redemption_attachment" 
                                            target="_blank" 
                                            class="text-blue-500 hover:underline">
                                            View Attachment
                                        </a>
                                        <span v-else>No attachment available</span>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else-if="redemption">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.bank_name || 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.expiry_date || 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.redemption_amount ? 'RM ' + redemption.redemption_amount : 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.monthly_installment ? 'RM ' + redemption.monthly_installment : 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ redemption?.remark || 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <a v-if="redemption?.redemption_attachment" 
                                        :href="redemption?.redemption_attachment" 
                                        target="_blank" 
                                        class="text-blue-500 hover:underline">
                                        View Attachment
                                    </a>
                                    <span v-else class="text-gray-500 dark:text-gray-400">N/A</span>
                                </td>
                            </tr>
                            <tr v-if="!redemption">
                                <td colspan="6" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">No redemption information available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>



        </div>

          
        </div>

        <!-- Right side column (1/3 width) -->
        <div class="space-y-6">
          <!-- Application Process -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Application Process</h2>
            
            <div class="space-y-4">
              <div class="flex items-start">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-200">1</span>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Review Your Information</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Confirm your personal and employment details are correct</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-200">2</span>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Submit Your Request</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Describe your loan request and purpose</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-200">3</span>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Application Review</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Our team will review your application</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-200">4</span>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Approval & Disbursement</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">If approved, funds will be disbursed to your account</p>
                </div>
              </div>
            </div>
          </div>
        <!-- Application Request Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
            <form @submit.prevent="submitForm" class="space-y-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                  <CreditCardIcon class="h-5 w-5 mr-2 text-blue-500" />
                  Loan Request Details
                </h3>
                <div class="grid grid-cols-1 gap-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <div class="w-full">
                    <label for="request_details" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Please describe your loan request and purpose
                    </label>
                    <div class="ql-container-wrapper">
                      <QuillEditor
                        v-model:content="form.request_details"
                        id="request_details"
                        contentType="html"
                        theme="snow"
                        toolbar="essential"
                        class="bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        style="min-height: 150px; max-width: 100%;"
                      />
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">
                      Provide details about your loan request, including purpose and any special requirements.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Confirmation Section -->
              <div class="space-y-4 mt-6">
                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input
                      id="information_correct"
                      v-model="form.information_correct"
                      name="information_correct"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="information_correct" class="font-medium text-gray-700 dark:text-gray-300">
                      I confirm that all my personal and employment information displayed above is accurate and up-to-date.
                    </label>
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input
                      id="terms_agreed"
                      v-model="form.terms_agreed"
                      name="terms_agreed"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="terms_agreed" class="font-medium text-gray-700 dark:text-gray-300">
                      I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a> and 
                      <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.
                    </label>
                  </div>
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-70"
                  :disabled="form.processing || customer.status === 'inactive' || customer.status === 'not verified' || unlockSubmitButtonValue === false"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                </button>
                
              </div>
            </form>
          </div>
      </div>
    </div>
    </div>
    <div v-else>    
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
        </div>
    </div>
  </AppLayout>
  
  <!-- Error Modal -->
  <ErrorModal
    :show="showErrorModal"
    :messages="errorMessages"
    @close="closeErrorModal"
  />

  <!-- Success Modal -->
  <SuccessModal
    :show="showSuccessModal"
    modalTitle="Application Submitted Successfully"
    :message="successMessage"
    :details="referenceId ? `Reference ID: ${referenceId}` : ''"
    @close="redirectToDashboard"
  />
</template>

<style scoped>
.ql-container-wrapper {
  width: 100%;
  overflow: hidden;
}

.ql-container-wrapper :deep(.ql-container),
.ql-container-wrapper :deep(.ql-editor) {
  max-width: 100%;
  overflow-x: auto;
}

.ql-container-wrapper :deep(.ql-toolbar) {
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
  background-color: #f9fafb;
}

.dark .ql-container-wrapper :deep(.ql-toolbar) {
  background-color: #374151;
  border-color: #4b5563;
}

.ql-container-wrapper :deep(.ql-container) {
  border-bottom-left-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
  background-color: white;
}

.dark .ql-container-wrapper :deep(.ql-container) {
  background-color: #1f2937;
  border-color: #4b5563;
}

.ql-container-wrapper :deep(.ql-editor) {
  min-height: 150px;
}
</style>
