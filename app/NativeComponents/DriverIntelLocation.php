<?php

namespace App\NativeComponents;

use App\Models\IntelLocation;
use App\Models\IntelPost;
use App\NativeComponents\Concerns\PaintsWindowBackground;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\NativeComponent;

class DriverIntelLocation extends NativeComponent
{
    use PaintsWindowBackground;

    public function navTitle(): string
    {
        return 'LOCATION DETAIL';
    }

    /** @var array<int, int> Post ids this driver already marked helpful. */
    public array $voted = [];

    public function markHelpful(int $postId): void
    {
        if (in_array($postId, $this->voted, true)) {
            return;
        }

        IntelPost::whereKey($postId)->increment('helpful_count');
        $this->voted[] = $postId;
    }

    #[Computed]
    public function location(): IntelLocation
    {
        return IntelLocation::findOrFail($this->param('id'));
    }

    /**
     * @return Collection<int, IntelPost>
     */
    #[Computed]
    public function intel(): Collection
    {
        return $this->location->posts()->latest()->get();
    }

    /** Wholly filled stars in the 5-star rating row. */
    public function filledStars(): int
    {
        return (int) floor($this->location->rating ?? 0);
    }

    /**
     * Fill state for one star in the rating row, so the graphic matches the
     * number printed beneath it — a 4.5 rating draws four full stars and a
     * half, not five full ones.
     *
     * @param  int  $position  1-5, left to right
     * @return 'full'|'half'|'empty'
     */
    public function starState(int $position): string
    {
        $rating = (float) ($this->location->rating ?? 0);

        return match (true) {
            $position <= $rating => 'full',
            $position - $rating <= 0.5 => 'half',
            default => 'empty',
        };
    }

    public function render(): View
    {
        return view('native.driver-intel-location');
    }
}
