<?php

use App\Models\IntelLocation;
use App\NativeComponents\DriverIntelFeed;
use App\NativeComponents\DriverIntelMap;
use App\NativeComponents\DriverIntelReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;
use Native\Mobile\Testing\TestableComponent;

uses(RefreshDatabase::class);

/**
 * Guards the layout regressions found on device: the collapsed category
 * stripe, labels squeezed out of flexible rows, and content clipped under
 * pinned action bars. These are all layout-node contracts, so they assert
 * against the serialized tree rather than visible text.
 */

/**
 * Flatten a rendered screen into a list of nodes.
 *
 * @return list<array<string, mixed>>
 */
function nodes(TestableComponent $screen): array
{
    $flat = [];

    $walk = function (array $node) use (&$walk, &$flat): void {
        $flat[] = $node;

        foreach ($node['children'] ?? [] as $child) {
            $walk($child);
        }
    };

    $walk($screen->tree());

    return $flat;
}

/**
 * Whether a serialized inset edge was explicitly authored. The wire packs
 * "unset" as +0.0 and an authored zero as -0.0, so the sign bit is the only
 * thing separating `inset-y-0` from an absent edge.
 */
function isAnchored(float $edge): bool
{
    return $edge != 0.0 || fdiv(1.0, $edge) < 0;
}

/**
 * The category stripes on a rendered screen: absolutely positioned, filled,
 * and anchored on both vertical edges.
 *
 * @return list<array<string, mixed>>
 */
function stripes(TestableComponent $screen): array
{
    return array_values(array_filter(
        nodes($screen),
        fn (array $node): bool => ($node['layout']['position_type'] ?? null) === 1
            && isset($node['style']['bg_color'])
            && isset($node['layout']['position'])
            && isAnchored($node['layout']['position'][0])
            && isAnchored($node['layout']['position'][2])
    ));
}

it('draws the feed category stripe as a full-height anchored bar', function () {
    $stripes = stripes(Native::test(DriverIntelFeed::class));

    expect($stripes)->not->toBeEmpty();

    foreach ($stripes as $stripe) {
        [, $right, , $left] = $stripe['layout']['position'];

        // Anchoring opposing vertical edges is what stretches an absolute
        // child to its parent; `w-2 self-stretch` collapsed to an 8x8pt
        // square on device. Left-anchored only, so it hugs the card edge.
        expect(isAnchored($left))->toBeTrue()
            ->and(isAnchored($right))->toBeFalse()
            ->and($stripe['layout']['width'])->toBe(6.0)
            ->and($stripe['style']['bg_color'])->toStartWith('#');
    }
});

it('insets the stripe clear of the card corner radius', function () {
    $stripe = stripes(Native::test(DriverIntelFeed::class))[0];

    [$top, , $bottom] = $stripe['layout']['position'];

    // Inset vertically and rounded on its free edge only — a stripe flush to
    // the corners fought the card's 16pt radius and poked outside it.
    expect($top)->toBe(16.0)
        ->and($bottom)->toBe(16.0)
        ->and($stripe['props'])->toMatchArray([
            'radius_tl' => 0.0,
            'radius_tr' => 9999.0,
            'radius_br' => 9999.0,
            'radius_bl' => 0.0,
        ]);
});

it('sizes cards to their own content', function () {
    // A `stack` wrapper gave every card the same height, so three-line notes
    // overflowed top and bottom. The card container must be a column, which
    // sizes to its in-flow children while the stripe stays out of flow.
    $cards = collect(nodes(Native::test(DriverIntelFeed::class)))
        ->filter(fn (array $node): bool => ($node['style']['border_radius'] ?? null) === 16.0
            && isset($node['style']['bg_color']));

    expect($cards)->not->toBeEmpty();

    $cards->each(function (array $card): void {
        expect($card['type'])->toBe('column');
    });
});

it('keeps every chip and inline button label at its intrinsic width', function (string $component) {
    $unshrinkable = collect(nodes(Native::test($component)))
        ->filter(fn (array $node): bool => ($node['layout']['flex_shrink'] ?? null) === 0.0);

    expect($unshrinkable)->not->toBeEmpty();
})->with([
    'feed' => DriverIntelFeed::class,
    'map' => DriverIntelMap::class,
]);

it('scrolls the map filters horizontally instead of squeezing them', function () {
    // Three labelled chips are wider than the viewport; a fixed row dropped
    // every label and left icon-only chips.
    $screen = Native::test(DriverIntelMap::class)
        ->assertSee('Restroom')
        ->assertSee('Access Point')
        ->assertSee('Dog Alert');

    $horizontal = collect(nodes($screen))
        ->filter(fn (array $node): bool => $node['type'] === 'scroll_view');

    expect($horizontal)->not->toBeEmpty();
});

it('shows the Details heading on the report screen', function () {
    Native::test(DriverIntelReport::class)
        ->assertSee('Select Category')
        ->assertSee('Details');
});

it('gives every screen a scroll tail that clears its bottom chrome', function (string $uri) {
    // On the tab screens that chrome is the floating Liquid Glass bar, which
    // overlays content rather than insetting it. On the pushed screens it is
    // our own pinned action row. Both land on pb-24 (96): padding is
    // [top, right, bottom, left].
    $padded = collect(nodes(Native::visit($uri)))
        ->contains(fn (array $node): bool => ($node['layout']['padding'][2] ?? null) === 96.0);

    expect($padded)->toBeTrue();
})->with([
    'feed' => '/',
    'map' => '/intel/map',
    'report' => '/intel/report',
    'location' => '/intel/location/1',
]);

it('never insets the safe area by hand on a chrome-wrapped screen', function (string $uri) {
    // Per the safe-area docs: "Don't apply safe-area-bottom to a child of a
    // chrome-wrapped screen — the chrome already handles it, and stacking the
    // insets will push content up even further." Every screen here sits under
    // a NativeLayout, so none of them may carry a safe-area inset.
    // All three variants encode into one key: safe_area = 1 (both), 2 (top),
    // 3 (bottom).
    $manual = collect(nodes(Native::visit($uri)))
        ->filter(fn (array $node): bool => isset($node['layout']['safe_area']));

    expect($manual)->toBeEmpty();
})->with([
    'feed' => '/',
    'map' => '/intel/map',
    'report' => '/intel/report',
    'location' => '/intel/location/1',
    'style guide' => '/intel/style-guide',
]);

it('draws a half star for a fractional rating', function (string $platform, array $expected) {
    // Icon names only resolve once a platform is known.
    $screen = Native::visit('/intel/location/1', platform: $platform)->assertSee('4.5 / 5.0');

    $stars = collect(nodes($screen))
        ->filter(fn (array $node): bool => $node['type'] === 'icon')
        ->map(fn (array $node): string => $node['props']['name'] ?? '')
        ->filter(fn (string $name): bool => str_contains($name, 'star'))
        ->values()
        ->all();

    expect($stars)->toBe($expected);
})->with([
    'ios' => ['ios', ['star.fill', 'star.fill', 'star.fill', 'star.fill', 'star.leadinghalf.filled']],
    'android' => ['android', ['star', 'star', 'star', 'star', 'star_half']],
]);

it('reports star fill state per position', function (?float $rating, array $expected) {
    $location = IntelLocation::factory()->create(['rating' => $rating]);

    $screen = Native::visit('/intel/location/'.$location->id);

    $states = array_map(
        fn (int $position): string => $screen->instance()->starState($position),
        [1, 2, 3, 4, 5]
    );

    expect($states)->toBe($expected);
})->with([
    'whole' => [4.0, ['full', 'full', 'full', 'full', 'empty']],
    'half' => [4.5, ['full', 'full', 'full', 'full', 'half']],
    'low half' => [0.5, ['half', 'empty', 'empty', 'empty', 'empty']],
    'perfect' => [5.0, ['full', 'full', 'full', 'full', 'full']],
    'unrated' => [null, ['empty', 'empty', 'empty', 'empty', 'empty']],
]);
