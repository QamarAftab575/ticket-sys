/**
 * Timezone Utility Functions
 * 
 * Handles timezone detection, conversion, and formatting for the application.
 * All timestamps are stored in UTC in the database and converted to local time on the frontend.
 */

/**
 * Get the user's browser timezone
 * @returns {string} IANA timezone identifier (e.g., 'America/New_York', 'Asia/Karachi')
 */
export function getUserTimezone() {
  try {
    return Intl.DateTimeFormat().resolvedOptions().timeZone
  } catch (error) {
    console.error('Failed to detect timezone:', error)
    return 'UTC' // Fallback to UTC
  }
}

/**
 * Format a UTC datetime string to local time
 * @param {string|Date} utcDatetime - UTC datetime string or Date object
 * @param {Object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted local datetime string
 */
export function formatToLocalTime(utcDatetime, options = {}) {
  if (!utcDatetime) return ''
  
  try {
    const date = new Date(utcDatetime)
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
      console.error('Invalid date:', utcDatetime)
      return ''
    }
    
    const defaultOptions = {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      ...options
    }
    
    return new Intl.DateTimeFormat('en-US', defaultOptions).format(date)
  } catch (error) {
    console.error('Failed to format date:', error)
    return ''
  }
}

/**
 * Format a UTC datetime to local date only (no time)
 * @param {string|Date} utcDatetime - UTC datetime string or Date object
 * @returns {string} Formatted local date string
 */
export function formatToLocalDate(utcDatetime) {
  if (!utcDatetime) return ''
  
  try {
    const date = new Date(utcDatetime)
    
    if (isNaN(date.getTime())) {
      console.error('Invalid date:', utcDatetime)
      return ''
    }
    
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(date)
  } catch (error) {
    console.error('Failed to format date:', error)
    return ''
  }
}

/**
 * Format a UTC datetime to local time only (no date)
 * @param {string|Date} utcDatetime - UTC datetime string or Date object
 * @returns {string} Formatted local time string
 */
export function formatToLocalTimeOnly(utcDatetime) {
  if (!utcDatetime) return ''
  
  try {
    const date = new Date(utcDatetime)
    
    if (isNaN(date.getTime())) {
      console.error('Invalid date:', utcDatetime)
      return ''
    }
    
    return new Intl.DateTimeFormat('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  } catch (error) {
    console.error('Failed to format time:', error)
    return ''
  }
}

/**
 * Format a UTC datetime to relative time (e.g., "2 hours ago", "just now")
 * @param {string|Date} utcDatetime - UTC datetime string or Date object
 * @returns {string} Relative time string
 */
export function formatToRelativeTime(utcDatetime) {
  if (!utcDatetime) return ''
  
  try {
    const date = new Date(utcDatetime)
    
    if (isNaN(date.getTime())) {
      console.error('Invalid date:', utcDatetime)
      return ''
    }
    
    const now = new Date()
    const diffInSeconds = Math.floor((now - date) / 1000)
    
    // Just now (less than 1 minute)
    if (diffInSeconds < 60) {
      return 'just now'
    }
    
    // Minutes ago
    if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60)
      return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`
    }
    
    // Hours ago
    if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600)
      return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`
    }
    
    // Days ago
    if (diffInSeconds < 604800) {
      const days = Math.floor(diffInSeconds / 86400)
      return `${days} ${days === 1 ? 'day' : 'days'} ago`
    }
    
    // Weeks ago
    if (diffInSeconds < 2592000) {
      const weeks = Math.floor(diffInSeconds / 604800)
      return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`
    }
    
    // Months ago
    if (diffInSeconds < 31536000) {
      const months = Math.floor(diffInSeconds / 2592000)
      return `${months} ${months === 1 ? 'month' : 'months'} ago`
    }
    
    // Years ago
    const years = Math.floor(diffInSeconds / 31536000)
    return `${years} ${years === 1 ? 'year' : 'years'} ago`
  } catch (error) {
    console.error('Failed to format relative time:', error)
    return ''
  }
}

/**
 * Format a date-only field (should NOT be timezone-shifted)
 * Use this for due_date, start_date, birthdays, etc.
 * @param {string} dateString - Date string in YYYY-MM-DD format
 * @returns {string} Formatted date string
 */
export function formatDateOnly(dateString) {
  if (!dateString) return ''
  
  try {
    // Parse as local date (no timezone conversion)
    const [year, month, day] = dateString.split('-').map(Number)
    const date = new Date(year, month - 1, day)
    
    if (isNaN(date.getTime())) {
      console.error('Invalid date:', dateString)
      return ''
    }
    
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }).format(date)
  } catch (error) {
    console.error('Failed to format date:', error)
    return ''
  }
}

/**
 * Convert local datetime to UTC for API submission
 * @param {Date} localDate - Local Date object
 * @returns {string} ISO 8601 UTC datetime string
 */
export function convertToUTC(localDate) {
  if (!localDate || !(localDate instanceof Date)) {
    console.error('Invalid date object:', localDate)
    return null
  }
  
  try {
    return localDate.toISOString()
  } catch (error) {
    console.error('Failed to convert to UTC:', error)
    return null
  }
}

/**
 * Check if a date is overdue (for tasks)
 * @param {string} dueDate - Due date string (YYYY-MM-DD format)
 * @param {string} status - Task status
 * @returns {boolean} True if overdue
 */
export function isOverdue(dueDate, status) {
  if (!dueDate || status === 'complete') return false
  
  try {
    const [year, month, day] = dueDate.split('-').map(Number)
    const due = new Date(year, month - 1, day)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    
    return due < today
  } catch (error) {
    console.error('Failed to check overdue status:', error)
    return false
  }
}

/**
 * Check if a date is today
 * @param {string} dateString - Date string (YYYY-MM-DD format)
 * @returns {boolean} True if today
 */
export function isToday(dateString) {
  if (!dateString) return false
  
  try {
    const [year, month, day] = dateString.split('-').map(Number)
    const date = new Date(year, month - 1, day)
    const today = new Date()
    
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear()
  } catch (error) {
    console.error('Failed to check if today:', error)
    return false
  }
}

/**
 * Get current date in YYYY-MM-DD format (for date-only fields)
 * @returns {string} Current date string
 */
export function getCurrentDate() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  
  return `${year}-${month}-${day}`
}
