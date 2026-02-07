import { onMounted, onUnmounted } from 'vue';

export function useDateInput() {
    const forceEnglishLocale = () => {
        // Force English locale for all date inputs
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(input => {
            // Set lang attribute
            input.setAttribute('lang', 'en');
            
            // Override the locale for date formatting
            const originalValue = input.value;
            
            // Add event listener to format date in English
            input.addEventListener('focus', function() {
                this.setAttribute('lang', 'en');
                // Force browser to use English locale
                if (this.value) {
                    const date = new Date(this.value + 'T00:00:00');
                    // Format date in English format
                    const formatted = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    });
                }
            });
            
            // Intercept the native calendar if possible
            input.addEventListener('click', function(e) {
                this.setAttribute('lang', 'en');
            });
        });
    };

    onMounted(() => {
        forceEnglishLocale();
        
        // Re-run when DOM changes (for dynamic inputs)
        const observer = new MutationObserver(() => {
            forceEnglishLocale();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Store observer for cleanup
        window.dateInputObserver = observer;
    });

    onUnmounted(() => {
        if (window.dateInputObserver) {
            window.dateInputObserver.disconnect();
        }
    });

    return { forceEnglishLocale };
}
