<?php

namespace App\NativeComponents\Concerns;

use Native\Mobile\Attributes\On;
use Native\Mobile\Events\System\AppearanceChanged;

/**
 * Pushes the theme's `background` token into the native window background.
 *
 * The OS owns regions our element tree never reaches — the ~84pt strip under
 * the floating tab bar, safe-area insets, overscroll, and the gap during
 * screen transitions — and paints them `systemBackground` (measured #FFFFFF
 * in light and #000000 in dark on an iPhone 17). Against the High-Vis ground
 * that reads as a hard band at the bottom of every tab screen.
 *
 * `UI.SetBackground` is the one hook that reaches them. Its own docblock
 * describes this exact symptom: "without this, a dark app shows a white band
 * in the bottom safe-area inset on every stack screen." Three surfaces read
 * it — the base layer each screen renders over, the NavigationStack container,
 * and the UIKit windows.
 *
 * The override is app-global sticky state, so every screen re-asserts it on
 * mount and on resume rather than assuming another screen set it, and
 * re-applies it when the user flips appearance mid-session.
 */
trait PaintsWindowBackground
{
    public function mount(): void
    {
        $this->paintWindowBackground();
    }

    public function onResume(): void
    {
        $this->paintWindowBackground();
    }

    /** Re-assert the ground when the system flips light/dark at runtime. */
    #[On(AppearanceChanged::class)]
    public function repaintWindowBackground(): void
    {
        $this->paintWindowBackground();
    }

    /** The ground for the current appearance — what the window is painted. */
    public function windowBackgroundColor(): string
    {
        return theme('background', '#000000');
    }

    /**
     * Hand the ground to the native window. No-op off-device, where the
     * bridge helper is absent.
     */
    protected function paintWindowBackground(): void
    {
        if (! function_exists('nativephp_call')) {
            return;
        }

        nativephp_call('UI.SetBackground', json_encode([
            'color' => $this->windowBackgroundColor(),
        ]));
    }
}
