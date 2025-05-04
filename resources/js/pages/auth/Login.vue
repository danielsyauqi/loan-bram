<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Mail, Lock } from 'lucide-vue-next';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { ref, onMounted } from 'vue';
// Define route function if it doesn't exist in TS
declare function route(name: string, params?: any): string;

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };

    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            successMessage.value = String(flash.success);
            showSuccessModal.value = true;
        }
    }
});

</script>

<template>
    <div class="auth-page">
        <Head title="Log in" />

        <div class="split-container">
            <!-- Left side: Brand Image -->
            <div class="split-image">
                <div class="brand-overlay">
                    <img src="/loanbram-white.png" alt="Loan Bram" class="auth-logo-large" />
                    <p class="brand-tagline">Streamlined loan management for modern businesses</p>
                </div>
            </div>

            <!-- Right side: Form -->
            <div class="split-form">
                <div class="form-container">
                    <div class="auth-header">
                        <h2 class="auth-title">Welcome Back</h2>
                        <p class="auth-description">Sign in to your account to continue</p>
                    </div>

                    <div v-if="status" class="status-message">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="auth-form">
                        <div class="form-fields">
                            <div class="form-group">
                                <Label for="emailusername" class="form-label">Login</Label>
                                <div class="input-with-icon">
                                    <Mail class="input-icon" />
                                    <Input
                                        id="emailusername"
                                        type="text"
                                        required
                                        autofocus
                                        :tabindex="1"
                                        autocomplete="email"
                                        v-model="form.email"
                                        placeholder="Email address / Username"
                                        class="auth-input"
                                    />
                                </div>
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="form-group">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="form-label">Password</Label>
                                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="forgot-password" :tabindex="5">
                                        Forgot password?
                                    </TextLink>
                                </div>
                                <div class="input-with-icon">
                                    <Lock class="input-icon" />
                                    <Input
                                        id="password"
                                        type="password"
                                        required
                                        :tabindex="2"
                                        autocomplete="current-password"
                                        v-model="form.password"
                                        placeholder="Password"
                                        class="auth-input"
                                    />
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="remember-me-container" :tabindex="3">
                                <Label for="remember" class="remember-me-label">
                                    <Checkbox id="remember" v-model:checked="form.remember" :tabindex="4" />
                                    <span>Remember me</span>
                                </Label>
                            </div>

                            <Button type="submit" class="submit-button" :tabindex="4" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                <span>Sign In</span>
                            </Button>
                        </div>

                        <div class="auth-footer">
                            <div class="auth-divider">
                                <hr />
                                <span>OR</span>
                                <hr />
                            </div>
                            
                            <div class="register-prompt">
                                Don't have an account?
                                <TextLink :href="route('register')" :tabindex="5" class="register-link">Create an account</TextLink>
                            </div>
                            
                            <div class="home-button-container mt-4">
                                <TextLink :href="route('home')" class="home-button" :tabindex="6">
                                    <span>← Back to Home</span>
                                </TextLink>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success modal -->
    <SuccessModal
        :show="showSuccessModal"
        title="Application Submitted Successfully"
        :message="successMessage"
        :details="successDetails"
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
}

.split-image {
    display: none;
    background-image: url('/sidebanner2.jpg');
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
    max-width: 28rem;
    animation: fadeInRight 0.6s ease-out forwards;
}

.auth-header {
    margin-bottom: 2.5rem;
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

.status-message {
    margin-bottom: 1.5rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-color: #ecfdf5;
    color: #047857;
    text-align: center;
    font-size: 0.875rem;
    animation: fadeIn 0.6s ease-out;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
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

.forgot-password {
    font-size: 0.75rem;
    color: #3b82f6;
    transition: color 0.2s;
}

.forgot-password:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.remember-me-container {
    display: flex;
    animation: fadeIn 1.4s ease-out;
}

.remember-me-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
    cursor: pointer;
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

@keyframes fadeInRight {
    from { 
        opacity: 0;
        transform: translateX(20px);
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
}
</style>

        padding-top: 4rem;