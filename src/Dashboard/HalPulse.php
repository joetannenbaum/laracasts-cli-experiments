<?php

namespace App\Dashboard;

use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Frames;

class HalPulse implements Loopable
{
    use Ticks;

    public Frames $frames;

    public function __construct()
    {
        $this->frames = new Frames;
    }

    public function onTick(): void
    {
        $this->onNthTick(10, fn () => $this->frames->next());
    }
}
