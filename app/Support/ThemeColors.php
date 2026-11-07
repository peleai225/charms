<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Couleurs du back-office (Paramètres → Général → Apparence) → variables CSS du thème Tailwind.
 *
 * Chaque couleur donne une gamme complète (--color-primary-50 … 950) et une couleur de texte
 * lisible posée dessus (--color-primary-contrast). Les classes bg-primary-600, text-accent-contrast…
 * suivent donc automatiquement les réglages, sans couleur codée en dur dans les composants.
 */
final class ThemeColors
{
    public const DEFAULTS = [
        'primary' => '#2563EB',
        'secondary' => '#8B5CF6',
        'accent' => '#F59E0B',
    ];

    // Part de la couleur mêlée à du blanc (teintes claires) ou à du noir (teintes foncées)
    private const LIGHT = [50 => 6, 100 => 12, 200 => 22, 300 => 40, 400 => 65, 500 => 90];

    private const DARK = [700 => 85, 800 => 70, 900 => 55, 950 => 40];

    private const DARK_TEXT = '#0F172A';

    /**
     * @return array<string, string> nom de variable CSS => valeur
     */
    public static function cssVariables(): array
    {
        $vars = [];
        foreach (self::DEFAULTS as $name => $default) {
            $hex = self::hex(Setting::get("{$name}_color"), $default);

            foreach (self::LIGHT as $shade => $percent) {
                $vars["--color-{$name}-{$shade}"] = "color-mix(in srgb, {$hex} {$percent}%, white)";
            }
            $vars["--color-{$name}-600"] = $hex;
            foreach (self::DARK as $shade => $percent) {
                $vars["--color-{$name}-{$shade}"] = "color-mix(in srgb, {$hex} {$percent}%, black)";
            }
            $vars["--color-{$name}-contrast"] = self::contrastText($hex);
        }

        return $vars;
    }

    /**
     * Couleur hexadécimale valide (#RRGGBB, en majuscules), sinon la valeur par défaut.
     * Protège aussi la feuille de style d'une valeur mal saisie.
     */
    public static function hex(?string $value, string $default): string
    {
        $value = trim((string) $value);
        if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i', $value, $m)) {
            $value = "#{$m[1]}{$m[1]}{$m[2]}{$m[2]}{$m[3]}{$m[3]}";
        }

        return preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtoupper($value) : $default;
    }

    /**
     * Texte blanc ou foncé selon ce qui contraste le mieux (luminance relative WCAG).
     */
    public static function contrastText(string $hex): string
    {
        $luminance = self::luminance($hex);
        $withWhite = 1.05 / ($luminance + 0.05);
        $withDark = ($luminance + 0.05) / (self::luminance(self::DARK_TEXT) + 0.05);

        return $withWhite >= $withDark ? '#FFFFFF' : self::DARK_TEXT;
    }

    private static function luminance(string $hex): float
    {
        [$r, $g, $b] = array_map(function ($pair) {
            $c = hexdec($pair) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
