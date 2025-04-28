<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex-shrink-0">
            <img 
                src="/loanbram-black.png" 
                alt="Loan Bram" 
                class="h-12 w-auto object-contain dark:hidden transition-all duration-300 hover:opacity-90" 
            />
            <img 
                src="/loanbram-white.png" 
                alt="Loan Bram" 
                class="h-12 w-auto object-contain hidden dark:block transition-all duration-300 hover:opacity-90" 
            />
        </div>
    </div>

</template>
