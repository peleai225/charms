// Marque une visite comme vue. Échec avalé : au pire la visite réapparaîtra.
export function markSeen(tour) {
    const cfg = window.__chamseOnboarding;
    if (!cfg || !cfg.endpoint) return;

    fetch(cfg.endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': cfg.csrf || '',
        },
        body: JSON.stringify({ tour }),
    }).catch(() => {});
}
