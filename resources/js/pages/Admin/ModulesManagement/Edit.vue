<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
declare const route: any;

const page = usePage<SharedData>();
const userAny = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (userAny.value as any)?.role || 'user');
const userStatus = computed(() => (userAny.value as any)?.status || 'not active');
const props = defineProps<{
    module: {
        id: number;
        slug: string;
        name: string;
        description: string;
        logo: string | null;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Management',
        href: '#',
    },
    {
        title: 'Modules Management',
        href: '/admin/modules',
    },
    {
        title: 'Edit Module',
        href: `/admin/modules/${props.module.id}/edit`,
    },
];

const form = useForm({
    name: props.module.name,
    description: props.module.description,
    logo: null as File | null,
    status: props.module.status,
    _method: 'PUT',
});

const logoPreview = ref<string | null>(props.module.logo);

const handleLogoChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        form.logo = file;
        
        // Create a preview URL
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('modules.management.update', { id: props.module.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.logo = null;
        },
    });
};
</script>

<template>
    <Head title="Edit Module" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin' || userRole === 'superuser' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Edit Module</h1>
                <p class="text-gray-600 dark:text-gray-300">Update the loan module details</p>
            </div>
            
            <!-- Form Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden p-4 sm:p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Module Name</label>
                        <input 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Enter module name"
                            required
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>
                    
                    <!-- Description Field -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea 
                            id="description" 
                            v-model="form.description" 
                            rows="4" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Enter module description"
                            required
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                    </div>
                    
                    <!-- Logo Field -->
                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Banner</label>
                        <div class="mt-1 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div v-if="logoPreview" class="flex-shrink-0 h-32 w-full sm:h-16 sm:w-40 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                <img :src="logoPreview" alt="Banner Preview" class="h-full w-full object-contain" />
                            </div>
                            <div v-else class="flex-shrink-0 h-32 w-full sm:h-16 sm:w-40 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-gray-400 text-xs text-center">No banner selected</span>
                            </div>
                            <div class="w-full sm:w-auto">
                                <label class="cursor-pointer bg-white dark:bg-gray-700 py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 inline-block">
                                    <span>Change Banner</span>
                                    <input 
                                        id="logo" 
                                        type="file" 
                                        class="sr-only" 
                                        accept="image/*"
                                        @change="handleLogoChange"
                                    />
                                </label>
                                <div class="text-xs text-gray-500 mt-1">Recommended size/ratio: 16:9</div>
                            </div>
                        </div>
                        <div v-if="form.errors.logo" class="text-red-500 text-sm mt-1">{{ form.errors.logo }}</div>
                    </div>
                    
                    <!-- Status Field -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select 
                            id="status" 
                            v-model="form.status" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row sm:justify-end gap-3 sm:gap-3 mt-6">
                        <Link 
                            :href="route('modules.management.index')" 
                            class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-center"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Module' }}
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
</template> 