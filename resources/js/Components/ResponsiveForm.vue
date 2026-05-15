<template>
  <form @submit.prevent="$emit('submit')" class="space-y-4">
    <slot />
  </form>
</template>

<script setup>
defineEmits(['submit'])
</script>

<style scoped>
/* Mobile responsiveness */
@media (max-width: 640px) {
  :deep(.form-group) {
    margin-bottom: 1rem;
  }

  :deep(.form-group label) {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
  }

  :deep(.form-group input),
  :deep(.form-group textarea),
  :deep(.form-group select) {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
  }

  :deep(.form-group input:focus),
  :deep(.form-group textarea:focus),
  :deep(.form-group select:focus) {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  :deep(.form-row) {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  :deep(.form-row.grid-2) {
    display: grid;
    grid-template-columns: 1fr;
  }

  :deep(.form-row.grid-2 > *) {
    width: 100%;
  }
}

@media (min-width: 641px) {
  :deep(.form-row.grid-2) {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
}
</style>
