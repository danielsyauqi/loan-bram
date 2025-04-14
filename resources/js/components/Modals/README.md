# Modal Components

This directory contains reusable modal components for the application. These components are designed to be easily integrated into any page.

## Available Components

### 1. SuccessModal

A modal that displays a success message with an optional details section.

#### Props

- `show`: Boolean - Controls visibility of the modal
- `title`: String (optional) - Modal title (defaults to "Success!")
- `message`: String - Main success message
- `details`: String (optional) - Additional details like reference IDs
- `okButtonText`: String (optional) - Text for the OK button (defaults to "OK")

#### Events

- `close`: Emitted when the user closes the modal

#### Example Usage

```vue
<script setup>
import SuccessModal from '@/components/Modals/SuccessModal.vue';
import { ref } from 'vue';

const showSuccessModal = ref(false);
const successMessage = ref('Operation completed successfully!');
const successDetails = ref('Reference ID: ABC123');

const closeSuccessModal = () => {
  showSuccessModal.value = false;
};
</script>

<template>
  <SuccessModal
    :show="showSuccessModal"
    :message="successMessage"
    :details="successDetails"
    @close="closeSuccessModal"
  />
</template>
```

### 2. ErrorModal

A modal that displays validation errors or other error messages.

#### Props

- `show`: Boolean - Controls visibility of the modal
- `title`: String (optional) - Modal title (defaults to "Form Validation Error")
- `messages`: Array of String - List of error messages
- `okButtonText`: String (optional) - Text for the OK button (defaults to "OK")

#### Events

- `close`: Emitted when the user closes the modal

#### Example Usage

```vue
<script setup>
import ErrorModal from '@/components/Modals/ErrorModal.vue';
import { ref } from 'vue';

const showErrorModal = ref(false);
const errorMessages = ref(['Please select a product', 'Email is required']);

const closeErrorModal = () => {
  showErrorModal.value = false;
};
</script>

<template>
  <ErrorModal
    :show="showErrorModal"
    :messages="errorMessages"
    @close="closeErrorModal"
  />
</template>
```

### 3. ToastNotification

A non-intrusive notification that appears at the edge of the screen.

#### Props

- `show`: Boolean - Controls visibility of the toast
- `message`: String - Notification message
- `type`: String (optional) - One of 'success', 'error', 'info', 'warning' (defaults to 'success')
- `duration`: Number (optional) - Auto-dismiss duration in milliseconds
- `position`: String (optional) - One of 'top-right', 'top-left', 'bottom-right', 'bottom-left' (defaults to 'top-right')

#### Events

- `close`: Emitted when the toast is closed

#### Example Usage

```vue
<script setup>
import ToastNotification from '@/components/Modals/ToastNotification.vue';
import { ref } from 'vue';

const showToast = ref(false);
const toastMessage = ref('Item deleted successfully');

const closeToast = () => {
  showToast.value = false;
};

// To show a toast
const showSuccessToast = () => {
  toastMessage.value = 'Operation completed successfully';
  showToast.value = true;
};
</script>

<template>
  <ToastNotification
    :show="showToast"
    :message="toastMessage"
    type="success"
    :duration="5000"
    position="top-right"
    @close="closeToast"
  />
</template>
```

### 4. DeleteConfirmationModal

A modal that confirms before performing a delete operation.

#### Props

- `show`: Boolean - Controls visibility of the modal
- `title`: String (optional) - Modal title (defaults to "Confirm Deletion")
- `message`: String - Question about deletion
- `details`: String (optional) - Additional details
- `itemIdentifier`: String (optional) - ID or reference number of item being deleted
- `confirmButtonText`: String (optional) - Text for confirm button (defaults to "Delete")
- `cancelButtonText`: String (optional) - Text for cancel button (defaults to "Cancel") 
- `isProcessing`: Boolean (optional) - Whether the deletion is in progress

#### Events

- `confirm`: Emitted when the user confirms the deletion
- `cancel`: Emitted when the user cancels the deletion

#### Example Usage

```vue
<script setup>
import DeleteConfirmationModal from '@/components/Modals/DeleteConfirmationModal.vue';
import { ref } from 'vue';

const showDeleteModal = ref(false);
const isProcessing = ref(false);
const itemToDelete = ref({ id: 123, reference: 'ABC123' });

const confirmDelete = () => {
  isProcessing.value = true;
  // Perform delete operation
  // Then close modal and reset
  showDeleteModal.value = false;
  isProcessing.value = false;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
};
</script>

<template>
  <DeleteConfirmationModal
    :show="showDeleteModal"
    message="Are you sure you want to delete this item?"
    :item-identifier="itemToDelete.reference"
    :is-processing="isProcessing"
    @confirm="confirmDelete"
    @cancel="cancelDelete"
  />
</template>
``` 