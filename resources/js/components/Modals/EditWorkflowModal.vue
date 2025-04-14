<script setup lang="ts">
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import StatusBadge from '@/components/ui/StatusBadge.vue';

interface Props {
  show: boolean;
  modalTitle: string;
  content: string;
  status?: string;
  statusOptions?: Array<{value: string, label: string}>;
  confirmText?: string;
  cancelText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  confirmText: 'Save Changes',
  cancelText: 'Cancel',
  status: 'Pending',
  statusOptions: () => [
    { value: 'New', label: 'New' },
    { value: 'Pending', label: 'Pending' },
    { value: 'Approved', label: 'Approved' },
    { value: 'Disbursed', label: 'Disbursed' },
    { value: 'Rejected', label: 'Rejected' },
    { value: 'Ready to Submit', label: 'Ready to Submit' },
    { value: 'Process', label: 'Process' },
    { value: 'Pending@Agency', label: 'Pending@Agency' },
    { value: 'Pending@Bank', label: 'Pending@Bank' }
  ]
});

// Define emits
const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'save', content: string, status: string): void;
}>();

const open = ref(props.show);
const editorContent = ref(props.content);
const selectedStatus = ref(props.status);
const isStatusDropdownOpen = ref(false);
const statusDropdownRef = ref<HTMLElement | null>(null);

// Watch for changes to the show prop
watch(() => props.show, (value) => {
  open.value = value;
});

// Watch for changes to the content prop
watch(() => props.content, (value) => {
  editorContent.value = value;
});

// Watch for changes to the status prop
watch(() => props.status, (value) => {
  selectedStatus.value = value;
});

// Handle close
const handleClose = () => {
  emit('close');
};

// Handle save
const handleSave = () => {
  emit('save', editorContent.value, selectedStatus.value);
};

// Close dropdown when clicking outside
const closeDropdownOnClickOutside = (event: MouseEvent) => {
  if (statusDropdownRef.value && !statusDropdownRef.value.contains(event.target as Node)) {
    isStatusDropdownOpen.value = false;
  }
};

// Add event listener when component is mounted
onMounted(() => {
  document.addEventListener('click', closeDropdownOnClickOutside);
});

// Remove event listener when component is unmounted
onUnmounted(() => {
  document.removeEventListener('click', closeDropdownOnClickOutside);
});
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
            <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                {{ modalTitle }}
              </DialogTitle>
              
              <!-- Status dropdown -->
              <div class="mt-4">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Status
                </label>
                <div class="relative">
                  <!-- Custom dropdown trigger -->
                  <div ref="statusDropdownRef">
                    <button 
                      type="button" 
                      @click="isStatusDropdownOpen = !isStatusDropdownOpen"
                      class="px-3 py-2 w-full text-left flex items-center justify-between rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    >
                      <div class="flex items-center">
                        <StatusBadge :status="selectedStatus" />
                        <span class="ml-2">{{ selectedStatus }}</span>
                      </div>
                      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                    
                    <!-- Dropdown menu -->
                    <div 
                      v-if="isStatusDropdownOpen" 
                      class="absolute z-10 mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg max-h-60 overflow-auto focus:outline-none"
                    >
                      <ul class="py-1">
                        <li 
                          v-for="option in props.statusOptions" 
                          :key="option.value"
                          @click="() => { selectedStatus = option.value; isStatusDropdownOpen = false; }"
                          class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center px-3 py-2"
                          :class="{ 'bg-gray-100 dark:bg-gray-700': selectedStatus === option.value }"
                        >
                          <StatusBadge :status="option.value" />
                          <span class="ml-2">{{ option.label }}</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Remarks
                </label>
                <div class="ql-container-wrapper ">
                  <QuillEditor
                    v-model:content="editorContent"
                    contentType="html"
                    theme="snow"
                    toolbar="essential"
                    class="bg-white dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600"
                    style="min-height: 150px; max-width: 100%;"
                  />
                </div>
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
                  class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  @click="handleSave"
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

<style scoped>
.ql-container-wrapper {
  width: 100%;
  overflow: hidden;
}

.ql-container-wrapper :deep(.ql-container),
.ql-container-wrapper :deep(.ql-editor) {
  max-width: 100%;
  overflow-x: auto;
}

.ql-container-wrapper :deep(.ql-toolbar) {
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
  background-color: #f9fafb;
}

.dark .ql-container-wrapper :deep(.ql-toolbar) {
  background-color: #374151;
  border-color: #4b5563;
}

.ql-container-wrapper :deep(.ql-container) {
  border-bottom-left-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
  background-color: white;
}

.dark .ql-container-wrapper :deep(.ql-container) {
  background-color: #1f2937;
  border-color: #4b5563;
}

.ql-container-wrapper :deep(.ql-editor) {
  min-height: 150px;
}
</style> 