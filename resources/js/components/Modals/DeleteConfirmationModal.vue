<script setup lang="ts">

const props = defineProps<{
    show: boolean;
    title?: string;
    message: string;
    details?: string;
    itemIdentifier?: string;
    confirmButtonText?: string;
    cancelButtonText?: string;
    isProcessing?: boolean;
}>();

const emit = defineEmits(['confirm', 'cancel']);

const confirmDelete = () => {
    emit('confirm');
};

const cancelDelete = () => {
    emit('cancel');
};

// Provide default values
const modalTitle = props.title || 'Confirm Deletion';
const confirmText = props.confirmButtonText || 'Delete';
const cancelText = props.cancelButtonText || 'Cancel';
</script>

<template>
    <div v-if="show" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="cancelDelete"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl transform transition-all p-6">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="rounded-full bg-red-100 p-3 mb-4">
                    <svg class="h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ modalTitle }}</h3>
                <p class="mt-2 text-base text-gray-600 dark:text-gray-300">{{ message }}</p>
                <p v-if="details" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ details }}</p>
                <p v-if="itemIdentifier" class="mt-2 text-sm text-gray-600 dark:text-gray-300">#{{ itemIdentifier }}</p>
                <p class="mt-2 text-sm text-red-500 dark:text-red-400">
                    This action cannot be undone.
                </p>
            </div>
            
            <div class="flex space-x-3">
                <button 
                    @click="confirmDelete"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    :disabled="isProcessing"
                >
                    <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isProcessing ? 'Deleting...' : confirmText }}
                </button>
                <button
                    @click="cancelDelete"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="isProcessing"
                >
                    {{ cancelText }}
                </button>
            </div>
        </div>
    </div>
</template> 