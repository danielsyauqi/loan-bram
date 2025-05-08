<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { 
  BarChart3, PieChart,PackageX, TrendingUp, TrendingDown, Users, Minus,
  FileText, CreditCard, DollarSign, Activity, Calendar, 
  AlertCircle, CheckCircle, Clock, ArrowUpRight,
  Plus
} from 'lucide-vue-next';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import RoleBadge from '@/components/ui/RoleBadge.vue';
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import axios from 'axios';
import { useSessionStorage } from '@vueuse/core';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);
declare function route(name: string, params?: any): string;

const session = useSessionStorage('flash', {
  success: '',
  error: ''
});

// Get user role from the auth user object
const userRole = computed(() => (user.value as any)?.role || 'user');

// Breadcrumbs for the page
const breadcrumbs: BreadcrumbItem[] = [
  userRole.value === 'admin' || userRole.value === 'superuser'
    ? {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
      }
    : (userRole.value === 'agent' || userRole.value === 'sub agent')
    ? {
        title: 'Agent Dashboard',
        href: '/agent/dashboard',
      }
    : {
        title: 'Dashboard',
        href: '/dashboard',
      }
];

const props = defineProps({
  monthlyDisbursed: {
    type: Array as () => number[],
    default: () => []
  },
  topModules: {
    type: Array,
    default: () => []
  },
  recentApplications: {
    type: Array,
    default: () => []
  },
  recentNotification: {
    type: Array,
    default: () => []
  },
  totalLoanApplications: {
    type: Number,
    default: 0
  },
  weekLoanApplications: {
    type: Number,
    default: 0
  },
  weekLoanActive: {
    type: Number,
    default: 0
  },
  weekLoanDisbursed: {
    type: Number,
    default: 0
  },
  totalLoanActive: {
    type: Number,
    default: 0
  },
  totalLoanPending: {
    type: Number,
    default: 0
  },
  totalLoanRejected: {
    type: Number,
    default: 0
  },
  totalLoanApproved: {
    type: Number,
    default: 0
  },
  totalLoanDisbursed: {
    type: String,
    default: '0'
  },
  totalCustomers: {
    type: Number,
    default: 0
  },
  weekTotalCustomers: {
    type: Number,
    default: 0
  },
  compareDisbursedLastYearPercentage: {
    type: Number,
    default: 0
  }
});

const totalLoanDisbursedNew = "RM " + formatNumberTwoDecimal(props.totalLoanDisbursed);
const weekLoanDisbursedNew = "RM " + formatNumberTwoDecimal(props.weekLoanDisbursed);

// Helper function to format currency with proper spacing for thousands/millions
function formatNumberTwoDecimal(value: string | number): string {
  // Convert to number if it's a string
  const num = typeof value === 'string' ? parseFloat(value.replace(/[^0-9.-]+/g, '')) : value;
  
  // Format with thousand separators and 2 decimal places
  return num.toLocaleString('en-MY', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

console.log(props.monthlyDisbursed);
// Defensive: If monthlyDisbursed is empty, fill with 12 zeros for chart
const disbursedData = Array.isArray(props.monthlyDisbursed) && props.monthlyDisbursed.length === 12
  ? props.monthlyDisbursed
  : Array(12).fill(0);
// Chart data
const monthlyDisbursed = ref({
  months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  values: disbursedData
});

// Defensive: If all values are zero, set maxDisbursed to 1 to avoid NaN
const maxDisbursed = Math.max(1, ...monthlyDisbursed.value.values);

// Chart data for application status
const applicationsData = ref({
  labels: ['Approved', 'Processing', 'Rejected'],
  values: [props.totalLoanApproved, props.totalLoanPending, props.totalLoanRejected],
  colors: ['#10B981', '#FBBF24', '#EF4444']
});

// Defensive: If all values are zero, set total to 1 to avoid division by zero
const calculateDonutValues = computed(() => {
  const total = applicationsData.value.values.reduce((acc, val) => acc + val, 0) || 1;
  const circumference = 2 * Math.PI * 40;
  let currentOffset = 0;
  const segments = applicationsData.value.values.map((value, index) => {
    const dashLength = (value / total) * circumference;
    const segment = {
      color: applicationsData.value.colors[index],
      dasharray: `${dashLength} ${circumference - dashLength}`,
      dashoffset: -currentOffset
    };
    currentOffset += dashLength;
    return segment;
  });
  return segments;
});

// Defensive getChartPoints
const getChartPoints = () => {
  const width = chartWidth.value - 20;
  const height = chartHeight.value - 20;
  if (!width || !height) return '';
  const data = monthlyDisbursed.value.values;
  if (!data.length || data.every(v => v === 0)) return '';
  const max = maxDisbursed * 1.1;
  const min = 0;
  const range = max - min;
  if (range === 0) return '';
  const points = data.map((value, index) => {
    const x = 10 + ((width - 10) / 11) * index;
    const y = height - ((value - min) / range) * height;
    return `${x},${y}`;
  }).join(' ');
  return points;
};

const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('');

const markAllNotificationsAsRead = async () => {
  try {
    // Changed from POST to GET based on the 405 Method Not Allowed error
    // The route is defined as a GET in web.php
    const response = await axios.post(route('admin.markAllNotifications'));
    console.log(response);
    
    window.location.reload();
    session.value.success = 'All notifications marked as read';

  } catch (err) {
    console.error('Failed to mark all notifications as read:', err);
    
    // Show error toast
    toastMessage.value = 'Failed to mark notifications as read';
    toastType.value = 'error';
    showToast.value = true;
    
    throw err;
  }
}

const currentTime = ref('');
const greeting = ref('');

// Update greeting based on time of day
const updateGreeting = () => {
  const hour = new Date().getHours();
  if (hour < 12) greeting.value = 'Good Morning';
  else if (hour < 17) greeting.value = 'Good Afternoon';
  else greeting.value = 'Good Evening';
};

// Responsive chart dimensions
const chartWidth = ref(0);
const chartHeight = ref(0);

// Initialize on component mount
onMounted(() => {
  updateGreeting();
  setInterval(() => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: true 
    });
  }, 1000);
  
  // Set initial chart dimensions
  if (window.innerWidth < 768) {
    chartWidth.value = 300;
    chartHeight.value = 150;
  } else {
    chartWidth.value = 600;
    chartHeight.value = 200;
  }
  
  // Update chart dimensions on window resize
  window.addEventListener('resize', () => {
    if (window.innerWidth < 768) {
      chartWidth.value = 300;
      chartHeight.value = 150;
    } else {
      chartWidth.value = 600;
      chartHeight.value = 200;
    }
  });
});

// Function to format number with commas
const formatNumber = (num: number) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};


// Format status text
const formatStatus = (status: string) => {
  return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};


// Get role-based color class for notifications
const getRoleColor = (role: string) => {
  switch(role) {
    case 'admin':
        return 'bg-purple-50 text-purple-800 dark:bg-purple-900 dark:text-purple-100';
    case 'agent':
        return 'bg-blue-50 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
    case 'customer':
        return 'bg-green-50 text-green-800 dark:bg-green-900 dark:text-green-100';
    case 'sub agent':
        return 'bg-orange-50 text-orange-800 dark:bg-orange-900 dark:text-orange-100';
    default: return 'border-gray-400 bg-gray-50 dark:bg-gray-700';
  }
};

// Chart tooltip
const tooltip = ref({
  show: false,
  value: '',
  month: '',
  x: 0,
  y: 0
});

// Show tooltip when hovering over data point
const showTooltip = (event: MouseEvent, value: string, month: string) => {
  const target = event.target as HTMLElement;
  const rect = target.getBoundingClientRect();
  
  tooltip.value = {
    show: true,
    value: `RM ${value}`,
    month,
    x: rect.left,
    y: rect.top - 40
  };
};

// Hide tooltip
const hideTooltip = () => {
  tooltip.value.show = false;
};

onMounted(() => {
  // Check for flash messages from the session
  if (session.value.success) {
    toastMessage.value = String(session.value.success);
    showToast.value = true;
    toastType.value = 'success';
    // Clear the flash message after displaying
    session.value.success = '';
  }
  
  // Also check page props for flash messages
  const { flash } = page.props as { flash?: Record<string, any> };
  if (flash && typeof flash === 'object' && 'success' in flash && flash.success) {
    toastMessage.value = String(flash.success);
    showToast.value = true;
    toastType.value = 'success';
  }
});

// Dashboard stats
const stats = ref([
  { 
    title: 'Total Applications', 
    value: props.totalLoanApplications, 
    change: props.weekLoanApplications, 
    trend: props.weekLoanApplications > 0 ? 'up' : 'down', 
    icon: FileText,
    color: 'blue'
  },
  { 
    title: 'Active Loans', 
    value: props.totalLoanActive, 
    change: props.weekLoanActive, 
    trend: props.weekLoanActive > 0 ? 'up' : 'down', 
    icon: CreditCard,
    color: 'green'
  },
  { 
    title: 'Total Customers', 
    value: props.totalCustomers, 
    change: props.weekTotalCustomers ?? 0, 
    trend: props.weekTotalCustomers > 0 ? 'up' : 'down', 
    icon: Users,
    color: 'indigo'
  },
  { 
    title: 'Total Disbursed', 
    value: totalLoanDisbursedNew, 
    change: weekLoanDisbursedNew, 
    trend: props.weekLoanDisbursed > 0 ? 'up' : 'down', 
    icon: DollarSign,
    color: 'amber'
  },
]);

</script>

<template>
  <Head :title="userRole === 'admin' ? 'Admin Dashboard' : userRole === 'agent' ? 'Agent Dashboard' : userRole === 'sub agent' ? 'Sub Agent Dashboard' : 'Dashboard'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="userRole === 'admin' || userRole === 'agent' || userRole === 'sub agent' || userRole === 'superuser'" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 bg-gray-50 dark:bg-gray-900 animate__animated animate__fadeIn">
      <!-- Welcome Header -->
      <div class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
              <img v-if="user.user_photo" :src="`/storage/${user.user_photo}`" class="h-16 w-16 rounded-full object-cover" />
              <span v-else class="text-2xl">{{ user.name.charAt(0) }}</span>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ greeting }}, {{ user.name }}!</h2>
              <p class="text-gray-600 dark:text-gray-300">Welcome to your Admin Dashboard</p>
            </div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ currentTime }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ new Date().toLocaleDateString() }}</div>
          </div>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="(stat, index) in stats" 
          :key="index"
          class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 transition-all duration-300 hover:shadow-xl animate__animated animate__fadeInUp"
          :style="{ animationDelay: index * 100 + 'ms' }"
        >
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ stat.title }}</h3>
            <div :class="`p-2 rounded-lg bg-${stat.color}-100 text-${stat.color}-600`">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </div>
          <div class="flex flex-col">
            <div>
              <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</span>
            </div>
            <div class="mt-1">
              <span 
                :class="`text-sm font-medium ${stat.trend === 'up' ? 'text-green-600' : 'hidden'}`" 
                class="flex items-center"
              >
                <component :is="stat.trend === 'up' ? Plus : Minus" class="h-3 w-3 mr-1" />
                {{ stat.change }} <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">last week</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Disbursed Chart -->
        <div class="lg:col-span-2 p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 transition-all duration-300 animate__animated animate__fadeIn">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Monthly Disbursed</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Total: RM {{ formatNumberTwoDecimal(monthlyDisbursed.values.reduce((a, b) => a + b, 0)) }}</p>
            </div>
            <div class="flex items-center">
              <span class="text-green-600 flex items-center text-sm font-medium">
                <TrendingUp class="h-4 w-4 mr-1" />
                {{props.compareDisbursedLastYearPercentage }}% vs last year
              </span>
            </div>
          </div>
          
          <div class="relative h-[300px] w-full">
            <!-- SVG Chart or Placeholder -->
            <template v-if="monthlyDisbursed.values.length && !monthlyDisbursed.values.every(v => v === 0)">
              <!-- SVG Chart -->
              <svg class="h-full w-full" :viewBox="`0 0 ${chartWidth} ${chartHeight + 30}`">
                <!-- Grid Lines -->
                <line v-for="i in 5" :key="i" x1="0" :x2="chartWidth" :y1="i * (chartHeight - 20) / 5" :y2="i * (chartHeight - 20) / 5" 
                      stroke="#e5e7eb" stroke-width="1" stroke-dasharray="5,5" />
                <!-- Y-axis labels -->
                <text v-for="i in 5" :key="i + 'label'" x="5" :y="i * (chartHeight - 20) / 5 - 5" 
                      font-size="10" fill="#9ca3af" class="dark:fill-gray-400">
                      RM {{ formatNumber(Math.round(maxDisbursed * 1.1 * (5 - i) / 5)) }}
                    </text>
                <!-- X-axis labels -->
                <text v-for="(month, i) in monthlyDisbursed.months" :key="month" 
                      :x="10 + i * (chartWidth - 20) / 11" 
                      :y="chartHeight - 5" 
                      font-size="11" 
                      font-weight="500"
                      fill="#6b7280" 
                      text-anchor="middle"
                      class="dark:fill-gray-300">
                  {{ month }}
                </text>
                <!-- X-axis line -->
                <line x1="10" :x2="chartWidth - 10" :y1="chartHeight - 20" :y2="chartHeight - 20" 
                      stroke="#e5e7eb" stroke-width="1" />
                <!-- Area under the line, line chart, data points, tooltips -->
                <path :d="`M10,${chartHeight - 20} ${getChartPoints()} ${chartWidth - 10},${chartHeight - 20} Z`"
                      fill="rgba(59, 130, 246, 0.1)" />
                <polyline :points="getChartPoints()" 
                          fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                <circle v-for="(value, i) in monthlyDisbursed.values" :key="i + 'point'"
                        :cx="8 + i * (chartWidth - 20) / 11" 
                        :cy="(chartHeight - 20) - (value / (Math.max(...monthlyDisbursed.values) * 1.1)) * (chartHeight - 20)"
                        r="4" fill="#3b82f6" />
                <g v-for="(value, i) in monthlyDisbursed.values" :key="i + 'tooltip'">
                  <circle 
                    :cx="10 + i * (chartWidth - 20) / 11" 
                    :cy="(chartHeight - 20) - (value / (Math.max(...monthlyDisbursed.values) * 1.1)) * (chartHeight - 20)"
                    r="8" 
                    fill="transparent" 
                    stroke="transparent"
                    class="cursor-pointer hover:fill-blue-200 hover:fill-opacity-50 transition-all duration-150"
                    @mouseenter="showTooltip($event, formatNumber(value), monthlyDisbursed.months[i])"
                    @mouseleave="hideTooltip()"
                  />
                </g>
              </svg>
            </template>
            <template v-else>
              <div class="flex items-center justify-center h-full w-full text-gray-400 text-lg">
                No disbursement data available.
              </div>
            </template>
          </div>
          
          <!-- Legend -->
          <div class="mt-4 flex items-center justify-center">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
              <span class="text-xs text-gray-600 dark:text-gray-300">Disbursed Amount</span>
            </div>
          </div>
        </div>
        
        <!-- Application Status Donut Chart -->
        <div class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 transition-all duration-300 animate__animated animate__fadeIn">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Application Status</h3>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              Total: {{ formatNumber(applicationsData.values.reduce((a, b) => a + b, 0)) }}
            </div>
          </div>
          
          <div class="flex justify-center">
            <div class="relative w-48 h-48">
              <!-- SVG Donut Chart -->
              <svg class="w-full h-full" viewBox="0 0 100 100">
                <!-- Chart segments -->
                <circle
                  v-for="(segment, index) in calculateDonutValues"
                  :key="index"
                  cx="50"
                  cy="50"
                  r="40"
                  fill="transparent"
                  :stroke="segment.color"
                  stroke-width="20"
                  :stroke-dasharray="segment.dasharray"
                  :stroke-dashoffset="segment.dashoffset"
                  class="donut-segment"
                  :style="{ '--animation-delay': index * 0.2 + 's' }"
                />
                <!-- Center text -->
                <text x="50" y="45" font-size="12" text-anchor="middle" fill="#374151" class="dark:fill-gray-300">Total</text>
                <text x="50" y="60" font-size="16" font-weight="bold" text-anchor="middle" fill="#111827" class="dark:fill-white">{{ formatNumber(applicationsData.values.reduce((a, b) => a + b, 0)) }}</text>
              </svg>
            </div>
          </div>
          
          <!-- Legend -->
          <div class="grid grid-cols-2 gap-3 mt-6">
            <div v-for="(label, i) in applicationsData.labels" :key="label" 
                 class="flex items-center p-2 rounded-lg transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700">
              <div class="w-4 h-4 rounded-full mr-2" :style="{ backgroundColor: applicationsData.colors[i] }"></div>
              <div class="flex-1 flex">
                <div class="flex justify-between items-center w-full">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ label }}</div>
                    <span class="text-xs text-gray-500">{{ applicationsData.values[i] }}</span>
                  </div>
                  <span class="text-xs text-gray-500">{{ 
                    applicationsData.values.reduce((a, b) => a + b, 0) === 0 
                      ? 0 
                      : Math.round((applicationsData.values[i] / applicationsData.values.reduce((a, b) => a + b, 0)) * 100) 
                  }}%</span>
                </div>
                </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Applications & Alerts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Applications Table -->
        <div class="lg:col-span-2 p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate__animated animate__fadeIn">
          <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Applications</h3>
        <Link href="/loan-applications/list" class="text-blue-600 hover:text-blue-800 text-sm">View All</Link>
          </div>
          
          <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Module</th>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
          <th scope="col" class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="application in recentApplications" :key="(application as any).id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ (application as any).customer }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ (application as any).module }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ (application as any).product }}</td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">RM {{ (application as any).amount ?? 0}}</td>
          <td class="px-4 py-3 whitespace-nowrap">
            <StatusBadge :status="(application as any).status" />
          </td>
          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ new Date((application as any).date).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
          </div>
        </div>
        
        <!-- Recent Notifications -->
        <div class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate__animated animate__fadeIn">
          <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Notifications</h3>
        <button @click="markAllNotificationsAsRead" class="text-blue-600 hover:text-blue-800 text-sm">Mark All Read</button>
          </div>
          
        <div class="space-y-3 max-h-[400px] overflow-y-auto">
          <div v-for="(alert, index) in recentNotification" :key="index" 
             class="p-3 rounded-lg border-l-4 animate__animated animate__fadeInRight flex items-start"
             :class="getRoleColor((alert as any).role)"
             :style="{ animationDelay: index * 100 + 'ms' }">
          <div class="flex-shrink-0">
          </div>
          <div class="mb-3 ml-3 flex-1">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-medium text-gray-800 dark:text-white">{{ (alert as any).title }}</h4>
              <div class="flex items-center">
                <span class="text-xs text-gray-500 dark:text-gray-400 mr-2">{{ new Date((alert as any).created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                <span v-if="(alert as any).role !== 'all'" class="inline-block px-2 py-0.5 text-xs rounded-full" :class="`bg-${(alert as any).role}-100 text-${(alert as any).role}-800 dark:bg-${(alert as any).role}-900 dark:text-${(alert as any).role}-100`">
                  <RoleBadge :status="(alert as any).role" size="sm" />
                </span>
              </div>
            </div>
            <p class="text-xs mt-2 text-gray-600 dark:text-gray-300">{{ (alert as any).description }}</p>
          </div>
        </div>
        
        <div class="text-center pt-2">
          <Link href="/notifications" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center justify-center w-full">
            <Activity class="h-4 w-4 mr-1" />
            View All Notifications
          </Link>
        </div>
          </div>
        </div>
      </div>

      <!-- Top Performing Modules -->
      <div class="p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800 animate__animated animate__fadeIn relative overflow-hidden">
        <div class="relative z-10">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Top Modules by Applications</h3>
            <Link href="/loan-modules" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
              <span>View All Modules</span>
              <ArrowUpRight class="h-4 w-4 ml-1" />
            </Link>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div v-for="(module, index) in topModules" :key="index"
                 class="relative p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-300 animate__animated animate__fadeInUp bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90"
                 :style="{ animationDelay: index * 100 + 'ms' }">
              <!-- Background fade image for each card -->
              <div class="absolute inset-0 opacity-5 dark:opacity-10 bg-repeat z-0 overflow-hidden rounded-lg">
                <img :src="`../${(module as any).image}`" class="w-full h-full object-contain" />
              </div>
              <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 flex items-center justify-center shadow-sm z-10">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">#{{ index + 1 }}</span>
              </div>
              
              <!-- Module Header -->
              <div class="flex items-center mb-3 relative z-10">
                <h4 class="font-semibold text-gray-800 dark:text-white">{{ (module as any).name }}</h4>
              </div>
              
              <!-- Application Count -->
              <div class="mb-3 relative z-10">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Applications</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ formatNumber((module as any).applications) }}</div>
              </div>
              
              <!-- Growth Indicator -->
              <div class="flex justify-between items-center relative z-10">
                <div class="w-3/4 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 overflow-hidden">
                  <div 
                    :class="`bg-green-500 h-full rounded-full`" 
                    :style="`width: ${((module as any).applications / Math.max(...topModules.map(m => (m as any).applications))) * 100}%`"
                  ></div>
                </div>
                <div :class="`flex items-center text-xs font-medium px-2 py-1 rounded-full ${(module as any).growth >= 0 ? 'text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-100' : 'text-red-800 bg-red-100 dark:bg-red-900 dark:text-red-100'}`">
                  <component :is="(module as any).growth >= 0 ? TrendingUp : TrendingDown" class="h-3 w-3 mr-1" />
                  {{ (module as any).growth > 0 ? '+' : '' }}{{ (module as any).growth }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Unauthorized Access Message -->
    <div v-else class="text-center py-12 animate__animated animate__fadeIn">
      <div class="text-gray-500 dark:text-gray-400">You are not authorized to access this page.</div>
    </div>
  </AppLayout>

  <ToastNotification
    :show="showToast"
    :message="toastMessage"
    :type="toastType as 'success' | 'error' | 'info' | 'warning' | undefined"
  />
</template>

<style>
/* Import Animate.css */
@import 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';

/* Animation durations */
.animate__animated.animate__fadeIn,
.animate__animated.animate__fadeInUp,
.animate__animated.animate__fadeInRight {
  --animate-duration: 0.8s;
}

/* SVG chart animations */
@keyframes growLine {
  from { stroke-dashoffset: 1000; }
  to { stroke-dashoffset: 0; }
}

polyline {
  animation: growLine 2s ease-out forwards;
  stroke-dasharray: 1000;
  stroke-dashoffset: 1000;
}

/* Donut chart animations */
@keyframes donutFade {
  0% { opacity: 0; transform: scale(0.8); }
  100% { opacity: 1; transform: scale(1); }
}

svg circle {
  animation: donutFade 1s ease-out forwards;
}

/* Card hover effect */
.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transform: translateY(-2px);
  transition: all 0.3s ease;
}

</style>