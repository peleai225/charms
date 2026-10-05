// Visites guidées du back-office. Les sélecteurs reposent sur des attributs
// data-tour posés dans les vues. Chaque étape pointe un élément de « chrome »
// stable (bouton, filtre, item de menu), jamais une ligne de données qui
// pourrait manquer sur un compte vide.
export const tours = {
    menus: [
        { element: '[data-tour="nav-dashboard"]', title: 'Votre tableau de bord', intro: "Le point de départ : un résumé de l'activité de la boutique.", side: 'right' },
        { element: '[data-tour="nav-orders"]', title: 'Les commandes', intro: 'Retrouvez ici toutes les commandes et leur statut.', side: 'right' },
        { element: '[data-tour="nav-products"]', title: 'Vos produits', intro: 'Ajoutez et modifiez vos articles, leurs prix et leur stock.', side: 'right' },
        { element: '[data-tour="nav-promotions"]', title: 'Les offres par lot', intro: 'Créez des offres « X articles pour Y F CFA ». On y revient en détail sur cet écran.', side: 'right' },
    ],
    'promotions.index': [
        { element: '[data-tour="promo-new"]', title: 'Créer une offre', intro: 'Cliquez ici pour créer une nouvelle offre par lot.', side: 'bottom' },
        { element: '[data-tour="promo-filters"]', title: 'Filtrer', intro: 'Recherchez une offre par nom ou filtrez par statut (active, programmée, expirée).', side: 'bottom' },
    ],
    'products.index': [
        { element: '[data-tour="product-new"]', title: 'Ajouter un produit', intro: 'Créez une nouvelle fiche produit : nom, prix, stock, catégorie.', side: 'bottom' },
        { element: '[data-tour="product-filters"]', title: 'Rechercher', intro: 'Retrouvez un produit par son nom ou filtrez la liste.', side: 'bottom' },
    ],
    'orders.index': [
        { element: '[data-tour="order-filters"]', title: 'Suivre les commandes', intro: 'Filtrez par statut pour voir ce qui est à traiter.', side: 'bottom' },
    ],
};
