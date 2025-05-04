<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
declare const route: any;

const props = defineProps<{
    user: User;
}>();

const tabs = [
    { name: 'Profile', active: true },
    { name: 'Password', active: false },
];

const activeTab = ref('Profile');

// Photo preview handling
const photoPreview = ref<string | null>(props.user.user_photo ? `/storage/${props.user.user_photo}` : null);
const photoInput = ref<HTMLInputElement | null>(null);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Field validation states
const isCheckingUsername = ref(false);
const isCheckingEmail = ref(false);
const usernameAvailable = ref<boolean | null>(null);
const emailAvailable = ref<boolean | null>(null);
const originalUsername = props.user.username || '';
const originalEmail = props.user.email;

// Pending email states
const hasPendingEmail = ref(!!props.user.pending_email);
const pendingEmail = ref(props.user.pending_email || '');
const pendingEmailSentAt = ref(props.user.pending_email_sent_at || null);
const newEmailInputVisible = ref(false);
const isProcessingEmailChange = ref(false);
const emailChangeError = ref('');
const emailChangeSuccess = ref('');
const emailResendSuccess = ref(false);
const emailCancelSuccess = ref(false);
const newEmailForm = useForm({
    new_email: '',
});

// Profile form
const profileForm = useForm({
    name: props.user.name,
    username: props.user.username || '',
    user_photo: null,
    _method: 'POST',
});

// Update photo preview when file is selected
const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];
    
    if (!photo) {
        return;
    }
    
    profileForm.user_photo = photo as any; // Use type assertion to avoid TS error
    
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };
    
    reader.readAsDataURL(photo);
};

// Select a new photo
const selectNewPhoto = () => {
    photoInput.value?.click();
};

// Remove the photo preview
const removePhotoPreview = () => {
    photoPreview.value = null;
    profileForm.user_photo = null;
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

// Check username availability
const checkUsernameAvailability = debounce(async (username: string) => {
    // Skip check if username is unchanged
    if (username === originalUsername) {
        usernameAvailable.value = null;
        return;
    }
    
    if (!username || username.length < 3) {
        usernameAvailable.value = null;
        return;
    }
    
    try {
        isCheckingUsername.value = true;
        const response = await axios.post(route('api.check-username'), { username });
        usernameAvailable.value = response.data.available;
    } catch (error) {
        console.error('Failed to check username:', error);
        usernameAvailable.value = null;
    } finally {
        isCheckingUsername.value = false;
    }
}, 500);

//Email Regex
const emailRegexMessage = ref(false);
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Check email availability - only called for new_email field
const checkEmailAvailability = debounce(async (email: string) => {
    if (!email || !email.includes('@')) {
        emailAvailable.value = null;
        emailRegexMessage.value = true;
        return;
    }
    
    try {
        if (!emailRegex.test(email)) {
            emailRegexMessage.value = true;
        } else {
            emailRegexMessage.value = false;
            isCheckingEmail.value = true;
            const response = await axios.post(route('api.check-email'), { email });
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

const passwordRegexMessage = ref(false);
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
const isCheckingPassword = ref(false);
const passwordAvailable = ref<boolean | null>(null);

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
    _method: 'POST',
});

// Check password requirements
const checkPasswordAvailability = debounce(async (password: string) => {
    // Skip check if current password is empty
    if (!passwordForm.current_password) {
        passwordAvailable.value = null;
        return;
    }
    
    try {
        isCheckingPassword.value = true;
        const response = await axios.post(route('api.check-password'), { 
            password: passwordForm.current_password 
        });
        passwordAvailable.value = response.data.available;
    } catch (error) {
        console.error('Failed to check password:', error);
        passwordAvailable.value = null;
    } finally {
        isCheckingPassword.value = false;
    }
}, 500);

// For validating password against regex
const validateNewPassword = () => {
    if (!passwordForm.password) {
        passwordRegexMessage.value = false;
        return;
    }
    
    passwordRegexMessage.value = !passwordRegex.test(passwordForm.password);
};

// For validating password confirmation
const passwordsMatch = ref(true);
const validatePasswordConfirmation = () => {
    if (!passwordForm.password || !passwordForm.password_confirmation) {
        passwordsMatch.value = true;
        return;
    }
    
    passwordsMatch.value = passwordForm.password === passwordForm.password_confirmation;
};

// Email change methods
const startEmailChange = () => {
    newEmailInputVisible.value = true;
    newEmailForm.new_email = '';
    emailAvailable.value = null;
    emailRegexMessage.value = false;
};

const cancelEmailChange = () => {
    newEmailInputVisible.value = false;
    newEmailForm.new_email = '';
    emailAvailable.value = null;
    emailRegexMessage.value = false;
};

// Request email change
const requestEmailChange = async () => {
    // Don't submit if email is not available or invalid
    if (emailAvailable.value === false || emailRegexMessage.value === true) {
        return;
    }
    
    try {
        isProcessingEmailChange.value = true;
        emailChangeError.value = '';
        
        const response = await axios.post('/email/change', { 
            new_email: newEmailForm.new_email 
        });
        
        // Success - update UI to show pending email
        hasPendingEmail.value = true;
        pendingEmail.value = newEmailForm.new_email;
        pendingEmailSentAt.value = new Date().toISOString();
        emailChangeSuccess.value = `Verification email sent to ${newEmailForm.new_email}`;
        newEmailInputVisible.value = false;
        
        // Clear after a few seconds
        setTimeout(() => {
            emailChangeSuccess.value = '';
        }, 5000);
        
    } catch (error: any) {
        console.error('Failed to request email change:', error);
        emailChangeError.value = error.response?.data?.message || 'Failed to request email change';
    } finally {
        isProcessingEmailChange.value = false;
    }
};

// Resend verification email
const resendVerificationEmail = async () => {
    try {
        isProcessingEmailChange.value = true;
        emailChangeError.value = '';
        
        await axios.post('/email/change/resend');
        
        // Update sent time
        pendingEmailSentAt.value = new Date().toISOString();
        emailResendSuccess.value = true;
        
        // Clear after a few seconds
        setTimeout(() => {
            emailResendSuccess.value = false;
        }, 5000);
        
    } catch (error: any) {
        console.error('Failed to resend verification:', error);
        emailChangeError.value = error.response?.data?.message || 'Failed to resend verification email';
    } finally {
        isProcessingEmailChange.value = false;
    }
};

// Cancel pending email change
const cancelPendingEmailChange = async () => {
    try {
        isProcessingEmailChange.value = true;
        emailChangeError.value = '';
        
        await axios.post('/email/change/cancel');
        
        // Reset UI
        hasPendingEmail.value = false;
        pendingEmail.value = '';
        pendingEmailSentAt.value = null;
        emailCancelSuccess.value = true;
        
        // Clear after a few seconds
        setTimeout(() => {
            emailCancelSuccess.value = false;
        }, 5000);
        
    } catch (error: any) {
        console.error('Failed to cancel email change:', error);
        emailChangeError.value = error.response?.data?.message || 'Failed to cancel email change';
    } finally {
        isProcessingEmailChange.value = false;
    }
};

// Watch for changes to new_email field
watch(() => newEmailForm.new_email, (newValue) => {
    if (newValue) {
        checkEmailAvailability(newValue);
    } else {
        emailAvailable.value = null;
        emailRegexMessage.value = false;
    }
});

// Watch for changes to username and email
watch(() => profileForm.username, (newValue) => {
    checkUsernameAvailability(newValue);
});

// Watch for changes in password fields
watch(() => passwordForm.current_password, (newValue) => {
    if (newValue) {
        checkPasswordAvailability(newValue);
    } else {
        passwordAvailable.value = null;
    }
});

watch(() => passwordForm.password, (newValue) => {
    validateNewPassword();
    validatePasswordConfirmation();
});

watch(() => passwordForm.password_confirmation, () => {
    validatePasswordConfirmation();
});

// Update profile
const updateProfile = () => {
    // Don't submit if username is not available
    if (usernameAvailable.value === false) {
        return;
    }

    profileForm.post(route('user.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            usernameAvailable.value = null;
            profileSuccess.value = true;
            setTimeout(() => {
                profileSuccess.value = false;
            }, 3000);
        },
    });
};

// Update password
const updatePassword = () => {
    if (passwordAvailable.value === false) {
        return;
    }
    
    passwordForm.post(route('user.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordSuccess.value = true;
            setTimeout(() => {
                passwordSuccess.value = false;
            }, 3000);
        },
    });
};


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Profile',
        href: '/user-profile',
    },
];

const profileSuccess = ref(false);
const passwordSuccess = ref(false);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="User Profile" />

        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 rounded-xl p-3 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="px-4 sm:px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center space-x-4">
                    <div class="h-12 w-12 rounded-full bg-blue-600 dark:bg-blue-500 flex items-center justify-center text-white font-bold">
                        <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" class="h-12 w-12 rounded-full object-cover" />
                        <span v-else class="text-xl">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white">User Profile</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Manage your profile information</p>
                    </div>
                </div>

                <!-- Tab navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-4 sm:px-6" aria-label="Tabs">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.name"
                            @click="activeTab = tab.name"
                            :class="[
                                activeTab === tab.name 
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm'
                            ]"
                        >
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Profile information form -->
                <div v-if="activeTab === 'Profile'" class="px-4 sm:px-6 py-6">
                    <form @submit.prevent="updateProfile" class="space-y-6">
                        <!-- Profile Photo -->
                        <div class="grid gap-4">
                            <Label class="text-sm font-medium text-gray-700 dark:text-gray-300">Profile Photo</Label>
                            <div class="flex flex-col items-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-6">
                                <!-- Photo Preview -->
                                <div class="relative">
                                    <img v-if="photoPreview" 
                                         :src="photoPreview" 
                                         alt="Profile photo preview" 
                                         class="h-24 w-24 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700" />
                                    
                                    <div v-else 
                                         class="h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <span class="text-gray-500 dark:text-gray-400 text-2xl font-medium">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    
                                    <!-- Remove button if preview exists -->
                                    <button v-if="photoPreview" 
                                            @click.prevent="removePhotoPreview"
                                            type="button"
                                            class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Photo Upload Controls -->
                                <div class="flex flex-col space-y-2">
                                    <Button
                                        type="button"
                                        @click="selectNewPhoto"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 w-full sm:w-auto"
                                    >
                                        {{ photoPreview || user.user_photo ? 'Change Photo' : 'Upload Photo' }}
                                    </Button>
                                    
                                    <input
                                        ref="photoInput"
                                        type="file"
                                        class="hidden"
                                        @input="updatePhotoPreview"
                                        accept="image/*"
                                    />
                                    
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        JPG, PNG, or GIF. Max size 2MB.
                                    </p>
                                </div>
                            </div>
                            <InputError :message="profileForm.errors.user_photo" class="mt-1" />
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Full Name -->
                            <div class="grid gap-2">
                                <Label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</Label>
                                <Input 
                                    id="name" 
                                    v-model="profileForm.name" 
                                    type="text" 
                                    class="block w-full" 
                                    required 
                                    autocomplete="name" 
                                    placeholder="Your full name"
                                />
                                <InputError :message="profileForm.errors.name" />
                                <p class="text-xs text-gray-600 dark:text-gray-400">Full name as per your IC.</p>
                            </div>
                            

                            <!-- Username with real-time validation -->
                            <div class="grid gap-2">
                                <Label for="username" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Username
                                    <span v-if="isCheckingUsername" class="ml-2 text-xs text-gray-500 dark:text-gray-400 animate-pulse">
                                        Checking...
                                    </span>
                                    <span v-else-if="usernameAvailable === true" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                        ✓ Available
                                    </span>
                                    <span v-else-if="usernameAvailable === false" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                        ✗ Already taken
                                    </span>
                                </Label>
                                <div class="relative">
                                    <Input 
                                        id="username" 
                                        v-model="profileForm.username" 
                                        type="text" 
                                        class="block w-full pr-10" 
                                        required 
                                        autocomplete="username" 
                                        placeholder="Your username"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg v-if="isCheckingUsername" class="w-5 h-5 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else-if="usernameAvailable === true" class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else-if="usernameAvailable === false" class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <InputError :message="profileForm.errors.username" />
                                <p v-if="usernameAvailable !== false" class="text-xs text-gray-600 dark:text-gray-400">
                                    Username must be at least 3 characters long and must be unique.
                                </p>
                                <p v-if="usernameAvailable === false" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                    This username is already taken. Please choose another one.
                                </p>
                            </div>
                        </div>

                        <!-- Email section -->
                        <div class="grid gap-2">
                            <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email address
                            </Label>
                            <div class="flex items-center gap-3">
                                <span class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-md text-gray-500 dark:text-gray-300 text-sm">
                                    {{ user.email }}
                                    <span v-if="user.email_verified_at" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                        (Verified)
                                    </span>
                                    <span v-else class="ml-2 text-xs text-amber-600 dark:text-amber-400">
                                        (Unverified)
                                    </span>
                                </span>
                                <Button 
                                    v-if="!hasPendingEmail && !newEmailInputVisible"
                                    type="button" 
                                    class="shrink-0 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                    @click="startEmailChange"
                                >
                                    Change Email
                                </Button>
                            </div>

                            <!-- Pending email change status -->
                            <div v-if="hasPendingEmail" class="mt-2 px-4 py-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-700 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <p>Pending change to: <strong>{{ pendingEmail }}</strong></p>
                                        <p class="text-xs mt-1">Verification email sent. Please check your inbox and click the confirmation link.</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button 
                                            type="button"
                                            class="text-xs px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                            @click="resendVerificationEmail"
                                            :disabled="isProcessingEmailChange"
                                        >
                                            {{ isProcessingEmailChange ? 'Resending...' : 'Resend' }}
                                        </Button>
                                        <Button 
                                            type="button"
                                            class="text-xs px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                            @click="cancelPendingEmailChange"
                                            :disabled="isProcessingEmailChange"
                                        >
                                            {{ isProcessingEmailChange ? 'Cancelling...' : 'Cancel' }}
                                        </Button>
                                    </div>
                                </div>
                                
                                <!-- Resend success message -->
                                <p v-if="emailResendSuccess" class="mt-2 text-xs text-green-600 dark:text-green-400">
                                    Verification email resent successfully!
                                </p>
                                
                                <!-- Cancel success message -->
                                <p v-if="emailCancelSuccess" class="mt-2 text-xs text-green-600 dark:text-green-400">
                                    Email change cancelled successfully!
                                </p>
                                
                                <!-- Error message -->
                                <p v-if="emailChangeError" class="mt-2 text-xs text-red-600 dark:text-red-400">
                                    {{ emailChangeError }}
                                </p>
                            </div>

                            <!-- New email input form -->
                            <div v-if="newEmailInputVisible" class="mt-2">
                                <div class="grid gap-2">
                                    <Label for="new_email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        New Email Address
                                        <span v-if="isCheckingEmail" class="ml-2 text-xs text-gray-500 dark:text-gray-400 animate-pulse">
                                            Checking...
                                        </span>   
                                        <span v-else-if="emailRegexMessage === true" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                            ✗ Invalid email
                                        </span>
                                        <span v-else-if="emailAvailable === true" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                            ✓ Available
                                        </span>
                                        <span v-else-if="emailAvailable === false" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                            ✗ Already taken
                                        </span>
                                    </Label>
                                    <div class="relative">
                                        <Input
                                            id="new_email"
                                            type="email"
                                            v-model="newEmailForm.new_email"
                                            class="block w-full pr-10"
                                            required
                                            placeholder="Enter your new email address"
                                        />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg v-if="isCheckingEmail" class="w-5 h-5 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else-if="emailAvailable === true" class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else-if="emailAvailable === false" class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p v-if="emailRegexMessage !== true || emailAvailable !== false" class="text-xs text-gray-600 dark:text-gray-400">
                                        Email must be a valid format (example@domain.com) and must be unique.
                                    </p>
                                    <p v-if="emailRegexMessage === true" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                        Please enter a valid email address.
                                    </p>
                                    <p v-if="emailAvailable === false" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                        This email is already in use. Please use another email address.
                                    </p>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <Button 
                                            type="button" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                            @click="requestEmailChange"
                                            :disabled="emailAvailable !== true || isProcessingEmailChange || emailRegexMessage === true"
                                        >
                                            {{ isProcessingEmailChange ? 'Sending...' : 'Send Verification' }}
                                        </Button>
                                        <Button 
                                            type="button" 
                                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                            @click="cancelEmailChange"
                                        >
                                            Cancel
                                        </Button>
                                    </div>
                                    
                                    <!-- Success message -->
                                    <p v-if="emailChangeSuccess" class="text-sm text-green-600 dark:text-green-400 mt-2">
                                        {{ emailChangeSuccess }}
                                    </p>
                                    
                                    <!-- Error message -->
                                    <p v-if="emailChangeError" class="text-sm text-red-600 dark:text-red-400 mt-2">
                                        {{ emailChangeError }}
                                    </p>
                                </div>
                            </div>

                            <!-- Email verification notice for current email -->
                            <div v-if="user.email_verified_at === null && !hasPendingEmail" class="px-4 py-3 bg-amber-50 border border-amber-200 rounded-md text-sm text-amber-700 dark:bg-amber-900/30 dark:border-amber-700 dark:text-amber-400">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    Your email address is unverified.
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="ml-2 underline text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>
                            </div>
                        </div>

                        <!-- Save button -->
                        <div class="flex items-center justify-between pt-2">
                            <Button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                :disabled="profileForm.processing || usernameAvailable === false || emailAvailable === false"
                            >
                                {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                            
                            <div v-if="profileSuccess" class="px-4 py-2 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 dark:bg-green-900/30 dark:border-green-700 dark:text-green-400 animate-fade-in-out">
                                Profile updated successfully!
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Password form -->
                <div v-if="activeTab === 'Password'" class="px-4 sm:px-6 py-6">
                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <!-- Current Password -->
                        <div class="grid gap-2">
                            <Label for="current_password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Current Password
                                <span v-if="isCheckingPassword" class="ml-2 text-xs text-gray-500 dark:text-gray-400 animate-pulse">
                                    Checking...
                                </span>
                                <span v-else-if="passwordAvailable === true" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                    ✓ Correct password
                                </span>
                                <span v-else-if="passwordAvailable === false" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                    ✗ Incorrect password
                                </span>
                            </Label>
                            <div class="relative">
                                <Input 
                                    id="current_password" 
                                    v-model="passwordForm.current_password" 
                                    :type="showCurrentPassword ? 'text' : 'password'" 
                                    class="block w-full pr-10" 
                                    required
                                    autocomplete="current-password"
                                    placeholder="Your current password"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button 
                                        type="button" 
                                        @click="showCurrentPassword = !showCurrentPassword" 
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                    >
                                        <svg v-if="showCurrentPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <svg v-if="isCheckingPassword" class="w-5 h-5 text-gray-400 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else-if="passwordAvailable === true" class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="passwordAvailable === false" class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <InputError :message="passwordForm.errors.current_password" />
                            <p class="text-xs text-gray-600 dark:text-gray-400">    
                                Please enter your current password to update your password.
                            </p>
                            <p v-if="passwordAvailable === false" class="text-xs text-red-600 dark:text-red-400 mt-1">
                                Incorrect password. Please try again.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- New Password -->
                            <div class="grid gap-2">
                                <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    New Password
                                    <span v-if="passwordRegexMessage" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                        ✗ Doesn't meet requirements
                                    </span>
                                    <span v-else-if="passwordForm.password && !passwordRegexMessage" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                        ✓ Meets requirements
                                    </span>
                                </Label>
                                <div class="relative">
                                    <Input 
                                        id="password" 
                                        v-model="passwordForm.password" 
                                        :type="showNewPassword ? 'text' : 'password'" 
                                        class="block w-full pr-10" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="New password"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <button 
                                            type="button" 
                                            @click="showNewPassword = !showNewPassword" 
                                            class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                        >
                                            <svg v-if="showNewPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <svg v-if="passwordForm.password && !passwordRegexMessage" class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else-if="passwordRegexMessage" class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <InputError :message="passwordForm.errors.password" />
                                <p :class="[
                                    passwordRegexMessage && passwordForm.password 
                                        ? 'text-xs text-red-600 dark:text-red-400' 
                                        : 'text-xs text-gray-600 dark:text-gray-400'
                                ]">
                                    Password must be at least 8 characters long with at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="grid gap-2">
                                <Label for="password_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Confirm Password
                                    <span v-if="!passwordsMatch && passwordForm.password_confirmation" class="ml-2 text-xs text-red-600 dark:text-red-400">
                                        ✗ Passwords don't match
                                    </span>
                                    <span v-else-if="passwordsMatch && passwordForm.password && passwordForm.password_confirmation" class="ml-2 text-xs text-green-600 dark:text-green-400">
                                        ✓ Passwords match
                                    </span>
                                </Label>
                                <div class="relative">
                                    <Input 
                                        id="password_confirmation" 
                                        v-model="passwordForm.password_confirmation" 
                                        :type="showConfirmPassword ? 'text' : 'password'" 
                                        class="block w-full pr-10" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="Confirm new password"
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
                                        <svg v-if="passwordsMatch && passwordForm.password && passwordForm.password_confirmation" class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else-if="!passwordsMatch && passwordForm.password_confirmation" class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <InputError :message="passwordForm.errors.password_confirmation" />
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Please re-enter your new password to confirm.
                                </p>
                            </div>
                        </div>

                        <!-- Save button -->
                        <div class="flex items-center justify-between pt-2">
                            <Button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                :disabled="passwordForm.processing || passwordAvailable === false || passwordRegexMessage || !passwordsMatch"
                            >
                                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                            </Button>
                            
                            <div v-if="passwordSuccess" class="px-4 py-2 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 dark:bg-green-900/30 dark:border-green-700 dark:text-green-400 animate-fade-in-out">
                                Password updated successfully!
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-out {
    animation: fadeInOut 3s ease-in-out;
}

@keyframes fadeInOut {
    0%, 100% { opacity: 0; }
    20%, 80% { opacity: 1; }
}

/* Responsive design */
@media (max-width: 640px) {
    .sm\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
}
</style>
