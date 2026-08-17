@use('App\Icons\Ios')
@use('App\Icons\Android')
@use('App\Icons\AndroidOutlined')

{{-- Spacing scale: gutter px-5 · card padding p-5 · card gap gap-3
     section gap gap-6 · control height h-11 · scroll tail pb-24 to clear
     the pinned action row (the layout already insets the home indicator). --}}
<column class="w-full h-full bg-theme-background">
    <scroll-view class="w-full flex-1">
        <column class="w-full px-5 pt-5 pb-24 gap-6">
            {{-- Name + open badge --}}
            <column class="w-full gap-4">
                <row class="w-full items-start gap-3">
                    <column class="flex-1 gap-1">
                        <text font="headline" class="text-[30] leading-tight text-theme-on-background">{{ $this->location->name }}</text>
                        <text font="body" class="text-[15] text-theme-secondary">{{ $this->location->address }}</text>
                    </column>
                    @if ($this->location->is_open)
                        <row class="shrink-0 items-center gap-2 h-9 px-3 rounded-full bg-theme-success">
                            <icon :size="15" class="text-theme-on-success" :ios="Ios::CheckmarkCircle" :android="Android::CheckCircle"/>
                            <text font="mono-bold" class="shrink-0 text-[13] text-theme-on-success">OPEN</text>
                        </row>
                    @else
                        <row class="shrink-0 items-center h-9 px-3 rounded-full bg-theme-destructive">
                            <text font="mono-bold" class="shrink-0 text-[13] text-theme-on-destructive">CLOSED</text>
                        </row>
                    @endif
                </row>

                {{-- Tag + distance chips. shrink-0 keeps each chip at its
                     intrinsic width so labels wrap by line, not mid-word. --}}
                <row class="w-full gap-2 flex-wrap">
                    @foreach ($this->location->tags as $tag)
                        <row class="shrink-0 items-center h-9 px-3 rounded-full bg-theme-surface-variant">
                            <text font="mono-bold" class="shrink-0 text-[13] text-theme-on-surface-variant">{{ $tag }}</text>
                        </row>
                    @endforeach
                    @if ($this->location->distance_km !== null)
                        <row class="shrink-0 items-center gap-2 h-9 px-3 rounded-full bg-theme-primary/15">
                            <icon :size="14" class="text-theme-accent" :ios="Ios::LocationFill" :android="Android::MyLocation"/>
                            <text font="mono-bold" class="shrink-0 text-[13] text-theme-accent">{{ rtrim(rtrim(number_format($this->location->distance_km, 1), '0'), '.') }}km away</text>
                        </row>
                    @endif
                </row>
            </column>

            {{-- Driver rating --}}
            @if ($this->location->rating !== null)
                <column class="w-full items-center gap-3 rounded-2xl border border-theme-outline-variant bg-theme-surface p-5">
                    <text font="mono-bold" class="text-[12] tracking-wide text-theme-secondary">DRIVER RATING</text>
                    <row class="items-center gap-1">
                        @for ($star = 1; $star <= 5; $star++)
                            @php $starState = $this->starState($star); @endphp
                            <icon :size="28"
                                  class="{{ $starState === 'empty' ? 'text-theme-outline-variant' : 'text-theme-primary' }}"
                                  :ios="match ($starState) {
                                      'full' => Ios::StarFill,
                                      'half' => Ios::StarLeadinghalfFilled,
                                      default => Ios::Star,
                                  }"
                                  :android="match ($starState) {
                                      'full' => Android::Star,
                                      'half' => Android::StarHalf,
                                      default => AndroidOutlined::Star,
                                  }"/>
                        @endfor
                    </row>
                    <text font="headline" class="text-[22] text-theme-on-surface">{{ number_format($this->location->rating, 1) }} / 5.0</text>
                </column>
            @endif

            {{-- Bathroom code — the one number a driver opens this screen for,
                 so it gets the largest type on the page. --}}
            @if ($this->location->bathroom_code !== null)
                <column class="w-full rounded-2xl border border-theme-outline-variant bg-theme-surface">
                    <row class="w-full items-center pl-6 pr-5 py-5 gap-4">
                        <column class="flex-1 gap-1">
                            <text font="mono-bold" class="text-[12] tracking-wide text-theme-secondary">BATHROOM CODE</text>
                            <text font="mono-bold" class="text-[38] leading-tight text-theme-on-surface">{{ $this->location->bathroom_code }}</text>
                            <row class="items-center gap-2">
                                <icon :size="14" class="text-theme-success" :ios="Ios::ClockFill" :android="Android::Schedule"/>
                                <text font="mono" class="text-[13] text-theme-success">Verified {{ $this->location->codeVerifiedAgo() }}</text>
                            </row>
                        </column>
                        <icon :size="52" class="shrink-0 text-theme-surface-variant" :ios="Ios::KeyFill" :android="Android::Key"/>
                    </row>
                    <column class="absolute inset-y-4 left-0 w-1.5 rounded-r-full bg-theme-success"/>
                </column>
            @endif

            {{-- Intel from drivers --}}
            <column class="w-full gap-3">
                <row class="w-full items-center gap-2">
                    <icon :size="20" class="text-theme-secondary" :ios="Ios::BubbleLeftFill" :android="Android::ChatBubble"/>
                    <text font="headline" class="text-[22] text-theme-on-background">Intel from Drivers</text>
                </row>

                @foreach ($this->intel as $post)
                    @php $hasVoted = in_array($post->id, $voted, true); @endphp
                    <column :native:key="'intel-'.$post->id" class="w-full rounded-2xl border border-theme-outline-variant bg-theme-surface p-5 gap-3">
                        <row class="w-full items-center gap-3">
                            <text font="mono-bold" class="text-[14] text-theme-on-surface">{{ $post->driver_handle }}</text>
                            <spacer/>
                            <text font="mono" class="shrink-0 text-[12] text-theme-secondary">{{ $post->displayTime() }}</text>
                        </row>
                        <text font="body" class="text-[17] leading-relaxed text-theme-on-surface">{{ $post->note }}</text>
                        <row class="w-full">
                            <pressable ref="helpful-{{ $post->id }}" @press="markHelpful({{ $post->id }})"
                                       class="shrink-0"
                                       a11y-label="Mark helpful: note by {{ $post->driver_handle }}">
                                <row class="shrink-0 items-center gap-2 h-11 px-4 rounded-full {{ $hasVoted ? 'bg-theme-primary/15' : 'bg-theme-surface-variant' }}">
                                    <icon :size="15" class="{{ $hasVoted ? 'text-theme-accent' : 'text-theme-secondary' }}"
                                          :ios="$hasVoted ? Ios::HandThumbsupFill : Ios::HandThumbsup"
                                          :android="$hasVoted ? Android::ThumbUp : Android::ThumbUpOffAlt"/>
                                    <text font="mono-bold" class="shrink-0 text-[13] {{ $hasVoted ? 'text-theme-accent' : 'text-theme-secondary' }}">Helpful {{ $post->helpful_count }}</text>
                                </row>
                            </pressable>
                            <spacer/>
                        </row>
                    </column>
                @endforeach
            </column>
        </column>
    </scroll-view>

    {{-- Pinned actions. Sits on `surface` so it reads as chrome over the
         content ground rather than a seam in the middle of it. --}}
    <row class="w-full items-center gap-3 px-5 py-4 bg-theme-background border-t border-theme-outline-variant">
        <pressable ref="photo" a11y-label="Add photo" class="shrink-0" @navigate.slideFromBottom('/intel/report')>
            <row class="shrink-0 items-center gap-2 h-14 px-5 rounded-2xl bg-theme-surface-variant">
                <icon :size="19" class="text-theme-on-surface" :ios="Ios::CameraFill" :android="Android::Camera"/>
                <text font="headline" class="shrink-0 text-[17] text-theme-on-surface">PHOTO</text>
            </row>
        </pressable>
        <pressable ref="update-status" @navigate.slideFromBottom('/intel/report') a11y-label="Update status" class="flex-1">
            <row class="w-full h-14 rounded-2xl bg-theme-primary items-center justify-center gap-2">
                <icon :size="19" class="text-theme-on-primary" :ios="Ios::MegaphoneFill" :android="Android::Campaign"/>
                <text font="headline" class="text-[18] text-theme-on-primary tracking-wide">UPDATE STATUS</text>
            </row>
        </pressable>
    </row>
</column>
