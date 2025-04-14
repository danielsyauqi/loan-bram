<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { ref, watch } from 'vue';

interface Props {
  show: boolean;
  modalTitle: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  confirmButtonClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  confirmButtonClass: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
});

// Define emits
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'confirm'): void;
}>();

const open = ref(props.show);

// Watch for changes to the show prop
watch(() => props.show, (value) => {
  open.value = value;
});

// Handle close
const handleClose = () => {
  emit('close');
};

// Handle confirm
const handleConfirm = () => {
  emit('confirm');
};
</script>

<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-10">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                {{ modalTitle }}
              </DialogTitle>
              
              <div class="mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400" v-if="typeof message === 'string'">{{ message }}</p>
                <slot name="content"></slot>
              </div>

              <div class="mt-6 flex justify-end space-x-3">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800"
                  @click="handleClose"
                >
                  {{ cancelText }}
                </button>
                <button
                  type="button"
                  :class="`inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 ${confirmButtonClass}`"
                  @click="handleConfirm"
                >
                  {{ confirmText }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template> 