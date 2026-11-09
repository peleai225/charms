<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Engagements de la boutique affichés sous la grande bannière de l'accueil
 * (Admin → Paramètres → Accueil), enregistrés en JSON dans le réglage « home_engagements ».
 * Les clés d'icône correspondent au composant Vue Components/Home/ShopFacts.vue.
 */
final class HomeEngagements
{
    public const SETTING = 'home_engagements';

    public const MAX = 6;

    public const TITLE_MAX = 60;

    public const TEXT_MAX = 120;

    public const ICONS = [
        'truck', 'shield-check', 'rotate-ccw', 'headset', 'banknote',
        'gift', 'message-circle', 'package', 'clock', 'map-pin',
    ];

    // Une page du site (« /boutique », jamais « // » ni « /\ ») ou une adresse web http(s)
    public const LINK_PATTERN = '#^(/(?![/\\\\])[^\s\\\\]*|https?://[^\s/\\\\][^\s\\\\]*)$#i';

    /**
     * Engagements enregistrés, en ignorant tout élément invalide (icône inconnue, titre vide…).
     *
     * @return array<int, array{icon: string, title: string, text: ?string, link: ?string}>
     */
    public static function saved(): array
    {
        $items = json_decode((string) Setting::get(self::SETTING), true);
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item)
                && in_array($item['icon'] ?? null, self::ICONS, true)
                && is_string($item['title'] ?? null)
                && trim($item['title']) !== '')
            ->take(self::MAX)
            ->map(fn (array $item) => self::normalize($item))
            ->values()
            ->all();
    }

    /**
     * @return array{icon: string, title: string, text: ?string, link: ?string}
     */
    public static function normalize(array $item): array
    {
        $text = is_string($item['text'] ?? null) ? trim($item['text']) : '';
        $link = is_string($item['link'] ?? null) ? trim($item['link']) : '';

        return [
            'icon' => $item['icon'],
            'title' => trim($item['title']),
            'text' => $text !== '' ? $text : null,
            'link' => $link !== '' && preg_match(self::LINK_PATTERN, $link) ? $link : null,
        ];
    }
}
