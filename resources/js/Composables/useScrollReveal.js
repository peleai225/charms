/**
 * Vue 3 directive v-scroll-reveal for animating elements on scroll.
 * Uses native IntersectionObserver.
 */
export const vScrollReveal = {
    mounted(el, binding) {
        // Marquer l'élément pour le CSS
        el.setAttribute('data-scroll-reveal', '');

        // Traiter les modificateurs de délai (delay-100, delay-200, etc.)
        if (binding.modifiers) {
            Object.keys(binding.modifiers).forEach(mod => {
                if (mod.startsWith('delay-')) {
                    el.classList.add(mod);
                }
            });
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        el.classList.add('revealed');
                        observer.unobserve(el);
                    }
                });
            },
            {
                threshold: binding.value?.threshold ?? 0.1,
                rootMargin: binding.value?.rootMargin ?? '0px 0px -40px 0px',
            }
        );

        el._scrollObserver = observer;
        observer.observe(el);
    },
    unmounted(el) {
        if (el._scrollObserver) {
            el._scrollObserver.disconnect();
            delete el._scrollObserver;
        }
    },
};
