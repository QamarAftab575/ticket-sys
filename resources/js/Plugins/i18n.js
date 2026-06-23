/**
 * i18n Plugin - Translation helper for Vue
 * 
 * Translations are passed from Laravel via Inertia (single source of truth)
 * No more duplicate JSON files - everything comes from PHP
 */
export default {
    install(app, options = {}) {
        app.config.globalProperties.$t = (key, params = {}) => {
            // Get translations from Inertia page props
            const translations = app.config.globalProperties.$page?.props?.translations || {};
            
            // Support nested keys (e.g., 'messages.dashboard')
            const keys = key.split('.');
            let value = translations;
            
            for (const k of keys) {
                if (value && typeof value === 'object') {
                    value = value[k];
                } else {
                    // Fallback to key itself if translation not found
                    return key;
                }
            }
            
            // If we ended up with an object instead of string, return key
            if (typeof value !== 'string') {
                return key;
            }
            
            // Replace parameters like {count}, {name}, etc.
            let result = value;
            Object.keys(params).forEach(param => {
                result = result.replace(new RegExp(`\\{${param}\\}`, 'g'), params[param]);
            });
            
            return result;
        };
    }
};
