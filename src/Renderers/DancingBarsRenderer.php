<?php

namespace App\Renderers;

use App\DancingBars;
use App\DancingBars\Bar;
use Chewie\Output\Lines;
use Laravel\Prompts\Themes\Default\Renderer;

class DancingBarsRenderer extends Renderer
{
    public function __invoke(DancingBars $prompt): string
    {
        $barWidth = 1;
        $barSpacing = 0;

        if ($prompt->barCount === 0) {
            $totalWidth = $prompt->terminal()->cols() - 2;
            $totalBarWidth = $barWidth + $barSpacing;

            $prompt->barCount = (int) floor($totalWidth / $totalBarWidth);
            $prompt->maxBarHeight = $prompt->terminal()->lines() - 6;

            return $this;
        }

        $cols = $prompt->bars->map(function (Bar $bar) use ($barWidth, $prompt) {
            $column = collect();

            foreach (range(1, $bar->value->current()) as $n) {
                $column->push($this->{$bar->color}(str_repeat('█', $barWidth)));
            }

            while ($column->count() < $prompt->maxBarHeight) {
                $column->prepend(str_repeat(' ', $barWidth));
            }

            return $column;
        });

        Lines::fromColumns($cols)
            ->spacing($barSpacing)
            ->lines()
            ->each($this->line(...));

        return $this;
    }
}
