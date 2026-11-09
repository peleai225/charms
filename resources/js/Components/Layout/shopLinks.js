// Rubriques de la boutique : deuxième ligne de l'en-tête (HeaderNavBar) et menu téléphone (MobileMenu).
export const SHOP_LINKS = [
    { href: '/', label: 'Accueil', isActive: (path) => path === '/' },
    { href: '/boutique?on_sale=1', label: 'Promotions', isActive: (path, q) => path === '/boutique' && q.has('on_sale') },
    { href: '/boutique?sort=newest', label: 'Nouveautés', isActive: (path, q) => path === '/boutique' && q.get('sort') === 'newest' && !q.has('on_sale') },
    { href: '/meilleures-ventes', label: 'Meilleures ventes', isActive: (path) => path === '/meilleures-ventes' },
]

export function isActiveLink(link, url) {
    const [path, search = ''] = String(url || '').split('?')

    return link.isActive(path, new URLSearchParams(search))
}
