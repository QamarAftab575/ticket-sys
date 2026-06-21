<template>
  <div class="mb-8">
    <!-- Date and Day -->
    <p class="text-sm text-gray-500 mb-2">{{ dayOfWeek }}, {{ formattedDate }}</p>
    
    <!-- Greeting -->
    <h1 class="text-3xl text-gray-700">
      {{ greeting }}, {{ userName }}
    </h1>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const currentTime = ref(new Date())

// Update time every minute
onMounted(() => {
  setInterval(() => {
    currentTime.value = new Date()
  }, 60000)
})

const userName = computed(() => {
  return page.props.auth?.user?.name?.split(' ')[0] || 'User'
})

// Format: "Sunday, June 21"
const dayOfWeek = computed(() => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const day = currentTime.value.getDay()
  return days[day]
})

const formattedDate = computed(() => {
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ]
  const month = months[currentTime.value.getMonth()]
  const date = currentTime.value.getDate()
  return `${month} ${date}`
})

// Time-based greeting
const greeting = computed(() => {
  const hour = currentTime.value.getHours()

  if (hour >= 5 && hour < 12) {
    // 5:00 AM - 11:59 AM: Morning
    return 'Good morning'
  } else if (hour >= 12 && hour < 17) {
    // 12:00 PM - 4:59 PM: Afternoon (Lunch + After Noon)
    if (hour >= 12 && hour < 13) {
      return 'Good lunch time'
    } else if (hour >= 13 && hour < 17) {
      return 'Good afternoon'
    }
  } else if (hour >= 17 && hour < 21) {
    // 5:00 PM - 8:59 PM: Evening (Great thinking time)
    return 'Good evening'
  } else if (hour >= 21 && hour < 24) {
    // 9:00 PM - 11:59 PM: Night (Very late)
    return 'Night Time'
  } else if (hour >= 0 && hour < 5) {
    // 12:00 AM - 4:59 AM: Very late/Early
    return 'Very late'
  }

  return 'Hello'
})
</script>
