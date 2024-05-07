<?php

namespace App\Renderers;

use App\BigText;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\DrawsArt;
use Chewie\Concerns\DrawsHotkeys;
use Chewie\Output\Lines;
use Laravel\Prompts\Themes\Default\Renderer;

class BigTextRenderer extends Renderer
{
    use DrawsArt;
    use Aligns;
    use DrawsHotkeys;

    public function __invoke(BigText $prompt): string
    {
        $message = mb_strtolower($prompt->message);

        $width = $prompt->terminal()->cols() - 2;
        $height = $prompt->terminal()->lines() - 5;

        $messageLines = wordwrap(
            string: $message,
            width: floor($width / 7),
            cut_long_words: true,
        );

        $lines = collect(explode(PHP_EOL, $messageLines))
            ->map(fn ($line) => collect(mb_str_split($line)))
            ->map(
                fn ($letters) => $letters->map(fn ($letter) => match ($letter) {
                    ' '     => array_fill(0, 7, str_repeat(' ', 4)),
                    '.'     => $this->artLines('period'),
                    ','     => $this->artLines('comma'),
                    '?'     => $this->artLines('question-mark'),
                    '!'     => $this->artLines('exclamation-point'),
                    "'"     => $this->artLines('apostrophe'),
                    default => $this->artLines($letter),
                })
            )
            ->flatMap(fn ($letters) => Lines::fromColumns($letters)->lines())
            ->slice(($height - 4) * -1);

        $this->center($lines, $width, $height)->each($this->line(...));

        $this->hotkey('Enter', 'Clear', $message !== '');

        $this->centerHorizontally($this->hotkeys(), $width)->each($this->line(...));

        return $this;
    }
}
