<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { watch } from 'vue';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { showToast } from '../../../lib/toast';
import { bankSelection } from '@/lib/bank';
import { MalaysianState, stateSelection, citySelection } from '@/lib/address';
import StatusBadge from '@/components/ui/StatusBadge.vue';
declare const route: any;

// Components registration
const components = {
    ErrorModal,
    SuccessModal
};

const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');

// Add modal state variables
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('User updated successfully.');

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const props = defineProps<{
    user: {
        id: number;
        name: string;
        password: string;
        username: string;
        email: string;
        phone_num: string;
        bank_name: string | null;
        bank_account: string | null;
        role: string;
        status: string;
        ic_num: string;
        user_photo: string | null;
        module_permissions: number[] | null;
        address: {
            id: number;
            address_line_1: string;
            address_line_2: string | null;
            city: string;
            state: string;
            zip: string;
            country: string;
        } | null;
    };
    loanModules: Array<{
        id: number;
        name: string;
        description: string;
        logo?: string;
        status: string;
    }>;
}>();


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'User Management',
        href: '/admin/users',
    },
    {
        title: 'Edit User',
        href: `/admin/users/${props.user.id}/edit`,
    },
];

const photoPreview = ref<string | null>(props.user.user_photo ? `/storage/${props.user.user_photo}` : null);

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    phone_num: props.user.phone_num,
    bank_name: props.user.bank_name || '',
    bank_account: props.user.bank_account || '',
    role: props.user.role,
    status: props.user.status,
    ic_num: props.user.ic_num,
    address_line_1: props.user.address?.address_line_1 || '',
    address_line_2: props.user.address?.address_line_2 || '',
    city: props.user.address?.city || '',
    state: props.user.address?.state || '',
    zip: props.user.address?.zip || '',
    country: props.user.address?.country || '',
    user_photo: null as unknown as any,
    module_permissions: props.user.module_permissions || [],
    _method: 'PUT',
});

const updatePhotoPreview = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target && target.files && target.files.length > 0) {
        const file = target.files[0];
        form.user_photo = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

//USERNAME VALIDATION

// Field validation states
const isCheckingUsername = ref(false);
const isCheckingEmail = ref(false);
const usernameAvailable = ref<boolean | null>(null);
const emailAvailable = ref<boolean | null>(null);


// Check username availability
const checkUsernameAvailability = debounce(async (username: string) => {
    if (!username || username.length < 3) {
        usernameAvailable.value = null;
        return;
    }
    
    try {
        isCheckingUsername.value = true;
        const response = await axios.post(route('api.check-username-edit', { username: props.user.username }), { username });
        usernameAvailable.value = response.data.available;
    } catch (error) {
        console.error('Failed to check username:', error);
        usernameAvailable.value = null;
    } finally {
        isCheckingUsername.value = false;
    }
}, 500);


// IC number validation
const icNumberValid = ref(true);
const icNumberAvailable = ref<boolean | null>(null);
const isCheckingICNumber = ref(false);
// Check ic number availability
const checkICNumberAvailability = debounce(async (icNum: string) => {
    if (!icNum || icNum.length !== 12) {
        icNumberAvailable.value = null;
        icNumberValid.value = false;
        return;
    }
    
    icNumberValid.value = true;

    try {
        isCheckingICNumber.value = true;
        const response = await axios.post(route('api.check-ic-number-edit', { username: props.user.username }), { ic_num: icNum });
        icNumberAvailable.value = response.data.available;
    } catch (error) {
        console.error('Failed to check ic number:', error);
        icNumberAvailable.value = null;
    } finally {
        isCheckingICNumber.value = false;
    }
}, 500);



//EMAIL VALIDATION
// Email validation
const emailRegexMessage = ref(false);
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Check email availability
const checkEmailAvailability = debounce(async (email: string) => {
    if (!email || !email.includes('@')) {
        emailAvailable.value = null;
        emailRegexMessage.value = true;
        return;
    }
    
    try {
        if (!emailRegex.test(email)) {
            emailRegexMessage.value = true;
            emailAvailable.value = null;
        } else {
            emailRegexMessage.value = false;
            isCheckingEmail.value = true;
            const response = await axios.post(route('api.check-email-edit', { username: props.user.username }), { email });
            emailAvailable.value = response.data.available;
        }
    } catch (error) {
        console.error('Failed to check email:', error);
        emailAvailable.value = null;
        emailRegexMessage.value = false;
    } finally {
        isCheckingEmail.value = false;
    }
}, 500);

//PASSWORD VALIDATION
// Password validation
const passwordRegexMessage = ref(false);
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
const passwordsMatch = ref(true);
const comparePassword = ref(false);

// For validating password against regex
const validateNewPassword = () => {
    if (!form.password) {
        passwordRegexMessage.value = false;
        return;
    }
    
    passwordRegexMessage.value = !passwordRegex.test(form.password);
};

// For validating password confirmation
const validatePasswordConfirmation = () => {
    if (!form.password || !form.password_confirmation) {
        passwordsMatch.value = true;
        return;
    }
    
    passwordsMatch.value = form.password === form.password_confirmation;
};

// Watch for changes to form fields
watch(() => form.username, (newValue) => {
    checkUsernameAvailability(newValue);
});

watch(() => form.email, (newValue) => {
    checkEmailAvailability(newValue);
});

watch(() => form.ic_num, (newValue) => {
    checkICNumberAvailability(newValue);
});

watch(() => form.password, (newValue) => {
    validateNewPassword();
    validatePasswordConfirmation();
});

watch(() => form.password_confirmation, () => {
    validatePasswordConfirmation();
});

// Function to close error modal
const closeErrorModal = () => {
    showErrorModal.value = false;
};

// Function to close success modal
const closeSuccessModal = () => {
    showSuccessModal.value = false;
    window.location.href = route('users.management.index');
};

const submit = () => {
    // Reset messages
    errorMessages.value = [];
    
    // Validate required fields
    let hasErrors = false;
    
    if (!icNumberValid.value) {
        errorMessages.value.push('IC Number must be 12 digits');
        hasErrors = true;
    }
    
    if (passwordRegexMessage.value) {
        errorMessages.value.push('Password must be at least 8 characters and include uppercase, lowercase, number, and special character');
        hasErrors = true;
    }
    
    if (!passwordsMatch.value) {
        errorMessages.value.push('Passwords do not match');
        hasErrors = true;
    }
    
    if (emailAvailable.value === false) {
        errorMessages.value.push('Email is already taken');
        hasErrors = true;
    }
    
    if (usernameAvailable.value === false) {
        errorMessages.value.push('Username is already taken');
        hasErrors = true;
    }

    if (icNumberAvailable.value === false) {
        errorMessages.value.push('IC Number is already taken');
        hasErrors = true;
    }

    // If there are errors, show the modal and do not submit
    if (hasErrors) {
        showErrorModal.value = true;
        return;
    }
    
    // Submit the form
    form.post(route('users.management.update', { id: props.user.id }), {
        onSuccess: () => {
            successMessage.value = 'User updated successfully.';
            console.log(form);
            
        },
        onError: (errors) => {
            // Convert errors object to array of messages
            errorMessages.value = Object.values(errors).flat() as string[];
        },
    });
};



</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Edit User</h1>
                <p class="text-gray-600 dark:text-gray-300">Update user information</p>
            </div>
            
            <!-- Form Section -->
            <div class="space-y-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Main Form Layout - Two Columns on Desktop -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column - User Information -->
                        <div>
                            <!-- User Information Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">User Information</h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- User Photo Section -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            Profile Photo
                                        </label>
                                        <div class="flex flex-col items-center space-y-4">
                                            <!-- Photo Preview -->
                                            <div v-if="photoPreview" class="mb-2">
                                                <img 
                                                    :src="photoPreview" 
                                                    alt="User photo preview" 
                                                    class="h-32 w-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700"
                                                />
                                            </div>
                                            <div v-else class="h-32 w-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <span class="text-gray-500 dark:text-gray-400 text-4xl">
                                                    {{ props.user.name ? props.user.name.charAt(0).toUpperCase() : '?' }}
                                                </span>
                                            </div>

                                            <!-- Photo Upload Button -->
                                            <label 
                                                for="user_photo" 
                                                class="cursor-pointer px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600"
                                            >
                                                {{ photoPreview && !props.user.user_photo ? 'Change Photo' : (props.user.user_photo ? 'Change Photo' : 'Upload Photo') }}
                                            </label>
                                            <input 
                                                id="user_photo"
                                                type="file"
                                                @input="updatePhotoPreview"
                                                accept="image/*"
                                                class="hidden"
                                            />
                                            <div v-if="form.errors.user_photo" class="text-red-500 text-sm mt-1">
                                                {{ form.errors.user_photo }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                                        <input 
                                            id="name" 
                                            v-model="form.name" 
                                            placeholder="e.g. John Doe"
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        />
                                        
                                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Name must be follow as per IC
                                        </p>
                                        
                                    </div>
                                    
                                    <!-- Username -->
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input 
                                                id="username" 
                                                v-model="form.username" 
                                                placeholder="e.g. johndoe"
                                                type="text" 
                                                class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-green-500': usernameAvailable === true,
                                                    'border-red-500': usernameAvailable === false,
                                                    'border-gray-300 dark:border-gray-600': usernameAvailable === null
                                                }"
                                                required
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg v-if="isCheckingUsername" class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else-if="usernameAvailable === true" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="usernameAvailable === false" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</div>
                                        <div v-else-if="usernameAvailable === false" class="text-red-500 text-sm mt-1">This username is already taken</div>
                                        <div v-else-if="usernameAvailable === true" class="text-green-500 text-sm mt-1">Username is available</div>
                                        <div v-else-if="form.username && form.username.length < 3" class="text-orange-500 text-sm mt-1">Username must be at least 3 characters</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Username must be at least 3 characters
                                        </p>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input 
                                                id="email" 
                                                v-model="form.email" 
                                                placeholder="e.g. johndoe@example.com"
                                                type="email" 
                                                class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-green-500': emailAvailable === true,
                                                    'border-red-500': emailAvailable === false || emailRegexMessage,
                                                    'border-gray-300 dark:border-gray-600': emailAvailable === null && !emailRegexMessage
                                                }"
                                                required
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg v-if="isCheckingEmail" class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else-if="emailAvailable === true" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="emailAvailable === false || emailRegexMessage" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                                        <div v-else-if="emailRegexMessage && form.email" class="text-red-500 text-sm mt-1">Please enter a valid email address</div>
                                        <div v-else-if="emailAvailable === false" class="text-red-500 text-sm mt-1">This email is already taken</div>
                                        <div v-else-if="emailAvailable === true" class="text-green-500 text-sm mt-1">Email is available</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Email must be valid
                                        </p>
                                    </div>
                                    
                                    <!-- Phone Number -->
                                    <div>
                                            <label for="phone_num" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                        <input 
                                            id="phone_num" 
                                            v-model="form.phone_num" 
                                            placeholder="e.g. 0123456789"
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        />
                                        <div v-if="form.errors.phone_num" class="text-red-500 text-sm mt-1">{{ form.errors.phone_num }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Phone number must be 10/11 digits
                                        </p>
                                    </div>
                                    
                                    <!-- IC Number -->
                                    <div>
                                        <label for="ic_num" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IC Number <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input 
                                                id="ic_num" 
                                                v-model="form.ic_num" 
                                                type="text" 
                                                class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-green-500': icNumberValid === true && icNumberAvailable === true, 
                                                    'border-red-500': icNumberValid === false || icNumberAvailable === false,
                                                    'border-gray-300 dark:border-gray-600': icNumberValid === null
                                                }"
                                                placeholder="e.g. 900101011234"
                                                required
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg v-if="isCheckingICNumber" class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else-if="icNumberAvailable === true" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="icNumberAvailable === false" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.ic_num" class="text-red-500 text-sm mt-1">{{ form.errors.ic_num }}</div>
                                        <div v-else-if="icNumberValid === false && form.ic_num" class="text-red-500 text-sm mt-1">IC Number must be 12 digits</div>
                                        <div v-else-if="icNumberAvailable === false" class="text-red-500 text-sm mt-1">IC Number is already taken</div>
                                        <div v-else-if="icNumberAvailable === true" class="text-green-500 text-sm mt-1">IC Number is available</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            IC Number must be 12 digits
                                        </p>
                                    </div>
                                    
                                    <!-- Role -->
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role <span class="text-red-500">*</span></label>
                                        <select 
                                            id="role" 
                                            v-model="form.role" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        >
                                            <option value="admin">Admin</option>
                                            <option value="agent">Agent</option>
                                            <option value="customer">Customer</option>
                                        </select>
                                        <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Role must be selected
                                        </p>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
                                        <select 
                                            id="status" 
                                            v-model="form.status" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        >
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Status must be active or inactive
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6 mt-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Security</h2>
                                <div class="grid grid-cols-1 gap-4 sm:gap-6">
                                    <!-- Password (Optional) -->
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current password</span>
                                        </div>
                                        <div class="relative">
                                            <input 
                                                id="password" 
                                                v-model="form.password" 
                                                :type="showPassword ? 'text' : 'password'" 
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                :class="{
                                                        'border-green-500': form.password && !passwordRegexMessage,
                                                        'border-red-500': passwordRegexMessage && form.password,
                                                        'border-gray-300 dark:border-gray-600': !form.password
                                                    }"
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <button 
                                                    type="button" 
                                                    @click="showPassword = !showPassword" 
                                                    class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                                >
                                                    <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                                <svg v-if="form.password && !passwordRegexMessage" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="passwordRegexMessage && form.password" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                                        <div v-else-if="passwordRegexMessage && form.password" class="text-red-500 text-sm mt-1">
                                            Password must contain at least 8 characters, including uppercase, lowercase, number, and special character
                                        </div>
                                        <div v-else-if="form.password && !passwordRegexMessage" class="text-green-500 text-sm mt-1">Password meets requirements</div>
                                    </div>
                                    
                                    <!-- Password Confirmation -->
                                    <div v-if="form.password">
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input 
                                                id="password_confirmation" 
                                                v-model="form.password_confirmation" 
                                                :type="showConfirmPassword ? 'text' : 'password'" 
                                                class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                :class="{
                                                    'border-green-500': form.password && form.password_confirmation && passwordsMatch,
                                                    'border-red-500': form.password && form.password_confirmation && !passwordsMatch,
                                                    'border-gray-300 dark:border-gray-600': !form.password_confirmation
                                                }"
                                                required
                                            />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <button 
                                                    type="button" 
                                                    @click="showConfirmPassword = !showConfirmPassword" 
                                                    class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                                >
                                                    <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    </svg>
                                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                                <svg v-if="form.password && form.password_confirmation && passwordsMatch" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="form.password && form.password_confirmation && !passwordsMatch" class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.password && form.password_confirmation && !passwordsMatch" class="text-red-500 text-sm mt-1">
                                            Passwords do not match
                                        </div>
                                        <div v-else-if="form.password && form.password_confirmation && passwordsMatch" class="text-green-500 text-sm mt-1">
                                            Passwords match
                                        </div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Make sure your password and confirmation password are the same
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Bank and Address Information -->
                        <div class="space-y-6">
                            <!-- Bank Information Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Bank Information</h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
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
                                            Your bank account name (Optional)
                                        </p>
                                    </div>
                                    
                                    <!-- Bank Account -->
                                    <div>
                                        <label for="bank_account" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Account</label>
                                        <input 
                                            id="bank_account" 
                                            v-model="form.bank_account" 
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            
                                        />

                                        
                                        <div v-if="form.errors.bank_account" class="text-red-500 text-sm mt-1">{{ form.errors.bank_account }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your bank account number (Optional)
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Address Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Address Information</h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- Address Line 1 -->
                                    <div class="sm:col-span-2">
                                        <label for="address_line_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                                        <input 
                                            id="address_line_1" 
                                            v-model="form.address_line_1" 
                                            placeholder="e.g. Seasons Avenue"
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required
                                        />
                                        <div v-if="form.errors.address_line_1" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_1 }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your address line 1
                                        </p>
                                    </div>
                                    
                                    <!-- Address Line 2 -->
                                    <div class="sm:col-span-2">
                                        <label for="address_line_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address Line 2 <span class="text-red-500">*</span></label>
                                        <input 
                                            id="address_line_2" 
                                            placeholder="e.g. Wangsa Maju Seksyen 23"
                                            v-model="form.address_line_2" 
                                            type="text" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div v-if="form.errors.address_line_2" class="text-red-500 text-sm mt-1">{{ form.errors.address_line_2 }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your address line 2
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
                                            Your state
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
                                            <option value="" disabled>Select a city</option>
                                            <option v-for="city in citySelection(form.state as MalaysianState)" :key="city" :value="city">
                                                {{ city }}
                                            </option>
                                        </select>
                                        <div v-if="form.errors.city" class="text-red-500 text-sm mt-1">{{ form.errors.city }}</div>
                                        <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                            Your city
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
                                </div>
                            </div>

                            <!-- Module Permissions Section -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Module Permissions</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                    <!-- Module Permissions -->
                                    <div class="md:col-span-2">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div v-for="module in props.loanModules" :key="module.id" class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        :id="`module-${module.id}`"
                                                        v-model="form.module_permissions"
                                                        :value="module.id"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                                    />
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label :for="`module-${module.id}`" class="font-medium text-gray-700 dark:text-gray-300">{{ module.name }} <span class="ml-2"><StatusBadge :status="module.status" :size="'sm'" /></span></label>
                                                    <p class="text-gray-500 dark:text-gray-400">{{ module.description }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.module_permissions" class="text-red-500 text-sm mt-1">{{ form.errors.module_permissions }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    
                    <!-- Form Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6 flex flex-wcol sm:flex-row sm:justify-end gap-3 sm:gap-4">
                        <a :href="route('users.management.index')" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600 text-center">
                            Cancel
                        </a>
                        <button 
                            @click="submit"
                            class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update User' }}
                        </button>
                    </div>
                </form>
            </div>
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
        modalTitle="User Updated Successfully"
        :message="successMessage"
        @close="closeSuccessModal"
    />
</template> 