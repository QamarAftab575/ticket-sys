import { ref, onMounted, onUnmounted } from 'vue'

export function useLazyLoad(callback, options = {}) {
  const {
    threshold = 0.1,
    rootMargin = '50px',
  } = options

  const elementRef = ref(null)
  const isLoaded = ref(false)
  let observer = null

  const handleIntersection = (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting && !isLoaded.value) {
        isLoaded.value = true
        callback()
        if (observer) {
          observer.unobserve(entry.target)
        }
      }
    })
  }

  onMounted(() => {
    if (elementRef.value) {
      observer = new IntersectionObserver(handleIntersection, {
        threshold,
        rootMargin,
      })
      observer.observe(elementRef.value)
    }
  })

  onUnmounted(() => {
    if (observer && elementRef.value) {
      observer.unobserve(elementRef.value)
      observer.disconnect()
    }
  })

  return {
    elementRef,
    isLoaded,
  }
}
