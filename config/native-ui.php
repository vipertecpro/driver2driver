<?php

/**
 * Native UI — Theme Tokens
 *
 * Published via `php artisan vendor:publish --tag=native-ui-config`.
 * Edit to customize your app's visual identity in one place.
 *
 * For dynamic per-tenant theming, use Native\Mobile\UI\Theme::merge([...])
 * from a service provider. Runtime merges deep-merge on top of these values.
 *
 * Decision log: /docs/NATIVE-UI-REWRITE-PLAN.md (D — theme layer)
 */

return [

    /*
    |---------------------------------------------------------------------------
    | Theme
    |---------------------------------------------------------------------------
    |
    | 17 color tokens, 4 radii, 4 font sizes, font family.
    |
    | "on-X" means "color of content placed ON a surface of color X"
    |   — i.e., text/icons on that background.
    |
    | Color tokens accept:
    |   - CSS hex: '#B91C1C', '#F00', or with alpha '#8B5CF680' (#RRGGBBAA)
    |   - Tailwind palette names: 'red-300', 'orange-800'
    |   - Opacity modifiers on either: 'red-300/20', '#8B5CF6/50'
    |
    | Dark mode is auto-derived from `light` when `dark` is not set. To opt
    | into explicit dark tokens, fill out the `dark` block.
    |
    | The default pairs meet WCAG AA (4.5:1) — if you customize, keep each
    | `on-*` color at 4.5:1 contrast against its background token.
    |
    */

    'theme' => [

        /*
        | High-Vis Utility — a driver works in two conditions, so the brand
        | ships two appearances rather than one fixed look.
        |
        |   Day   — a windscreen in full sun. High-luminance grounds, and
        |           accents that go DARKER and more saturated. Glare destroys
        |           mid-tones, so daylight is where the palette deepens; a
        |           lightened version of the night palette washes out.
        |   Night — a low-glare cab at 2am. Near-black asphalt grounds and
        |           bright signal colours for a dark-adapted eye, with Safety
        |           Orange held back for the one action worth interrupting for.
        |
        | Both blocks hold every `on-*` pair at 4.5:1 or better against its
        | background, and border tokens at 3:1 as non-text UI components.
        */
        'light' => [
            // Safety Orange — primary actions only. Must be impossible to
            // miss, and identical in both appearances: it is the brand.
            'primary' => '#FF6B00',
            'on-primary' => '#16181A',

            // Muted text and secondary actions.
            'secondary' => '#55595C',
            'on-secondary' => '#FFFFFF',

            // Surface = cards / sheets. Background = warm concrete root.
            // The OS paints its own `systemBackground` behind the safe-area
            // and tab-bar regions; PaintsWindowBackground pushes this value
            // through `UI.SetBackground` so those regions match instead.
            'surface' => '#FFFFFF',
            'on-surface' => '#1A1715',
            'background' => '#F4F2F0',
            'on-background' => '#1A1715',

            // Elevated chips / filled inputs; warm muted labels on top.
            'surface-variant' => '#E7E3E0',
            'on-surface-variant' => '#6B4A38',

            // Warm brand outline for emphasized borders; neutral variant
            // for card edges and dividers. (Custom tokens — the theme map
            // is open-ended, any key works in *-theme-* classes.)
            'outline' => '#A66A42',
            'outline-variant' => '#CFCAC5',

            'destructive' => '#B3261E',
            'on-destructive' => '#FFFFFF',

            // "Safe to proceed": open status, verified codes.
            'success' => '#00713C',
            'on-success' => '#FFFFFF',

            // Burnt orange — headlines, wayfinding, active-state text.
            // Safety Orange itself is only 2.4:1 on white, so accent text
            // takes the deeper tint rather than the brand hue.
            'accent' => '#A63C00',
            'on-accent' => '#FFFFFF',

            // Map canvas and street grid. Was a hardcoded `bg-[#9AA1A8]` —
            // the one bright surface in an otherwise dark app.
            'map-surface' => '#E3E6E8',
            'map-road' => '#FFFFFF',

            // Category identity. Five hues that stay apart under glare, and
            // none of them is Safety Orange — a pin must never read as an
            // action. Consumed via IntelCategory, never inline.
            'cat-access' => '#00713C',
            'on-cat-access' => '#FFFFFF',
            'cat-bathroom' => '#00627D',
            'on-cat-bathroom' => '#FFFFFF',
            'cat-dog' => '#B3261E',
            'on-cat-dog' => '#FFFFFF',
            'cat-gas' => '#7A5400',
            'on-cat-gas' => '#FFFFFF',
            'cat-traffic' => '#6B3FA0',
            'on-cat-traffic' => '#FFFFFF',
        ],

        'dark' => [
            'primary' => '#FF6B00',
            'on-primary' => '#16181A',

            'secondary' => '#A8ADB1',
            'on-secondary' => '#16181A',

            'surface' => '#1B1F21',
            'on-surface' => '#E6E8E9',
            'background' => '#101315',
            'on-background' => '#E6E8E9',

            'surface-variant' => '#2B3033',
            'on-surface-variant' => '#C2B0A6',

            'outline' => '#8A6047',
            'outline-variant' => '#454B50',

            'destructive' => '#93000A',
            'on-destructive' => '#FFDAD6',

            'success' => '#00E475',
            'on-success' => '#00351A',

            'accent' => '#FFB693',
            'on-accent' => '#351000',

            'map-surface' => '#23282B',
            'map-road' => '#343A3E',

            'cat-access' => '#00E475',
            'on-cat-access' => '#00351A',
            'cat-bathroom' => '#6FD4FF',
            'on-cat-bathroom' => '#002F3E',
            'cat-dog' => '#FF9E93',
            'on-cat-dog' => '#3F0A04',
            'cat-gas' => '#FFC94D',
            'on-cat-gas' => '#3B2A00',
            'cat-traffic' => '#C9A6FF',
            'on-cat-traffic' => '#23103F',
        ],

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
        'radius-full' => 9999,

        // Font size scale (points / sp).
        'font-sm' => 14,
        'font-md' => 16,
        'font-lg' => 20,
        'font-xl' => 24,

    ],

    /*
    |---------------------------------------------------------------------------
    | Font aliases
    |---------------------------------------------------------------------------
    |
    | Semantic names for bundled fonts (resources/fonts/ file tokens, minus
    | the extension). Use an alias anywhere a font token works — the `font`
    | attribute (`font="accent"`), chrome ->font() builders, or the layout
    | $font property. The special `default` alias sets the app-wide default
    | font (and supersedes the `font-family` token above).
    |
    |   'fonts' => [
    |       'default' => 'Inter-Regular',
    |       'accent'  => 'DynaPuff-Regular',
    |   ],
    |
    */

    'fonts' => [
        // Atkinson Hyperlegible body copy app-wide — distinct character
        // recognition for house numbers and gate codes.
        'default' => 'AtkinsonHyperlegible-Regular',
        'body' => 'AtkinsonHyperlegible-Regular',
        'body-bold' => 'AtkinsonHyperlegible-Bold',

        // Archivo Narrow — headlines and primary actions (more characters
        // per line for long addresses).
        'headline' => 'ArchivoNarrow-Bold',

        // JetBrains Mono — technical data: codes, timestamps, driver ids.
        'mono' => 'JetBrainsMono-Regular',
        'mono-bold' => 'JetBrainsMono-Bold',
    ],

];
