<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        data-sidebar="content"
        :class="cn('flex min-h-0 flex-1 flex-col gap-2 overflow-auto group-data-[collapsible=icon]:overflow-hidden', props.class)"
    >
        <slot />
    </div>
</template>

<style scoped>
/* Minimalist scrollbar for sidebar */
::-webkit-scrollbar {
  width: 6px;
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

/* Firefox */
:host {
  scrollbar-width: thin;
  scrollbar-color: #e5e7eb transparent;
}
</style>
