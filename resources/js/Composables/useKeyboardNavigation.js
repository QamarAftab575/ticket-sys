import { onMounted, onUnmounted } from 'vue'

export function useKeyboardNavigation(handlers = {}) {
  const handleKeyDown = (event) => {
    const key = event.key.toLowerCase()

    // Tab key - handled by browser
    if (key === 'tab') {
      return
    }

    // Escape key
    if (key === 'escape' && handlers.onEscape) {
      event.preventDefault()
      handlers.onEscape()
    }

    // Enter key
    if (key === 'enter' && handlers.onEnter) {
      event.preventDefault()
      handlers.onEnter()
    }

    // Arrow keys
    if (key === 'arrowup' && handlers.onArrowUp) {
      event.preventDefault()
      handlers.onArrowUp()
    }

    if (key === 'arrowdown' && handlers.onArrowDown) {
      event.preventDefault()
      handlers.onArrowDown()
    }

    if (key === 'arrowleft' && handlers.onArrowLeft) {
      event.preventDefault()
      handlers.onArrowLeft()
    }

    if (key === 'arrowright' && handlers.onArrowRight) {
      event.preventDefault()
      handlers.onArrowRight()
    }
  }

  onMounted(() => {
    window.addEventListener('keydown', handleKeyDown)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown)
  })

  return {
    handleKeyDown,
  }
}
