<?php

namespace App\NativeComponents\Layouts;

use App\Icons\Android;
use App\Icons\Ios;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

/**
 * Root chrome for the Driver-to-Driver tab screens (feed + map): native
 * NavigationStack/TabView with the High-Vis wordmark up top and the
 * three-tab bar below. Bar colors read from the published theme tokens so
 * the chrome re-skins with the config — the builders take raw color
 * strings, so we resolve tokens here instead of hardcoding hex.
 *
 * Every tab does something. Inbox and Profile were `Tab::action()` with no
 * press handler — they rendered but taps did nothing, so they are gone.
 */
class IntelTabsLayout extends NativeLayout
{
    protected ?string $font = 'headline';

    public function usesNativeChrome(): bool
    {
        return true;
    }

    public function navBar(NativeComponent $screen): ?NavBar
    {
        return NavBar::make()
            ->title('DRIVER-TO-DRIVER')
            // The nav bar honors an explicit color on both platforms, so it
            // takes `background` and reads as a seamless continuation of the
            // content ground rather than a separate band.
            ->backgroundColor(theme('background'))
            ->textColor(theme('accent'))
            ->action(
                NavAction::make('style-guide')
                    ->icon(ios: Ios::Paintpalette, android: Android::Palette)
                    ->a11yLabel('Open style guide')
                    ->url('/intel/style-guide')
            )
            ->back(false);
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        // No backgroundColor on purpose. Setting one makes the renderer apply
        // `.toolbarBackground(.visible, .tabBar)`, which turns the whole strip
        // opaque — and the iOS 26 floating bar does not honor our color there,
        // so the strip fell back to the system window background (pure black
        // in dark, pure white in light). Omitting it keeps the adaptive Liquid
        // Glass material, so the content ground shows through around the
        // capsule. Screens reserve `pb-24` so nothing hides under it.
        return TabBar::make()
            ->activeColor(theme('primary'))
            ->textColor(theme('secondary'))
            ->font('mono')
            ->add(Tab::link('Home', '/', ios: Ios::HouseFill, android: Android::Home))
            ->add(Tab::link('Map', '/intel/map', ios: Ios::MapFill, android: Android::Map))
            // Per-platform on purpose. `plus.circle.fill` renders as a solid
            // white disc on iOS 26 — far heavier than house.fill / map.fill
            // beside it — because Liquid Glass tab bars reject the appearance
            // overrides that would tint it. A bare `plus` matches their weight.
            // Android's `add_circle` draws as a light outline and is fine.
            ->add(Tab::action('Report', ios: Ios::Plus, android: Android::AddCircle)->press('openReport'));
    }
}
