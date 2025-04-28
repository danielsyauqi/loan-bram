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
        <img src="/loanbram-black.png" alt="Loan Bram" class="w-full object-contain dark:hidden" />
    </div>
</template>
