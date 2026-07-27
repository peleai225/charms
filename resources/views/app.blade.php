<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Chamse'));
        $siteFavicon = \App\Models\Setting::get('favicon');
        $primaryColor = \App\Models\Setting::get('primary_color', '#2563EB');
        $secondaryColor = \App\Models\Setting::get('secondary_color', '#8b5cf6');
        $accentColor = \App\Models\Setting::get('accent_color', '#f59e0b');
    @endphp

    @if($siteFavicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteFavicon) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%232563EB' rx='15' width='100' height='100'/><text x='50%' y='55%' dominant-baseline='middle' text-anchor='middle' font-size='50' fill='white'>{{ substr($siteName, 0, 1) }}</text></svg>">
    @endif

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Tailwind v4 : on écrase les variables --color-primary-* directement dans :root.
           Toutes les classes bg-primary-600, text-primary-600, etc. lisent ces vars nativement. */
        :root {
            --color-primary-50:  color-mix(in srgb, {{ $primaryColor }}  6%, white);
            --color-primary-100: color-mix(in srgb, {{ $primaryColor }} 12%, white);
            --color-primary-200: color-mix(in srgb, {{ $primaryColor }} 22%, white);
            --color-primary-300: color-mix(in srgb, {{ $primaryColor }} 40%, white);
            --color-primary-400: color-mix(in srgb, {{ $primaryColor }} 65%, white);
            --color-primary-500: color-mix(in srgb, {{ $primaryColor }} 90%, white);
            --color-primary-600: {{ $primaryColor }};
            --color-primary-700: color-mix(in srgb, {{ $primaryColor }} 85%, black);
            --color-primary-800: color-mix(in srgb, {{ $primaryColor }} 70%, black);
            --color-primary-900: color-mix(in srgb, {{ $primaryColor }} 55%, black);
            --color-primary-950: color-mix(in srgb, {{ $primaryColor }} 40%, black);

            --color-secondary-50:  color-mix(in srgb, {{ $secondaryColor }}  6%, white);
            --color-secondary-100: color-mix(in srgb, {{ $secondaryColor }} 12%, white);
            --color-secondary-500: color-mix(in srgb, {{ $secondaryColor }} 90%, white);
            --color-secondary-600: {{ $secondaryColor }};
            --color-secondary-700: color-mix(in srgb, {{ $secondaryColor }} 85%, black);

            --color-accent-50:  color-mix(in srgb, {{ $accentColor }}  8%, white);
            --color-accent-100: color-mix(in srgb, {{ $accentColor }} 14%, white);
            --color-accent-400: color-mix(in srgb, {{ $accentColor }} 70%, white);
            --color-accent-500: color-mix(in srgb, {{ $accentColor }} 90%, white);
            --color-accent-600: {{ $accentColor }};
            --color-accent-700: color-mix(in srgb, {{ $accentColor }} 85%, black);
            --color-accent-800: color-mix(in srgb, {{ $accentColor }} 70%, black);
        }
    </style>

    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
