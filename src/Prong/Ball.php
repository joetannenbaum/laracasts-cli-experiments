<?php

namespace App\Prong;

use App\Prong;
use Chewie\Concerns\Ticks;
use Chewie\Contracts\Loopable;
use Chewie\Support\Animatable;

class Ball implements Loopable
{
    use Ticks;

    public int $maxY;

    public int $maxX;

    public int $y;

    public int $direction;

    public Animatable $x;

    public array $steps = [];

    protected array $directionChangeCallbacks = [];

    public int $directionChangeCount = 0;

    public function __construct(protected Prong $prompt)
    {
        $this->maxY = $this->prompt->gameHeight - 1;
        $this->maxX = $this->prompt->gameWidth - 1;

        $xStart = collect([0, $this->maxX])->random();

        $this->x = Animatable::fromValue($xStart)
            ->lowerLimit(0)
            ->upperLimit($this->maxX)
            ->toggle();

        $this->y = rand(0, $this->maxY);
    }

    public function onTick(): void
    {
        $this->x->whenDoneAnimating(function () {
            $this->start();
            $this->x->toggle();
        });

        if (count($this->steps) > 0) {
            $this->y = array_shift($this->steps);
        }
    }

    public function onDirectionChange(callable $cb)
    {
        $this->directionChangeCallbacks[] = $cb;
    }

    public function start(): void
    {
        $nextY = rand(0, $this->maxY);

        $this->steps = $this->getSteps($nextY);
        $this->direction = $this->x->current() === 0 ? 1 : -1;

        foreach ($this->directionChangeCallbacks as $cb) {
            $cb();
        }

        $this->directionChangeCount++;
    }

    protected function getSteps(int $nextY): array
    {
        $steps = range($this->y, $nextY);

        $i = 0;

        while (count($steps) < $this->maxX) {
            $steps[] = $steps[$i];
            $i++;
        }

        sort($steps);

        if ($nextY < $this->y) {
            return array_reverse($steps);
        }

        return $steps;
    }
}
