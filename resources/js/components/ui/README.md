# UI Components

## StatusBadge

This component provides standardized styling for application status badges across the application.

### Usage

```vue
<script setup>
import StatusBadge from '@/components/ui/StatusBadge.vue';
</script>

<template>
  <StatusBadge status="New" />
  <StatusBadge status="Pending" size="sm" />
  <StatusBadge status="Approved" size="lg" />
</template>
```

### Props

| Prop   | Type                | Default | Description                  |
|--------|---------------------|---------|------------------------------|
| status | string (required)   | -       | The status text to display   |
| size   | 'sm' \| 'md' \| 'lg'| 'md'    | The size of the badge        |

### Supported Statuses

The component supports the following status values (case-insensitive):

- New
- Pending
- Approved
- Disbursed
- Rejected
- Ready to Submit
- Process
- Pending@Agency
- Pending@Bank

### Customizing Content

You can use slots to customize the content:

```vue
<StatusBadge status="Approved">
  <span class="flex items-center">
    <CheckIcon class="w-3 h-3 mr-1" />
    Approved
  </span>
</StatusBadge>
```

### TypeScript Support

We provide TypeScript types for application statuses in `@/types/applicationStatus.ts`.

```ts
import { ApplicationStatus } from '@/types/applicationStatus';

const status: ApplicationStatus = 'Approved';
``` 