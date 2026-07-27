import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import { vScrollReveal } from './Composables/useScrollReveal';

const appName = import.meta.env.VITE_APP_NAME || 'Chamse';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob(['./Pages/**/*.vue', './Pages/*.vue'])),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue);

        vueApp.directive('scroll-reveal', vScrollReveal);

        return vueApp.mount(el);
    },
    progress: {
        color: '#2563EB',
        showSpinner: true,
    },
});
