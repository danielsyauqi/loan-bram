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
import { ref, onMounted, watch, computed} from 'vue';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { showToast } from '@/lib/toast';
import axios from 'axios';
import VueCookies from 'vue-cookies';



// Define route function if it doesn't exist in TS
declare function route(name: string, params?: any): string;




// Add modal state variables
const showErrorModal = ref(false);
const errorMessages = ref<string[]>([]);

// Add state variables for success modal
const showSuccessModal = ref(false);
const successMessage = ref('User created successfully.');

// Step state
const step = ref(1);

// Step 1: Email verification
const emailForm = useForm({
    email: '',
});
const emailVerificationStatus = ref<'idle' | 'sending' | 'sent' | 'error'>('idle');
const emailVerificationError = ref('');

// Step 2: Personal details
const detailsForm = useForm({
    name: '',
    username: '',
    ic_number: '',
    phone: '',
});

// Step 3: Password setup
const passwordForm = useForm({
    password: '',
    password_confirmation: '',
});

// Store verified email (from session or after verification)
const verifiedEmail = ref('');

// Retrieve the preverify_token cookie value
function getCookie(name: string): string | null {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()!.split(';').shift() || null;
    return null;
}


// Watch for step changes to 2 or 3 and refetch verified email
watch(step, async (newStep) => {
    if (newStep === 2 || newStep === 3) {
        try {
            const response = await axios.get(route('email.preverify.current'));
            if (response.data.verified_email) {
                verifiedEmail.value = response.data.verified_email;
                emailForm.email = response.data.verified_email;
            }
        } catch (e) {}
    }
});

// Add for frontend code verification
const verificationForm = useForm({
    code: '',
});
const codeVerificationStatus = ref('');
const verificationError = ref('');

// Step 1: Send verification email (real backend)
const sendVerificationEmail = async () => {
    // Validate email format
    if (!emailForm.email || !emailRegex.test(emailForm.email)) {
        emailVerificationStatus.value = 'error';
        emailVerificationError.value = 'Please enter a valid email address.';
        return;
    }
    emailVerificationStatus.value = 'sending';
    emailVerificationError.value = '';
    try {
        await emailForm.post(route('email.preverify.send'), {
            preserveScroll: true,
            onSuccess: () => {
                emailVerificationStatus.value = 'sent';
            },
            onError: (errors) => {
                emailVerificationStatus.value = 'error';
                emailVerificationError.value = errors.email || 'Failed to send verification email.';
            },
        });
    } catch (e) {
        emailVerificationStatus.value = 'error';
        emailVerificationError.value = 'Failed to send verification email.';
    }
};

// Step 1.5: Verify code (real backend)
const verifyEmailCode = async () => {
    codeVerificationStatus.value = 'verifying';
    verificationError.value = '';
    try {
        await axios.post(route('email.preverify.verify'), { code: verificationForm.code });
        codeVerificationStatus.value = 'verified';
        verifiedEmail.value = emailForm.email;
        step.value = 2;
    } catch (err) {
        // console.log('Error verifying email code:', err); // Hide 422 error from console
        const e = err as any;
        codeVerificationStatus.value = 'error';
        if (e.response && e.response.status === 422 && e.response.data && e.response.data.message) {
            verificationError.value = e.response.data.message;
        } else {
            verificationError.value = 'Invalid or expired code.';
        }
    } finally {
        codeVerificationStatus.value = 'idle';
    }
};

// Step 2: Personal details validation (reuse your existing logic)
// ... existing username, IC, phone validation ...

const goToStep3 = () => {
    errorMessages.value = [];
    // Check required fields for step 2
    if (!detailsForm.name) errorMessages.value.push('Full Name is required.');
    if (!detailsForm.username) errorMessages.value.push('Username is required.');
    if (!detailsForm.ic_number) errorMessages.value.push('IC Number is required.');
    if (!detailsForm.phone) errorMessages.value.push('Phone Number is required.');
    if (usernameAvailable.value === false) errorMessages.value.push('Username is already taken.');
    if (icNumberAvailable.value === false) errorMessages.value.push('IC Number is already registered.');
    if (errorMessages.value.length > 0) {
        showErrorModal.value = true;
        return;
    }
    step.value = 3;
};

// Step 3: Password validation (reuse your existing logic)
const finalForm = useForm({
    email: '',
    name: '',
    username: '',
    ic_number: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const completeRegistration = () => {
    errorMessages.value = [];
    // Check required fields for step 3
    if (!passwordForm.password) errorMessages.value.push('Password is required.');
    if (!passwordForm.password_confirmation) errorMessages.value.push('Password confirmation is required.');
    if (passwordForm.password !== passwordForm.password_confirmation) errorMessages.value.push('Passwords do not match.');
    // Password rules
    passwordRules.forEach(rule => {
        if (!rule.test(passwordForm.password)) errorMessages.value.push(rule.label);
    });
    if (errorMessages.value.length > 0) {
        showErrorModal.value = true;
        return;
    }
    finalForm.email = verifiedEmail.value || emailForm.email;
    finalForm.name = detailsForm.name;
    finalForm.username = detailsForm.username;
    finalForm.ic_number = detailsForm.ic_number;
    finalForm.phone = detailsForm.phone;
    finalForm.password = passwordForm.password;
    finalForm.password_confirmation = passwordForm.password_confirmation;
    finalForm.post(route('register.finished'), {
        onFinish: () => {
            finalForm.reset('password', 'password_confirmation');
        },
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
    
    detailsForm.phone = phoneNumber;
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

        // Apply formatting: XXXXXX-XX-XXXX
        if (icNumber.length > 6) {
            icNumber = icNumber.replace(/(\d{6})(\d{1,})/, '$1-$2');
            if (icNumber.length > 8) {
                icNumber = icNumber.replace(/(\d{6})-(\d{2})(\d{1,})/, '$1-$2-$3');
            }
        }
    }

    detailsForm.ic_number = icNumber;
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
    console.log('icNum', icNum);
    
    if (!icNum || icNum.length !== 14) {
        icNumberAvailable.value = null;
        icNumberValid.value = false;
        return;
    }

    // Also check if dashIcNum matches the dash-prefixed format
    if (!/^\d{6}-\d{2}-\d{4}$/.test(icNum)) {
        icNumberValid.value = false;
        icNumberAvailable.value = null;
        return;
    }

    icNumberValid.value = true;

    try {
        isCheckingICNumber.value = true;
        // Send both formats for clarity, but backend expects cleanIcNum
        const response = await axios.post(route('api.check-ic-number'), { ic_num: icNum, ic_num_dash: icNum });
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
    if (!passwordForm.password) {
        passwordRegexMessage.value = false;
        return;
    }
    
    passwordRegexMessage.value = !passwordRegex.test(passwordForm.password);
};

// For validating password confirmation
const validatePasswordConfirmation = () => {
    if (!passwordForm.password || !passwordForm.password_confirmation) {
        passwordsMatch.value = true;
        return;
    }
    
    passwordsMatch.value = passwordForm.password === passwordForm.password_confirmation;
};

// Watch for changes to form fields
watch(() => detailsForm.username, (newValue) => {
    checkUsernameAvailability(newValue);
});

watch(() => emailForm.email, (newValue) => {
    checkEmailAvailability(newValue);
});

watch(() => detailsForm.ic_number, (newValue) => {
    checkICNumberAvailability(newValue);
});

watch(() => passwordForm.password, (newValue) => {
    validateNewPassword();
    validatePasswordConfirmation();
});

watch(() => passwordForm.password_confirmation, () => {
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

// Password rules for UI
const passwordRules = [
    { label: 'At least 8 characters', test: (pw: string) => pw.length >= 8 },
    { label: 'At least one uppercase letter', test: (pw: string) => /[A-Z]/.test(pw) },
    { label: 'At least one lowercase letter', test: (pw: string) => /[a-z]/.test(pw) },
    { label: 'At least one number', test: (pw: string) => /\d/.test(pw) },
    { label: 'At least one special character', test: (pw: string) => /[@$!%*?&]/.test(pw) },
];

// Cancel email verification and reset registration
const cancelEmailVerification = async () => {
    try {
        await axios.post(route('email.preverify.cancel'), { email: verifiedEmail.value || emailForm.email });
        // Reset all forms and state
        emailForm.email = '';
        detailsForm.name = '';
        detailsForm.username = '';
        detailsForm.ic_number = '';
        detailsForm.phone = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
        verifiedEmail.value = '';
        location.reload();
    } catch (e) {
        showToast('Failed to cancel verification. Please try again.', 'error');
    }
};

const confirmPasswordError = computed(() => {
    if (!passwordForm.password_confirmation) return '';
    if (passwordForm.password !== passwordForm.password_confirmation) {
        return 'Passwords do not match.';
    }
    return '';
});

const page = usePage();

onMounted(async () => {

    const cookies = document.cookie.split(';');
    let preverifyCode = null;
    
    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();
      if (cookie.startsWith('preverify_code=')) {
        preverifyCode = cookie.substring('preverify_code='.length);
        break;
      }
    }
    if(preverifyCode) {
        // Try to get the email from the code via backend
        try {
            const response = await axios.get(route('email.by-code'));
            if (response.data.email) {
                verifiedEmail.value = response.data.email;
                emailForm.email = response.data.email;

                if (step.value === 1) step.value = 2;
            }
        } catch (e) {
            // Not verified yet or token invalid/expired
            console.log('No valid email found for preverify_code');
        }
    }

});

</script>

<template>
    <div class="auth-page">
        <Head title="Register" />

        <div class="split-container">
            <!-- Left side: Form -->
            <div class="split-form">
                <div class="form-container">
                    <div class="auth-header">
                        <h2 class="auth-title">Create an Account</h2>
                        <p class="auth-description">Join Loan Bram for streamlined loan management</p>
                    </div>

                    <form v-if="step === 1" @submit.prevent="sendVerificationEmail" class="auth-form">
                        <div class="form-group span-full">
                            <Label for="email" class="form-label">Email Address</Label>
                            <div class="input-with-icon">
                                <Mail class="input-icon" />
                                <Input id="email" type="email" required autofocus autocomplete="email" v-model="emailForm.email" placeholder="email@example.com" class="auth-input" />
                            </div>
                            <InputError :message="emailForm.errors.email || emailVerificationError" />
                        </div>
                        <Button type="submit" class="submit-button span-full" :disabled="emailVerificationStatus === 'sending'">
                            <LoaderCircle v-if="emailVerificationStatus === 'sending'" class="h-4 w-4 animate-spin mr-2" />
                            <span v-else>Send Verification Email</span>
                        </Button>
                        <div v-if="emailVerificationStatus === 'sent'" class="text-green-500 mt-2">Verification code sent! Please check your email.</div>

                        <!-- Verification Code Section (frontend only) -->
                        <div v-if="emailVerificationStatus === 'sent'" class="form-group span-full mt-6">
                            <Label for="verification_code" class="form-label">Enter Verification Code</Label>
                            <div class="input-with-icon">
                                <svg class="input-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                <Input
                                    id="verification_code"
                                    type="text"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    v-model="verificationForm.code"
                                    placeholder="6-digit code"
                                    class="auth-input"
                                />
                            </div>
                            <InputError :message="verificationForm.errors.code || verificationError" />
                        </div>
                        <Button
                            v-if="emailVerificationStatus === 'sent'"
                            type="button"
                            class="submit-button span-full mt-2"
                            :disabled="!verificationForm.code || verificationForm.code.length !== 6"
                            @click="verifyEmailCode"
                        >
                            Verify Code
                        </Button>
                        <div v-if="codeVerificationStatus === 'verifying'" class="flex items-center text-blue-500 mt-2">
                            <LoaderCircle class="h-4 w-4 animate-spin mr-2" />
                            Verifying code...
                        </div>
                        <div v-if="codeVerificationStatus === 'verified'" class="text-green-500 mt-2">
                            Email verified! Proceeding to next step...
                        </div>
                        <div v-if="codeVerificationStatus === 'error'" class="text-red-500 mt-2">
                            {{ verificationError || 'Invalid or expired code.' }}
                        </div>
                        <!-- End Verification Code Section -->

                        <div class="auth-footer">
                            <div class="auth-divider">
                                <hr />
                                <span>OR</span>
                                <hr />
                            </div>
                            
                            <div class="register-prompt">
                                Already have an account?
                                <TextLink :href="route('login')" :tabindex="5" class="register-link">Sign in</TextLink>
                            </div>
                            
                            <div class="home-button-container mt-4">
                                <TextLink :href="route('home')" class="home-button" :tabindex="6">
                                    <span>← Back to Home</span>
                                </TextLink>
                            </div>
                        </div>
                    </form>
                    <form v-else-if="step === 2" @submit.prevent="goToStep3" class="auth-form">
                        <!-- Readonly Email Field -->
                        <div class="form-group span-full">
                            <Label for="verified_email" class="form-label">Email Address</Label>
                            <div class="input-with-icon">
                                <Mail class="input-icon" />
                                <span
                                    id="verified_email"
                                    class="auth-input bg-gray-100 cursor-not-allowed flex items-center h-10 px-3 rounded border border-gray-300 text-gray-700 select-text"
                                    tabindex="-1"
                                >{{ verifiedEmail }}</span>
                            </div>
                        </div>
                        <!-- Cancel Button -->
                        <div class="form-group span-full" style="margin-bottom: -1.5rem;">
                            <Button type="button" class="bg-red-600 hover:bg-red-700 text-white mb-2" @click="cancelEmailVerification">Cancel & Change Email</Button>
                        </div>
                        <!-- Personal details fields -->
                        <!-- Full Name -->
                        <div class="form-group span-full">
                            <Label for="name" class="form-label">Full Name</Label>
                            <div class="input-with-icon">
                                <User class="input-icon" />
                                <Input id="name" type="text" required autofocus autocomplete="name" v-model="detailsForm.name" placeholder="John Doe" class="auth-input" />
                            </div>
                            <InputError :message="detailsForm.errors.name" />
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
                                    v-model="detailsForm.username" 
                                    placeholder="johndoe123"
                                    class="auth-input" 
                                />
                            </div>
                            <div v-if="detailsForm.errors.username" class="text-red-500 text-sm mt-1">{{ detailsForm.errors.username }}</div>
                            <div v-else-if="usernameAvailable === false" class="text-red-500 text-sm mt-1">This username is already taken</div>
                            <div v-else-if="usernameAvailable === true" class="text-green-500 text-sm mt-1">Username is available</div>
                            <div v-else-if="detailsForm.username && detailsForm.username.length < 3" class="text-orange-500 text-sm mt-1">Username must be at least 3 characters</div>
                            <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                Username must be at least 3 characters
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
                                    v-model="detailsForm.ic_number" 
                                    placeholder="000000-00-0000"
                                    class="auth-input"
                                    @input="formatICNumber"
                                />
                            </div>
                            <div v-if="detailsForm.errors.ic_number" class="text-red-500 text-sm mt-1">{{ detailsForm.errors.ic_number }}</div>
                            <div v-else-if="icNumberAvailable === false" class="text-red-500 text-sm mt-1">This IC number is already registered</div>
                            <div v-else-if="!icNumberValid && detailsForm.ic_number" class="text-orange-500 text-sm mt-1">Please enter a valid IC number</div>
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
                                    v-model="detailsForm.phone" 
                                    placeholder="011-123 4567"
                                    class="auth-input"
                                    @input="formatPhoneNumber"
                                />

                            </div>
                            <div v-if="detailsForm.errors.phone" class="text-red-500 text-sm mt-1">{{ detailsForm.errors.phone }}</div>
                            <div v-else-if="detailsForm.phone && detailsForm.phone.length < 10" class="text-orange-500 text-sm mt-1">Please enter a complete phone number</div>
                            <div v-else-if="detailsForm.phone && detailsForm.phone.length >= 10" class="text-green-500 text-sm mt-1">Valid phone number format</div>
                            <p v-else class="text-xs text-gray-600 dark:text-gray-400">
                                Please enter a valid phone number
                            </p>
                        </div>

                        <div class="flex w-full gap-4">
                            <Button
                                type="submit"
                                class="submit-button"
                                style="flex: 0 0 70%; max-width: 70%;"
                            >
                                Next
                            </Button>
                            <Button
                                type="button"
                                class="back-button"
                                style="flex: 0 0 30%; max-width: 30%;"
                                @click="$inertia.visit(route('home'))"
                            >
                                Back to Home
                            </Button>
                        </div>
                    </form>
                    <form v-else-if="step === 3" @submit.prevent="completeRegistration" class="auth-form">
                        <!-- Password fields (reuse your existing fields/validation) -->
                        <!-- Password -->
                        <div class="form-group span-half">
                            <Label for="password" class="form-label">Password</Label>
                            <div class="input-with-icon">
                                <Lock class="input-icon" />
                                <Input id="password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password" v-model="passwordForm.password" placeholder="Create a strong password" class="auth-input" />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                    <span v-if="showPassword">Hide</span>
                                    <span v-else>Show</span>
                                </button>
                            </div>
                            <InputError :message="passwordForm.errors.password" />
                            <!-- Password rules -->
                            <ul class="password-rules">
                                <li v-for="rule in passwordRules" :key="rule.label" :class="{'rule-met': rule.test(passwordForm.password), 'rule-unmet': !rule.test(passwordForm.password)}">
                                    <span v-if="rule.test(passwordForm.password)" class="rule-icon">✔️</span>
                                    <span v-else class="rule-icon">❌</span>
                                    {{ rule.label }}
                                </li>
                            </ul>
                        </div>
                        <!-- Confirm Password -->
                        <div class="form-group span-half">
                            <Label for="password_confirmation" class="form-label">Confirm Password</Label>
                            <div class="input-with-icon">
                                <Lock class="input-icon" />
                                <Input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required autocomplete="new-password" v-model="passwordForm.password_confirmation" placeholder="Confirm your password" class="auth-input" />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                    <span v-if="showConfirmPassword">Hide</span>
                                    <span v-else>Show</span>
                                </button>
                            </div>
                            <InputError :message="confirmPasswordError" />
                        </div>
                        <!-- INSERT_YOUR_CODE -->
                        <!-- Back Button -->
                        <div class="form-group span-full" style="margin-bottom: -1.5rem;">
                            <Button type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 mb-2" @click="step = 2">
                                Back
                            </Button>
                        </div>
                        <Button @click="completeRegistration" class="submit-button span-full">Create Account</Button>
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
    <!--
    <SuccessModal
        :show="showSuccessModal"
        modalTitle="User Created Successfully"
        :message="successMessage"
        @close="closeSuccessModal"
    />
    -->
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
    background-image: url('sidebanner.jpg');
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

.back-button {
    background-color: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    height: 3rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    animation: fadeIn 1.6s ease-out;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 0.5rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.back-button:hover {
    background-color: #e2e8f0;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.back-button:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
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

.register-prompt {
    text-align: center;
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 1rem;
}

.register-link {
    color: #3b82f6;
    font-weight: 500;
    transition: color 0.2s;
    margin-left: 0.25rem;
}

.register-link:hover {
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

.password-rules {
    margin: 0.5rem 0 0 0;
    padding: 0;
    list-style: none;
}
.password-rules li {
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
    color: #64748b;
    transition: color 0.2s;
}
.password-rules .rule-icon {
    margin-right: 0.5rem;
    font-size: 1.1em;
}
.password-rules .rule-met {
    color: #16a34a;
}
.password-rules .rule-unmet {
    color: #64748b;
}
</style>
