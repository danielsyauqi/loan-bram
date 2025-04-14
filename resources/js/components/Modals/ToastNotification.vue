<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';

const props = defineProps<{
    show: boolean;
    message: string;
    type?: 'success' | 'error' | 'info' | 'warning';
    duration?: number;
    position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left';
}>();

const emit = defineEmits(['close']);

const autoCloseTimer = ref<number | null>(null);

const closeToast = () => {
    if (autoCloseTimer.value) {
        clearTimeout(autoCloseTimer.value);
        autoCloseTimer.value = null;
    }
    emit('close');
};

// Handle auto-dismiss
watch(() => props.show, (newValue) => {
    if (newValue && props.duration) {
        if (autoCloseTimer.value) {
            clearTimeout(autoCloseTimer.value);
        }
        
        autoCloseTimer.value = window.setTimeout(() => {
            closeToast();
        }, props.duration);

        // Clear any stored toast data from sessionStorage when toast is shown
        if (newValue) {
            sessionStorage.removeItem('showToast');
            sessionStorage.removeItem('toastMessage');
            sessionStorage.removeItem('toastType');
        }
    }
});

// Clean up timer on component unmount
onMounted(() => {
    return () => {
        if (autoCloseTimer.value) {
            clearTimeout(autoCloseTimer.value);
        }
    };
});

// Compute toast position classes
const positionClasses = computed(() => {
    switch(props.position || 'top-right') {
        case 'top-left': return 'top-4 left-4';
        case 'bottom-right': return 'bottom-4 right-4';
        case 'bottom-left': return 'bottom-4 left-4';
        default: return 'top-4 right-4';
    }
});

// Compute toast type classes
const typeClasses = computed(() => {
    switch(props.type || 'success') {
        case 'error': 
            return {
                border: 'border-red-400', 
                bg: 'bg-red-50',
                textColor: 'text-red-700',
                iconColor: 'text-red-400',
                hoverBg: 'hover:bg-red-100'
            };
        case 'warning': 
            return {
                border: 'border-yellow-400', 
                bg: 'bg-yellow-50',
                textColor: 'text-yellow-700',
                iconColor: 'text-yellow-400',
                hoverBg: 'hover:bg-yellow-100'
            };
        case 'info': 
            return {
                border: 'border-blue-400', 
                bg: 'bg-blue-50',
                textColor: 'text-blue-700',
                iconColor: 'text-blue-400',
                hoverBg: 'hover:bg-blue-100'
            };
        default: 
            return {
                border: 'border-green-400', 
                bg: 'bg-green-50',
                textColor: 'text-green-700',
                iconColor: 'text-green-400',
                hoverBg: 'hover:bg-green-100'
            };
    }
});
</script>

<template>
    <transition name="fade">
        <div 
            v-if="show" 
            :class="[
                'fixed z-50 max-w-md border-l-4 p-4 rounded-lg shadow-lg',
                positionClasses,
                typeClasses.bg,
                typeClasses.border
            ]"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Success Icon -->
                    <svg v-if="type === 'success' || !type" class="h-5 w-5" :class="typeClasses.iconColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <!-- Error Icon -->
                    <svg v-else-if="type === 'error'" class="h-5 w-5" :class="typeClasses.iconColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <!-- Warning Icon -->
                    <svg v-else-if="type === 'warning'" class="h-5 w-5" :class="typeClasses.iconColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <!-- Info Icon -->
                    <svg v-else-if="type === 'info'" class="h-5 w-5" :class="typeClasses.iconColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm" :class="typeClasses.textColor">{{ message }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button 
                            @click="closeToast" 
                            :class="[
                                'inline-flex rounded-md p-1.5',
                                typeClasses.iconColor,
                                typeClasses.hoverBg,
                                'focus:outline-none focus:ring-2 focus:ring-offset-2',
                            ]"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 