import { computed } from 'vue'
import {
  getUserTimezone,
  formatToLocalTime,
  formatToLocalDate,
  formatToLocalTimeOnly,
  formatToRelativeTime,
  formatDateOnly,
  convertToUTC,
  isOverdue,
  isToday,
  getCurrentDate
} from '@/Utils/timezone'

/**
 * Composable for timezone-aware date/time handling
 * 
 * Usage:
 * const { formatDateTime, formatDate, formatTime, formatRelative } = useTimezone()
 * 
 * // Format timestamps (timezone-aware)
 * formatDateTime(task.created_at) // "Jan 15, 2024, 10:30 AM"
 * formatRelative(comment.created_at) // "2 hours ago"
 * 
 * // Format date-only fields (NOT timezone-shifted)
 * formatDateField(task.due_date) // "Jan 15, 2024"
 */
export function useTimezone() {
  // Get user's timezone
  const userTimezone = computed(() => getUserTimezone())

  /**
   * Format a UTC datetime to local datetime
   * Use for: created_at, updated_at, completed_at, comment timestamps, notifications, etc.
   */
  const formatDateTime = (utcDatetime, options = {}) => {
    return formatToLocalTime(utcDatetime, options)
  }

  /**
   * Format a UTC datetime to local date only
   * Use for: displaying just the date part of timestamps
   */
  const formatDate = (utcDatetime) => {
    return formatToLocalDate(utcDatetime)
  }

  /**
   * Format a UTC datetime to local time only
   * Use for: displaying just the time part of timestamps
   */
  const formatTime = (utcDatetime) => {
    return formatToLocalTimeOnly(utcDatetime)
  }

  /**
   * Format a UTC datetime to relative time
   * Use for: activity feeds, comments, notifications
   */
  const formatRelative = (utcDatetime) => {
    return formatToRelativeTime(utcDatetime)
  }

  /**
   * Format a date-only field (NO timezone conversion)
   * Use for: due_date, start_date, sprint dates, birthdays
   */
  const formatDateField = (dateString) => {
    return formatDateOnly(dateString)
  }

  /**
   * Convert local datetime to UTC for API submission
   */
  const toUTC = (localDate) => {
    return convertToUTC(localDate)
  }

  /**
   * Check if a task is overdue
   */
  const checkOverdue = (dueDate, status) => {
    return isOverdue(dueDate, status)
  }

  /**
   * Check if a date is today
   */
  const checkIsToday = (dateString) => {
    return isToday(dateString)
  }

  /**
   * Get current date in YYYY-MM-DD format
   */
  const todayDate = () => {
    return getCurrentDate()
  }

  return {
    userTimezone,
    formatDateTime,
    formatDate,
    formatTime,
    formatRelative,
    formatDateField,
    toUTC,
    checkOverdue,
    checkIsToday,
    todayDate
  }
}
