<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    PencilIcon, 
    TrashIcon, 
    EyeIcon, 
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';
// @ts-ignore
import { debounce } from 'lodash';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { identifyBankByAccountNumber } from '@/lib/bank';

declare const route: any;

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');
const userStatus = computed(() => (user.value as any)?.status || 'not active');

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            username: string;
            email: string;
            phone_num: string;
            bank_name: string | null;
            bank_account: string | null;
            role: string;
            status: string;
            ic_num: string;
            user_photo: string | null;
            address: {
                id: number;
                address_line_1: string;
                address_line_2: string | null;
                city: string;
                state: string;
                zip: string;
                country: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search: string;
        role: string;
        status: string;
    };
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
];

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || 'all');
const status = ref(props.filters.status || 'all');

const debouncedSearch = debounce(() => {
    form.get(route('users.management.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['users'],
    });
}, 300);

const form = useForm({
    search: props.filters.search || '',
    role: props.filters.role || 'all',
    status: props.filters.status || 'all',
});

watch(search, (value) => {
    form.search = value;
    debouncedSearch();
});

watch(role, (value) => {
    form.role = value;
    form.get(route('users.management.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['users'],
    });
});

watch(status, (value) => {
    form.status = value;
    form.get(route('users.management.index'), {
        preserveState: true,
        preserveScroll: true,
        only: ['users'],
    });
});



const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'admin':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
        case 'agent':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
        case 'customer':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'sub agent':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100';
        case 'inactive':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        case 'not verified':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100';
    }
};


const confirmDelete = (id: number, name: string) => {
    userToDelete.value = { id, name };
    showDeleteModal.value = true;
};

const showDeleteModal = ref(false);
const userToDelete = ref<{ id: number; name: string } | null>(null);
const toastMessage = ref('');
const showToast = ref(false);
const showSuccessModal = ref(false);
const successMessage = ref('');
const successDetails = ref('');
const deleteForm = useForm({});

const closeToast = () => {
    showToast.value = false;
};

const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

const moduleName = ref('');

onMounted(() => {
    const page = usePage();
    const { flash } = page.props as { flash?: Record<string, any> };

    if (flash && typeof flash === 'object') {
        if ('success' in flash && flash.success) {
            successMessage.value = String(flash.success);

            if ('moduleName' in flash && flash.moduleName) {
                moduleName.value = String(flash.moduleName);
                successDetails.value = `Module Name: ${moduleName.value}`;
                showSuccessModal.value = true;
            } else {
                toastMessage.value = successMessage.value;
                showToast.value = true;

                setTimeout(() => {
                    showToast.value = false;
                }, 5000);
            }
        }
    }
});



const performDelete = (id: number) => {
    if (!userToDelete.value) return;
    
    deleteForm.delete(route('users.management.destroy', { id: userToDelete.value.id }), {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;

            toastMessage.value = 'User has been successfully deleted.';
            showToast.value = true;

            setTimeout(() => {
                showToast.value = false;    
            }, 5000);
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;  
    userToDelete.value = null;
};



</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="userRole === 'admin' || userRole === 'superuser' && userStatus !== 'not active'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">User Management</h1>
                    <p class="text-gray-600 dark:text-gray-300">Manage users, their roles, and permissions</p>
                </div>
                
                <Link :href="route('users.management.create')" 
                      class="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <PlusIcon class="h-5 w-5" />
                    <span>Add User</span>
                </Link>
            </div>
            
            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Search users..."
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    
                    <!-- Role Filter -->
                    <div class="w-full md:w-48">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <FunnelIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                v-model="role"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="all">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="agent">Agent</option>
                                <option value="customer">Customer</option>
                                <option value="sub agent">Sub Agent</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="w-full md:w-48">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <FunnelIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                v-model="status"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="not verified">Not Verified</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                <!-- Mobile View (visible on small screens) -->
                <div class="block md:hidden text-xs">
                    <div v-for="user in users.data" :key="user.id" class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                    <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" class="h-8 w-8 rounded-full object-cover" />
                                </div>
                                <div class="ml-2">
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ user.username }}</div>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <Link :href="route('users.management.show', { username: user.username })" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <EyeIcon class="h-4 w-4" />
                                </Link>
                                <Link :href="route('users.management.edit', { username: user.username })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    <PencilIcon class="h-4 w-4" />
                                </Link>
                                <button
                                    v-if="userRole === 'superuser' || (userRole === 'admin' && user.role !== 'admin')"
                                    @click="confirmDelete(user.id, user.name)"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                <div class="text-gray-900 dark:text-white break-all">{{ user.email }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Phone:</span>
                                <div class="text-gray-900 dark:text-white break-all">{{ user.phone_num }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Role:</span>
                                <div>
                                    <div v-if="user.role === 'sub agent'">
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full" :class="getRoleBadgeClass(user.role)">
                                            {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1, 4) + (user.role.length > 4 ? user.role.charAt(4).toUpperCase() + user.role.slice(5) : '') : '' }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full" :class="getRoleBadgeClass(user.role)">
                                            {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <div>
                                    <div v-if="user.status === 'not verified'">
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full" :class="getStatusBadgeClass(user.status)">
                                            {{ user.role ? user.status.charAt(0).toUpperCase() + user.status.slice(1, 4) + (user.status.length > 4 ? user.status.charAt(4).toUpperCase() + user.status.slice(5) : '') : '' }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span class="px-2 inline-flex text-[10px] leading-4 font-semibold rounded-full" :class="getStatusBadgeClass(user.status)">
                                            {{ user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop View (visible on medium screens and up) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Phone
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Role
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                            <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" class="h-10 w-10 rounded-full object-cover" />
                                            <template v-else>
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </template>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ user.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ user.username }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ user.phone_num }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="user.role === 'sub agent'">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getRoleBadgeClass(user.role)">
                                            {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1, 4) + (user.role.length > 4 ? user.role.charAt(4).toUpperCase() + user.role.slice(5) : '') : '' }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getRoleBadgeClass(user.role)">
                                            {{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="user.status === 'not verified'">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(user.status)">
                                            {{ user.role ? user.status.charAt(0).toUpperCase() + user.status.slice(1, 4) + (user.status.length > 4 ? user.status.charAt(4).toUpperCase() + user.status.slice(5) : '') : '' }}
                                        </span>
                                    </div>
                                    <div v-else>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(user.status)">
                                            {{ user.status ? user.status.charAt(0).toUpperCase() + user.status.slice(1) : '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('users.management.show', { username: user.username })" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <EyeIcon class="h-5 w-5" />
                                        </Link>
                                        <Link v-if="userRole === 'superuser' || (userRole === 'admin' && user.role !== 'admin')"
                                        :href="route('users.management.edit', { username: user.username })" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            v-if="userRole === 'superuser' || (userRole === 'admin' && user.role !== 'admin')"
                                            @click="confirmDelete(user.id, user.name)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-4 md:px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-medium">{{ users.from }}</span> to <span class="font-medium">{{ users.to }}</span> of <span class="font-medium">{{ users.total }}</span> users
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, i) in users.links" :key="i">
                                <Link v-if="link.url" 
                                      :href="link.url" 
                                      v-html="link.label"
                                      class="px-3 py-1 rounded border border-gray-300 dark:border-gray-700 text-sm"
                                      :class="{ 'bg-blue-500 text-white': link.active, 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300': !link.active }"
                                      preserve-scroll
                                />
                                <span v-else 
                                      v-html="link.label" 
                                      class="px-3 py-1 rounded border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 text-sm"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- No Users Found -->
            <div v-if="users.data.length === 0" class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="text-gray-500 dark:text-gray-400">No users found matching your criteria.</div>
            </div>
        </div>
        <!-- Customer Actions Section -->
        <div v-else class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
        </div>

              <!-- Toast notification -->
      <ToastNotification
        :show="showToast"
        :message="toastMessage"
        type="success"
        :duration="5000"
        position="top-right"
        @close="closeToast"
    />
    

    <!-- Delete confirmation modal -->
    <DeleteConfirmationModal
        :show="showDeleteModal"
        message="Are you sure you want to delete this product?"
        :item-identifier="userToDelete?.name?.toString()"
        :is-processing="deleteForm.processing"
        @confirm="performDelete"
        @cancel="cancelDelete"
    />

    
    <!-- Success modal -->
    <SuccessModal
        :show="showSuccessModal"
        title="Application Submitted Successfully"
        :message="successMessage"
        :details="successDetails"
        @close="closeSuccessModal"
    />
    </AppLayout>
</template> 