<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, User, Mail, Lock, CreditCard, Phone, UserCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { watch } from 'vue';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { showToast } from '@/lib/toast';
import { bankSelection } from '@/lib/bank';
import { MalaysianState, stateSelection, citySelection } from '@/lib/address';
import StatusBadge from '@/components/ui/StatusBadge.vue';



// Define route function if it doesn't exist in TS
declare function route(name: string, params?: any): string;


// Add modal state variables
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('User created successfully.');

const form = useForm({
    name: '',
    username: '',
    ic_number: '',
    phone: '',
    email: '',
    password: '',
    password_confirmation: '',
});

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

    form.phone = form.phone.replace(/\D/g, '');
    form.ic_number = form.ic_number.replace(/\D/g, '');
    
    // If there are errors, show the modal and do not submit
    if (hasErrors) {
        showErrorModal.value = true;
        return;
    }

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const formatPhoneNumber = (event: Event) => {
    const input = event.target as HTMLInputElement;
    // Remove all non-numeric characters
    let phoneNumber = input.value.replace(/\D/g, '');
    
    // Format the phone number (Malaysia format: 011-3778 1946)
    if (phoneNumber.length > 0) {
        if (phoneNumber.length > 11) {
            phoneNumber = phoneNumber.slice(0, 11); // Limit to 11 digits
        }
        
        // Apply formatting
        if (phoneNumber.length > 3) {
            phoneNumber = phoneNumber.replace(/(\d{3})(\d{1,})/, '$1-$2');
            if (phoneNumber.length > 7) {
                phoneNumber = phoneNumber.replace(/(\d{3})-(\d{4})(\d{1,})/, '$1-$2 $3');
            }
        }
    }
    
    form.phone = phoneNumber;
};

const formatICNumber = (event: Event) => {
    const input = event.target as HTMLInputElement;
    // Remove all non-numeric characters
    let icNumber = input.value.replace(/\D/g, '');
    
    // Format the IC number (Malaysian IC format: XXXXXX-XX-XXXX)
    if (icNumber.length > 0) {
        if (icNumber.length > 12) {
            icNumber = icNumber.slice(0, 12); // Limit to 12 digits
        }
        
        // Apply formatting
        if (icNumber.length > 6) {
            icNumber = icNumber.replace(/(\d{6})(\d{1,})/, '$1-$2');
            if (icNumber.length > 9) {
                icNumber = icNumber.replace(/(\d{6})-(\d{2})(\d{1,})/, '$1-$2-$3');
            }
        }
    }
    
    form.ic_number = icNumber;
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
        const response = await axios.post(route('api.check-username'), { username });
        usernameAvailable.value = response.data.available;
    } catch (error) {
        console.error('Failed to check username:', error);
        usernameAvailable.value = null;
    } finally {
        isCheckingUsername.value = false;
    }
}, 500);


// IC number validation
const icNumberValid = ref<boolean | null>(null);
const icNumberAvailable = ref<boolean | null>(null);
const isCheckingICNumber = ref(false);
// Check ic number availability
const checkICNumberAvailability = debounce(async (icNum: string) => {
    // Remove any hyphens from the IC number
    const cleanIcNum = icNum.replace(/-/g, '');
    
    if (!cleanIcNum || cleanIcNum.length !== 12) {
        icNumberAvailable.value = null;
        icNumberValid.value = false;
        return;
    }
    
    icNumberValid.value = true;

    try {
        isCheckingICNumber.value = true;
        const response = await axios.post(route('api.check-ic-number'), { ic_num: cleanIcNum });
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

//PASSWORD VALIDATION
// Password validation
const passwordRegexMessage = ref(false);
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
const passwordsMatch = ref(true);

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

watch(() => form.ic_number, (newValue) => {
    checkICNumberAvailability(newValue);
});

watch(() => form.password, (newValue) => {
    validateNewPassword();
    validatePasswordConfirmation();
});

watch(() => form.password_confirmation, () => {
    validatePasswordConfirmation();
});

const closeErrorModal = () => {
    showErrorModal.value = false;
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

const showPassword = ref(false);
const showConfirmPassword = ref(false);

</script>

<template>
    <div class="auth-page">
        <Head title="Register" />

        <div class="split-container">
            <!-- Left side: Form -->
            <div class="split-form">
                <div class="form-container">
                    <div class="auth-header">
                        <a href="/">
                            <img src="/newlogo.png" alt="Loan Bram" class="form-logo" />
                        </a>
                        <h2 class="auth-title">Create an Account</h2>
                        <p class="auth-description">Join Loan Bram for streamlined loan management</p>
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <div class="form-grid">
                            <!-- Full Name -->
                            <div class="form-group span-full">
                                <Label for="name" class="form-label">Full Name</Label>
                                <div class="input-with-icon">
                                    <User class="input-icon" />
                                    <Input 
                                        id="name" 
                                        type="text" 
                                        required 
                                        autofocus 
                                        :tabindex="1" 
                                        autocomplete="name" 
                                        v-model="form.name" 
                                        placeholder="John Doe"
                                        class="auth-input" 
                                    />
                                </div>
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Username -->
                            <div class="form-group span-half">
                                <Label for="username" class="form-label">Username</Label>
                                <div class="input-with-icon">
                                    <UserCircle class="input-icon" />
                                    <Input 
                                        id="username" 
                                        type="text" 
                                        required 
                                        :tabindex="2" 
                                        autocomplete="username" 
                                        v-model="form.username" 
                                        placeholder="johndoe123"
                                        class="auth-input" 
                                    />
                                </div>
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
                                <div v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</div>
                                <div v-else-if="usernameAvailable === false" class="text-red-500 text-sm mt-1">This username is already taken</div>
                                <div v-else-if="usernameAvailable === true" class="text-green-500 text-sm mt-1">Username is available</div>
                                <div v-else-if="form.username && form.username.length < 3" class="text-orange-500 text-sm mt-1">Username must be at least 3 characters</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Username must be at least 3 characters
                                </p>
                            </div>

                            <!-- Email Address -->
                            <div class="form-group span-half">
                                <Label for="email" class="form-label">Email Address</Label>
                                <div class="input-with-icon">
                                    <Mail class="input-icon" />
                                    <Input 
                                        id="email" 
                                        type="email" 
                                        required 
                                        :tabindex="3" 
                                        autocomplete="email" 
                                        v-model="form.email" 
                                        placeholder="email@example.com"
                                        class="auth-input" 
                                    />
                                </div>
                                <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                                <div v-else-if="emailAvailable === false" class="text-red-500 text-sm mt-1">This email is already registered</div>
                                <div v-else-if="emailAvailable === true" class="text-green-500 text-sm mt-1">Email is available</div>
                                <div v-else-if="emailRegexMessage" class="text-orange-500 text-sm mt-1">Please enter a valid email address</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Enter a valid email address
                                </p>
                            </div>

                            <!-- IC Number -->
                            <div class="form-group span-half">
                                <Label for="ic_number" class="form-label">IC Number</Label>
                                <div class="input-with-icon">
                                    <CreditCard class="input-icon" />
                                    <Input 
                                        id="ic_number" 
                                        type="text" 
                                        required 
                                        :tabindex="4" 
                                        v-model="form.ic_number" 
                                        placeholder="000000-00-0000"
                                        class="auth-input"
                                        @input="formatICNumber"
                                    />
                                </div>
                                <div v-if="form.errors.ic_number" class="text-red-500 text-sm mt-1">{{ form.errors.ic_number }}</div>
                                <div v-else-if="icNumberAvailable === false" class="text-red-500 text-sm mt-1">This IC number is already registered</div>
                                <div v-else-if="!icNumberValid && form.ic_number" class="text-orange-500 text-sm mt-1">Please enter a valid IC number</div>
                                <div v-else-if="icNumberAvailable === true" class="text-green-500 text-sm mt-1">IC number is valid</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Please enter a valid phone number
                                </p>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group span-half">
                                <Label for="phone" class="form-label">Phone Number</Label>
                                <div class="input-with-icon">
                                    <Phone class="input-icon" />
                                    <Input 
                                        id="phone" 
                                        type="text" 
                                        required 
                                        :tabindex="5" 
                                        autocomplete="tel" 
                                        v-model="form.phone" 
                                        placeholder="011-123 4567"
                                        class="auth-input"
                                        @input="formatPhoneNumber"
                                    />

                                </div>
                                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
                                <div v-else-if="form.phone && form.phone.length < 10" class="text-orange-500 text-sm mt-1">Please enter a complete phone number</div>
                                <div v-else-if="form.phone && form.phone.length >= 10" class="text-green-500 text-sm mt-1">Valid phone number format</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Please enter a valid phone number
                                </p>
                            </div>

                            <!-- Password -->
                            <div class="form-group span-half">
                                <Label for="password" class="form-label">Password</Label>
                                <div class="input-with-icon">
                                    <Lock class="input-icon" />
                                    <Input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        required
                                        :tabindex="6"
                                        autocomplete="new-password"
                                        v-model="form.password"
                                        placeholder="Create a strong password"
                                        class="auth-input"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showPassword = !showPassword" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                    >
                                        <span v-if="showPassword">Hide</span>
                                        <span v-else>Show</span>
                                    </button>
                                </div>
                                <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                                <div v-else-if="passwordRegexMessage && form.password" class="text-orange-500 text-sm mt-1">Password must have at least 8 characters, including uppercase, lowercase, number and special character</div>
                                <div v-else-if="form.password && !passwordRegexMessage" class="text-green-500 text-sm mt-1">Strong password</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    8+ characters with uppercase, lowercase, number and special character
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group span-half">
                                <Label for="password_confirmation" class="form-label">Confirm Password</Label>
                                <div class="input-with-icon">
                                    <Lock class="input-icon" />
                                    <Input
                                        id="password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        required
                                        :tabindex="7"
                                        autocomplete="new-password"
                                        v-model="form.password_confirmation"
                                        placeholder="Confirm your password"
                                        class="auth-input"
                                    />
                                    <button 
                                        type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                    >
                                        <span v-if="showConfirmPassword">Hide</span>
                                        <span v-else>Show</span>
                                    </button>
                                </div>
                                <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
                                <div v-else-if="form.password_confirmation && !passwordsMatch" class="text-red-500 text-sm mt-1">Passwords do not match</div>
                                <div v-else-if="form.password_confirmation && passwordsMatch" class="text-green-500 text-sm mt-1">Passwords match</div>
                                <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                    Re-enter your password for confirmation
                                </p>
                            </div>

                            <div class="terms-agreement span-full">
                                <p class="terms-text">
                                    By creating an account, you agree to our 
                                    <a href="#" class="terms-link">Terms of Service</a> and 
                                    <a href="#" class="terms-link">Privacy Policy</a>
                                </p>
                            </div>

                            <Button type="submit" class="submit-button span-full" :tabindex="8" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                <span>Create Account</span>
                            </Button>
                        </div>

                        <div class="auth-footer">
                            <div class="auth-divider">
                                <hr />
                                <span>OR</span>
                                <hr />
                            </div>
                            
                            <div class="login-prompt">
                                Already have an account?
                                <TextLink :href="route('login')" class="login-link" :tabindex="9">Sign in</TextLink>
                            </div>
                            
                            <div class="home-button-container">
                                <TextLink :href="route('home')" class="home-button" :tabindex="10">
                                    <span>← Back to Home</span>
                                </TextLink>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right side: Image/Branding -->
            <div class="split-image">
                <div class="brand-overlay">
                    <img src="/loanbram-white.png" alt="Loan Bram" class="auth-logo-large" />
                    <p class="brand-tagline">Smart financial solutions for your business growth</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <ErrorModal
        :show="showErrorModal"
        :messages="errorMessages"
        @close="closeErrorModal"
    />

    <!-- Success Modal -->
    <SuccessModal
        :show="showSuccessModal"
        modalTitle="User Created Successfully"
        :message="successMessage"
        @close="closeSuccessModal"
    />
</template>



<style scoped>
.auth-page {
    min-height: 100vh;
    background-color: #f8fafc;
}

.split-container {
    display: flex;
    min-height: 100vh;
    flex-direction: row-reverse;
}

.split-image {
    display: none;
    background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2073&q=80');
    background-size: cover;
    background-position: center;
    position: relative;
}

.brand-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.8));
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    text-align: center;
}

.auth-logo-large {
    max-width: 70%;
    height: auto;
    margin-bottom: 2rem;
    filter: drop-shadow(0 0 0.5rem rgba(0, 0, 0, 0.5));
    animation: fadeInDown 0.8s ease-out;
}

.brand-tagline {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    max-width: 24rem;
    line-height: 1.6;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    animation: fadeInDown 1.2s ease-out;
}

.split-form {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.form-container {
    width: 100%;
    max-width: 36rem;
    animation: fadeInLeft 0.6s ease-out forwards;
}

.auth-header {
    margin-bottom: 2rem;
    text-align: center;
}

.form-logo {
    max-width: 50px;
    height: auto;
    margin-bottom: 1.5rem;
    animation: fadeIn 0.8s ease-out;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.auth-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.5rem;
    animation: fadeIn 1s ease-out;
}

.auth-description {
    color: #64748b;
    font-size: 1rem;
    animation: fadeIn 1.2s ease-out;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.span-full {
    grid-column: span 2;
}

.span-half {
    grid-column: span 1;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    animation: fadeIn 1.2s ease-out;
}

.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    color: #334155;
}

.input-with-icon {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    height: 1rem;
    width: 1rem;
}

.auth-input {
    padding-left: 2.5rem;
    height: 3rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    background-color: #f8fafc;
    width: 100%;
}

.auth-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.input-hint {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: -0.25rem;
}

.terms-agreement {
    margin-top: 0.5rem;
    animation: fadeIn 1.4s ease-out;
}

.terms-text {
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.5;
}

.terms-link {
    color: #3b82f6;
    transition: color 0.2s;
}

.terms-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.submit-button {
    height: 3rem;
    background-color: #0f172a;
    color: white;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    animation: fadeIn 1.6s ease-out;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 0.5rem;
}

.submit-button:hover:not(:disabled) {
    background-color: #1e293b;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.submit-button:active:not(:disabled) {
    transform: translateY(0);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.auth-footer {
    animation: fadeIn 1.8s ease-out;
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 1rem 0;
    color: #94a3b8;
    font-size: 0.75rem;
}

.auth-divider hr {
    flex-grow: 1;
    border: none;
    border-top: 1px solid #e2e8f0;
}

.auth-divider span {
    padding: 0 1rem;
}

.login-prompt {
    text-align: center;
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 1rem;
}

.login-link {
    color: #3b82f6;
    font-weight: 500;
    transition: color 0.2s;
    margin-left: 0.25rem;
}

.login-link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInLeft {
    from { 
        opacity: 0;
        transform: translateX(-20px);
    }
    to { 
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInDown {
    from { 
        opacity: 0;
        transform: translateY(-20px);
    }
    to { 
        opacity: 1;
        transform: translateY(0);
    }
}

/* Media Queries */
@media (min-width: 768px) {
    .split-image {
        display: block;
        flex: 1;
    }
}

@media (min-width: 1024px) {
    .split-image {
        flex: 1.2;
    }
}

@media (max-width: 767px) {
    .form-container {
        max-width: 100%;
    }
    
    .split-form {
        padding: 1.5rem;
        align-items: flex-start;
        padding-top: 4rem;
    }
    
    .split-container {
        flex-direction: column;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .span-half {
        grid-column: span 2;
    }
}
</style>
