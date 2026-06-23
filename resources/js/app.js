import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import 'tippy.js/dist/tippy.css';
import { setupCacheInvalidation } from '@/Composables/useCacheInvalidation';
import i18n from '@/Plugins/i18n';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Asira';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(Vue3Toastify, {
                autoClose: 5000,
                position: 'top-right',
                theme: 'light',
            })
            .use(i18n);
        
        // Make route() available globally in all components
        app.config.globalProperties.$route = window.route;
        
        app.mount(el);
        
        // Initialize cache invalidation on app start
        setupCacheInvalidation();
    },
    progress: {
        color: '#4f46e5',
    },
});
