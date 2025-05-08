<script setup lang="ts">
// Add global route function declaration for TypeScript
declare function route(name: string, params?: any): string;

import { dateUtils, formatCurrency as formatCurrencyUtil, arrayUtils } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/solid';
import { reactive, onMounted, ref, computed, onUnmounted } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import axios from 'axios';

// Define application interface
interface Application {
    id: number;
    reference_id: string;
    created_at?: string;
    status: string;
    customer_id?: number;
    module_id?: number;
    // Add other properties as needed
}

// Ensure correct typing for usePage
const page = usePage<{ 
  flash?: { 
    referenceId?: string, 
    success?: string, 
    foundUser?: any
  },
  auth: { user: any },
  applications?: Application[],
  foundUser?: any,
  error?: string
}>();
const user = computed(() => page.props.auth?.user);

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');
const userStatus = computed(() => (user.value as any)?.status || 'not active');


// Register components
const components = {
  QuillEditor,
  ErrorModal,
  SuccessModal
};

// Add default values to the props
const props = withDefaults(defineProps<{
    moduleId: number;
    applications: any;
    admins: any;
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
        slug: string;
    };
    products?: Array<{
        id: number;
        name: string;
        minimum_loan: number;
        maximum_loan: number;
        rate: JSON;
        tenure: number;
    }>;
    agents?: Array<{
        id: number;
        name: string;
        email: string;
        phone_num?: string;
        role: string;
    }>;
    foundUser?: {
        id: number;
        name: string;
        email: string;
        phone_num: string;
        ic_num: string;
        bank_name?: string;
        bank_account?: string;
        role?: string;
        status?: string;
        password?: string;
        user_photo?: string; 
        
        // Employment details
        job_title?: string;
        phone_num_employment?: string;
        bank?: string;
        pension?: string;
        company_name?: string;
        date_joined?: string;
        account_num?: string;
        emp_type?: string;
        
        // Salary details
        basic_income?: string;
        month?: string;
        year?: string;
        income?: string;
        deduction?: string;
        attachements?: string;
        
        // Addresses
        user_address?: string;
        employment_address?: string;

        // Redemption information
        bank_coop?: string;
        expiry_date?: string;
        redemption_amount?: string;
        monthly_installment?: string;
        redemptionRemarks?: string;


    };
    error?: string;
    message?: string;
    flash?: {
        foundUser?: {
            id: number;
            name: string;
            email: string;
            phone_num: string;
            ic_num: string;
            bank_name: string;
            bank_account: string;
            role: string;
            status: string;
            password: string;
            user_photo: string; 

            job_title: string;
            phone_num_employment: string;
            bank: string;
            pension: string;
            address: string;
            company_name: string;
            date_joined: string;
            account_num: string;
            emp_type: string;

            basic_income: string;
            month: string;
            year: string;
            income: string;
            deduction: string;
            
            bank_coop: string;
            expiry_date: string;
            redemption_amount: string;
            monthly_installment: string;
            redemptionRemarks: string;

            attachements: string;

            user_address: string;
            employment_address: string;
        };
        error?: string;
        message?: string;
    };
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
        slug: ''
    }),
});

console.log(props.admins);

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
    {
        title: 'New Application',
        href: `/loan-modules/${props.module.slug}/applications/create`,
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

// Function to reset form and state when navigating back to this page
const resetFormAndState = () => {
    // Reset form fields
    form.reset();
    
    // Reset application-related states
    existingApplications.value = [];
    showApplicationsList.value = false;
    selectedReferenceId.value = null;
    noApplicationsFound.value = false;
    newApplication.value = false;
    
    // Reset modal states
    showErrorModal.value = false;
    errorMessages.value = [];
    showSuccessModal.value = false;
    successMessage.value = 'Application submitted successfully.';
    referenceId.value = '';
};



// Define the form object
const form = useForm({
    ic_number: '',
    module_id: props.moduleId,
    product_id: '',
    rates: '',
    biro: '',
    banca: '',
    document_checklist: [],
    date_received: '',
    agent_id: '',
    customer_id: (props.foundUser || props.flash?.foundUser)?.id,
    workflow_remarks: '',
    tenure_applied: '',
    for_admin: '',
});

// Add state variables for the applications list
const existingApplications = ref<Application[]>([]);
const showApplicationsList = ref(false);
const selectedReferenceId = ref<string | null>(null);
const noApplicationsFound = ref(false);

// Add modal state variables
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('Application submitted successfully.');
const referenceId = ref('');

const newApplication = ref(false);

// Add a debug function
const debug = (message: string) => {
};

// Form submission with enhanced validation
const submitForm = () => {
    // Set the IC number from the found user
    if (props.foundUser || props.flash?.foundUser) {
        form.ic_number = (props.foundUser || props.flash?.foundUser)?.ic_num || '';
    }
    
    // Reset messages
    errorMessages.value = [];
    
    // Validate required fields
    let hasErrors = false;
    
   /*  if(newApplication.value === false){
        if (!form.product_id) {
            errorMessages.value.push('Please select a product');
            hasErrors = true;
        }
        
        if (!form.rates) {
            errorMessages.value.push('Please select a rate');
            hasErrors = true;
        }
        
        if (!form.tenure_applied) {
            errorMessages.value.push('Loan tenure is required');
            hasErrors = true;
        }
        
        if (!form.agent_id) {
            errorMessages.value.push('Please select an agent');
            hasErrors = true;
        }



        if(!form.date_received){
            errorMessages.value.push('Please select a date received');
            hasErrors = true;
        }
        
        // If there are errors, show the modal and do not submit
        if (hasErrors) {
            showErrorModal.value = true;
            return;
        }
        
        
       
    } */
 
    form.post(route('loan-applications.store'), {
            preserveScroll: false,
            preserveState: false,
            onSuccess: () => {
                // Set the reference ID from the response
                referenceId.value = page.props.flash?.referenceId || '';
                showSuccessModal.value = true; // Show the success modal
            },
            onError: (errors) => {
                // Convert errors object to array of messages
                errorMessages.value = Object.values(errors).flat();
                showErrorModal.value = true;
            }
        });
    newApplication.value = false;
};

//Cancel Form
const cancelForm = () => {
    // Redirect to the index page
    window.location.href = route('loan-modules.applications', {
        moduleSlug: props.module.slug,
    });
};

// Function to close modal
const closeErrorModal = () => {
    showErrorModal.value = false;
};

const searchUser = () => {
    // Reset application-related states
    existingApplications.value = [];
    showApplicationsList.value = false;
    selectedReferenceId.value = null;
    noApplicationsFound.value = false;
    
    // Post to the current URL with the search param
    form.post(window.location.href, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            // Check if user was found in the response
            if (response.props.error) {
                console.log(response.props.error);
            } else if (response.props.applications && response.props.applications.length > 0) {
                // Set the existing applications and show the list
                existingApplications.value = response.props.applications as Application[];
                showApplicationsList.value = true;
            } else if (response.props.foundUser || response.props.flash?.foundUser) {
                // If user was found but no applications
                noApplicationsFound.value = true;
            }
        },
        onError: (errors) => {
            console.log(errors);
        }
    });
};

const selectApplication = (referenceId: string) => {
    selectedReferenceId.value = referenceId;
};
const continueWithApplication = () => {

    if (selectedReferenceId.value) {
        // Redirect to edit page for the selected application
        newApplication.value = true;

            axios.post(route('loan-modules.applications.module.post', {
                moduleSlug: props.module.slug,
                referenceId: selectedReferenceId.value,
            }), {
                module_id: props.moduleId
            })
            .then(response => {
                console.log(response);
                window.location.reload();
                window.location.href = route('loan-modules.applications.show', { 
                    moduleSlug: props.module.slug,
                    referenceId: selectedReferenceId.value,
                });
            })
            .catch(error => {
                console.error('Error redirecting to application:', error);
            });

       
    }
};

const createNewApplication = () => {
    noApplicationsFound.value = true;
    newApplication.value = true;

    const applicationList = document.getElementById('applicationList');
    if (applicationList) {
        applicationList.style.display = 'none';
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

// Add computed property to extract rates from selected product
const getSelectedProduct = () => {
    if (!form.product_id || !props.products) return null;
    return props.products.find(product => product.id === parseInt(form.product_id));
};

const getProductRates = () => {
    const product = getSelectedProduct();
    if (!product || !product.rate) return [];
    
    // Parse the product's rate JSON and ensure it's an array
    let rates: any[] = [];
    try {
        if (typeof product.rate === 'string') {
            rates = JSON.parse(product.rate);
        } else if (Array.isArray(product.rate)) {
            rates = product.rate;
        }
    } catch (e) {
        console.error('Error parsing product rates:', e);
    }
    
    // Filter out non-numeric values and sort rates
    return rates
        .filter((rate: any) => !isNaN(parseFloat(rate)))
        .map((rate: any) => parseFloat(rate))
        .sort((a: number, b: number) => a - b);
};

// Update handleProductSelection function to set default values
const handleProductSelection = () => {
    const product = getSelectedProduct();
    if (product) {
        // Reset selected rate when product changes
        form.rates = '';
        
        // Set default tenure based on product
        if (!form.tenure_applied && product.tenure) {
            form.tenure_applied = product.tenure.toString();
        }
    }
};

// Add state variables for expanded sections
const expandedSections = reactive({
  userInfo: true,
  employmentInfo: false,
  salaryInfo: false,
  redemptionInfo: false
});

// Toggle section visibility
const toggleSection = (section: keyof typeof expandedSections) => {
  expandedSections[section] = !expandedSections[section];
};

// Add a mounted hook to set the current date when the component mounts
onMounted(() => {
    // Set default date to today if not already set
    if (!form.date_received) {
        form.date_received = new Date().toISOString().substr(0, 10);
    }
    if(!form.workflow_remarks){
        form.workflow_remarks = 'PASS TO DATA ENTRY';
    }
});

// Function to view application after successful submission
const viewApplicationsList = () => {
    window.location.href = route('loan-modules.applications', { moduleId: props.moduleId });
};

// Function to handle redirection after closing the modal
const redirectToIndex = () => {
    window.location.href = route('loan-modules.applications', { moduleId: props.moduleId });
};

// Remove icInput computed property and use a formatter function for xxxxxx-xx-xxxx
function formatICNumberInput(event: Event) {
    const input = event.target as HTMLInputElement;
    let value = input.value.replace(/\D/g, ''); // Only digits
    if (value.length > 12) value = value.slice(0, 12);
    if (value.length > 6 && value.length <= 8) {
        value = value.replace(/(\d{6})(\d{1,})/, '$1-$2');
    } else if (value.length > 8) {
        value = value.replace(/(\d{6})(\d{2})(\d{1,})/, '$1-$2-$3');
    }
    else if (value.length > 6) {
        value = value.replace(/(\d{6})(\d{1,})/, '$1-$2');
    }
    form.ic_number = value;
}

</script>

<template>
    <Head :title="`New ${module.title} Application`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin' || userRole === 'superuser' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <form @submit.prevent="submitForm" class="space-y-6">                        

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <Link :href="`/loan-modules/${module.slug}/applications`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                            <ArrowLeftIcon class="h-4 w-4" />
                            <span>Back to Applications</span>
                        </Link>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-2">New {{ module.title }} Application</h1>
                    <p class="text-gray-600 dark:text-gray-300">Fill out the form below to apply for a {{ module.title }}</p>
                </div>
            </div>

            <!-- Application Form -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Form Fields (2/3 width) -->
                <div v-if="foundUser || flash?.foundUser " class="md:col-span-2 space-y-6">
                    <!-- Applications list when applications found -->
                    <div v-if="showApplicationsList " id="applicationList" class="  bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <div class="mb-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Applications Found for {{ (foundUser || flash?.foundUser)?.name }}
                                </h3>
                                <button 
                                    @click="createNewApplication"
                                    class="inline-flex items-center py-2 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Create New Application
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Select an existing application to continue or create a new one.
                            </p>
                        </div>
                        
                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Select
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Reference ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date Created
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="application in existingApplications" :key="application.id" 
                                        :class="{'bg-blue-50 dark:bg-blue-900/20': selectedReferenceId === application.reference_id}"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                        @click="selectApplication(application.reference_id)">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input 
                                                type="radio" 
                                                :checked="selectedReferenceId === application.reference_id"
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ application.reference_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ application.created_at ? dateUtils.formatDate(application.created_at, 'DD MMMM YYYY') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <StatusBadge :status="application.status" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button 
                                @click="continueWithApplication" 
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                :disabled="!selectedReferenceId"
                            >
                                Continue with Selected Application
                            </button>
                        </div>
                    </div>
                            
                    <!-- User found and no applications - old UI remains here as fallback -->
                     <div v-if="noApplicationsFound" >
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        User found! Please complete the loan application form.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="above-module-summary space-y-6 mt-8">
                        
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit order-first md:order-none">
                            <!-- Add banner image at the top -->
                            <div class="mb-4" v-if="module.banner">
                                <img :src="module.banner" alt="Loan Banner" class="w-full h-32 object-contain rounded-lg">
                            </div>
                            
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Loan Summary</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Loan Module</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.title }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Minimum Loan</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.minAmount) }}</div>
                                </div>

                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Maximum Loan</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.maxAmount) }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Interest Rate</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                            
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mt-6">
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
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.name || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">IC Number</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.ic_num || 'N/A' }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.email || 'N/A' }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.phone_num || 'N/A' }}</p>
                                    </div>
                                <div class="w-full md:col-span-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Address</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-words">{{ (foundUser || flash?.foundUser)?.user_address || 'N/A' }}</p>
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
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.company_name  || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Job Title</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.job_title || 'N/A' }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone Number Employment</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.phone_num  || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Bank</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.bank  || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Account Number</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.account_num  || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pension</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.pension  || 'N/A'}}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Date Joined</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ (foundUser || flash?.foundUser)?.date_joined ? 
                                           dateUtils.formatDate(String((foundUser || flash?.foundUser)?.date_joined || ''), 'DD MMMM YYYY') : 'N/A' }}
                                    </p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Employment Type</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-words">{{ (foundUser || flash?.foundUser)?.emp_type  || 'N/A'}}</p>
                                </div>

                                <div class="w-full md:col-span-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Employment Address</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-words">{{ (foundUser || flash?.foundUser)?.employment_address  || 'N/A'}}</p>
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
                            <div v-show="expandedSections.salaryInfo" class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Month</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ dateUtils.getMonthName((foundUser || flash?.foundUser)?.month || '') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Year</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.year }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Attachments</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <a v-if="(foundUser || flash?.foundUser)?.attachements" 
                                           :href="(foundUser || flash?.foundUser)?.attachements" 
                                           target="_blank" 
                                           class="text-blue-500 hover:underline">
                                            View Attachment
                                        </a>
                                        <span v-else>No attachment available</span>
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
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.bank_coop || 'N/A' }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.expiry_date || 'N/A' }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">RM {{ (foundUser || flash?.foundUser)?.redemption_amount || 'N/A' }}</td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">RM {{ (foundUser || flash?.foundUser)?.monthly_installment || 'N/A' }}</td>
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ (foundUser || flash?.foundUser)?.redemptionRemarks || 'N/A' }}</td>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>



                    </div>

                    <!-- Loan Details Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mt-8">
                                    <div>
                                <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    Loan Details
                                </h3>

                            </div>
                                
                                <!-- Add your loan application form fields here -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    
                                    <!-- Example field -->
                                    <div>
                                        <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Loan Product
                                        </label>
                                        <select 
                                            v-model="form.product_id"
                                            id="product_id" 
                                            name="product_id"
                                            @change="handleProductSelection"
                                            class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.product_id }"
                                        >
                                            <option value="">Select a product</option>
                                            <option 
                                                v-for="product in props.products" 
                                                :key="product.id" 
                                                :value="product.id"
                                            >
                                                {{ product.name }} ({{ product.tenure }} Years)
                                            </option>
                                        </select>
                                        <p v-if="form.errors.product_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.product_id }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Please select a module product to proceed the application.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="rates" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Loan Rates
                                        </label>
                                        <div class="mt-1">
                                            <select 
                                                v-model="form.rates"
                                                id="rates" 
                                                name="rates"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.rates }"
                                                :disabled="!form.product_id"
                                            >
                                                <option value="">Select a rate</option>
                                                <option 
                                                    v-for="(rate, index) in getProductRates()" 
                                                    :key="index" 
                                                    :value="rate"
                                                >
                                                    {{ rate }}%
                                                </option>
                                            </select>
                                        </div>
                                        <p v-if="form.errors.rates" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.rates }}
                                        </p>
                                        <p v-else-if="!form.product_id" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Please select a product first to view available rates
                                        </p>
                                        <p v-else-if="getProductRates().length === 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            No rates available for this product
                                        </p>
                                        <p v-else-if="form.product_id" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Please select a rate to proceed the application.
                                        </p>
                        </div>
                        
                        <div>
                                        <label for="biro" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Loan Biro
                                        </label>
                                        <div class="mt-1">
                                            <div class="flex items-center space-x-4">
                                                <label class="inline-flex items-center">
                                    <input 
                                                        type="radio" 
                                        v-model="form.biro" 
                                                        value="Yes" 
                                                        class="form-radio h-4 w-4 text-blue-600"
                                                    />
                                                    <span class="ml-2">Yes</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                    <input 
                                                        type="radio" 
                                                        v-model="form.biro" 
                                                        value="No" 
                                                        class="form-radio h-4 w-4 text-blue-600"
                                                    />
                                                    <span class="ml-2">No</span>
                                                </label>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.biro" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.biro }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Please choose the loan biro to proceed the application.
                                        </p>
                                </div>
                                
                                <div>
                                        <label for="banca" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Loan Banca
                                        </label>
                                        <div class="mt-1">
                                            <div class="flex items-center space-x-4">
                                                <label class="inline-flex items-center">
                                    <input 
                                                        type="radio" 
                                                        v-model="form.banca" 
                                                        value="Yes" 
                                                        class="form-radio h-4 w-4 text-blue-600"
                                                    />
                                                    <span class="ml-2">Yes</span>
                                    </label>
                                                <label class="inline-flex items-center">
                                        <input 
                                                        type="radio" 
                                                        v-model="form.banca" 
                                                        value="No" 
                                                        class="form-radio h-4 w-4 text-blue-600"
                                                    />
                                                    <span class="ml-2">No</span>
                                                </label>
                                    </div>
                                        </div>
                                        <p v-if="form.errors.banca" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.banca }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Please choose the loan banca to proceed the application.
                                        </p>
                                </div>
                                
                                <div>
                                        <label for="date_received" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date Received
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                                type="date" 
                                                id="date_received" 
                                                v-model="form.date_received"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.date_received }"
                                        />
                                    </div>
                                        <p v-if="form.errors.date_received" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.date_received }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Automatically set today as the received date. You can change it if needed.
                                        </p>
                                </div>

                                <div>
                                        <label for="tenure_applied" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Loan Tenure (Years)
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                            type="number" 
                                                id="tenure_applied"
                                                readonly 
                                                v-model="form.tenure_applied"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 cursor-not-allowed text-gray-500 dark:bg-gray-600 dark:border-gray-600 dark:text-gray-400 sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.tenure_applied }"
                                                min="1"
                                                :max="getSelectedProduct()?.tenure || 30"
                                                placeholder="Enter loan tenure in years"
                                        />
                                    </div>
                                        <p v-if="form.errors.tenure_applied" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.tenure_applied }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the loan duration in years
                                        </p>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center mt-4 ">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    Workflow Remarks
                                </h3>
                                
                                <!-- Add your loan application form fields here -->
                                <div class="grid grid-cols-1 gap-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    
                                
                                        
                                        <!-- Workflow Remarks Editor -->
                                        <div class="ql-container-wrapper">
                                            <QuillEditor
                                                v-model:content="form.workflow_remarks"
                                                id="workflow_remarks"
                                                contentType="html"
                                                theme="snow"
                                                toolbar="essential"
                                                class="bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                style="min-height: 150px; max-width: 100%;"
                                            />
                                        </div>
                                <p class=" text-sm text-gray-500 dark:text-gray-400 italic">
                                    Add any special instructions or notes about this loan application.
                                </p>
                            </div>
                            <div class="flex justify-end space-x-4 mt-3">
                                <button 
                                    type="button" 
                                    class="inline-flex items-center justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                    @click="cancelForm"
                                    >
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" :disabled="form.processing"
                                    @click="submitForm"
                                    
                                    >
                                    {{ form.processing ? 'Submitting...' : 'Submit Form' }}
                                </button>
                            </div>
                    </div>

                            
                    </div>

                </div>
                

                    

                <!-- The IC Number search form - this is what shows first -->
                <div v-else class="md:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                    <div v-if="error || flash?.error" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    {{ error || flash?.error }}
                                </p>
                            </div>
                            </div>
                                </div>
                                
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Search Customer by IC Number</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Enter the customer's IC number to find existing applications or create a new one.
                        </p>
                                </div>
                                
                    <form @submit.prevent="searchUser" class="space-y-6">
                                <div>
                            <label for="ic_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                IC Number
                            </label>
                            <div class="mt-1">
                                    <input 
                                        v-model="form.ic_number"
                                        @input="formatICNumberInput"
                                        type="text"
                                        id="ic_number"
                                        name="ic_number"
                                        maxlength="14"
                                        class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.ic_number }"
                                        placeholder="xxxxxx-xx-xxxx"
                                    />
                                </div>
                            <p v-if="form.errors.ic_number" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.ic_number }}
                            </p>
                            <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Please enter IC number in the format xxxxxx-xx-xxxx.
                            </p>
                            </div>
                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Searching...' : 'Search' }}
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Right side column (1/3 width) with Loan Summary -->
                <div class="space-y-6">
                
                <!-- Loan Summary (1/3 width) -->
                <div class="below-module-summary bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                    <!-- Add banner image at the top -->
                    <div class="mb-4" v-if="module.banner">
                        <img :src="module.banner" alt="Loan Banner" class="w-full h-32 object-contain rounded-lg">
                    </div>
                    
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Loan Summary</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Loan Module</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.title }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Minimum Loan</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.minAmount) }}</div>
                        </div>

                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Maximum Loan</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(module.maxAmount) }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Tenure</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.tenure }}</div>
                        </div>
                        
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Interest Rate</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</div>
                        </div>
                    </div>
                </div>

                <div v-if="foundUser || flash?.foundUser" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">For Admin Selection</h2>
                    <div class="space-y-4">
                        <div>
                            <label for="for_admin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Select Admin
                            </label>
                            <div class="mt-1">
                                <select
                                    v-model="form.for_admin"
                                    id="for_admin"
                                    name="for_admin"
                                    class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.for_admin }"
                                >
                                    <option value="">Select an admin</option>
                                    <option
                                        v-for="admin in props.admins"
                                        :key="admin.id"
                                        :value="admin.id"
                                    >
                                        {{ admin.name }} ({{ admin.email }})
                                    </option>
                                </select>
                                <p v-if="form.errors.for_admin" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.for_admin }}
                                </p>
                                <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Please select an admin to assign this application
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agent/Sub Agent Section -->
                <div v-if="foundUser || flash?.foundUser " class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                    
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Agent/Sub Agent</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Select Agent
                            </label>
                            <div class="mt-1">
                                <select 
                                    v-model="form.agent_id"
                                    id="agent_id" 
                                    name="agent_id"
                                    class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.agent_id }"
                                >
                                    <option value="">Select an agent</option>
                                    <option 
                                        v-for="agent in props.agents" 
                                        :key="agent.id" 
                                        :value="agent.id"
                                    >
                                        {{ agent.name }} ({{ agent.email }})
                                    </option>
                                </select>
                                <p v-if="form.errors.agent_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.agent_id }}
                                </p>
                                <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Please select an agent to handle this loan application
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                    
            
                    <div v-if="foundUser || flash?.foundUser" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Document Checklist</h2>
                        
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="Original Payslip"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">Original Payslip</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="Copy Payslip"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">Copy Payslip</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="IC Copy"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">IC Copy</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="HRMIS Report"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">HRMIS Report</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="ANM Password"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">ANM Password</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="QR Code Payslip"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">QR Code Payslip</span>
                            </li>
                            <li class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    v-model="form.document_checklist" 
                                    value="Confirmation Letter"
                                    class="h-5 w-5 text-blue-600 border-gray-300 rounded mr-2 mt-0.5 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600" 
                                />
                                <span class="text-gray-700 dark:text-gray-300">Confirmation Letter</span>
                            </li>
                        </ul>
                    </div> 
                </div>

            </div>
        </form>

        </div>
        <!-- Customer Actions Section -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
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
        :details="`Reference ID: ${referenceId}`"
        @close="redirectToIndex"
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

@media (min-width: 768px) {
    .above-module-summary {
        display: none !important;
    }
}

@media (max-width: 768px) {
    .below-module-summary {
        display: none !important;
    }
}
</style>


