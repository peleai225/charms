<?php

namespace App\Support;

/**
 * Icônes proposées pour les catégories (menu de gauche, « Nos univers populaires »).
 * Les clés correspondent au composant Vue Components/Storefront/CategoryIcon.vue.
 */
final class CategoryIcons
{
    public const ICONS = [
        'shirt' => 'Mode & vêtements',
        'footprints' => 'Chaussures',
        'baby' => 'Enfant & bébé',
        'house' => 'Maison',
        'sofa' => 'Meubles & décoration',
        'utensils' => 'Cuisine',
        'laptop' => 'Informatique',
        'smartphone' => 'Téléphones',
        'tv' => 'TV & électroménager',
        'headphones' => 'Audio',
        'watch' => 'Montres',
        'gem' => 'Bijoux',
        'sparkles' => 'Beauté',
        'pill' => 'Santé',
        'dumbbell' => 'Sport',
        'bike' => 'Vélo & loisirs',
        'car' => 'Auto & moto',
        'shopping-basket' => 'Alimentation',
        'book-open' => 'Librairie',
        'hammer' => 'Bricolage & jardin',
        'gift' => 'Cadeaux',
        'paw-print' => 'Animaux',
        'package' => 'Autre',
    ];

    public static function keys(): array
    {
        return array_keys(self::ICONS);
    }
}
