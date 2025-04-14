<script setup lang="ts">
import { computed } from 'vue';
import { getRoleColor } from '@/types/applicationStatus';

interface Props {
  status: string;
  size?: 'sm' | 'md' | 'lg';
}   

const props = withDefaults(defineProps<Props>(), {
  size: 'md'
});

// Get color scheme from our helper function
const colorScheme = computed(() => getRoleColor(props.status));

// Define size classes
const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-2 py-0.5 text-xs';
    case 'lg':
      return 'px-3.5 py-1.5 text-sm';
    case 'md':
    default:
      return 'px-2.5 py-1 text-xs';
  }
});

// Format status text for display (capitalize first letter of each word)
const formattedStatus = computed(() => {
  if (!props.status) return 'Unknown';
  
  return props.status
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
});
</script>

<template>
  <span 
    class="inline-flex items-center font-medium rounded-full"
    :class="[colorScheme, sizeClasses]"
  >
    <slot>{{ formattedStatus }}</slot>
  </span>
</template> 