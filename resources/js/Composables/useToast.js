import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  const showToast = (message, type = 'info', duration = 3000) => {
    const id = Date.now()
    const toast = { id, message, type }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }

    return id
  }

  const removeToast = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  const success = (message, duration = 3000) => {
    return showToast(message, 'success', duration)
  }

  const error = (message, duration = 5000) => {
    return showToast(message, 'error', duration)
  }

  const warning = (message, duration = 4000) => {
    return showToast(message, 'warning', duration)
  }

  const info = (message, duration = 3000) => {
    return showToast(message, 'info', duration)
  }

  return {
    toasts,
    showToast,
    removeToast,
    success,
    error,
    warning,
    info,
  }
}
