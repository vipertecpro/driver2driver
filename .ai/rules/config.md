---
paths:
  - config/native-ui.php
---

# Config

## Push the ground into the window with UI.SetBackground
The OS owns regions our element tree never reaches — the ~84pt strip under the floating tab bar, safe-area insets, overscroll, and the gap during screen transitions — and paints them `systemBackground` (measured #FFFFFF light, #000000 dark on an iPhone 17 simulator). Against the High-Vis ground that reads as a hard band at the bottom of every tab screen.

Neither `TabBar::backgroundColor()` nor any prop on `NativeRootTabsRenderer` reaches those pixels. The one hook that does is the `UI.SetBackground` bridge call, whose own docblock names this exact symptom: "without this, a dark app shows a white band in the bottom safe-area inset on every stack screen." Three surfaces read it — the base layer each screen renders over, the NavigationStack container, and the UIKit windows.

`App\NativeComponents\Concerns\PaintsWindowBackground` does this: every screen `use`s it, and it pushes `theme('background')` on mount, on resume, and on `AppearanceChanged`. The override is app-global sticky state, which is why every screen re-asserts it rather than trusting another screen to have set it.

Consequence: `theme.*.background` stays a designed value (#F4F2F0 day / #101315 night). Do NOT retint the palette to #FFFFFF/#000000 to "match" the window — that was an earlier attempt, and it is unnecessary now that the window follows the palette instead. A new screen that does not `use PaintsWindowBackground` will show the band; ThemeSystemTest guards all five.
