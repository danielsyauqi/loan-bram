import { ref, computed } from 'vue';
import axios from 'axios';

// Interface for notification objects
export interface Notification {
    id: number;
    message: string;
    date: string;
    status: 'read' | 'unread';
    created_at: string;
    reference_id: string;
}

// Create a reactive notification store
const notifications = ref<Notification[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const lastFetchTime = ref<Date | null>(null);

// Computed properties
const unreadCount = computed(() => {
    return notifications.value.filter(n => n.status === 'unread').length;
});

const hasUnread = computed(() => unreadCount.value > 0);

// Action to fetch notifications
const fetchNotifications = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        // Call the real API endpoint
        const response = await axios.get('/api/notifications', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        // Check if we have valid data
        if (response.data && Array.isArray(response.data.notifications)) {
            notifications.value = response.data.notifications;
        } else {
            console.warn('Received unexpected data format from notifications API');
            // Use fallback data if needed
            if (notifications.value.length === 0) {
                notifications.value = getSampleNotifications();
            }
        }
        
        lastFetchTime.value = new Date();
    } catch (err) {
        console.error('Failed to fetch notifications:', err);
        error.value = 'Failed to load notifications';
        
        // Use fallback data if we have no notifications yet
        if (notifications.value.length === 0) {
            notifications.value = getSampleNotifications();
            console.log('Using sample notification data');
        }
    } finally {
        loading.value = false;
    }
};

// Helper function to generate sample notifications
const getSampleNotifications = (): Notification[] => {
    const now = new Date();
    return [
        {
            id: 1,
            message: 'This is a sample notification. Real data will come from your database.',
            date: '1 hour ago',
            status: 'unread',
            reference_id: '1234567890',
            created_at: new Date(now.getTime() - 60 * 60 * 1000).toISOString()
        },
        {
            id: 2,
            message: 'Your profile was successfully updated.',
            date: 'Today at 9:30 AM',
            status: 'unread',
            reference_id: '1234567890',
            created_at: new Date(now.getTime() - 3 * 60 * 60 * 1000).toISOString()
        },
        {
            id: 3,
            message: 'Welcome to our loan management system!',
            date: 'Yesterday',
            status: 'read',
            reference_id: '1234567890',
            created_at: new Date(now.getTime() - 24 * 60 * 60 * 1000).toISOString()
        }
    ];
};

// Action to mark a notification as read
const markAsRead = async (id: number) => {
    try {
        await axios.post(`/api/notifications/${id}/mark-as-read`, {}, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        // Update local state
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.status = 'read';
        }
    } catch (err) {
        console.error('Failed to mark notification as read:', err);
        error.value = 'Failed to mark notification as read';
        throw err; // Re-throw to allow the UI to handle it
    }
};

// Action to mark all notifications as read
const markAllAsRead = async () => {
    try {
        await axios.post('/api/notifications/mark-all-as-read', {}, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        // Update local state
        notifications.value.forEach(notification => {
            notification.status = 'read';
        });
    } catch (err) {
        console.error('Failed to mark all notifications as read:', err);
        error.value = 'Failed to mark all notifications as read';
        throw err; // Re-throw to allow the UI to handle it
    }
};

// Action to add a new notification (used for real-time updates)
const addNotification = (notification: Notification) => {
    notifications.value.unshift(notification);
};

// Setup polling for new notifications (in a real app, use WebSockets instead)
let pollingInterval: number | null = null;

const startPolling = (intervalMs = 30000) => {
    if (pollingInterval) clearInterval(pollingInterval);
    
    // Initial fetch
    fetchNotifications();
    
    // Set up interval for polling
    pollingInterval = window.setInterval(() => {
        fetchNotifications();
    }, intervalMs);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

// Action to delete a notification
const deleteNotification = async (id: number) => {
    try {
        await axios.delete(`/api/notifications/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        // Update local state
        notifications.value = notifications.value.filter(n => n.id !== id);
    } catch (err) {
        console.error('Failed to delete notification:', err);
        error.value = 'Failed to delete notification';
        throw err; // Re-throw to allow the UI to handle it
    }
};

// Action to delete all notifications
const deleteAllNotifications = async () => {
    try {
        await axios.delete('/api/notifications', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        // Update local state
        notifications.value = [];
    } catch (err) {
        console.error('Failed to delete all notifications:', err);
        error.value = 'Failed to delete all notifications';
        throw err; // Re-throw to allow the UI to handle it
    }
};

// Export the notification store
export const useNotificationStore = () => {
    return {
        // State
        notifications,
        loading,
        error,
        lastFetchTime,
        
        // Computed
        unreadCount,
        hasUnread,
        
        // Actions
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        deleteAllNotifications,
        addNotification,
        startPolling,
        stopPolling
    };
}; 

// Retrieve reference id from notification
