<?php

namespace App\DancingBars;

use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Animatable;

class Bar implements Loopable
{
    use Ticks;

    public Animatable $value;

    public string $color;

    protected array $colors = ['red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'];

    public function __construct(protected int $maxHeight)
    {
        $this->value = Animatable::fromValue(1)
            ->upperLimit($this->maxHeight)
            ->lowerLimit(1)
            ->pauseAfter(10);

        $this->setNewColor();
    }

    public function onTick(): void
    {
        $this->value->whenDoneAnimating(function () {
            $this->setNewColor();
            $this->value->toRandom();
        });
    }

    public function setNewColor(): void
    {
        $this->color = $this->colors[array_rand($this->colors)];
    }
}
