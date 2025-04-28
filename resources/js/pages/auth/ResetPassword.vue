<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

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

const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Attach validation to input events
defineExpose({ validateNewPassword, validatePasswordConfirmation });

// Watchers for real-time validation
watch(() => form.password, validateNewPassword);
watch(() => form.password_confirmation, validatePasswordConfirmation);

// Define route function if it doesn't exist in TS
declare function route(name: string, params?: any): string;

</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
               

                <!-- Password Section -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-4 sm:gap-6">


                     <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="mt-1 block w-full text-gray-500 bg-gray-100" readonly />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Must meet requirements</span>
                            </div>
                            <div class="relative">
                                <input 
                                    id="password" 
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    class="block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    :class="{
                                        'border-green-500': form.password && !passwordRegexMessage,
                                        'border-red-500': passwordRegexMessage && form.password,
                                        'border-gray-300 dark:border-gray-600': !form.password
                                    }"
                                    @input="validateNewPassword"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button 
                                        type="button" 
                                        @click="showPassword = !showPassword" 
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                        tabindex="-1"
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
                                    @input="validatePasswordConfirmation"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button 
                                        type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword" 
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none mr-2"
                                        tabindex="-1"
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

                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Reset password
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
