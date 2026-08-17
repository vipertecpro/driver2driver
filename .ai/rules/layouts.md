---
paths:
  - 'app/NativeComponents/Layouts/**'
---

# Layouts

## Never set a tab bar background color on iOS 26
`TabBar::backgroundColor()` makes NativeRootTabsRenderer apply `.toolbarBackground(.visible, .tabBar)`, which turns the whole bottom strip opaque. The iOS 26 floating tab bar does NOT honor our color for that strip, so it falls back to the system window background — pure black in dark mode, pure white in light — producing a band that clashes with the app ground.

Omit `backgroundColor()` entirely so the bar keeps its adaptive Liquid Glass material and the content ground shows through around the capsule. Screens must reserve `pb-24` (96) at the end of scrollable content, since the floating bar is not inset for automatically.

`NavBar::backgroundColor()` is fine and IS honored — keep it on `theme('background')` so the top reads as a continuation of the content.
