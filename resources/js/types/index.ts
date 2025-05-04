import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
    isActive?: boolean;
    badge?: number;
    badgeColor?: string;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
}

export interface User {
    id: number;
    name: string;
    email: string;
    user_photo?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    username: string;
    pending_email?: string | null;
    pending_email_token?: string | null;
    pending_email_sent_at?: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;


export function formatNumberTwoDecimal(value: string | number): string {
    // Convert to number if it's a string
    const num = typeof value === 'string' ? parseFloat(value.replace(/[^0-9.-]+/g, '')) : value;
    
    // Format with thousand separators and 2 decimal places
    return num.toLocaleString('en-MY', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


