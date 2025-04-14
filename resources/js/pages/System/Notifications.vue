<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import { useNotificationStore } from '../../stores/notificationStore';
declare function route(name: string, params?: any): string;

// Add toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error' | 'warning'>('success');

const page = usePage();

// Initialize notification store
const notificationStore = useNotificationStore();
const { 
    notifications, 
    unreadCount,
    loading, 
    fetchNotifications, 
    markAsRead, 
    markAllAsRead,
    deleteNotification,
    deleteAllNotifications,
    startPolling, 
    stopPolling 
} = notificationStore;

// Function to close toast
const closeToast = () => {
    showToast.value = false;
};

console.log(notifications);
// Function to show toast
const showToastMessage = (message: string, type: 'success' | 'error' | 'warning' = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
};

// Filter state - default to showing unread notifications
const filterStatus = ref<'all' | 'unread'>('unread');

// Filtered notifications based on current filter
const filteredNotifications = computed(() => {
    if (filterStatus.value === 'all') {
        return notifications.value;
    }
    return notifications.value.filter(notification => notification.status === 'unread');
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Notifications',
        href: '/notifications',
    },
];

// Handle marking a notification as read
const handleMarkAsRead = async (id: number) => {
    try {
        await markAsRead(id);
        showToastMessage('Notification marked as read');
    } catch (error) {
        showToastMessage('Failed to mark notification as read', 'error');
    }
};

// Handle marking all notifications as read
const handleMarkAllAsRead = async () => {
    try {
        await markAllAsRead();
        showToastMessage('All notifications marked as read');
    } catch (error) {
        showToastMessage('Failed to mark all notifications as read', 'error');
    }
};

// Handle deleting all notifications
const handleDeleteAll = async () => {
    try {
        await deleteAllNotifications();
        showToastMessage('All notifications deleted');
    } catch (error) {
        showToastMessage('Failed to delete all notifications', 'error');
    }
};

// Handle deleting a notification
const handleDelete = async (id: number) => {
    try {
        await deleteNotification(id);
        showToastMessage('Notification deleted');
    } catch (error) {
        showToastMessage('Failed to delete notification', 'error');
    }
};

// Toggle filter between all and unread
const toggleFilter = () => {
    filterStatus.value = filterStatus.value === 'all' ? 'unread' : 'all';
};

// Setup real-time polling
onMounted(() => {
    // Fetch notifications when the component mounts
    fetchNotifications();
    
    // Start polling for real-time updates
    startPolling(15000); // Poll every 15 seconds for new notifications
});

// Clean up when component unmounts
onUnmounted(() => {
    stopPolling();
});

const goToReferenceId = (referenceId: string, notificationId: number) => {
    try{
        router.visit(route('notifications.goToReferenceId', {referenceId: referenceId, notificationId: notificationId}));
    }catch(error){
        console.log(error);
    }
    
};

console.log(notifications);

</script>

<template>
    <Head title="Notifications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 bg-gray-50 dark:bg-gray-900">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Notification</h1>
                
                <div class="flex flex-wrap w-full sm:w-auto items-center gap-3">
                    <button 
                        @click="toggleFilter" 
                        class="flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ filterStatus === 'unread' ? 'Unread' : 'All' }}
                    </button>
                    
                    <button 
                        @click="handleMarkAllAsRead" 
                        class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Mark All As Read
                    </button>

                    <button 
                        @click="handleDeleteAll"
                        class="flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete All
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-700"></div>
            </div>

            <div v-else>
                <!-- Notifications Table for larger screens -->
                <div 
                    class="hidden sm:block bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden"
                    
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[5%]">
                                        No.
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[45%]">
                                        Message
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[15%]">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[10%]">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[25%]">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(notification, index) in filteredNotifications" :key="notification.id" 
                                    :class="{'bg-blue-50 dark:bg-blue-900/20': notification.status === 'unread'}"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ index + 1 }}
                                    </td>
                                    <td 
                                        class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 cursor-pointer"
                                        @click="goToReferenceId(notification.reference_id, notification.id)"
                                    >
                                        <div class="max-w-prose break-words">{{ notification.message }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ notification.date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span 
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': notification.status === 'unread',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': notification.status === 'read'
                                            }"
                                        >
                                            {{ notification.status === 'unread' ? 'Unread' : 'Read' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex space-x-2">
                                            <button 
                                                @click="handleMarkAsRead(notification.id)" 
                                                :disabled="notification.status === 'read'"
                                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 px-4 rounded inline-flex items-center text-sm font-medium transition-colors duration-200"
                                            >
                                                <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Mark As Read
                                            </button>

                                            <button 
                                                @click="handleDelete(notification.id)"
                                                class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded inline-flex items-center text-sm font-medium transition-colors duration-200"
                                            >
                                                <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Empty state when no notifications match the filter (Desktop) -->
                                <tr v-if="filteredNotifications.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No notifications</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ filterStatus === 'unread' ? 'You have no unread notifications.' : 'You have no notifications.' }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card layout for mobile -->
                <div class="sm:hidden space-y-4">
                    <div 
                        v-for="(notification, index) in filteredNotifications" 
                        :key="notification.id"
                        class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 border-l-4 notification-card"
                        :class="{
                            'border-blue-500 dark:border-blue-600': notification.status === 'unread',
                            'border-gray-200 dark:border-gray-700': notification.status === 'read'
                        }"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ notification.date }}</div>
                            </div>
                            <span 
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': notification.status === 'unread',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': notification.status === 'read'
                                }"
                            >
                                {{ notification.status === 'unread' ? 'Unread' : 'Read' }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 break-words leading-relaxed">
                            {{ notification.message }}
                        </p>
                        
                        <div class="mt-3 flex justify-end">
                            <button 
                                @click="handleMarkAsRead(notification.id)" 
                                :disabled="notification.status === 'read'"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 px-3 rounded inline-flex items-center text-xs font-medium"
                            >
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mark As Read
                            </button>
                            <button
                                @click="handleDelete(notification.id)"
                                class="ml-2 bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded inline-flex items-center text-xs font-medium"
                            >
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Empty state for mobile -->
                    <div v-if="filteredNotifications.length === 0" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No notifications</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ filterStatus === 'unread' ? 'You have no unread notifications.' : 'You have no notifications.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Toast notification -->
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
@media (max-width: 640px) {
    .notification-card {
        transition: all 0.3s ease;
    }
    .notification-card:active {
        transform: scale(0.98);
    }
}
</style>
