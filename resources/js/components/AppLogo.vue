<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useSidebar } from '@/components/ui/sidebar';

const { state } = useSidebar();
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex-shrink-0">
            <img 
                src="/loanbram-black.png" 
                alt="Loan Bram" 
                :class="[
                    'w-auto object-contain dark:hidden transition-all duration-300 hover:opacity-90',
                    state === 'collapsed' ? 'h-8' : 'h-12'
                ]"
            />
            <img 
                src="/loanbram-white.png" 
                alt="Loan Bram" 
                :class="[
                    'w-auto object-contain hidden dark:block transition-all duration-300 hover:opacity-90',
                    state === 'collapsed' ? 'h-8' : 'h-12'
                ]"
            />
        </div>
    </div>
</template>
