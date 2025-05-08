<script setup lang="ts">

const props = defineProps<{
    show: boolean;
    title?: string;
    messages: string[];
    okButtonText?: string;
}>();

const emit = defineEmits(['close']);

const closeModal = () => {
    emit('close');
};

// Provide default values
const modalTitle = props.title || 'Form Validation Error';
const buttonText = props.okButtonText || 'OK';
</script>

<template>
    <div v-if="show" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl transform transition-all p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ modalTitle }}
                </h3>
                <button 
                    @click="closeModal"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">Please correct the following errors:</p>
                <ul class="list-disc pl-5 text-sm text-red-600 dark:text-red-400 space-y-1">
                    <li v-for="(message, index) in messages" :key="index">
                        {{ message }}
                    </li>
                </ul>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button
                    @click="closeModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    {{ buttonText }}
                </button>
            </div>
        </div>
    </div>
</template> 