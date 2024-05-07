<?php

namespace App\Renderers;

use App\Tabs;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\DrawsHotkeys;
use Laravel\Prompts\Themes\Default\Renderer;

class TabsRenderer extends Renderer
{
    use Aligns;
    use DrawsHotkeys;

    public function __invoke(Tabs $prompt): string
    {
        $width = $prompt->terminal()->cols() - 2;
        $height = $prompt->terminal()->lines() - 5;

        $colors = ['bgRed', 'bgGreen', 'bgMagenta', 'bgBlue'];

        $tabs = collect($prompt->tabs)
            ->pluck('tab')
            ->map(fn ($tab) => ' ' . $tab . ' ')
            ->map(function ($tab, $index) use ($prompt, $colors) {
                if ($index === $prompt->selectedTab) {
                    return $this->{$colors[$index]}($tab);
                }

                return $tab;
            });

        $this->centerHorizontally($tabs->implode(str_repeat(' ', 4)), $width)->each($this->line(...));

        $contentWidth = (int) floor($width * .75);

        $this->centerHorizontally($this->dim(str_repeat('─', $contentWidth + 4)), $width)->each($this->line(...));

        $this->newLine();

        $content = wordwrap(
            string: $prompt->tabs[$prompt->selectedTab]['content'],
            width: $contentWidth,
            cut_long_words: true,
        );

        $this->centerHorizontally($content, $width)->each($this->line(...));

        $this->pinToBottom($height, function () use ($prompt, $width) {
            $this->newLine();
            $this->hotkey('←', 'Previous Tab', $prompt->selectedTab > 0);
            $this->hotkey('→', 'Next Tab', $prompt->selectedTab < count($prompt->tabs) - 1);
            $this->hotkey('q', 'Quit');

            $this->centerHorizontally($this->hotkeys(), $width)->each($this->line(...));
        });

        return $this;
    }
}
