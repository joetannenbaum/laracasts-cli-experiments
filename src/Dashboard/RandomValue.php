<?php

namespace App\Dashboard;

use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Animatable;

class RandomValue implements Loopable
{
    use Ticks;

    public Animatable $value;

    public function __construct($lowerLimit, $upperLimit, $initialValue = null, ?int $pauseAfter = null)
    {
        $this->value = ($initialValue) ? Animatable::fromValue($initialValue) : Animatable::fromRandom($lowerLimit, $upperLimit);

        $this->value->lowerLimit($lowerLimit)->upperLimit($upperLimit);

        if ($pauseAfter) {
            $this->value->pauseAfter($pauseAfter);
        }
    }

    public function onTick(): void
    {
        $this->value->whenDoneAnimating(fn () => $this->value->toRandom());
    }
}
