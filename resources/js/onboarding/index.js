import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';
import './driver-theme.css';
import { tours } from './tours.mjs';
import { markSeen } from './api.js';

const cfg = window.__chamseOnboarding || { seen: [] };
const seen = Array.isArray(cfg.seen) ? [...cfg.seen] : [];
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let active = null; // instance driver.js en cours

function currentKey() {
    const el = document.querySelector('[data-tour-page]');
    return el ? el.getAttribute('data-tour-page') : null;
}

// Ne garder que les étapes dont la cible existe réellement dans le DOM.
function stepsFor(key) {
    const steps = tours[key] || [];
    return steps.filter((s) => document.querySelector(s.element));
}

function run(key) {
    const steps = stepsFor(key);
    if (steps.length === 0) return;

    active = driver({
        animate: !reduce,
        showProgress: true,
        nextBtnText: 'Suivant',
        prevBtnText: 'Précédent',
        doneBtnText: 'Terminer',
        progressText: '{{current}} / {{total}}',
        popoverClass: 'chamse-tour',
        steps: steps.map((s) => ({
            element: s.element,
            popover: { title: s.title, description: s.intro, side: s.side || 'bottom' },
        })),
        onDestroyed: () => {
            active = null;
            if (!seen.includes(key)) {
                seen.push(key);
                markSeen(key);
            }
        },
    });
    active.drive();
}

function evaluate() {
    const key = currentKey();
    const btn = document.querySelector('[data-tour-replay]');
    if (btn) btn.hidden = !key || !tours[key];

    if (!key || !tours[key]) return;
    if (!seen.includes(key)) run(key);
}

// Bouton « ? » : relance la visite de la page courante.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-tour-replay]');
    if (!btn) return;
    const key = currentKey();
    if (key && tours[key]) run(key);
});

// Fermer une visite en cours avant une navigation Inertia.
document.addEventListener('inertia:before', () => {
    if (active) active.destroy();
});

// Démarrage : chargement complet (Blade/Livewire) et navigation Inertia (SPA).
document.addEventListener('inertia:finish', () => requestAnimationFrame(evaluate));
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', evaluate);
} else {
    evaluate();
}
