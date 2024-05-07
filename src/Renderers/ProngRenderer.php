<?php

namespace App\Renderers;

use App\Prong;
use App\Prong\Paddle;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\CapturesOutput;
use Chewie\Concerns\DrawsBigNumbers;
use Chewie\Concerns\DrawsBigText;
use Chewie\Concerns\DrawsHotkeys;
use Chewie\Output\Lines;
use Illuminate\Support\Collection;
use Laravel\Prompts\Themes\Default\Concerns\DrawsBoxes;
use Laravel\Prompts\Themes\Default\Renderer;

use function Chewie\stripEscapeSequences;

class ProngRenderer extends Renderer
{
    use DrawsBigText;
    use DrawsBigNumbers;
    use Aligns;
    use DrawsBoxes;
    use CapturesOutput;
    use DrawsHotkeys;

    protected int $width;

    protected int $height;

    public function __invoke(Prong $prompt): string
    {
        $this->width = $prompt->terminal()->cols() - 2;
        $this->height = $prompt->terminal()->lines() - 4;

        return match ($prompt->state) {
            'title' => $this->renderTitle($prompt),
            'winner' => $this->renderWinner($prompt),
            default => $this->renderGame($prompt),
        };
    }

    protected function renderWinner(Prong $prompt): static
    {
        $winner = $prompt->winner === 1 ? 'you' : 'computer';

        $title = $this->bigText($winner . ' won');

        $title->push('');
        $title->push('Press ' . $this->bold($this->cyan('q')) . ' to quit or ' . $this->bold($this->cyan('r')) . ' to restart');

        $this->center($title, $this->width, $this->height)->each($this->line(...));

        return $this;
    }

    protected function renderTitle(Prong $prompt): static
    {
        $title = $this->bigText('prong')
            ->push('')
            ->push('Press ' . $this->bold($this->cyan('ENTER')) . ' to start')
            ->map(
                fn ($line, $index) => $index > $prompt->title->value->current()
                    ? str_repeat(' ', mb_strwidth(stripEscapeSequences($line)))
                    : $line
            );

        $this->center($title, $this->width, $this->height)->each($this->line(...));

        return $this;
    }

    protected function renderGame(Prong $prompt): static
    {
        $paddle1 = $this->paddle($prompt->player1, 'red');
        $paddle2 = $this->paddle($prompt->computer, 'green');

        $center = ($prompt->countdown > 0) ? $this->countdown($prompt) : $this->ball($prompt);

        $linesFromCols = Lines::fromColumns([$paddle1, $center, $paddle2])->lines();

        $this->padVertically($linesFromCols, $prompt->gameHeight);

        $boxed = $this->captureOutput(fn () => $this->box('', $linesFromCols->implode(PHP_EOL)));

        $lines = collect(explode(PHP_EOL, $boxed));

        $this->hotkey('↑ ↓', 'Move paddle');
        $this->hotkey('q', 'Quit');

        $hotkeys = collect([
            $this->bold($this->red('← You are Player 1')),
            implode(PHP_EOL, $this->hotkeys()),
        ])->implode(str_repeat(' ', 4));

        $hotkeyLines = $this->centerHorizontally($hotkeys, $this->width);

        $lines->push(...$hotkeyLines);

        $this->center($lines, $this->width, $this->height)->each($this->line(...));

        return $this;
    }

    protected function ball(Prong $prompt): Collection
    {
        $ballLine = str_repeat(' ', $prompt->ball->x->current())
            . $this->cyan('●')
            . str_repeat(' ', max($prompt->gameWidth - $prompt->ball->x->current() - 1, 0));

        return collect(array_fill(0, $prompt->ball->y, ''))->push($ballLine);
    }

    protected function countdown(Prong $prompt): Collection
    {
        return $this->center(
            $this->bigNumber($prompt->countdown)->map(fn ($line) => $this->bold($this->cyan($line))),
            $prompt->gameWidth,
            $prompt->gameHeight,
        );
    }

    protected function paddle(Paddle $paddle, string $color): Collection
    {
        return collect(array_fill(0, $paddle->value->current(), ''))->merge(
            array_fill(0, $paddle->height, $this->{$color}('█')),
        );
    }
}
