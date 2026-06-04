/**
 * Request Deduplication & Caching
 * Prevents duplicate in-flight requests and caches identical requests
 */

const requestCache = new Map()
const inFlightRequests = new Map()

const CACHE_DURATION = {
  DEFAULT: 30 * 1000,        // 30 seconds
  SHORT: 5 * 1000,           // 5 seconds
  LONG: 5 * 60 * 1000,       // 5 minutes
  PERSISTENT: Infinity        // Until tab close
}

/**
 * Generate cache key from request config
 */
function getCacheKey(method, url, params = null) {
  const paramStr = params ? JSON.stringify(params) : ''
  return `${method}:${url}:${paramStr}`
}

/**
 * Check if cached request is still valid
 */
function isCacheValid(cacheEntry) {
  if (cacheEntry.duration === CACHE_DURATION.PERSISTENT) return true
  return Date.now() - cacheEntry.timestamp < cacheEntry.duration
}

/**
 * Deduplicate requests - if same request is already in-flight, wait for it
 * Otherwise make request and cache for future similar requests
 */
export async function cachedRequest(method, url, options = {}) {
  const { params = null, cacheDuration = CACHE_DURATION.DEFAULT, skipCache = false } = options
  
  const cacheKey = getCacheKey(method, url, params)
  
  // Skip cache if explicitly requested (e.g., manual refresh)
  if (!skipCache) {
    // Check if request is cached and still valid
    if (requestCache.has(cacheKey)) {
      const cached = requestCache.get(cacheKey)
      if (isCacheValid(cached)) {
        return Promise.resolve(cached.data)
      } else {
        requestCache.delete(cacheKey)
      }
    }
    
    // Check if request is already in-flight - wait for it instead of duplicate
    if (inFlightRequests.has(cacheKey)) {
      return inFlightRequests.get(cacheKey)
    }
  }
  
  // Create the actual request promise
  const requestPromise = fetch(url, {
    method,
    ...(method !== 'GET' && { body: JSON.stringify(params) }),
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
      ...options.headers
    }
  })
    .then(res => res.json())
    .then(data => {
      // Cache the successful response
      requestCache.set(cacheKey, {
        data,
        timestamp: Date.now(),
        duration: cacheDuration
      })
      inFlightRequests.delete(cacheKey)
      return data
    })
    .catch(err => {
      inFlightRequests.delete(cacheKey)
      throw err
    })
  
  // Track in-flight request
  inFlightRequests.set(cacheKey, requestPromise)
  
  return requestPromise
}

/**
 * Invalidate specific cache entry
 */
export function invalidateCache(method, url, params = null) {
  const cacheKey = getCacheKey(method, url, params)
  requestCache.delete(cacheKey)
}

/**
 * Invalidate all cache entries matching pattern
 */
export function invalidateCachePattern(pattern) {
  const regex = new RegExp(pattern)
  for (const [key] of requestCache) {
    if (regex.test(key)) {
      requestCache.delete(key)
    }
  }
}

/**
 * Clear all cache
 */
export function clearCache() {
  requestCache.clear()
}

/**
 * Get cache stats for debugging
 */
export function getCacheStats() {
  return {
    cachedRequests: requestCache.size,
    inFlightRequests: inFlightRequests.size,
    cacheSize: new Blob([JSON.stringify(Array.from(requestCache.values()))]).size
  }
}
