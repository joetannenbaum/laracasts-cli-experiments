<?php

namespace App\Renderers;

use App\Dashboard;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\CapturesOutput;
use Chewie\Concerns\DrawsBigNumbers;
use Chewie\Output\Lines;
use Illuminate\Support\Collection;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Renderer;

class DashboardRenderer extends Renderer
{
    use Aligns;
    use DrawsBigNumbers;
    use CapturesOutput;
    use DrawsBoxes;

    protected int $width;

    protected int $height;

    protected int $columnWidth;

    protected int $contentHeight;

    public function __invoke(Dashboard $prompt): string
    {
        $this->width = $prompt->terminal()->cols() - 2;
        $this->height = $prompt->terminal()->lines() - 6;

        $columnSpacing = 1;

        $this->columnWidth = floor($this->width / 2) - ($columnSpacing * 2) - 1;

        $this->renderHeader($prompt);

        $this->contentHeight = $this->height - $this->currentLineCount();

        $health = $this->renderHealth($prompt);
        $stats = $this->renderStats($prompt);

        $spacing = collect(['', '']);

        $leftColumn = $spacing->merge($health)->merge($spacing)->merge($stats);

        $chat = $this->renderChat($prompt);

        $dividerLine = collect(range(1, $chat->count()))->map(fn () => $this->dim('│'));

        Lines::fromColumns([$leftColumn, $dividerLine, $chat])
            ->spacing($columnSpacing)
            ->lines()
            ->each($this->line(...));

        return $this;
    }

    protected function renderChat(Dashboard $prompt): Collection
    {
        $messages = $prompt->chat->messages
            ->map(fn ($message) => [
                $message[0] === 'HAL' ? $this->red($message[0]) : $this->cyan($message[0]),
                wordwrap($message[1], $this->columnWidth),
            ])
            ->map(function ($lines) {
                [$speaker, $message] = $lines;

                return collect(explode(PHP_EOL, $message))
                    ->prepend($speaker)
                    ->push('')
                    ->map(fn ($line) => '  ' . $line);
            })
            ->flatten();

        $input = $this->captureOutput(fn () => $this->box('', $prompt->valueWithCursor(60)));
        $input = collect(explode(PHP_EOL, $input))->filter();

        $messages = $messages->slice(-$this->contentHeight);

        while ($messages->count() < $this->contentHeight) {
            $messages->prepend('');
        }

        return $messages->merge($input);
    }


    protected function renderHealth(Dashboard $prompt): Collection
    {
        $lines = $this->bigNumber($prompt->health->value->current());

        $lines->prepend($this->bold($this->cyan('SHIP HEALTH')));

        return $this->centerHorizontally($lines, $this->columnWidth);
    }

    protected function renderStats(Dashboard $prompt): Collection
    {
        $labels = [
            'POWER',
            'SHIELDS',
            'WEAPONS',
        ];

        $colors = [
            'yellow',
            'green',
            'blue',
        ];

        return collect([
            $prompt->bar1->value,
            $prompt->bar2->value,
            $prompt->bar3->value,
        ])
            ->map(fn ($value) => round($value->current() / 100 * $this->columnWidth))
            ->map(fn ($value, $index) => [
                'value' => $value,
                'color' => ($value < $this->columnWidth * .4) ? 'red' : $colors[$index],
                'label' => $labels[$index],
            ])
            ->map(fn ($item) => [
                $this->bold($this->{$item['color']}($item['label'])),
                $this->{$item['color']}(str_repeat('█', $item['value'])),
                '',
            ])
            ->flatten();
    }

    protected function renderHeader(Dashboard $prompt): void
    {
        $leftHalf = $this->bold(
            $this->red($prompt->halPulse->frames->frame(['●', '○'])) . ' Good afternoon, Dave.'
        );

        $rightHalf = $this->dim(date('Y-m-d H:i:s'));

        $this->line($this->spaceBetween($this->width, $leftHalf, $rightHalf));

        $this->line($this->dim(str_repeat('─', $this->width)));
    }
}
