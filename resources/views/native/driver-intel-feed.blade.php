@use('App\Icons\Ios')
@use('App\Icons\Android')
@use('App\IntelCategory')

{{-- Spacing scale for every Driver-to-Driver screen (4pt grid):
     gutter px-5 (20) · card padding p-5 (20) · card gap gap-3 (12)
     section gap gap-6 (24) · control height h-11 (44, gloved-hand minimum)
     scroll tail pb-24 (96) to clear the floating tab bar. --}}
<column class="w-full h-full bg-theme-background">
    {{-- Category filter pills --}}
    <scroll-view axis="horizontal" class="w-full flex-none">
        <row class="px-5 py-3 gap-2 items-center">
            @foreach ($this->filters() as $key => $label)
                @php $chipCategory = $key === 'all' ? null : IntelCategory::from($key); @endphp
                <pressable ref="filter-{{ $key }}" @press="setFilter('{{ $key }}')" class="shrink-0" a11y-label="Filter alerts: {{ $label }}">
                    <row class="shrink-0 items-center gap-2 h-11 px-4 rounded-full border {{ $filter === $key ? 'border-theme-primary bg-theme-primary/15' : 'border-theme-outline-variant bg-theme-surface' }}">
                        <icon :size="17"
                              class="{{ $filter === $key ? 'text-theme-accent' : ($chipCategory?->accentClass() ?? 'text-theme-secondary') }}"
                              :ios="$chipCategory?->iosIcon() ?? Ios::ExclamationmarkTriangleFill"
                              :android="$chipCategory?->androidIcon() ?? Android::Warning"/>
                        <text font="mono-bold" class="shrink-0 text-[14] {{ $filter === $key ? 'text-theme-accent' : 'text-theme-on-surface' }}">{{ $label }}</text>
                    </row>
                </pressable>
            @endforeach
        </row>
    </scroll-view>

    {{-- Intel feed --}}
    <scroll-view class="w-full flex-1">
        <column class="w-full px-5 pt-2 pb-24 gap-3">
            @if ($this->posts->isEmpty())
                <column class="w-full items-center gap-3 pt-16">
                    <icon :size="40" class="text-theme-outline-variant" :ios="Ios::TrayFill" :android="Android::Inbox"/>
                    <text font="body" class="text-[15] text-center text-theme-secondary">
                        No alerts in this category yet.
                    </text>
                </column>
            @endif

            @foreach ($this->posts as $post)
                @if ($post->intel_location_id !== null)
                    <pressable :native:key="'post-'.$post->id" @navigate('/intel/location/'.$post->intel_location_id)
                               a11y-label="Open location details for this alert" class="w-full">
                        @include('native.partials.intel-feed-card-body', ['post' => $post, 'voted' => $voted])
                    </pressable>
                @else
                    <column :native:key="'post-'.$post->id" class="w-full">
                        @include('native.partials.intel-feed-card-body', ['post' => $post, 'voted' => $voted])
                    </column>
                @endif
            @endforeach

            @if ($this->hasMore())
                <pressable ref="load-more" @press="loadMore" a11y-label="Load more alerts" class="w-full pt-2">
                    <row class="w-full h-14 rounded-2xl border border-theme-outline items-center justify-center gap-2">
                        <icon :size="18" class="text-theme-accent" :ios="Ios::ArrowDownCircle" :android="Android::ExpandMore"/>
                        <text font="headline" class="text-[17] text-theme-accent tracking-wide">LOAD MORE ALERTS</text>
                    </row>
                </pressable>
            @endif
        </column>
    </scroll-view>
</column>
