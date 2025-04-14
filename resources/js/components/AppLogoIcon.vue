<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-center justify-center rounded-md overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md ">
        <img src="/newlogo.png" alt="Logo" class="object-contain w-8 h-8">
    </div>
</template>
