import { ref, computed, onMounted, onUnmounted } from 'vue'

export function useVirtualScroll(items, itemHeight = 50, containerHeight = 600) {
  const scrollTop = ref(0)
  const containerRef = ref(null)

  const visibleStart = computed(() => {
    return Math.floor(scrollTop.value / itemHeight)
  })

  const visibleEnd = computed(() => {
    return Math.ceil((scrollTop.value + containerHeight) / itemHeight)
  })

  const visibleItems = computed(() => {
    return items.value.slice(visibleStart.value, visibleEnd.value)
  })

  const offsetY = computed(() => {
    return visibleStart.value * itemHeight
  })

  const handleScroll = (event) => {
    scrollTop.value = event.target.scrollTop
  }

  const totalHeight = computed(() => {
    return items.value.length * itemHeight
  })

  onMounted(() => {
    if (containerRef.value) {
      containerRef.value.addEventListener('scroll', handleScroll)
    }
  })

  onUnmounted(() => {
    if (containerRef.value) {
      containerRef.value.removeEventListener('scroll', handleScroll)
    }
  })

  return {
    containerRef,
    visibleItems,
    offsetY,
    totalHeight,
    visibleStart,
    visibleEnd,
  }
}
