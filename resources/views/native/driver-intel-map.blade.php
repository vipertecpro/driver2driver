@use('App\Icons\Ios')
@use('App\Icons\Android')
@use('App\IntelCategory')

{{-- Spacing scale: gutter px-5 · section gap gap-4 · control height h-11
     panel tail pb-24 to clear the floating tab bar. --}}
<column class="w-full h-full bg-theme-background">
    {{-- Stylized map canvas — pins are positioned from seeded coords --}}
    <stack class="w-full flex-1 bg-theme-map-surface overflow-hidden">
        {{-- Faux street grid --}}
        <column class="absolute top-[90] left-0 w-full h-2 bg-theme-map-road"/>
        <column class="absolute top-[300] left-0 w-full h-2 bg-theme-map-road"/>
        <column class="absolute top-[430] left-0 w-full h-1 bg-theme-map-road"/>
        <column class="absolute top-0 left-[80] w-2 h-full bg-theme-map-road"/>
        <column class="absolute top-0 right-[100] w-1 h-full bg-theme-map-road"/>

        @foreach ($this->locations as $pinLocation)
            <pressable :native:key="'pin-'.$pinLocation->id" @navigate('/intel/location/'.$pinLocation->id)
                       a11y-label="{{ $pinLocation->name }}, {{ $pinLocation->pin_category->label() }}"
                       class="absolute top-[{{ $pinLocation->map_y }}] left-[{{ $pinLocation->map_x }}]">
                <column class="items-center">
                    {{-- The pin's ring uses `surface`, matching the chrome, so
                         the marker reads as lifted off the canvas in both
                         appearances instead of punching a hole in it. --}}
                    <column class="w-12 h-12 rounded-full border-[3px] border-theme-surface items-center justify-center {{ $pinLocation->pin_category->pinClass() }}">
                        <icon :size="22" class="{{ $pinLocation->pin_category->pinIconClass() }}"
                              :ios="$pinLocation->pin_category->iosIcon()" :android="$pinLocation->pin_category->androidIcon()"/>
                    </column>
                    <column class="w-1 h-3 {{ $pinLocation->pin_category->pinClass() }}"/>
                    <column class="w-3 h-1 rounded-full bg-theme-on-surface/25"/>
                </column>
            </pressable>
        @endforeach
    </stack>

    {{-- Bottom panel --}}
    <column class="w-full bg-theme-background border-t border-theme-outline-variant px-5 pt-4 pb-24 gap-4">
        <row class="w-full items-center gap-3">
            <text font="headline" class="text-[22] text-theme-on-surface">Nearby Hacks</text>
            <spacer/>
            <pressable ref="add-intel" @navigate.slideFromBottom('/intel/report') class="shrink-0" a11y-label="Post new intel">
                <row class="shrink-0 items-center gap-2 h-11 px-4 rounded-full bg-theme-primary">
                    <icon :size="18" class="text-theme-on-primary" :ios="Ios::Plus" :android="Android::Add"/>
                    <text font="headline" class="shrink-0 text-[15] text-theme-on-primary tracking-wide">POST</text>
                </row>
            </pressable>
        </row>

        {{-- Three labelled chips do not fit the viewport width, so they
             scroll horizontally like the feed's. Squeezing them into a
             fixed row dropped every label and left icon-only chips. --}}
        <scroll-view axis="horizontal" class="w-full flex-none">
            <row class="gap-2 items-center">
                @foreach ($this->filters() as $key => $label)
                    @php $chipCategory = IntelCategory::from($key); @endphp
                    <pressable ref="map-filter-{{ $key }}" @press="setFilter('{{ $key }}')" class="shrink-0" a11y-label="Filter pins: {{ $label }}">
                        <row class="shrink-0 items-center gap-2 h-11 px-4 rounded-full border {{ $filter === $key ? 'bg-theme-primary/15 border-theme-primary' : 'border-theme-outline-variant bg-theme-surface' }}">
                            <icon :size="17" class="{{ $filter === $key ? 'text-theme-accent' : $chipCategory->accentClass() }}"
                                  :ios="$chipCategory->iosIcon()" :android="$chipCategory->androidIcon()"/>
                            <text font="mono-bold" class="shrink-0 text-[14] {{ $filter === $key ? 'text-theme-accent' : 'text-theme-on-surface' }}">{{ $label }}</text>
                        </row>
                    </pressable>
                @endforeach
            </row>
        </scroll-view>
    </column>
</column>
