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
  const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
  const day = currentTime.value.getDay()
  const dayKey = days[day]
  return page.props.translations?.[dayKey] || dayKey
})

const formattedDate = computed(() => {
  const months = [
    'january', 'february', 'march', 'april', 'may', 'june',
    'july', 'august', 'september', 'october', 'november', 'december'
  ]
  const month = months[currentTime.value.getMonth()]
  const date = currentTime.value.getDate()
  const monthTranslation = page.props.translations?.[month] || month
  return `${monthTranslation} ${date}`
})

// Time-based greeting
const greeting = computed(() => {
  const hour = currentTime.value.getHours()

  if (hour >= 5 && hour < 12) {
    return page.props.translations?.good_morning || 'Good Morning'
  } else if (hour >= 12 && hour < 17) {
    if (hour >= 12 && hour < 13) {
      return page.props.translations?.good_lunch_time || 'Good Lunch Time'
    } else if (hour >= 13 && hour < 17) {
      return page.props.translations?.good_afternoon || 'Good Afternoon'
    }
  } else if (hour >= 17 && hour < 21) {
    return page.props.translations?.good_evening || 'Good Evening'
  } else if (hour >= 21 && hour < 24) {
    return page.props.translations?.night_time || 'Good Night'
  } else if (hour >= 0 && hour < 5) {
    return page.props.translations?.very_late || 'Late Night'
  }

  return page.props.translations?.hello || 'Hello'
})
</script>
