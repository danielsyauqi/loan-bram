<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmailConfirmationComponent from '@/components/EmailConfirmation.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
  verified: boolean;
  errorMessage?: string;
}>();
</script>

<template>
  <Head title="Email Confirmation" />
  
  <AppLayout :breadcrumbs="[]">
    <EmailConfirmationComponent :verified="verified" :error-message="errorMessage" />
  </AppLayout>
</template> 