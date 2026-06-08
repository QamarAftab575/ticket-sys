import axios from 'axios'

// Create axios instance with base URL pointing to /api
export const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Add CSRF token to every request
api.interceptors.request.use((config) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token
  }

  // Add user timezone
  try {
    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone
    config.headers['X-Timezone'] = userTimezone
  } catch (error) {
    config.headers['X-Timezone'] = 'UTC'
  }

  return config
})

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle common error scenarios
    if (error.response?.status === 401) {
      // Unauthorized - redirect to login
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
