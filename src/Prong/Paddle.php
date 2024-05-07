<?php

namespace App\Prong;

use App\Prong;
use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Animatable;

class Paddle implements Loopable
{
    use Ticks;

    public Animatable $value;

    public int $height = 5;

    public function __construct(protected Prong $prompt)
    {
        $halfGameHeight = floor($this->prompt->gameHeight / 2);
        $halfPaddleHeight = floor($this->height / 2);

        $this->value = Animatable::fromValue((int) ($halfGameHeight - $halfPaddleHeight))
            ->lowerLimit(0)
            ->upperLimit($this->prompt->gameHeight - $this->height);
    }

    public function onTick(): void
    {
        $this->value->animate();
    }

    public function moveUp(): void
    {
        $this->value->toRelative(-1);
    }

    public function moveDown(): void
    {
        $this->value->toRelative(1);
    }
}
