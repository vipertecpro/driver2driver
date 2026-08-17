@use('App\Icons\Ios')
@use('App\Icons\Android')

{{-- One feed card. Three bands, no rules between them — category, note,
     attribution — with the category stripe carrying identity before the
     text is read (DESIGN.md). Expects $post and $voted.

     The container is a `column`, not a `stack`: a column sizes to its
     in-flow children while the absolutely positioned stripe stays out of
     flow. A stack gave every card the same height and clipped three-line
     notes at the top and bottom.

     The stripe is inset vertically so it never fights the card's 16pt
     corner radius, and anchored on both vertical edges — opposing-edge
     anchoring is what stretches an absolute child to its parent. --}}
<column class="w-full rounded-2xl border border-theme-outline-variant bg-theme-surface">
    <column class="w-full pl-6 pr-5 py-5 gap-3">
        <row class="w-full items-center gap-2">
            <icon :size="16" class="{{ $post->category->accentClass() }}" :ios="$post->category->iosIcon()" :android="$post->category->androidIcon()"/>
            <text font="mono-bold" class="text-[12] tracking-wide {{ $post->category->accentClass() }}">{{ strtoupper($post->category->label()) }}</text>
            <spacer/>
            <text font="mono" class="shrink-0 text-[12] text-theme-secondary">{{ $post->timeAgo() }}</text>
        </row>

        <text font="body-bold" class="text-[18] leading-snug text-theme-on-surface">{{ $post->note }}</text>

        <row class="w-full items-center gap-3">
            <text font="mono" class="text-[13] text-theme-secondary">{{ $post->driver_handle }}</text>
            <spacer/>
            @php $hasVoted = in_array($post->id, $voted, true); @endphp
            <pressable ref="helpful-{{ $post->id }}" @press="markHelpful({{ $post->id }})"
                       class="shrink-0"
                       a11y-label="Mark helpful: {{ $post->category->label() }} by {{ $post->driver_handle }}">
                <row class="shrink-0 items-center gap-2 h-11 px-4 rounded-full {{ $hasVoted ? 'bg-theme-primary/15' : 'bg-theme-surface-variant' }}">
                    <icon :size="15" class="{{ $hasVoted ? 'text-theme-accent' : 'text-theme-secondary' }}"
                          :ios="$hasVoted ? Ios::HandThumbsupFill : Ios::HandThumbsup"
                          :android="$hasVoted ? Android::ThumbUp : Android::ThumbUpOffAlt"/>
                    <text font="mono-bold" class="shrink-0 text-[13] {{ $hasVoted ? 'text-theme-accent' : 'text-theme-secondary' }}">Helpful {{ $post->helpful_count }}</text>
                </row>
            </pressable>
        </row>
    </column>

    <column class="absolute inset-y-4 left-0 w-1.5 rounded-r-full {{ $post->category->barClass() }}"/>
</column>
