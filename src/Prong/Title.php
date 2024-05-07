<?php

namespace App\Prong;

use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Animatable;

class Title implements Loopable
{
    use Ticks;

    public Animatable $value;

    public function __construct()
    {
        $this->value = Animatable::fromValue(8)->lowerLimit(0);
    }

    public function onTick(): void
    {
        $this->value->animate();
    }

    public function hide(): void
    {
        $this->value->to(0);
    }
}
