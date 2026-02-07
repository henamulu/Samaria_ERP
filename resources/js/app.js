import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

// Configure English locale for flatpickr
flatpickr.localize({
    firstDayOfWeek: 0,
    weekdays: {
        shorthand: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    },
    months: {
        shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        longhand: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    },
    time_24hr: false
});

const appName = import.meta.env.VITE_APP_NAME || 'Samaria ERP';

// Initialize flatpickr on all date inputs with English locale
function initDateInputs() {
    const dateInputs = document.querySelectorAll('input[type="date"]:not([data-flatpickr-initialized])');
    
    dateInputs.forEach(input => {
        // Mark as initialized to avoid re-initialization
        input.setAttribute('data-flatpickr-initialized', 'true');
        
        // Initialize flatpickr with English locale
        flatpickr(input, {
            dateFormat: 'Y-m-d',
            locale: 'en',
            allowInput: true,
            clickOpens: true,
            // Force English month and day names
            monthSelectorType: 'static',
            animate: true
        });
    });
}

// Run initialization
function setupDateInputs() {
    initDateInputs();
    
    // Watch for new inputs added dynamically
    const observer = new MutationObserver(() => {
        initDateInputs();
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
    
    // Also run on Inertia page visits
    document.addEventListener('inertia:visit', () => {
        setTimeout(initDateInputs, 100);
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupDateInputs);
} else {
    setupDateInputs();
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        app.mount(el);
        
        // Initialize date inputs after mount
        setTimeout(initDateInputs, 100);
        
        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
