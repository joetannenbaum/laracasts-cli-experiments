<?php

namespace App\Renderers;

use App\Stopwatch;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\DrawsBigNumbers;
use Chewie\Concerns\DrawsHotkeys;
use Laravel\Prompts\Themes\Default\Renderer;

class StopwatchRenderer extends Renderer
{
    use DrawsBigNumbers;
    use Aligns;
    use DrawsHotkeys;

    public function __invoke(Stopwatch $prompt): string
    {
        $width = $prompt->terminal()->cols() - 2;
        $height = $prompt->terminal()->lines() - 7;

        [$minutes, $seconds, $milliseconds] = $this->timeSegments($prompt->elapsedTime);

        $colon = <<<'COLON'

        •
        •
        COLON;

        $bigMinutes = $this->bigNumber($minutes);
        $bigSeconds = $this->bigNumber($seconds);
        $bigColon = collect(explode(PHP_EOL, $colon))->map(
            fn ($line) => mb_str_pad($line, 1, ' '),
        );

        $lines = collect($bigMinutes)
            ->zip($bigColon, $bigSeconds)
            ->map(fn ($lines) => $lines->implode(''))
            ->map(fn ($line, $index) => $index === 1 ? $line . ' ' . $milliseconds : $line . str_repeat(' ', 4))
            ->map(fn ($line) => str_repeat(' ', 4) . $line);

        if (count($prompt->laps) > 0) {
            $lines->push('');

            $timeLength = strlen('00:00.000');

            $lapTitle = mb_str_pad('Lap', $timeLength + 3, ' ');
            $totalTitle = mb_str_pad('Total', $timeLength, ' ');

            $leftPad = str_repeat(' ', 5);

            $lines->push($leftPad . $this->bold($this->cyan($lapTitle)) . $this->bold($this->green($totalTitle)));
            $lines->push($leftPad . $this->dim(str_repeat('─', strlen($lapTitle . $totalTitle))));
        }

        foreach ($prompt->laps as $index => $lap) {
            $previousLap = $prompt->laps[$index - 1] ?? 0;
            $lines->push($this->renderLap($index, $lap, $previousLap));
        }

        $this->center($lines, $width, $height)->each($this->line(...));

        $this->pinToBottom($height, function () use ($prompt, $width) {
            $this->newLine();

            if ($prompt->started) {
                $this->hotkey('Space', 'Lap');
                $this->hotkey('r', 'Reset');
            } else {
                $this->hotkey('Space', 'Start');
            }

            $this->hotkey('q', 'Quit');

            $this->centerHorizontally($this->hotkeys(), $width)->each($this->line(...));

            $this->newLine();
        });

        return $this;
    }

    protected function renderLap(int $index, int $totalTime, int $previousLap): string
    {
        $lap = $totalTime - $previousLap;

        $lapNumber = mb_str_pad($index + 1, 2, '0', STR_PAD_LEFT);

        return collect([
            $this->dim($lapNumber),
            $this->cyan($this->timeFormatted($lap)),
            $this->green($this->timeFormatted($totalTime)),
        ])->implode(str_repeat(' ', 3));
    }

    protected function timeSegments(int $milliseconds): array
    {
        $minutes = (int) floor($milliseconds / 60_000);
        $seconds = (int) floor(($milliseconds - $minutes * 60_000) / 1000);
        $milliseconds = $milliseconds - ($minutes * 60_000) - ($seconds * 1000);

        return [
            mb_str_pad($minutes, 2, '0', STR_PAD_LEFT),
            mb_str_pad($seconds, 2, '0', STR_PAD_LEFT),
            mb_str_pad($milliseconds, 3, '0', STR_PAD_LEFT),
        ];
    }

    protected function timeFormatted(int $milliseconds): string
    {
        [$minutes, $seconds, $milliseconds] = $this->timeSegments($milliseconds);

        return "{$minutes}:{$seconds}.{$milliseconds}";
    }
}
