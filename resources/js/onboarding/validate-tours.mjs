import { tours } from './tours.mjs';

// Liste blanche attendue (doit refléter OnboardingController::TOURS).
const EXPECTED_KEYS = ['menus', 'promotions.index', 'products.index', 'orders.index'];

const errors = [];
const keys = Object.keys(tours);

for (const key of EXPECTED_KEYS) {
    if (!keys.includes(key)) errors.push(`clé manquante : ${key}`);
}
for (const key of keys) {
    if (!EXPECTED_KEYS.includes(key)) errors.push(`clé inattendue : ${key}`);

    const steps = tours[key];
    if (!Array.isArray(steps) || steps.length === 0) {
        errors.push(`${key} : aucune étape`);
        continue;
    }
    steps.forEach((s, i) => {
        if (!s.element || typeof s.element !== 'string') errors.push(`${key}[${i}] : "element" manquant`);
        if (!s.title || typeof s.title !== 'string') errors.push(`${key}[${i}] : "title" manquant`);
        if (!s.intro || typeof s.intro !== 'string') errors.push(`${key}[${i}] : "intro" manquant`);
    });
}

if (errors.length) {
    console.error('tours.mjs invalide :\n' + errors.map((e) => '  - ' + e).join('\n'));
    process.exit(1);
}
console.log(`OK — ${keys.length} visites valides`);
