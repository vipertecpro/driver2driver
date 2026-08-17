---
paths:
  - 'resources/views/native/**'
---

# Native

## Driver-to-Driver native screen spacing scale
One 4pt scale across every native screen — do not introduce new values ad hoc:
- Screen gutter: px-5 (20). Card padding: p-5 (20), pl-6 when the card carries a category stripe.
- Card-to-card gap: gap-3 (12). Intra-card gap: gap-3. Section gap: gap-6 (24).
- Control height: h-11 (44) for chips/pills, h-14 (56) for primary actions. Never rely on py-* alone for touch targets.
- Radii: rounded-2xl (16) for cards and primary buttons, rounded-full for chips and pills.
- Scroll tails: pb-24 (96) on tab screens to clear the floating tab bar (it is NOT inset for automatically), pb-28 (112) on screens with a pinned action row.

## Card containers must be columns, never stacks
A `<stack>` used as a card wrapper does NOT size to its content — every card ends up the same height and longer text overflows the top and bottom. Use `<column>` as the card container: it sizes to its in-flow children while an absolutely positioned child (the category stripe) stays out of flow.

For the stripe itself, `self-stretch` does not work — it collapses to a square. Anchor an absolute child on both vertical edges instead: `absolute inset-y-4 left-0 w-1.5 rounded-r-full`. Inset it (inset-y-4, not inset-y-0) so it never fights the card's 16pt corner radius.

Also note `flex-none` parses to a no-op in this toolkit — use `shrink-0` to stop a row squeezing a chip or button label to nothing.

## Never hand-inset the safe area under a NativeLayout
Per https://nativephp.com/docs/mobile/4/the-basics/safe-area: "Don't apply safe-area-bottom to a child of a chrome-wrapped screen — the chrome already handles it, and stacking the insets will push content up even further."

Every Driver-to-Driver screen sits under IntelTabsLayout or IntelStackLayout, so none may carry safe-area/safe-area-top/safe-area-bottom. Reserve those for genuinely chrome-less screens. IntelLayoutTest guards this across all five routes.

Consequence for scroll tails: the layout already insets the home indicator, so a pinned action row needs padding only for itself (py-4), not for the safe area on top. pb-24 (96) is the tail on every screen — it clears the floating tab bar on the tab screens and our own pinned row on the pushed ones.

## Bottom-edge containers must use the background token
Anything that touches the bottom of the screen — a pinned action row, the map's bottom panel — must be `bg-theme-background`, not `bg-theme-surface`.

The OS-owned safe-area strip below it is painted by `UI.SetBackground`, which pushes `theme('background')`. A `surface` bar therefore ends in a visible two-tone step where the bar meets the strip, which reads as a stray second hairline under the button.

Same reasoning applies to any future bottom sheet or docked bar that reaches the screen edge.
