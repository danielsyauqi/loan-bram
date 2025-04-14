<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-2">
        <AppLogoIcon class="h-8 w-8 text-primary" />
        <img 
            src="/loanbram-black.png" 
            alt="Loan Bram" 
            class="h-8 object-contain dark:hidden" 
        />
        <img 
            src="/loanbram-white.png" 
            alt="Loan Bram" 
            class="hidden h-8 object-contain dark:block" 
        />
    </div>

</template>
