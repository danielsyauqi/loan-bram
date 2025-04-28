<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Bell, Folder, LayoutGrid, Library, List, ClipboardList, Settings, UserIcon, Plus, LayoutDashboard } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { useNotificationStore } from '../stores/notificationStore';
import AppLogo from './AppLogo.vue';    

const { url } = usePage();

// Get user information from the page props
const page = usePage();
const user = page.props.auth?.user as any;

// Initialize notification store
const notificationStore = useNotificationStore();
const { unreadCount, hasUnread, startPolling, stopPolling } = notificationStore;

// Start polling for notifications when component is mounted
onMounted(() => {
    startPolling(30000); // Poll every 30 seconds
});

// Stop polling when component is unmounted
onUnmounted(() => {
    stopPolling();
});

// Check if the user is an admin
const isAdmin = user?.is_admin === 1;

// Check if the user has specific roles
const hasRole = (role: string) => {
  return user?.roles?.includes(role);
};

// Custom notification item with badge
const createNotificationItem = (title: string, href: string, icon: any): NavItem => {
    return {
        title,
        href,
        icon,
        isActive: url.startsWith(href),
        badge: hasUnread.value ? unreadCount.value : undefined,
        badgeColor: 'bg-red-500 text-white'
        
    };
};

// Platform section navigation items
const platformNavItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        isActive: url.startsWith('/dashboard'),
    },
    createNotificationItem('Notifications', '/notifications', Bell),
]);

// Customer dashboard link - only shown to customers
const customerNavItems = computed(() => [
    {
        title: 'Dashboard',
        href: '/customer-dashboard',
        icon: LayoutGrid,
        isActive: url.startsWith('/customer-dashboard'),
    },
    createNotificationItem('Notifications', '/notifications', Bell),
]);

const customerApplicationsNavItems: NavItem[] = [
    {
        title: 'Applications List',
        href: '/customer-applications',
        icon: List,
        isActive: url.startsWith('/customer-applications'),
    },
    {
        title: 'New Applications',
        href: '/customer-applications-new',
        icon: ClipboardList,
        isActive: url.startsWith('/customer-applications-new'),
    },
];

const IdentityFormItems: NavItem[] = [
    {
        title: 'Identity Form',
        href: '/identity-form',
        icon: UserIcon,
        isActive: url.startsWith('/identity-form'),  
    },
];

// Loan Management section navigation items
const loanManagementNavItems: NavItem[] = [
    {
        title: 'Loan Modules',
        href: '/loan-modules',
        icon: Library,
        isActive: url.startsWith('/loan-modules') && !url.startsWith('/admin'),
    },
    {
        title: 'New Application',
        href: '/new-application',
        icon: Plus,
        isActive: url.startsWith('/new-application'),
    },
    {
        title: 'Loan Applications',
        href: '/loan-applications/list',
        icon: List,
        isActive: url.startsWith('/loan-applications/list'),
    },
];

const loanManagementNavItemsForAgent: NavItem[] = [
    {
        title: 'Loan Modules',
        href: '/loan-modules',
        icon: Library,
        isActive: url.startsWith('/loan-modules'),
    },
    {
        title: 'Loan Applications',
        href: '/loan-applications/list',
        icon: List,
        isActive: url.startsWith('/loan-applications/list'),
    },
];

// Management section navigation items
const managementNavItems: NavItem[] = [

    {
        title: 'Modules Management',
        href: '/admin/modules',
        icon: Settings,
        isActive: url.startsWith('/admin/modules'),
    },
    {
        title: 'User Management',
        href: '/admin/users',
        icon: ClipboardList,
        isActive: url.startsWith('/admin/users'),
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="xl" as-child>
                        <Link :href="user?.role === 'customer' ? '/customer-dashboard' : '/dashboard'" class="scale-110">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- Platform Section -->
            <div v-if="user?.role !== 'customer'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Platform</SidebarGroupLabel>
                <NavMain :items="platformNavItems" />
            </div>

            <!-- Customer Dashboard -->
            <div v-if="user?.role === 'customer'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Platform</SidebarGroupLabel>
                <NavMain :items="customerNavItems" />
            </div>

            <!-- Customer Applications -->
            <div v-if="user?.role === 'customer'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Customer Applications</SidebarGroupLabel>
                <NavMain :items="customerApplicationsNavItems" />
            </div>

            <!-- Customer Profile -->
            <div v-if="user?.role === 'customer'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Profile Management</SidebarGroupLabel>
                <NavMain :items="IdentityFormItems" />
            </div>

            <!-- Loan Management Section -->
            <div v-if="user?.role === 'admin'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Loan Management</SidebarGroupLabel>
                <NavMain :items="loanManagementNavItems" />
            </div>

            <!-- Loan Management Section for Agent -->
            <div v-if="user?.role === 'agent' || user?.role === 'sub agent'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Loan Management</SidebarGroupLabel>
                <NavMain :items="loanManagementNavItemsForAgent" />
            </div>

            <!-- Management Section -->
            <div v-if="user?.role === 'admin'" class="px-2 py-2">
                <SidebarGroupLabel class="text-sm font-semibold tracking-wide text-gray-800 dark:text-gray-900">Management</SidebarGroupLabel>
                <NavMain :items="managementNavItems" />
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
