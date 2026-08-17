@use('App\Icons\Ios')
@use('App\Icons\Android')

{{-- Spacing scale: gutter px-5 · card padding p-5 · section gap gap-6
     control height h-11 · scroll tail pb-24 to clear the pinned action
     (the layout already insets the home indicator). --}}
<column class="w-full h-full bg-theme-background">
    <scroll-view class="w-full flex-1">
        <column class="w-full px-5 pt-5 pb-24 gap-6">
            <column class="w-full gap-3">
                <text font="headline" class="text-[24] text-theme-on-background">Select Category</text>

                {{-- Oversized 2×2 grid — one tap, gloved-hand sized. Selection
                     reads as a tonal fill plus a ring, not a colour swap, so
                     the icon stays legible in both appearances. --}}
                <column class="w-full gap-3">
                    @foreach (array_chunk($this->gridCategories(), 2) as $pair)
                        <row class="w-full gap-3">
                            @foreach ($pair as $gridCategory)
                                @php $isSelected = $category === $gridCategory->value; @endphp
                                <pressable ref="category-{{ $gridCategory->value }}" @press="selectCategory('{{ $gridCategory->value }}')"
                                           a11y-label="Category: {{ $gridCategory->reportLabel() }}" class="flex-1">
                                    <column class="w-full h-36 items-center justify-center gap-3 rounded-2xl border {{ $isSelected ? 'border-theme-primary bg-theme-primary/15' : 'border-theme-outline-variant bg-theme-surface' }}">
                                        <icon :size="38"
                                              class="{{ $isSelected ? 'text-theme-accent' : $gridCategory->accentClass() }}"
                                              :ios="$gridCategory->iosIcon()" :android="$gridCategory->androidIcon()"/>
                                        <text font="mono-bold" class="text-[14] {{ $isSelected ? 'text-theme-accent' : 'text-theme-on-surface' }}">
                                            {{ $gridCategory->reportLabel() }}
                                        </text>
                                    </column>
                                </pressable>
                            @endforeach
                        </row>
                    @endforeach
                </column>
            </column>

            {{-- Heading and field are one labelled group: as siblings of the
                 grid the heading collapsed to zero height on device even
                 though it reached the render tree correctly. The stack still
                 sizes to the input so it keeps growing from 5 to 8 lines. --}}
            <column class="w-full gap-3">
                <text font="headline" class="text-[24] text-theme-on-background">Details</text>

                <stack class="w-full">
                    <bare-text-input native:model.blur="note" multiline min-lines="5" max-lines="8"
                                     placeholder="Add short note…" font="body"
                                     a11y-label="Intel details"
                                     class="w-full p-5 rounded-2xl border border-theme-outline-variant bg-theme-surface text-theme-on-surface text-[17] leading-relaxed"/>
                    {{-- Voice-note affordance (visual only in this demo) --}}
                    <column class="absolute bottom-[12] right-[12] w-11 h-11 rounded-full bg-theme-surface-variant items-center justify-center">
                        <icon :size="18" class="text-theme-secondary" :ios="Ios::MicFill" :android="Android::Mic"/>
                    </column>
                </stack>
            </column>
        </column>
    </scroll-view>

    {{-- Pinned primary action. Disabled is a surface-variant fill rather than
         a half-opacity orange — 50% orange over the ground turned to mud and
         swallowed the label. --}}
    @php $canPost = $category !== null && trim($note) !== ''; @endphp
    <column class="w-full px-5 py-4 bg-theme-background border-t border-theme-outline-variant">
        <pressable ref="post-intel" @press="postIntel" a11y-label="Post intel"
                   a11y-hint="Shares your report with nearby drivers" class="w-full">
            <row class="w-full h-14 rounded-2xl items-center justify-center gap-3 {{ $canPost ? 'bg-theme-primary' : 'bg-theme-surface-variant' }}">
                <icon :size="20" class="{{ $canPost ? 'text-theme-on-primary' : 'text-theme-secondary' }}" :ios="Ios::PaperplaneFill" :android="Android::Send"/>
                <text font="headline" class="text-[20] tracking-wide {{ $canPost ? 'text-theme-on-primary' : 'text-theme-secondary' }}">POST INTEL</text>
            </row>
        </pressable>
    </column>
</column>
