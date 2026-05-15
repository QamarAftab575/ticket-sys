import { ref, computed } from 'vue'

export function useFormValidation() {
  const errors = ref({})
  const isSubmitting = ref(false)

  const hasError = (field) => {
    return !!errors.value[field]
  }

  const getError = (field) => {
    return errors.value[field]?.[0] || ''
  }

  const setErrors = (newErrors) => {
    errors.value = newErrors
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const clearError = (field) => {
    delete errors.value[field]
  }

  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0
  })

  return {
    errors,
    isSubmitting,
    hasError,
    getError,
    setErrors,
    clearErrors,
    clearError,
    isValid,
  }
}
