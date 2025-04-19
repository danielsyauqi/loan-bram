<script setup lang="ts">
// Add global route function declaration for TypeScript
declare function route(name: string, params?: any): string;

import { dateUtils, formatCurrency as formatCurrencyUtil, arrayUtils } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/solid';
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

// Ensure correct typing for usePage - simplified
const page = usePage<SharedData>();

// Get user role from the auth user object
const userRole = computed(() => (page.props.auth?.user as any)?.role || 'user');


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
    moduleId?: number;
    application?: any;
    allModules: any;
    module?: {
        title: string;
        slug: string;
        description: string;
        interestRate: string;
        minAmount: number;
        maxAmount: number;
        processingTime: string;
        status: string;
        banner: string;
        tenure: string;
        id: number;
    };
    workflow_remarks?: any;
    user?: any;
    employments?: any;
    salaries?: any;
    employmentAddresses?: any;
    addresses?: any;
    user_address?: any;
    redemption?: any;
    employment_address?: any;
    agents?: any;
    products?: any;
    error?: string;
    success?: string;
    flash?: {
        error?: string;
        success?: string;
        referenceId?: string;
    };
    autosaveNotifications?: boolean;
}>(), {
    moduleId: 0,
    module: () => ({
        title: 'Loan',
        slug: '',
        description: '',
        banner: '',
        interestRate: '0%',
        minAmount: 1000,
        maxAmount: 10000,
        processingTime: '',
        status: '',
        tenure: '',
        id: 0,
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
        title: 'Application Details',
        href: `/loan-modules/${props.module.slug}/applications/${props.application.reference_id}`,
    },
    {
        title: props.application.reference_id,
        href: `/loan-modules/${props.module.slug}/applications/${props.application.reference_id}`,
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
    ic_number: '',
    module_id: props.moduleId,
    biro: '',
    banca: '',
    rates: '',
    document_checklist: [],
    date_received: '',
    agent_id: '',
    product_id: '',
    customer_id: (props.user)?.id,
    tenure_applied: '',
    date_approved: '',
    date_disbursed: '',
    date_rejected: '',
    tenure_approved: '',
    amount_applied: '',
    amount_approved: '',
    amount_disbursed: '',
});

const workflowForm = useForm({
    remarks: '',
    status: 'Pending',
});

// Add modal state variables
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('Application submitted successfully.');
const referenceId = ref('');

// Add state variables for workflow edit and delete modals
const showEditWorkflowModal = ref(false);
const showDeleteWorkflowModal = ref(false);
const selectedWorkflowItem = ref<any>(null);
const editWorkflowContent = ref('');
const selectedWorkflowStatus = ref('');

// Add state variables for toast notification
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'info' | 'warning'>('success');




// Define the correct type for flash with optional properties
const flash = ref<{ referenceId?: string, success?: string, error?: string }>({});

// Add these utility functions for debouncing
const debounceTimeouts: Record<string, number> = {};

function debounce<T extends (...args: any[]) => void>(
    fn: T,
    delay: number,
    id: string
): (...args: Parameters<T>) => void {
    return (...args: Parameters<T>) => {
        if (debounceTimeouts[id]) {
            clearTimeout(debounceTimeouts[id]);
        }
        debounceTimeouts[id] = setTimeout(() => {
            fn(...args);
            delete debounceTimeouts[id];
        }, delay);
    };
}


// Function to show toast notifications
const showToastNotification = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};


// Form submission with enhanced validation
const submitForm = () => {
    // Set the IC number from the found user
    if (props.user) {
        form.ic_number = (props.user)?.ic_num || '';
    }
    
    // Reset messages
    errorMessages.value = [];
    
    // Validate required fields
    let hasErrors = false;
    
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
    
    // If there are errors, show the modal and do not submit
    if (hasErrors) {
        showErrorModal.value = true;
        return;
    }
    
    
    form.post(route('loan-applications.store'), {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => {
            
            // Get flash data safely
            try {
                flash.value = page.props.flash || {};
                referenceId.value = flash.value.referenceId || '';
            } catch (error) {
                console.error('Error accessing page.props.flash:', error);
                referenceId.value = '';
            }
            
            showSuccessModal.value = true; // Show the success modal
        },
        onError: (errors) => {
            // Convert errors object to array of messages
            errorMessages.value = Object.values(errors).flat();
            showErrorModal.value = true;
        }
    });
};

// Function to close modal
const closeErrorModal = () => {
    showErrorModal.value = false;
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
    return props.products.find((product: any) => product.id === parseInt(form.product_id));
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
    
    // Filter out non-numeric values, sort rates, and ensure 2 decimal places
    return rates
        .filter((rate: any) => !isNaN(parseFloat(rate)))
        .map((rate: any) => {
            const parsedRate = parseFloat(rate);
            // Format to always show 2 decimal places
            return parsedRate.toFixed(2);
        })
        .sort((a: any, b: any) => parseFloat(a) - parseFloat(b));
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

// Function to open edit workflow modal
const openEditWorkflowModal = (item: any) => {
  selectedWorkflowItem.value = item;
  editWorkflowContent.value = item.remarks || '';
  selectedWorkflowStatus.value = item.status || 'Pending';
  showEditWorkflowModal.value = true;
  
  // Set hash fragment to scroll to workflow section after modal close
  if (window.location.hash !== '#workflow-remarks-timeline') {
    window.history.pushState(null, '', '#workflow-remarks-timeline');
  }
};

// Function to close edit workflow modal
const closeEditWorkflowModal = () => {
  showEditWorkflowModal.value = false;
  selectedWorkflowItem.value = null;
  editWorkflowContent.value = '';
  
  // Scroll to the workflow section after closing the modal
  scrollToHashFragment();
};

// Function to save edited workflow
const saveEditedWorkflow = (content: string, status: string) => {
  // Create a form to submit the data
  const form = useForm({
    remarks: content,
    status: status
  });
  
  // Submit the form with the updated content
  form.post(route('loan-modules.applications.updateWorkflow', { 
    moduleSlug: props.module.slug, 
    applicationId: props.application.id, 
    workflowId: selectedWorkflowItem.value.id 
  }), {
    preserveScroll: true, // This preserves the scroll position
    onSuccess: () => {
      toastMessage.value = 'Workflow remark updated successfully!';
      toastType.value = 'success';
      
      // Close the modal after successful update
      closeEditWorkflowModal();

      // Store toast state in sessionStorage to persist through reload
      sessionStorage.setItem('showToast', 'true');
      sessionStorage.setItem('toastMessage', toastMessage.value);
      sessionStorage.setItem('toastType', toastType.value);
      
      // Clear the form fields
      workflowForm.remarks = '';
      
      // Ensure the hash is set for proper scrolling after redirect
      if (window.location.hash !== '#workflow-remarks-timeline') {
        window.history.pushState(null, '', '#workflow-remarks-timeline');
      }
      
      // Reload the page to show the new workflow remark
      window.location.reload();

      // Scroll to the workflow section
      scrollToHashFragment();
    },
    onError: (errors) => {
      // Show error toast notification
      toastMessage.value = 'Failed to update workflow remark. Please try again.';
      toastType.value = 'error';
      showToast.value = true;
    }
  });
};

// Function to add new workflow remark
const addWorkflowRemark = () => {
  // Validate the form fields
  if (!workflowForm.remarks.trim()) {
    // Show error toast notification
    toastMessage.value = 'Please enter a workflow remark before submitting.';
    toastType.value = 'error';
    showToast.value = true;
    return;
  }
  
  // Submit the form to add a new workflow remark
  workflowForm.post(route('loan-modules.applications.addWorkflow', { 
    moduleSlug: props.module.slug, 
    applicationId: props.application.id
  }), {
    preserveScroll: true, // This preserves the scroll position
    onSuccess: () => {      
      // Set toast notification values before reload
      toastMessage.value = 'Workflow remark added successfully!';
      toastType.value = 'success';
      
      // Store toast state in sessionStorage to persist through reload
      sessionStorage.setItem('showToast', 'true');
      sessionStorage.setItem('toastMessage', toastMessage.value);
      sessionStorage.setItem('toastType', toastType.value);
      
      // Clear the form fields
      workflowForm.remarks = '';
      
      // Ensure the hash is set for proper scrolling after redirect
      if (window.location.hash !== '#workflow-remarks-timeline') {
        window.history.pushState(null, '', '#workflow-remarks-timeline');
      }
      
      // Reload the page to show the new workflow remark
      window.location.reload();

      // Scroll to the workflow section
      scrollToHashFragment();
    },
    onError: (errors) => {
      // Show error toast notification
      toastMessage.value = 'Failed to add workflow remark. Please try again.';
      toastType.value = 'error';
      showToast.value = true;
    }
  });
};

// Function to open delete workflow modal
const openDeleteWorkflowModal = (item: any) => {
  selectedWorkflowItem.value = item;
  showDeleteWorkflowModal.value = true;
  
  // Set hash fragment to scroll to workflow section after modal close
  if (window.location.hash !== '#workflow-remarks-timeline') {
    window.history.pushState(null, '', '#workflow-remarks-timeline');
  }
};

// Function to close delete workflow modal
const closeDeleteWorkflowModal = () => {
  showDeleteWorkflowModal.value = false;
  selectedWorkflowItem.value = null;
  
  // Scroll to the workflow section after closing the modal
  scrollToHashFragment();
};

// Function to delete workflow
const deleteWorkflow = () => {
  // Create a form to submit the delete request
  const form = useForm({});
  
  // Submit the form to delete the workflow remark
  form.delete(route('loan-modules.applications.deleteWorkflow', { 
    moduleSlug: props.module.slug, 
    applicationId: props.application.id, 
    workflowId: selectedWorkflowItem.value.id 
  }), {
    preserveScroll: true, // This preserves the scroll position
    onSuccess: () => {
      // Show success toast notification
      toastMessage.value = 'Workflow remark deleted successfully!';
      toastType.value = 'success';
      
      // Close the modal after successful deletion
      closeDeleteWorkflowModal();

      // Store toast state in sessionStorage to persist through reload
      sessionStorage.setItem('showToast', 'true');
      sessionStorage.setItem('toastMessage', toastMessage.value);
      sessionStorage.setItem('toastType', toastType.value);
      
      // Ensure the hash is set for proper scrolling after redirect
      if (window.location.hash !== '#workflow-remarks-timeline') {
        window.history.pushState(null, '', '#workflow-remarks-timeline');
      }
      
      window.location.reload();
      
      // Scroll to the workflow section
      scrollToHashFragment();
    },
    onError: (errors) => {
      // Show error toast notification
      toastMessage.value = 'Failed to delete workflow remark. Please try again.';
      toastType.value = 'error';
      showToast.value = true;
    }
  });
};

// Function to close toast
const closeToast = () => {
  showToast.value = false;
};

// Add scroll to hash fragment functionality
const scrollToHashFragment = () => {
  if (window.location.hash) {
    const element = document.querySelector(window.location.hash);
    if (element) {
      // If we're navigating to workflow section, make sure all parent sections
      // are expanded to make it visible
      if (window.location.hash === '#workflow-remarks-timeline') {
        // Make sure any collapsed sections are expanded
        expandedSections.userInfo = true; // Keep this expanded for context
      }

      // Add a slight delay to ensure the DOM is fully rendered
      setTimeout(() => {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }
};

// Add to the existing onMounted hook
onMounted(() => {
    form.product_id = props.application?.product_id || '';
    form.rates = props.application?.rates || '';
    form.biro = props.application?.biro || '';
    form.banca = props.application?.banca || '';
    form.date_received = props.application?.date_received || '';
    form.tenure_applied = props.application?.tenure_applied || '';
    form.agent_id = props.application?.agent_id || '';
    form.amount_applied = props.application?.amount_applied || '';
    form.amount_approved = props.application?.amount_approved || '';
    form.amount_disbursed = props.application?.amount_disbursed || '';
    form.tenure_approved = props.application?.tenure_approved || '';
    form.date_approved = props.application?.date_approved || '';
    form.date_disbursed = props.application?.date_disbursed || '';
    form.date_rejected = props.application?.date_rejected || '';

    form.document_checklist = props.application?.document_checklist ? 
        (typeof props.application.document_checklist === 'string' ? 
            JSON.parse(props.application.document_checklist) : 
            props.application.document_checklist) : 
        [];
    
    // Initialize flash data safely
    try {
        if (page.props) {
            flash.value = page.props.flash || {};
        }
    } catch (error) {
        console.error('Error accessing page.props in onMounted:', error);
        flash.value = {};
    }
        
    // Check for flash success message
    if (props.success) {
        toastMessage.value = props.success;
        toastType.value = 'success';
        showToast.value = true;
    }
    
    // Check for flash error message
    if (props.error) {
        toastMessage.value = props.error;
        toastType.value = 'error';
        showToast.value = true;
    }

    // Check if toast state exists in sessionStorage
    if (sessionStorage.getItem('showToast')) {
        showToast.value = true;
        toastMessage.value = sessionStorage.getItem('toastMessage') || '';
        toastType.value = sessionStorage.getItem('toastType') as 'error' | 'success' | 'info' | 'warning';
    }
        
    // Scroll to hash fragment if present
    scrollToHashFragment();

    // Add listener for hash changes (browser back/forward buttons)
    window.addEventListener('hashchange', scrollToHashFragment);
});

// Add cleanup for the event listener
onUnmounted(() => {
  // Remove the event listener when component is unmounted
  window.removeEventListener('hashchange', scrollToHashFragment);
});

// Function to view application after successful submission
const viewApplicationsList = () => {
    window.location.href = route('loan-modules.applications', { moduleSlug: props.module.slug });
};

// Function to handle redirection after closing the modal
const redirectToIndex = () => {
    window.location.href = route('loan-modules.applications', { moduleSlug: props.module.slug });
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


// Refactored saveApplication function for clarity and flexibility
const saveApplication = (field = null, value = null) => {
    // Prepare the payload
    let data: Record<string, any> = {};

    if (field && value !== null) {
        // Save a single field (inline/auto-save)
        data[field] = value;
        // If the field is document_checklist, ensure it's always sent as an array
        if (field === 'document_checklist') {
            data['document_checklist'] = Array.isArray(value) ? value : [value];
        }
    } else {
        // Save all fields (full form save)
        data = {
            rates: form.rates,
            biro: form.biro,
            banca: form.banca,
            tenure_applied: form.tenure_applied,
            date_received: form.date_received,
            amount_applied: form.amount_applied,
            amount_approved: form.amount_approved,
            amount_disbursed: form.amount_disbursed,
            tenure_approved: form.tenure_approved,
            date_approved: form.date_approved,
            date_disbursed: form.date_disbursed,
            date_rejected: form.date_rejected,
            product_id: form.product_id,
            document_checklist: form.document_checklist,
        };
    }

    // POST to the backend
    axios.post(route('loan-modules.applications.saveApplication', {
        moduleSlug: props.module.slug,
        applicationId: props.application.id
    }), data)
    .then(response => {
        if (response.data.success) {
            toastMessage.value = 'Changes saved successfully';
            toastType.value = 'success';
            showToast.value = true;
        } else {
            toastMessage.value = response.data.message || 'Failed to save changes';
            toastType.value = 'error';
            showToast.value = true;
        }
    })
    .catch(error => {
        console.error('Error saving application:', error);
        toastMessage.value = 'Failed to save changes';
        toastType.value = 'error';
        showToast.value = true;
    });
};




</script>

<template>
    <Head :title="'Application Details'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole !== 'customer'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                            <Link :href="`/loan-modules/${module.slug}/applications`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                            <ArrowLeftIcon class="h-4 w-4" />
                            <span>Back to Applications</span>
                        </Link>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-2">Application Details - {{ application.reference_id }}   <StatusBadge :status="application.status" /></h1>
                    <p class="text-gray-600 dark:text-gray-300">View the application details below</p>
                </div>
            </div>

            <!-- Application Form -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                
                <!-- Form Fields (2/3 width) -->
                <div class="md:col-span-2 space-y-6">

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                            Select your loan module
                        </h3>

                        <div>
                            <label for="module_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Loan Product
                            </label>
                            <select 
                                v-model="form.module_id"
                                id="module_id" 
                                name="module_id"
                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.module_id }"
                            >
                                <option value="">Select a module</option>
                                <option 
                                    v-for="module in props.allModules" 
                                    :key="module.id" 
                                    :value="module.id"
                                >
                                    {{ module.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.module_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.module_id }}
                            </p>
                            <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Please select a module product to proceed the application.
                            </p>
                        </div>
                        
                    </div>

                    <div class="above-module-summary space-y-6">
                        
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
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Tenure</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.tenure }}</div>
                                </div>
                                
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Interest Rate</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ module.interestRate }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                                
                    <!-- User Information Section -->
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
                                    <p class="font-medium text-gray-900 dark:text-white break-words">{{ user_address }}</p>
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
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.company_name }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Job Title</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.job_title }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone Number Employment</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.phone_num }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Bank</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.bank }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Account Number</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.account_num }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pension</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (employments)?.pension }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Date Joined</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ (employments)?.date_joined ? 
                                           dateUtils.formatDate(String((employments)?.date_joined || ''), 'DD MMMM YYYY') : '' }}
                                    </p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Employment Type</p>
                                    <p class="font-medium text-gray-900 dark:text-white break-words">{{ (employments)?.emp_type }}</p>
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
                                    <p class="font-medium text-gray-900 dark:text-white">{{ dateUtils.getMonthName((salaries)?.month || '') }}</p>
                                    </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Year</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ (salaries)?.year }}</p>
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
                                                        <div v-if="(salaries)?.income">
                                                            <div v-for="(item, index) in arrayUtils.safeJsonParse((salaries)?.income)" :key="index" class="border-b border-gray-100 dark:border-gray-600 last:border-0 py-1">
                                                                {{ item.label }}: RM {{ item.amount }}
                                </div>
                                    </div>
                                                    </td>
                                                    <td class="px-3 text-sm font-medium text-gray-900 dark:text-white">
                                                        <div v-if="(salaries)?.deduction">
                                                            <div v-for="(item, index) in arrayUtils.safeJsonParse((salaries)?.deduction)" :key="index" class="border-b border-gray-100 dark:border-gray-600 last:border-0 py-1">
                                                                {{ item.label }}: RM {{ item.amount }}
                                </div>
                                    </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-gray-100 dark:bg-gray-800">
                                                <tr>
                                                    <td class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Income: RM {{ getTotalIncome((salaries) || {}) }}
                                                    </td>
                                                    <td class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Deduction: RM {{ getTotalDeduction((salaries) || {}) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                </div>
                            </div>
                        <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nett Income</p>
                                    <p class="font-medium text-gray-900 dark:text-white">RM {{ getNettIncome((salaries) || {}) }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Attachments</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                            <a v-if="(salaries)?.attachements" 
                                           :href="(salaries)?.attachements" 
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
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ (redemption)?.bank_coop || 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ (redemption)?.expiry_date || 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">RM {{ (redemption)?.redemption_amount || 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">RM {{ (redemption)?.monthly_installment || 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ (redemption)?.redemptionRemarks || 'N/A' }}</td>
                                    </tbody>
                                </table>
                            </div>
                        </div>



                    </div>
                                    
                    
                    
                    <!-- Loan Details Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">                        
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

                                <div>
                                        <label for="date_approved" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date Approved
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                            type="date" 
                                                id="date_approved"
                                                v-model="form.date_approved"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.date_approved }"
                                        />
                                    </div>
                                        <p v-if="form.errors.date_approved" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.date_approved }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the date when the loan was approved
                                        </p>
                                </div>

                                <div>
                                        <label for="date_disbursed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date Disbursed
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                            type="date" 
                                                id="date_disbursed"
                                                v-model="form.date_disbursed"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.date_disbursed }"
                                        />
                                    </div>
                                        <p v-if="form.errors.date_disbursed" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.date_disbursed }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the date when the loan was disbursed
                                        </p>
                                </div>

                                <div>
                                        <label for="date_rejected" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Date Rejected
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                            type="date" 
                                                id="date_rejected"
                                                v-model="form.date_rejected"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.date_disbursed }"
                                        />
                                    </div>
                                        <p v-if="form.errors.date_rejected" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.date_rejected }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the date when the loan was rejected
                                        </p>
                        </div>
                        
                                <div>
                                        <label for="tenure_approved" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tenure Approved
                                        </label>
                                        <div class="mt-1">
                                        <input 
                                            type="date" 
                                                id="tenure_approved"
                                                v-model="form.tenure_approved"
                                                class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.tenure_approved }"
                                        />
                                    </div>
                                        <p v-if="form.errors.tenure_approved" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.tenure_approved }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the loan duration in years
                                        </p>
                                </div>

                                <div>
                                        <label for="amount_applied" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Amount Applied
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">RM</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                id="amount_applied"
                                                v-model="form.amount_applied"
                                                class="pl-12 px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.amount_applied }"
                                            />
                                        </div>
                                        <p v-if="form.errors.amount_applied" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.amount_applied }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the loan amount applied for
                                        </p>
                                </div>

                                <div>
                                        <label for="amount_approved" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Amount Approved 
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">RM</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                id="amount_approved"
                                                v-model="form.amount_approved"
                                                class="pl-12 px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.amount_approved }"
                                            />
                                        </div>
                                        <p v-if="form.errors.amount_approved" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.amount_approved }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the loan amount to be approved
                                        </p>
                                </div>

                                <div>
                                        <label for="amount_disbursed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Amount Disbursed
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">RM</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                id="amount_disbursed"
                                                v-model="form.amount_disbursed"
                                                class="pl-12 px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.amount_disbursed }"
                                            />
                                        </div>
                                        <p v-if="form.errors.amount_disbursed" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ form.errors.amount_disbursed }}
                                        </p>
                                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Specify the loan amount to be disbursed
                                        </p>
                                </div>

                                
                            </div>
                        </div>
                        </form>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    Workflow Remarks
                                </h3>
                                <!-- Workflow Remarks Timeline -->
                                <div id="workflow-remarks-timeline" class="grid grid-cols-1 gap-4 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg mb-3">
                                    <div class="w-full">
                                        <h4 class="text-md font-medium text-gray-800 dark:text-white mb-1">Workflow Timeline</h4>
                                        <div v-if="props.workflow_remarks && props.workflow_remarks.length > 0" 
                                             class="bg-white dark:bg-gray-800 rounded-md border border-gray-300 dark:border-gray-600 p-3 max-h-60 overflow-y-auto">
                                            <div class="relative">
                                                <!-- Timeline line -->
                                                <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-blue-200 dark:bg-blue-700"></div>
                                                
                                                    <!-- Timeline items -->
                                                    <div v-for="(item, index) in getSortedWorkflowRemarks()" 
                                                     :key="index" 
                                                     class="relative pl-8 pb-3 last:pb-0">
                                                    <!-- Timeline dot -->
                                                    <div class="absolute left-3 top-1 h-5 w-5 rounded-full bg-blue-500 flex items-center justify-center -ml-2.5 -mt-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    
                                                    <!-- Timeline content -->
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-2">
                                                        <div class="flex justify-between items-center">
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                                                                {{ item.created_at ? dateUtils.formatDate(item.created_at, 'DD MMMM YYYY, HH:mm') : 'N/A' }} <StatusBadge :status="item.status" size ="sm" class="ml-1" />
                                                                <span v-if="item.user" class="ml-1">by {{ item.user }}</span>
                                                            </div>
                                                            <div class="flex gap-2">
                                                                <button 
                                                                    type="button"
                                                                    @click="openEditWorkflowModal(item)" 
                                                                    class="p-1 text-blue-600 hover:bg-blue-100 rounded-full dark:text-blue-400 dark:hover:bg-blue-900"
                                                                    title="Edit workflow remark"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                <button 
                                                                    type="button"
                                                                    @click="openDeleteWorkflowModal(item)" 
                                                                    class="p-1 text-red-600 hover:bg-red-100 rounded-full dark:text-red-400 dark:hover:bg-red-900"
                                                                    title="Delete workflow remark"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="prose dark:prose-invert max-w-none ql-editor text-sm" v-html="item.remarks"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else-if="props.application?.workflow_remarks" class="bg-white dark:bg-gray-800 rounded-md border border-gray-300 dark:border-gray-600 p-3 max-h-60 overflow-y-auto">
                                            <!-- Legacy format support -->
                                            <div class="relative">
                                                <div class="absolute left-3 top-0 bottom-0 w-0.5 bg-blue-200 dark:bg-blue-700"></div>
                                                <div class="relative pl-8 pb-2">
                                                    <div class="absolute left-2 top-1 h-5 w-5 rounded-full bg-blue-500 flex items-center justify-center -ml-2.5 -mt-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-2">
                                                        <div class="flex justify-between items-center">
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                                                                {{ props.application?.updated_at ? dateUtils.formatDate(props.application.updated_at, 'DD MMM YYYY, HH:mm') : 'N/A' }} 
                                                                <StatusBadge :status="props.application?.status || 'New'" size="sm" class="ml-1" />
                                                            </div>
                                                            <div class="flex gap-2">
                                                                <button 
                                                                    type="button"
                                                                    @click="openEditWorkflowModal({
                                                                        id: props.application?.id,
                                                                        remarks: props.application?.workflow_remarks,
                                                                        status: props.application?.status || 'New'
                                                                    })" 
                                                                    class="p-1 text-blue-600 hover:bg-blue-100 rounded-full dark:text-blue-400 dark:hover:bg-blue-900"
                                                                    title="Edit workflow remark"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </button>
                                                                <button 
                                                                    type="button"
                                                                    @click="openDeleteWorkflowModal({
                                                                        id: props.application?.id,
                                                                        remarks: props.application?.workflow_remarks,
                                                                        status: props.application?.status || 'New'
                                                                    })" 
                                                                    class="p-1 text-red-600 hover:bg-red-100 rounded-full dark:text-red-400 dark:hover:bg-red-900"
                                                                    title="Delete workflow remark"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="prose prose-xs dark:prose-invert max-w-none text-sm" v-html="props.application?.workflow_remarks"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="bg-white dark:bg-gray-800 rounded-md border border-gray-300 dark:border-gray-600 p-2 text-gray-500 dark:text-gray-400 italic text-sm">
                                            No workflow history available.
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">
                                            Please review the workflow history and add any special instructions or notes about this loan application.
                                        </p>
                                    </div>
                                </div>
                                <!-- Add your loan application form fields here -->
                                <div class="grid grid-cols-1 gap-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    
                                    <div class="w-full">
                                        <!-- Status Selection Dropdown -->
                                        <div class="mb-4">
                                            <label for="workflow_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Status
                                            </label>
                                            <div class="relative">
                                                <select 
                                                    v-model="workflowForm.status"
                                                    id="workflow_status" 
                                                    name="workflow_status"
                                                    class="px-2 py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                                >
                                                    <option value="New">New</option>
                                                    <option value="Pending">Pending</option>
                                                    <option value="Approved">Approved</option>
                                                    <option value="Disbursed">Disbursed</option>
                                                    <option value="Rejected">Rejected</option>
                                                    <option value="Ready to Submit">Ready to Submit</option>
                                                    <option value="Processing">Processing</option>
                                                    <option value="Pending@Agency">Pending@Agency</option>
                                                    <option value="Pending@Bank">Pending@Bank</option>
                                                    <option value="Delete Request">Delete Request</option>
                                                </select>
                                            </div>
                                            <div class="mt-2 flex items-center">
                                                <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Selected status:</span>
                                                <StatusBadge :status="workflowForm.status" />
                                            </div>
                                        </div>
                                        
                                        <!-- Workflow Remarks Editor -->
                                        <div class="ql-container-wrapper">
                                            <QuillEditor
                                                v-model:content="workflowForm.remarks"
                                                id="workflow_remarks"
                                                contentType="html"
                                                theme="snow"
                                                toolbar="essential"
                                                class="bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                style="min-height: 150px; max-width: 100%;"
                                            />
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                            Add any special instructions or notes about this loan application.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                                <button 
                                    type="button"
                                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-70"
                                    :disabled="workflowForm.processing"
                                    @click="addWorkflowRemark"
                                >
                                    <svg v-if="workflowForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ workflowForm.processing ? 'Submitting...' : 'Add Workflow Remark' }}
                                </button>
                        </div>
                    </div>
                </div>
                
                <!-- Right side column (1/3 width) -->
                <div class="space-y-6">
                
                <!-- Loan Summary (1/3 width on desktop, full width on mobile) -->
                <div class="below-module-summary bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit order-first md:order-none">
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

                <!-- Agent/Sub Agent Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                    
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Agent/Sub Agent</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center">
                            <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Select Master Agent
                            </label>
    
                            </div>
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
                    
            
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 h-fit">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Document Checklist</h2>
                        </div>
                        
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

                    <div class="mt-6 flex justify-end">
                        <button 
                            type="button" 
                            @click="saveApplication()" 
                            class="shadow-lg w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        >
                            Save
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
        <div v-else class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <div class="text-center py-12">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">You are not authorized to view this page</h1>
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
        :details="`Reference ID: ${referenceId}`"
        @close="redirectToIndex"
    />

    <!-- Edit Workflow Modal -->
    <EditWorkflowModal
        :show="showEditWorkflowModal"
        modalTitle="Edit Workflow Remark"
        :content="editWorkflowContent"
        :status="selectedWorkflowStatus"
        :statusOptions="[
          { value: 'New', label: 'New' },
          { value: 'Pending', label: 'Pending' },
          { value: 'Approved', label: 'Approved' },
          { value: 'Disbursed', label: 'Disbursed' },
          { value: 'Rejected', label: 'Rejected' },
          { value: 'Ready to Submit', label: 'Ready to Submit' },
          { value: 'Process', label: 'Process' },
          { value: 'Pending@Agency', label: 'Pending@Agency' },
          { value: 'Pending@Bank', label: 'Pending@Bank' }
        ]"
        @close="closeEditWorkflowModal"
        @save="saveEditedWorkflow"
    />

    <!-- Delete Workflow Modal -->
    <ConfirmationModal
        :show="showDeleteWorkflowModal"
        modalTitle="Delete Workflow Remark"
        message="Are you sure you want to delete this workflow remark? This action cannot be undone."
        confirmText="Delete"
        confirmButtonClass="bg-red-600 hover:bg-red-700 focus:ring-red-500"
        @close="closeDeleteWorkflowModal"
        @confirm="deleteWorkflow"
    />

    <!-- Toast Notification -->
    <ToastNotification
        :show="showToast"
        :message="toastMessage"
        :type="toastType"
        :duration="5000"
        position="top-right"
        @close="closeToast"
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

/* Add transition effect for the workflow timeline when scrolled to */
#workflow-remarks-timeline {
    transition: all 0.3s ease-in-out;
}

#workflow-remarks-timeline:target {
    box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
    outline: 2px solid rgba(59, 130, 246, 0.6);
    animation: highlight-pulse 2s ease-in-out;
}

@keyframes highlight-pulse {
    0% { background-color: rgba(59, 130, 246, 0.1); }
    50% { background-color: rgba(59, 130, 246, 0.2); }
    100% { background-color: transparent; }
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


