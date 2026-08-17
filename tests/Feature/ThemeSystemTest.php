<?php

use App\IntelCategory;
use App\NativeComponents\Concerns\PaintsWindowBackground;
use App\NativeComponents\DriverIntelFeed;
use App\NativeComponents\DriverIntelLocation;
use App\NativeComponents\DriverIntelMap;
use App\NativeComponents\DriverIntelReport;
use App\NativeComponents\DriverIntelStyleGuide;
use Native\Mobile\Edge\CallbackRegistry;
use Native\Mobile\Edge\TailwindParser;
use Native\Mobile\Facades\System;
use Native\Mobile\Testing\Native;
use Native\Mobile\UI\Elements\Badge;
use Native\Mobile\UI\Elements\Button;

/**
 * Guards the High-Vis design system contract: every theme role used by the
 * views resolves through config/native-ui.php — including the app-defined
 * success / outline-variant tokens — and the theme() helper feeds chrome
 * builders from the same source of truth.
 */
it('resolves every theme token the views rely on', function (string $class, array $expected) {
    expect(TailwindParser::parse($class))->toBe($expected);
})->with([
    // Day value first, Night carried alongside under `dark`. The two blocks
    // were byte-identical before, which collapsed the pair to a single value
    // and left the app dark-only on a light-mode device.
    'background' => ['bg-theme-background', ['dark' => ['bg' => '#101315'], 'bg' => '#F4F2F0']],
    'surface' => ['bg-theme-surface', ['dark' => ['bg' => '#1B1F21'], 'bg' => '#FFFFFF']],
    'on-surface text' => ['text-theme-on-surface', ['dark' => ['color' => '#E6E8E9'], 'color' => '#1A1715']],
    'accent text' => ['text-theme-accent', ['dark' => ['color' => '#FFB693'], 'color' => '#A63C00']],
    'custom success' => ['bg-theme-success', ['dark' => ['bg' => '#00E475'], 'bg' => '#00713C']],
    'custom on-success' => ['text-theme-on-success', ['dark' => ['color' => '#00351A'], 'color' => '#FFFFFF']],
    'custom outline-variant' => ['border-theme-outline-variant', ['dark' => ['borderColor' => '#454B50'], 'borderColor' => '#CFCAC5', 'borderWidth' => 1]],
    // Safety Orange is the brand and is deliberately identical in both
    // appearances, so it emits no dark companion.
    'primary' => ['bg-theme-primary', ['bg' => '#FF6B00']],
]);

it('gives every category and map token a day and a night value', function (string $class) {
    $parsed = TailwindParser::parse($class);

    expect($parsed)->toHaveKey('dark')
        ->and($parsed['dark'])->not->toBe(array_diff_key($parsed, ['dark' => 1]));
})->with([
    'cat-access' => 'bg-theme-cat-access',
    'cat-bathroom' => 'bg-theme-cat-bathroom',
    'cat-dog' => 'bg-theme-cat-dog',
    'cat-gas' => 'bg-theme-cat-gas',
    'cat-traffic' => 'bg-theme-cat-traffic',
    'on-cat-access' => 'text-theme-on-cat-access',
    'on-cat-traffic' => 'text-theme-on-cat-traffic',
    'map-surface' => 'bg-theme-map-surface',
    'map-road' => 'bg-theme-map-road',
]);

it('keeps Safety Orange out of the category palette', function () {
    $categories = collect(IntelCategory::cases())
        ->flatMap(fn (IntelCategory $c): array => [
            TailwindParser::parse($c->barClass()),
            TailwindParser::parse($c->accentClass()),
        ]);

    $primary = theme('primary');

    $categories->each(function (array $parsed) use ($primary): void {
        expect($parsed['bg'] ?? $parsed['color'] ?? null)->not->toBe($primary)
            ->and($parsed['dark']['bg'] ?? $parsed['dark']['color'] ?? null)->not->toBe($primary);
    });
});

it('drains hardcoded hex out of the category presentation contract', function () {
    collect(IntelCategory::cases())->each(function (IntelCategory $category): void {
        foreach (['barClass', 'accentClass', 'pinClass', 'pinIconClass'] as $method) {
            expect($category->{$method}())->toContain('-theme-')
                ->and($category->{$method}())->not->toContain('#');
        }
    });
});

it('paints the OS-owned regions with the ground on every screen', function (string $component) {
    // The OS owns regions our element tree never reaches — the ~84pt strip
    // under the floating tab bar, safe-area insets, overscroll, transition
    // gaps — and paints them `systemBackground` (measured #FFFFFF light,
    // #000000 dark). `UI.SetBackground` is the only hook that reaches them,
    // so every screen pushes the ground through it. Without this the app
    // shows a hard band at the bottom of every tab screen.
    expect(class_uses_recursive($component))->toContain(PaintsWindowBackground::class);
})->with([
    'feed' => DriverIntelFeed::class,
    'map' => DriverIntelMap::class,
    'report' => DriverIntelReport::class,
    'location' => DriverIntelLocation::class,
    'style guide' => DriverIntelStyleGuide::class,
]);

it('sends the ground matching the active appearance to the window', function () {
    expect((new DriverIntelFeed)->windowBackgroundColor())->toBe('#F4F2F0');

    System::shouldReceive('isDarkMode')->andReturn(true);

    expect((new DriverIntelFeed)->windowBackgroundColor())->toBe('#101315');
});

it('supports opacity modifiers on theme classes for tonal fills', function () {
    expect(TailwindParser::parse('bg-theme-primary/15'))->toBe(['bg' => '#26FF6B00']);
});

it('feeds chrome colors through the appearance-aware theme() helper', function () {
    // Chrome builders (nav bar, tab bar) resolve through theme(), so splitting
    // the blocks is what stops the Liquid Glass tab bar clashing on a
    // light-mode device — no layout change required.
    expect(theme('primary'))->toBe('#FF6B00')
        ->and(theme('success'))->toBe('#00713C')
        ->and(theme('background'))->toBe('#F4F2F0')
        ->and(theme('missing-token', '#000000'))->toBe('#000000');
});

it('resolves chrome colors to the night palette in dark mode', function () {
    System::shouldReceive('isDarkMode')->andReturn(true);

    expect(theme('background'))->toBe('#101315')
        ->and(theme('success'))->toBe('#00E475')
        ->and(theme('primary'))->toBe('#FF6B00');
});

it('exposes the success variant on button and badge elements', function () {
    $button = Button::make()
        ->variant('success')
        ->toArray(new CallbackRegistry);

    $badge = Badge::make()
        ->variant('success')
        ->toArray(new CallbackRegistry);

    expect($button['props']['variant'])->toBe('success')
        ->and($badge['props']['variant'])->toBe('success');
});

it('renders the style guide with every variant on both platforms', function (?string $platform) {
    $screen = $platform
        ? Native::test(DriverIntelStyleGuide::class, platform: $platform)
        : Native::test(DriverIntelStyleGuide::class);

    $screen->assertSee('Theme Tokens')
        ->assertSee('Signal Green (custom)')
        ->assertSee('Category Identity')
        ->assertSee('Traffic/Parking')
        ->assertSee('Map Canvas')
        ->assertSee('Theme Opacity Ramp')
        ->assertSee('variant="success"')
        ->assertSee('Badge Variants')
        ->assertSee('Font Aliases')
        ->assertElement('button', fn (array $node): bool => ($node['props']['variant'] ?? null) === 'success')
        ->assertElement('badge', fn (array $node): bool => ($node['props']['variant'] ?? null) === 'success')
        ->assertAccessible();
})->with(['ios' => 'ios', 'android' => 'android', 'default' => [null]]);

it('keeps every style guide button variant interactive', function () {
    $screen = Native::test(DriverIntelStyleGuide::class);

    foreach (['primary', 'secondary', 'success', 'destructive', 'ghost'] as $i => $variant) {
        $screen->tap('Demo button: '.$variant)
            ->assertSet('presses', $i + 1);
    }
});

it('registers the design-system font aliases', function () {
    expect(config('native-ui.fonts'))
        ->toHaveKey('headline', 'ArchivoNarrow-Bold')
        ->toHaveKey('mono', 'JetBrainsMono-Regular')
        ->toHaveKey('default', 'AtkinsonHyperlegible-Regular');
});
