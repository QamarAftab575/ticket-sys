import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add user timezone to all requests
try {
  const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
  window.axios.defaults.headers.common['X-Timezone'] = userTimezone;
} catch (error) {
  console.error('Failed to detect timezone:', error);
  window.axios.defaults.headers.common['X-Timezone'] = 'UTC';
}
