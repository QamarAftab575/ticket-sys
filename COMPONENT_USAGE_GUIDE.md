# Workspace Components Usage Guide

## Quick Start

### Using StepProgressIndicator

```vue
<template>
  <StepProgressIndicator 
    :current-step="currentStep"
    :total-steps="2"
    :step-labels="['Step 1', 'Step 2']"
  />
</template>

<script setup>
import { ref } from 'vue'
import StepProgressIndicator from '@/Components/Workspace/StepProgressIndicator.vue'

const currentStep = ref(1)
</script>
```

### Using WorkspaceFormStep

```vue
<template>
  <WorkspaceFormStep
    :workspace="workspace"
    :is-name-duplicate="isNameDuplicate"
    :errors="errors"
    :show-skip="true"
    name-label="Workspace"
    name-placeholder="Acme Studio"
    @update:workspace="workspace = $event"
    @next="handleNext"
    @skip="handleSkip"
  >
    <template #cancel>
      <button type="button" @click="handleCancel">
        Cancel
      </button>
    </template>
  </WorkspaceFormStep>
</template>

<script setup>
import { ref, computed } from 'vue'
import WorkspaceFormStep from '@/Components/Workspace/WorkspaceFormStep.vue'

const workspace = ref({ name: '', types: [], description: '' })
const existingWorkspaces = ref([])
const errors = ref({})

const isNameDuplicate = computed(() => {
  if (!workspace.value.name.trim()) return false
  return existingWorkspaces.value.some(ws => 
    ws.name.toLowerCase() === workspace.value.name.toLowerCase()
  )
})

const handleNext = () => {
  // Navigate to next step
}

const handleSkip = () => {
  // Skip to next step
}

const handleCancel = () => {
  // Handle cancellation
}
</script>
```

## Component API Reference

### StepProgressIndicator

#### Props
```javascript
{
  currentStep: Number,      // Required: Current step (1-indexed)
  totalSteps: Number,       // Optional: Total steps (default: 2)
  stepLabels: Array<String> // Optional: Labels for each step
}
```

#### Example with 3 steps
```vue
<StepProgressIndicator 
  :current-step="2"
  :total-steps="3"
  :step-labels="['Setup', 'Members', 'Review']"
/>
```

---

### WorkspaceFormStep

#### Props
```javascript
{
  workspace: Object,                    // Required: { name, types, description }
  organizationTypes: Array<String>,     // Optional: Types list (default provided)
  isNameDuplicate: Boolean,             // Optional: Name duplicate flag
  errors: Object,                       // Optional: Validation errors
  showSkip: Boolean,                    // Optional: Show skip button (default: true)
  nameLabel: String,                    // Optional: Workspace label text
  namePlaceholder: String,              // Optional: Name input placeholder
  typeLabel: String,                    // Optional: Type selection label
  typeDropdownPlaceholder: String,      // Optional: Type dropdown placeholder
  descriptionLabel: String,             // Optional: Description label text
  descriptionPlaceholder: String,       // Optional: Description placeholder
  skipButtonLabel: String,              // Optional: Skip button text
  nextButtonLabel: String,              // Optional: Next button text
  duplicateErrorMessage: String         // Optional: Duplicate name error message
}
```

#### Emits
```javascript
{
  'update:workspace': (newWorkspace) => {},  // Fired when workspace data changes
  'next': () => {},                          // Fired when next button clicked
  'skip': () => {}                           // Fired when skip button clicked
}
```

#### Slots
```vue
<template #cancel>
  <!-- Custom cancel button content -->
</template>
```

#### Basic Example
```vue
<WorkspaceFormStep
  :workspace="workspace"
  @update:workspace="workspace = $event"
  @next="currentStep = 2"
  @skip="currentStep = 2"
>
  <template #cancel>
    <Link href="/dashboard">Cancel</Link>
  </template>
</WorkspaceFormStep>
```

#### Advanced Example with Custom Labels
```vue
<WorkspaceFormStep
  :workspace="workspace"
  :is-name-duplicate="isDuplicate"
  :errors="validationErrors"
  :show-skip="false"
  name-label="Project Space Name"
  name-placeholder="e.g., Marketing Q1 2025"
  type-label="What's the primary use case?"
  description-label="Project Description"
  skip-button-label="Skip for now"
  next-button-label="Continue"
  duplicate-error-message="This name is already taken in your workspace"
  @update:workspace="workspace = $event"
  @next="createProject"
>
  <template #cancel>
    <button @click="goBack" class="custom-button">
      ← Back
    </button>
  </template>
</WorkspaceFormStep>
```

## Workspace Data Structure

```javascript
const workspace = {
  name: String,              // Workspace display name
  types: Array<String>,      // Selected organization types
  description: String        // Workspace description
}

// Example:
const workspace = {
  name: 'Marketing Team',
  types: ['Marketing', 'Design'],
  description: 'Central hub for marketing campaigns and collateral'
}
```

## Common Patterns

### Pattern 1: Multi-Step Form (2 steps)

```vue
<template>
  <div>
    <StepProgressIndicator :current-step="step" />
    
    <div v-if="step === 1">
      <WorkspaceFormStep
        :workspace="form"
        @next="step = 2"
      />
    </div>
    
    <div v-if="step === 2">
      <InvitationForm
        :workspace="form"
        @submit="submit"
      />
    </div>
  </div>
</template>
```

### Pattern 2: Onboarding Flow

```vue
<template>
  <StepProgressIndicator 
    :current-step="step"
    :step-labels="['Setup', 'Invite Members']"
  />
  
  <WorkspaceFormStep
    v-if="step === 1"
    :workspace="workspace"
    :show-skip="true"
    @next="step = 2"
    @skip="step = 2"
  />
</template>
```

### Pattern 3: Duplicate Name Validation

```javascript
const isNameDuplicate = computed(() => {
  if (!workspace.value.name.trim()) return false
  
  return userWorkspaces.value.some(ws => 
    ws.name.toLowerCase() === workspace.value.name.toLowerCase()
  )
})
```

### Pattern 4: Error Handling

```javascript
const errors = ref({})

const handleSubmit = async () => {
  try {
    await router.post('/workspace/create', {
      workspace: workspace.value
    })
  } catch (error) {
    errors.value = error.response.data.errors
  }
}
```

## Customization Examples

### Changing Organization Types

```vue
<WorkspaceFormStep
  :workspace="workspace"
  :organization-types="['Startup', 'Agency', 'Enterprise', 'Non-profit']"
  @update:workspace="workspace = $event"
/>
```

### Custom Button Labels

```vue
<WorkspaceFormStep
  :workspace="workspace"
  skip-button-label="Create Later"
  next-button-label="Let's Go!"
  @update:workspace="workspace = $event"
  @next="handleNext"
/>
```

### Custom Cancel Slot

```vue
<WorkspaceFormStep
  :workspace="workspace"
  @update:workspace="workspace = $event"
>
  <template #cancel>
    <router-link to="/dashboard" class="btn btn-secondary">
      ← Back to Dashboard
    </router-link>
  </template>
</WorkspaceFormStep>
```

## Migration Guide

### Before (Duplicated Form)
```vue
<!-- Form markup repeated in two files -->
<div>
  <label>Workspace Name</label>
  <input v-model="workspace.name" />
  <!-- ... more fields ... -->
</div>
```

### After (Using Component)
```vue
<WorkspaceFormStep
  :workspace="workspace"
  @update:workspace="workspace = $event"
/>
```

## Performance Notes

1. **Components are lightweight**: Minimal reactivity overhead
2. **No external dependencies**: Uses only Vue 3 and Tailwind CSS
3. **Optimized for re-renders**: Props-based architecture
4. **No async operations**: All state management in parent

## Accessibility Features

- Semantic HTML (form, labels, inputs)
- Proper input type attributes
- Focus management with keyboard navigation
- ARIA labels for screen readers
- Error message associations

## Browser Support

- All modern browsers (Chrome, Firefox, Safari, Edge)
- Vue 3.x
- Tailwind CSS 4.x

## Troubleshooting

### Issue: Duplicate validation not working
**Solution**: Ensure `userWorkspaces` prop is properly passed and contains the user's workspaces.

### Issue: Form not submitting
**Solution**: Listen to `@next` emit event instead of form submission.

### Issue: Styles not applying
**Solution**: Ensure Tailwind CSS is properly configured in your Vue app.

### Issue: Skip button not showing
**Solution**: Set `:show-skip="true"` prop explicitly.

## Contributing

When adding new features to these components:
1. Keep them generic and reusable
2. Support v-model or emit patterns consistently
3. Use Tailwind CSS for styling
4. Document all new props and slots
5. Test in multiple contexts
