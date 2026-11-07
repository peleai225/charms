<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\ThemeColors;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les couleurs du back-office (principale, secondaire, accent) pilotent tout le site
 * via les variables CSS du thème Tailwind.
 */
class ThemeColorsTest extends TestCase
{
    use RefreshDatabase;

    private function colors(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }
        Setting::clearCache();
    }

    public function test_every_back_office_color_gets_a_full_range_of_shades_on_the_site(): void
    {
        $this->colors(['primary_color' => '#C99A06', 'secondary_color' => '#0F766E', 'accent_color' => '#E11D48']);

        $html = $this->get('/')->assertOk()->getContent();

        foreach (['primary' => '#C99A06', 'secondary' => '#0F766E', 'accent' => '#E11D48'] as $name => $hex) {
            $this->assertStringContainsString("--color-{$name}-600: {$hex};", $html);
            foreach ([50, 100, 200, 300, 400, 500, 700, 800, 900, 950] as $shade) {
                $this->assertStringContainsString("--color-{$name}-{$shade}: color-mix(in srgb, {$hex}", $html);
            }
        }
    }

    public function test_text_on_a_theme_color_stays_readable(): void
    {
        $this->colors(['primary_color' => '#FACC15', 'accent_color' => '#1E3A8A']);

        $this->get('/')->assertOk()
            ->assertSee('--color-primary-contrast: #0F172A;', false)
            ->assertSee('--color-accent-contrast: #FFFFFF;', false);
    }

    public function test_a_malformed_color_falls_back_to_the_default(): void
    {
        $this->colors(['primary_color' => 'red;}', 'accent_color' => '#abc']);

        $this->get('/')->assertOk()
            ->assertDontSee('red;}', false)
            ->assertSee('--color-primary-600: #2563EB;', false)
            ->assertSee('--color-accent-600: #AABBCC;', false);
    }

    public function test_contrast_follows_the_wcag_luminance(): void
    {
        $this->assertSame('#FFFFFF', ThemeColors::contrastText('#2563EB'));
        $this->assertSame('#FFFFFF', ThemeColors::contrastText('#DC2626'));
        $this->assertSame('#0F172A', ThemeColors::contrastText('#F59E0B'));
        $this->assertSame('#0F172A', ThemeColors::contrastText('#FFFFFF'));
    }
}
