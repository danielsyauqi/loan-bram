<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { bankSelection } from '@/lib/bank';
import { MalaysianState, citySelection, stateSelection } from '@/lib/address';

declare function route(name: string, params?: any): string;

const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');

const props = defineProps<{
    user: any;
    address: any;
    employment: any;
    salary: any;
    redemption: any;
    companyAddress: any;
    flash: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Update Identity Form',
        href: `/identity-form`,
    },
];


// Initialize incomeItems and deductionItems first
const incomeItems = ref([
    // Parse income data from the database if available
    ...(props.salary?.income ? 
        (typeof props.salary.income === 'string' ? 
            JSON.parse(props.salary.income) : 
            Array.isArray(props.salary.income) ? 
                props.salary.income : 
                [{ label: '', amount: '' }]
        ) : 
        [{ label: '', amount: '' }]
    )
]);

const deductionItems = ref([
    // Parse deduction data from the database if available
    ...(props.salary?.deduction ? 
        (typeof props.salary.deduction === 'string' ? 
            JSON.parse(props.salary.deduction) : 
            Array.isArray(props.salary.deduction) ? 
                props.salary.deduction : 
                [{ label: '', amount: '' }]
        ) : 
        [{ label: '', amount: '' }]
    )
]);

// Function to handle file attachment updates
const updateAttachment = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        const file = target.files[0];
        form.attachment = file;
        

    }
};

// Initialize form with static values first
const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    phone_num: props.user.phone_num,
    bank_name: props.user.bank_name || '',
    bank_account: props.user.bank_account || '',
    ic_num: props.user.ic_num,
    address_line_1: props.address?.address_line_1 || '',
    address_line_2: props.address?.address_line_2 || '',
    city: props.address?.city || '',
    state: props.address?.state || '',
    zip: props.address?.zip || '',
    country: props.address?.country || '',
    
    // Employment fields
    company_name: props.employment?.company_name || '',
    date_joined: props.employment?.date_joined || '',
    job_title: props.employment?.job_title || '',
    emp_type: props.employment?.emp_type || '',
    phone_num_employment: props.employment?.phone_num || '',
    pension: props.employment?.pension || '',
    address_line_1_employment: props.companyAddress?.address_line_1 || '',
    address_line_2_employment: props.companyAddress?.address_line_2 || '',
    city_employment: props.companyAddress?.city || '',
    state_employment: props.companyAddress?.state || '',
    zipcode_employment: props.companyAddress?.zip || '',
    country_employment: props.companyAddress?.country || '',
    bank: props.employment?.bank || '',
    account_num: props.employment?.account_num || '',

    // Initialize with string values
    income: JSON.stringify(incomeItems.value),
    deduction: JSON.stringify(deductionItems.value),
    month: props.salary?.month || '',
    year: props.salary?.year || '',
    attachment: props.salary?.attachment || props.salary?.attachments || '',

    
    // Redemption fields
    bank_coop: props.redemption?.bank_coop || '',
    expiry_date: props.redemption?.expiry_date || '',
    redemption_amount: props.redemption?.redemption_amount || '',
    monthly_installment: props.redemption?.monthly_installment || '',
    remark: props.redemption?.remark || '',

    
    
    
    user_photo: null as unknown as any,
});



// Update form data for income and deductions separately
const updateIncome = () => {
    form.income = JSON.stringify(incomeItems.value);
};

const updateDeduction = () => {
    form.deduction = JSON.stringify(deductionItems.value);
};

const viewAttachment = () => {
    if (!form.attachment) return;
    
    // Handle either attachment format
    const attachmentPath = form.attachment;
    if (typeof attachmentPath === 'string' && attachmentPath.startsWith('http')) {
        window.open(attachmentPath, '_blank');
    } else {
        window.open(`/storage/${attachmentPath}`, '_blank');
    }
};

// Function to add new salary item
const addIncomeItem = () => {
    incomeItems.value.push({ 
        label: '', 
        amount: '' 
    });
    updateIncome();
};

const addDeductionItem = () => {
    deductionItems.value.push({ 
        label: '', 
        amount: '' 
    });
    updateDeduction();
};

// Function to remove salary item
const removeIncomeItem = (index: number) => {
    if (incomeItems.value.length > 1) {
        incomeItems.value.splice(index, 1);
        updateIncome();
    }
};

// Function to remove deduction item
const removeDeductionItem = (index: number) => {
    if (deductionItems.value.length > 1) {
        deductionItems.value.splice(index, 1);
        updateDeduction();
    }
};

// Add validation modal state
const showValidationModal = ref(false);
const showSuccessModal = ref(false);
const showFailedModal = ref(false);
const validationErrors = ref<string[]>([]);
const errorMessage = ref('');




// Function to check if all required fields are filled
const validateForm = () => {
    validationErrors.value = [];
    
    // Personal/Identity Information validation
    if (!form.ic_num) validationErrors.value.push('IC Number is required');
    if (!form.bank_name) validationErrors.value.push('Bank Name is required');
    if (!form.bank_account) validationErrors.value.push('Bank Account Number is required');
    if (!form.phone_num) validationErrors.value.push('Phone Number is required');
    
    // Address Information validation
    if (!form.address_line_1) validationErrors.value.push('Address Line 1 is required');
    if (!form.city) validationErrors.value.push('City is required');
    if (!form.state) validationErrors.value.push('State is required');
    if (!form.zip) validationErrors.value.push('Zip/Postal Code is required');
    if (!form.country) validationErrors.value.push('Country is required');
    
    // Employment Information validation
    if (!form.company_name) validationErrors.value.push('Company Name is required');
    if (!form.job_title) validationErrors.value.push('Job Title is required');
    if (!form.emp_type) validationErrors.value.push('Employment Type is required');
    if (!form.phone_num_employment) validationErrors.value.push('Company Phone Number is required');
    if (!form.pension) validationErrors.value.push('Pension Age is required');
    if (!form.bank) validationErrors.value.push('Company Bank Name is required');
    if (!form.account_num) validationErrors.value.push('Company Bank Account is required');
    if (!form.address_line_1_employment) validationErrors.value.push('Company Address Line 1 is required');
    if (!form.city_employment) validationErrors.value.push('Company City is required');
    if (!form.state_employment) validationErrors.value.push('Company State is required');
    if (!form.zipcode_employment) validationErrors.value.push('Company Zip/Postal Code is required');
    
    // Salary Information validation
    if (!form.month) validationErrors.value.push('Salary Month is required');
    if (!form.year) validationErrors.value.push('Salary Year is required');
    if (!form.attachment) validationErrors.value.push('Salary Attachment is required');
    
    // Check if we have any income items with empty fields
    incomeItems.value.forEach((item, index) => {
        if (!item.label) validationErrors.value.push(`Income Label for record #${index + 1} is required`);
        if (!item.amount) validationErrors.value.push(`Income Amount for record #${index + 1} is required`);
    });
    
    // Check if we have any deduction items with empty fields
    deductionItems.value.forEach((item, index) => {
        if (!item.label) validationErrors.value.push(`Deduction Label for record #${index + 1} is required`);
        if (!item.amount) validationErrors.value.push(`Deduction Amount for record #${index + 1} is required`);
    });
    
    // Make sure we update the form with the latest values
    updateIncome();
    updateDeduction();
    
    return validationErrors.value.length === 0;
};

const submit = () => {
    // Validate form before submission
    if (validateForm()) {
        form.post(route('customer.identityForm.store'), {
            onSuccess: (response) => {
                // If we received a redirect with flash data, the flash message 
                // will be shown on the next page load
                if (response?.props?.flash) {
                    // We're being redirected with flash data, let it handle naturally
                    return;
                }
                
                // Otherwise show our success modal
                showSuccessModal.value = true;
            },
            onError: (errors) => {
                // If this is a validation error, it will be shown inline with the form
                // For other errors, show the failed modal
                if (Object.keys(errors).length === 0) {
                    errorMessage.value = 'There was an error submitting your form. Please try again.';
                    showFailedModal.value = true;
                }
                console.error('Form submission errors:', errors);
            }
        });
    } else {
        showValidationModal.value = true;
    }
};

const closeValidationModal = () => {
    showValidationModal.value = false;
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

const closeFailedModal = () => {
    showFailedModal.value = false;
};

// Function to parse and initialize income and deduction items from the database
const initializeFromDatabase = () => {
    try {

        // Parse income data if available
        if (props.salary?.income) {
            try {
                // If it's a string, parse it
                if (typeof props.salary.income === 'string') {
                    const parsedData = JSON.parse(props.salary.income);
                    if (Array.isArray(parsedData) && parsedData.length > 0) {
                        incomeItems.value = parsedData;
                    }
                }
            } catch (error) {
                console.error('Error parsing income data:', error);
            }
        }
        
        // Parse deduction data if available
        if (props.salary?.deduction) {
            try {
                // If it's a string, parse it
                if (typeof props.salary.deduction === 'string') {
                    const parsedData = JSON.parse(props.salary.deduction);
                    if (Array.isArray(parsedData) && parsedData.length > 0) {
                        deductionItems.value = parsedData;
                    }
                }
            } catch (error) {
                console.error('Error parsing deduction data:', error);
            }
        }
        
        // Update form with parsed values
        updateIncome();
        updateDeduction();
    } catch (error) {
        console.error('Error initializing data:', error);
    }
};

// Call the initialization function when the component is loaded
initializeFromDatabase();

// Add flash message state
const showFlash = ref(true);

// Auto-dismiss flash messages after 5 seconds
onMounted(() => {
    // Check if we have flash messages on component load
    if (props.flash) {
        if (props.flash.success) {
            // Set showFlash to true to ensure the message is displayed
            showFlash.value = true;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                showFlash.value = false;
            }, 5000);
        }
        
        if (props.flash.error) {
            // Set showFlash to true to ensure the message is displayed
            showFlash.value = true;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                showFlash.value = false;
            }, 5000);
        }
    }
});
</script>

<template>
    <Head title="Update Identity Form" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Update Identity Form <span class="ml-2 text-gray-600 dark:text-gray-300"> <StatusBadge :status="user.status" /></span></h1> 
                <p class="text-gray-600 dark:text-gray-300">Update your identity information for passes the verification process</p>
            </div>
            
            <!-- Flash Messages Section -->
            <div v-if="props.flash && (props.flash.success || props.flash.error)" class="transition-all duration-500 ease-in-out" :class="{ 'opacity-100': showFlash, 'opacity-0': !showFlash }">
                <div v-if="props.flash.success" class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 mb-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-300">{{ props.flash.success }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="showFlash = false" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="props.flash.error" class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 mb-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 dark:text-red-300">{{ props.flash.error }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button @click="showFlash = false" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Section -->
            <div class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Main Form Layout - Two Columns on Desktop -->            
                        
                        <!-- Right Column - Bank and Address Information -->
                        <div class="space-y-6">
                            <!-- Bank Information Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <div class ="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                    </svg>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Identity Information</h2>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">

                                    <!-- IC Number -->
                                    <div>
                                        <label for="ic_num" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IC Number <span class="text-red-500">*</span></label>
                                        <input 
                                            id="ic_num" 
                                            v-model="form.ic_num" 
                                            type="text" 
                                            placeholder="123456789012"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div v-if="form.errors.ic_num" class="text-red-500 text-sm mt-1">{{ form.errors.ic_num }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your IC number is used for verification purposes.
                                        </p>
                                    </div>

                                    
                                    <!-- Bank Name -->
                                    <div>
                                        <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                                        <select
                                            id="bank_name" 
                                            v-model="form.bank_name" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        >
                                            <option value="" disabled selected>Select a bank</option>
                                            <option v-for="bank in bankSelection()" :key="bank.code" :value="bank.name">
                                                {{ bank.name }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.bank_name" class="text-red-500 text-sm mt-1">{{ form.errors.bank_name }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated bank name.
                                        </p>
                                    </div>
                                    
                                    <!-- Bank Account -->
                                    <div>
                                        <label for="bank_account" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Account <span class="text-red-500">*</span></label>
                                        <input 
                                            id="bank_account" 
                                            v-model="form.bank_account" 
                                            type="text" 
                                            placeholder="e.g. 2112223433"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div v-if="form.errors.bank_account" class="text-red-500 text-sm mt-1">{{ form.errors.bank_account }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated bank account number.
                                        </p>
                                    </div>

                                    <!-- Phone Number -->
                                    <div>
                                        <label for="phone_num" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                        <input 
                                            id="phone_num" 
                                            v-model="form.phone_num" 
                                            type="text" 
                                            placeholder="e.g. 011232212"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div v-if="form.errors.phone_num" class="text-red-500 text-sm mt-1">{{ form.errors.phone_num }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated phone number.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <div class ="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13.071 2a1 1 0 0 0-1.414-1.414L8 6.586 3.707 2.293z"/>
                                    </svg>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Address Information</h2>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- Address Line 1 -->
                                    <div>
                                        <label for="address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                                        <input 
                                            id="address_line_1" 
                                            v-model="form.address_line_1" 
                                            placeholder="e.g. 123, Jln ABC"
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        />
                                        <div v-if="form.errors.address_line_1" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_1 }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated address line 1.
                                         </p>
                                    
                                    </div>
                                    
                                    <!-- Address Line 2 -->
                                    <div>
                                        <label for="address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 2 (Optional)</label>
                                        <input 
                                            id="address_line_2" 
                                            v-model="form.address_line_2" 
                                            placeholder="e.g. Apartment, Suite, etc."
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div v-if="form.errors.address_line_2" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_2 }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated address line 2.
                                        </p>
                                    </div>

                                    <!-- Postcode -->
                                    <div>
                                        <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postcode <span class="text-red-500">*</span></label>
                                        <input 
                                            id="postcode" 
                                            v-model="form.zip" 
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                            pattern="\d{5}"
                                        />
                                        <div v-if="form.errors.zip" class="text-red-500 text-sm mt-1">{{ form.errors.zip }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your postcode (5 digits)
                                        </p>
                                    </div>
                                    
                                    <!-- City -->
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City <span class="text-red-500">*</span></label>
                                        <select 
                                            id="city" 
                                            v-model="form.city" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        >
                                            <div v-if="form.state">
                                                <option value="" disabled>Select a city</option>
                                                <option v-for="city in citySelection(form.state as MalaysianState)" :key="city" :value="city">
                                                    {{ city }}
                                                </option>
                                            </div>
                                            <div v-else>
                                                <option value="" disabled>Select a state first</option>
                                            </div>
                                        </select>
                                        <div v-if="form.errors.city" class="text-red-500 text-sm mt-1">{{ form.errors.city }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated city.
                                        </p>
                                    </div>
                                    
                                    <!-- State -->
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State <span class="text-red-500">*</span></label>
                                        <select 
                                            id="state" 
                                            v-model="form.state" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        >
                                            <option value="" disabled>Select a state</option>
                                            <option v-for="state in stateSelection()" :key="state" :value="state">
                                                {{ state }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.state" class="text-red-500 text-sm mt-1">{{ form.errors.state }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your personal associated state.
                                        </p>
                                    </div>
                                    
                                    
                                    
                                    
                                    
                                
                                </div>
                            </div>
                        </div>
                                                
                    <!-- Employment Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                        <div class ="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank h-5 w-5 mr-2 text-blue-500" viewBox="0 0 16 16">
                                <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Employment Details</h2>
                        </div>
                        
                        <h2 class="text-sm font-medium text-gray-800 dark:text-white mb-2">Employment Information</h2>
                        <div class=" bg-gray-50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
                            <!-- Company Name -->
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name <span class="text-red-500">*</span></label>
                                <input 
                                    id="company_name" 
                                    v-model="form.company_name" 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.company_name" class="text-red-500 text-sm mt-1">{{ form.errors.company_name }}</div>
                            </div>
                            
                            
                            
                            <!-- Job Title -->
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title <span class="text-red-500">*</span></label>
                                <input 
                                    id="job_title" 
                                    v-model="form.job_title" 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.job_title" class="text-red-500 text-sm mt-1">{{ form.errors.job_title }}</div>
                            </div>
                            
                            <!-- Employment Type -->
                            <div>
                                <label for="emp_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employment Type <span class="text-red-500">*</span></label>
                                <select 
                                    id="emp_type" 
                                    v-model="form.emp_type" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">Select Type</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Self Employed">Self Employed</option>
                                </select>
                                <div v-if="form.errors.emp_type" class="text-red-500 text-sm mt-1">{{ form.errors.emp_type }}</div>
                            </div>

                            <!-- Date Joined -->
                            <div>
                                <label for="pension" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pension Age <span class="text-red-500">*</span></label>
                                <input 
                                    id="pension" 
                                    v-model="form.pension" 
                                    type="number" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.pension" class="text-red-500 text-sm mt-1">{{ form.errors.pension }}</div>
                            </div>
                            
                            <!-- Phone Number (Employment) -->
                            <div>
                                <label for="phone_num_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Phone Number <span class="text-red-500">*</span></label>
                                <input 
                                    id="phone_num_employment" 
                                    v-model="form.phone_num_employment" 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.phone_num_employment" class="text-red-500 text-sm mt-1">{{ form.errors.phone_num_employment }}</div>
                            </div>

                        </div>
                        <h2 class="text-sm font-medium text-gray-800 dark:text-white mb-2">Employment Associated Bank Information</h2>
                        <div class=" bg-gray-50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
                            <!-- Bank Name -->
                            <div>
                                <label for="bank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Bank Name</label>
                                <select
                                    id="bank" 
                                    v-model="form.bank"    
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="" disabled selected>Select a bank</option>
                                    <option v-for="bank in bankSelection()" :key="bank.code" :value="bank.name">
                                        {{ bank.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.bank" class="text-red-500 text-sm mt-1">{{ form.errors.bank }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated bank name.
                                </p>
                            </div>

                            <!-- Bank Account -->
                            <div>
                                <label for="account_num" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Bank Account <span class="text-red-500">*</span></label>
                                <input 
                                    id="account_num" 
                                    v-model="form.account_num"
                                    type="text"     
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.account_num" class="text-red-500 text-sm mt-1">{{ form.errors.account_num }}</div>
                            </div>
                            </div>
                            
                            <h2 class="text-sm font-medium text-gray-800 dark:text-white mb-2">Employment Associated Address Information</h2>
                            <div class=" bg-gray-50 p-4 rounded-xl grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                            <!-- Address Line 1 -->
                            <div>
                                <label for="address_line_1_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                                <input 
                                    id="address_line_1_employment" 
                                    v-model="form.address_line_1_employment" 
                                    placeholder="e.g. 123 Main St"
                                    type="text"     
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.address_line_1_employment" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_1_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated address line 1.
                                </p>
                            </div>
                            <!---Address Line 2-->
                            <div>
                                <label for="address_line_2_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 2</label>
                                <input 
                                    id="address_line_2_employment" 
                                    v-model="form.address_line_2_employment" 
                                    type="text"     
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.address_line_2_employment" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_2_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated address line 2.
                                </p>
                            </div>

                            <!-- City -->
                            <div>
                                <label for="city_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City <span class="text-red-500">*</span></label>
                                <input 
                                    id="city_employment"
                                    v-model="form.city_employment"
                                    type="text"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.city_employment" class="text-red-500 text-sm mt-1">{{ form.errors.city_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated city.
                                </p>
                            </div>      

                            <!-- Poscode    -->
                            <div>
                                <label for="zipcode_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postcode <span class="text-red-500">*</span></label>
                                <input 
                                    id="zipcode_employment"    
                                    v-model="form.zipcode_employment"
                                    type="text"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.zipcode_employment" class="text-red-500 text-sm mt-1">{{ form.errors.zipcode_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated postcode.
                                </p>
                            </div>

                            <!-- State  -->
                            <div>
                                <label for="state_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State <span class="text-red-500">*</span></label>
                                <input 
                                    id="state_employment"
                                    v-model="form.state_employment"
                                    type="text"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.state_employment" class="text-red-500 text-sm mt-1">{{ form.errors.state_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated state.
                                </p>
                            </div>

                            <!-- Country -->
                            <div>
                                <label for="country_employment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country <span class="text-red-500">*</span></label>
                                <input 
                                    id="country_employment"
                                    v-model="form.country_employment"
                                    type="text"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <div v-if="form.errors.country_employment" class="text-red-500 text-sm mt-1">{{ form.errors.country_employment }}</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Your company associated country.
                                </p>
                            </div>      

                        </div>
                    </div>
                    <!-- Salary Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Salary Information</h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                    <label :for="`month`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Month <span class="text-red-500">*</span></label>
                                    <select 
                                        :id="`month`" 
                                        v-model="form.month" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="">Select Month</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <div v-if="form.errors.month" class="text-red-500 text-sm mt-1">{{ form.errors.month }}</div>
                                    <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                        Your salary associated month.
                                    </p>
                                </div>
                                
                                <div>
                                    <label :for="`year`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year <span class="text-red-500">*</span></label>
                                    <input 
                                        :id="`year`"  
                                        v-model="form.year" 
                                        type="number" 
                                        min="2000"
                                        max="2050"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                    <div v-if="form.errors.year" class="text-red-500 text-sm mt-1">{{ form.errors.year }}</div>
                                    <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                        Your salary associated year.
                                    </p>
                                </div>

                            <div>
                                <label :for="`attachment`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary Attachment <span class="text-red-500">*</span> <span v-if="form.attachment" class="ml-3 text-sm text-blue-500 dark:text-blue-400 underline" @click="viewAttachment">View Attachment</span></label>
                                <input 
                                    id="attachment" 
                                    type="file" 
                                    class="block w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    @change="updateAttachment"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload your salary slip or other supporting documents (PDF only and max size 2MB)</p>
                            </div>

                        </div>

                        <!-- Modified layout structure - split into two columns with vertical stacking -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                            <!-- Allowance Records Column -->
                            <div class="space-y-4">
                                <h3 class="text-md font-medium text-gray-800 dark:text-white border-b pb-2">Income Records</h3>
                                
                                <div v-for="(incomeItem, index) in incomeItems" :key="`income-${index}`" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="flex justify-between items-center mb-3">
                                        <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Income Record #{{ index + 1 }} <span class="text-red-500">*</span></h3>
                                <button 
                                            v-if="incomeItems.length > 1" 
                                    type="button" 
                                            @click="removeIncomeItem(index)" 
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    <TrashIcon class="h-4 w-4 mr-1" />
                                    Remove
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                            <label :for="`income-label-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Income Label</label>
                                    <input 
                                                :id="`income-label-${index}`" 
                                                v-model="incomeItem.label" 
                                        type="text" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                @input="updateIncome"
                                    />
                                    
                                </div>
                                
                                <div>
                                            <label :for="`income-amount-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Income</label>

                                    <div class="mt-1 relative rounded-md shadow-sm">

                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 dark:text-gray-400 sm:text-s">RM</span>
                                                </div>
                                    <input 
                                        :id="`income-amount-${index}`" 
                                        v-model="incomeItem.amount" 
                                        type="number" 
                                        class="pl-12 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        @input="updateIncome"
                                    />
                                  
                                    </div>
                                </div>
                            </div>
                        </div>

                                <button 
                                    type="button" 
                                    @click="addIncomeItem" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full justify-center"
                                >
                                    <PlusIcon class="h-4 w-4 mr-1" />
                                    Add Income Record
                                </button>
                            </div>
                            
                            <!-- Deduction Records Column -->
                            <div class="space-y-4">
                                <h3 class="text-md font-medium text-gray-800 dark:text-white border-b pb-2">Deduction Records</h3>
                                
                                <div v-for="(deductionItem, index) in deductionItems" :key="`deduction-${index}`" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-red-50 dark:bg-red-900/20">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-md font-medium text-gray-700 dark:text-gray-300">Deduction Record #{{ index + 1 }} <span class="text-red-500">*</span></h3>
                                <button 
                                    v-if="deductionItems.length > 1" 
                                    type="button" 
                                    @click="removeDeductionItem(index)" 
                                    class="inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    <TrashIcon class="h-4 w-4 mr-1" />
                                    Remove
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                            <label :for="`deduction-label-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deduction Label</label>
                                    <input 
                                                :id="`deduction-label-${index}`" 
                                        v-model="deductionItem.label" 
                                        type="text" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        @input="updateDeduction"
                                    />
                                </div>
                                
                                <div>
                                            <label :for="`deduction-amount-${index}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Deduction</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">

                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 sm:text-s">RM</span>
                                            </div>
                                    <input 
                                                :id="`deduction-amount-${index}`" 
                                        v-model="deductionItem.amount" 
                                        type="number" 
                                        class="pl-12 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        @input="updateDeduction"
                                    />
                                    </div>
                                </div>
                            </div>
                        </div>

                                <button 
                                    type="button" 
                                    @click="addDeductionItem" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center"
                                >
                                    <PlusIcon class="h-4 w-4 mr-1" />
                                    Add Deduction Record
                                </button>
                            </div>
                    </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 mb-6">
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-green-50 dark:bg-green-900/20">
                                <h3 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Total Income</h3>
                                <div class="text-xl font-bold text-green-600 dark:text-green-400">
                                    RM {{ incomeItems.reduce((sum, item) => sum + (Number(item.amount) || 0), 0).toFixed(2) }}
                                </div>
                            </div>

                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-red-50 dark:bg-red-900/20">
                                <h3 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Total Deduction</h3>
                                <div class="text-xl font-bold text-red-600 dark:text-red-400">
                                    RM {{ deductionItems.reduce((sum, item) => sum + (Number(item.amount) || 0), 0).toFixed(2) }}
                                </div>
                            </div>

                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <h3 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Nett Income</h3>
                                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                    RM {{ (incomeItems.reduce((sum, item) => sum + (Number(item.amount) || 0), 0) - deductionItems.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Redemption Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Redemption Information (Optional)</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="redemption-bank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Coop </label>
                                <select 
                                    id="redemption-bank" 
                                    v-model="form.bank_coop" 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                <option value="" disabled selected>Select a bank</option>
                                    <option v-for="bank in bankSelection()" :key="bank.code" :value="bank.name">
                                        {{ bank.name }}
                                    </option>
                                </select>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated bank.
                                    </p>
                            </div>
                            
                            <div>
                                <label for="redemption-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                                <input 
                                    id="redemption-amount" 
                                    v-model="form.expiry_date" 
                                    type="date" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated expiry date.
                                    </p>
                            </div>
                            
                            <div>
                                <label for="redemption-installment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Redemption Amount</label>

                                <div class="mt-1 relative rounded-md shadow-sm">

                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400 sm:text-s">RM</span>
                                    </div>
                                <input 
                                    id="redemption-installment" 
                                    v-model="form.redemption_amount" 
                                    type="number" 
                                    class="pl-12 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                               
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated redemption amount.
                                    </p>
                            </div>
                            
                            <div>
                                <label for="redemption-tenor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Installment</label>
                                <div class="mt-1 relative rounded-md shadow-sm">

                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400 sm:text-s">RM</span>
                                    </div>
                                <input 
                                    id="redemption-tenor" 
                                    v-model="form.monthly_installment" 
                                        type="float" 
                                        class="pl-12 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                    
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated monthly installment.
                                    </p>
                            </div>
                            
                            <div>
                                <label for="redemption-remaining" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remark</label>
                                <textarea 
                                    id="redemption-remaining" 
                                    v-model="form.remark" 
                                    rows="4" 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Your personal associated remark.
                                    </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6 flex flex-wcol sm:flex-row sm:justify-end gap-3 sm:gap-4">
                        <a :href="route('customer.dashboard')" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-center">
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="form.processing"
                        >
                            {{ user.status === 'not verified' || user.status === 'inactive' ? 'Verify Identity' : form.processing ? 'Updating...' : 'Update User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Validation Error Modal -->
        <div v-if="showValidationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-2">Validation Error</h3>
                    <div class="mt-2 px-7 py-3 text-left">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc pl-5 space-y-1 text-sm text-red-500 max-h-60 overflow-y-auto">
                            <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
                        </ul>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button 
                            @click="closeValidationModal" 
                            class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Success Modal -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-2">Success!</h3>
                    <div class=" px-7 py-3">
                        <h1>Your identity has been verified!</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Now you are eligible to apply for a loan.</p>
                    </div>
                    <div class="items-center px-4 py-3 space-y-3">
                        <a 
                            :href="route('customer.applications.new')"
                            class="block px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                        >
                            Apply for a Loan
                        </a>
                        <button 
                            @click="closeSuccessModal" 
                            class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Failed Modal -->
        <div v-if="showFailedModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mt-2">Submission Failed</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ errorMessage }}</p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button 
                            @click="closeFailedModal" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 