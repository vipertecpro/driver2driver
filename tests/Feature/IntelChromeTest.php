<?php

use App\NativeComponents\DriverIntelFeed;
use App\NativeComponents\Layouts\IntelTabsLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

/**
 * Guards the native chrome contract: every tab is wired to something, and
 * the tab bar stays on the system's adaptive material.
 */
it('ships only tabs that do something', function () {
    // Mount by route so the nativeGroup layout is attached.
    $screen = Native::visit('/')
        ->assertHasTabBar()
        ->assertHasTab('Home')
        ->assertHasTab('Map')
        ->assertHasTab('Report');

    // Inbox and Profile were Tab::action() with no press handler: they
    // rendered but taps did nothing.
    expect(fn () => $screen->assertHasTab('Inbox'))->toThrow(Exception::class)
        ->and(fn () => $screen->assertHasTab('Profile'))->toThrow(Exception::class);
});

it('leaves the tab bar background to the system material', function () {
    $bar = (new IntelTabsLayout)->tabBar(new DriverIntelFeed)->toElement();

    // An explicit color makes the renderer apply
    // `.toolbarBackground(.visible, .tabBar)`, which turns the whole strip
    // opaque; the iOS 26 floating bar then falls back to the system window
    // background (pure black in dark, pure white in light) around the
    // capsule. Omitting it keeps Liquid Glass and shows the content ground.
    expect($bar->getRawProps())->not->toHaveKey('background_color');
});

it('keeps the nav bar on the content ground so the top has no seam', function () {
    $bar = (new IntelTabsLayout)->navBar(new DriverIntelFeed)->toElement();

    expect($bar->getRawProps()['background_color'] ?? null)->toBe(theme('background'));
});
