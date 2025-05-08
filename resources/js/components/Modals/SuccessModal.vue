<script setup lang="ts">

const props = defineProps<{
    show: boolean;
    title?: string;
    message: string;
    details?: string;
    okButtonText?: string;
}>();

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Provide default values
const modalTitle = props.title || 'Success!';
</script>

<template>
    <div v-if="show" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl transform transition-all p-6">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="rounded-full bg-green-100 p-3 mb-4">
                    <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ modalTitle }}</h3>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300">{{ message }}</p>
                <div v-if="details" class="mt-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg w-full">
                    <p class="text-md font-medium text-gray-700 dark:text-gray-300">{{ details }}</p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button
                    @click="$emit('close')"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</template> 