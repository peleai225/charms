// Calculs du compte à rebours, séparés de l'affichage.

const DAY = 86400000

// Temps restant découpé en jours / heures / minutes / secondes (jamais négatif)
export function remainingParts(endsAtMs, nowMs) {
    const total = Math.max(0, endsAtMs - nowMs)
    return {
        total,
        days: Math.floor(total / DAY),
        hours: Math.floor((total % DAY) / 3600000),
        minutes: Math.floor((total % 3600000) / 60000),
        seconds: Math.floor((total % 60000) / 1000),
    }
}

export const pad = (n) => String(n).padStart(2, '0')

// Texte compact : « 2 j 05:09:03 », ou « 05:09:03 » sous 24 h
export function compactText(parts) {
    const clock = `${pad(parts.hours)}:${pad(parts.minutes)}:${pad(parts.seconds)}`
    return parts.days > 0 ? `${parts.days} j ${clock}` : clock
}

// Décalage entre l'horloge du serveur et celle de l'appareil, pour ne pas dépendre d'un téléphone mal réglé
export function clockOffset(serverNowIso, deviceNowMs) {
    const server = Date.parse(serverNowIso ?? '')
    return Number.isNaN(server) ? 0 : server - deviceNowMs
}

// Une fin lointaine s'affiche en date, une fin proche en compte à rebours
export function isEndingSoon(endsAtIso, nowMs, withinDays = 7) {
    const end = Date.parse(endsAtIso ?? '')
    return !Number.isNaN(end) && end > nowMs && end - nowMs <= withinDays * DAY
}

export function endDateText(endsAtIso) {
    const date = new Date(endsAtIso)
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' })
}
