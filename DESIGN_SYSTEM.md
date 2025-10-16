# POS App Design System

## Overview
This document outlines the unified design system for creating professional and consistent pages across the application.

## Components Created

### 1. PageHeader Component
**Location:** `/resources/js/Components/PageHeader.vue`

**Purpose:** Provides consistent page titles, subtitles, and action buttons.

**Usage:**
```vue
<PageHeader title="Page Title" subtitle="Optional description">
  <template #actions>
    <Button variant="primary">Action Button</Button>
  </template>
</PageHeader>
```

### 2. Card Component
**Location:** `/resources/js/Components/Card.vue`

**Purpose:** Wraps content in a consistent white card with shadow and rounded corners.

**Usage:**
```vue
<Card title="Card Title">
  <!-- Your content here -->
</Card>
```

### 3. DataTable Component
**Location:** `/resources/js/Components/DataTable.vue`

**Purpose:** Provides uniform table styling with borders, shadows, and hover effects.

**Usage:**
```vue
<DataTable>
  <template #header>
    <th class="px-4 py-3 text-left font-semibold">Column 1</th>
    <th class="px-4 py-3 text-left font-semibold">Column 2</th>
  </template>

  <tr v-for="item in items" :key="item.id">
    <td class="px-4 py-3">{{ item.name }}</td>
    <td class="px-4 py-3">{{ item.value }}</td>
  </tr>
</DataTable>
```

### 4. Button Component
**Location:** `/resources/js/Components/Button.vue`

**Purpose:** Unified button styling with multiple variants.

**Variants:**
- `primary` - Blue background (main actions)
- `secondary` - Gray background with border (secondary actions)
- `danger` - Red background (delete/destructive actions)
- `success` - Green background (success actions)
- `ghost` - Transparent with hover (text buttons)

**Sizes:**
- `sm` - Small (compact areas)
- `md` - Medium (default)
- `lg` - Large (prominent actions)

**Usage:**
```vue
<!-- Link button -->
<Button href="/products/create" variant="primary">Create</Button>

<!-- Regular button -->
<Button @click="handleClick" variant="secondary" size="sm">Cancel</Button>

<!-- Disabled button -->
<Button :disabled="true" variant="danger">Delete</Button>
```

### 5. EmptyState Component
**Location:** `/resources/js/Components/EmptyState.vue`

**Purpose:** Shows a friendly message when there's no data.

**Usage:**
```vue
<EmptyState 
  icon="📦" 
  message="No items found" 
  subtitle="Add your first item to get started"
/>
```

## Design Standards

### Colors
- **Primary:** Blue-600 (#2563eb)
- **Secondary:** Gray-100/Gray-700
- **Success:** Green-600 (#16a34a)
- **Danger:** Red-600 (#dc2626)
- **Warning:** Yellow-600 (#ca8a04)
- **Info:** Purple-600 (#9333ea)

### Spacing
- **Page Container:** `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8`
- **Section Gap:** `mb-6` or `gap-6`
- **Card Padding:** `p-6`
- **Table Cell Padding:** `px-4 py-3`

### Typography
- **Page Title:** `text-3xl font-bold text-gray-800`
- **Subtitle:** `text-sm text-gray-600`
- **Card Title:** `text-lg font-semibold text-gray-800`
- **Table Header:** `font-semibold text-gray-700`
- **Body Text:** `text-sm text-gray-700`

### Borders & Shadows
- **Card Shadow:** `shadow-sm`
- **Table Border:** `border border-gray-200`
- **Rounded Corners:** `rounded-lg` (cards, buttons, tables)
- **Dividers:** `border-b border-gray-200`

## Page Structure Template

```vue
<template>
  <AppLayout title="Page Title">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <PageHeader title="Page Title" subtitle="Page description">
        <template #actions>
          <Button href="/create" variant="primary">
            <svg class="w-4 h-4 mr-2"><!-- icon --></svg>
            Add New
          </Button>
        </template>
      </PageHeader>

      <!-- Main Content Card -->
      <Card>
        <DataTable>
          <template #header>
            <th class="px-4 py-3 text-left font-semibold">Column</th>
          </template>

          <!-- Empty State -->
          <tr v-if="items.length === 0">
            <td colspan="X" class="p-0">
              <EmptyState icon="📦" message="No items" />
            </td>
          </tr>

          <!-- Data Rows -->
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3">{{ item.name }}</td>
          </tr>
        </DataTable>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import Card from '@/Components/Card.vue'
import DataTable from '@/Components/DataTable.vue'
import Button from '@/Components/Button.vue'
import EmptyState from '@/Components/EmptyState.vue'

defineProps({
  items: Array
})
</script>
```

## Best Practices

1. **Always use PageHeader** for consistent page titles and actions
2. **Wrap content in Card** components for visual grouping
3. **Use DataTable** for all tabular data
4. **Use Button component** instead of raw links/buttons
5. **Show EmptyState** when no data is available
6. **Maintain consistent spacing** using the defined standards
7. **Use semantic colors** (danger for delete, primary for create, etc.)
8. **Add hover states** to interactive elements
9. **Use responsive classes** (md:, lg:) for mobile support
10. **Include loading states** where appropriate

## Migration Checklist

When updating an existing page:

- [ ] Replace page header with `<PageHeader>`
- [ ] Wrap content in `<Card>` component
- [ ] Replace table with `<DataTable>`
- [ ] Replace buttons/links with `<Button>` component
- [ ] Add `<EmptyState>` for zero data scenarios
- [ ] Update container to use standard max-width and padding
- [ ] Import all required components
- [ ] Test responsive behavior
- [ ] Verify consistent spacing and colors

## Next Steps

To complete the uniform design:

1. Update all remaining index pages (Categories, Units, Expenses, Ads, Facilities, Members)
2. Update all create/edit forms with consistent styling
3. Update dashboard with new components
4. Update reports pages
5. Add loading skeletons for better UX
6. Consider adding toast notifications component
