<script setup lang="ts">
import StatusBadge from './StatusBadge.vue';

// All possible application statuses
const statuses = [
  'New',
  'Pending',
  'Approved',
  'Disbursed',
  'Rejected',
  'Ready to Submit',
  'Process',
  'Pending@Agency',
  'Pending@Bank'
];
</script>

<template>
  <div class="space-y-6 p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status Badge Examples</h2>
    
    <!-- Standard size badges -->
    <div class="space-y-2">
      <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Standard Size</h3>
      <div class="flex flex-wrap gap-2">
        <StatusBadge v-for="status in statuses" :key="status" :status="status" />
      </div>
    </div>
    
    <!-- Small size badges -->
    <div class="space-y-2">
      <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Small Size</h3>
      <div class="flex flex-wrap gap-2">
        <StatusBadge v-for="status in statuses" :key="status" :status="status" size="sm" />
      </div>
    </div>
    
    <!-- Large size badges -->
    <div class="space-y-2">
      <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Large Size</h3>
      <div class="flex flex-wrap gap-2">
        <StatusBadge v-for="status in statuses" :key="status" :status="status" size="lg" />
      </div>
    </div>
    
    <!-- Usage example in a list/table context -->
    <div class="space-y-2">
      <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Context Example</h3>
      <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Application</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
            <tr v-for="(status, index) in statuses" :key="index">
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                Application #{{ 1000 + index }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <StatusBadge :status="status" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template> 