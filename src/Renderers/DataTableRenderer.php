<?php

namespace App\Renderers;

use App\DataTable;
use Chewie\Concerns\Aligns;
use Chewie\Concerns\DrawsHotkeys;
use Chewie\Concerns\DrawsTables;
use Laravel\Prompts\Themes\Default\Renderer;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;

class DataTableRenderer extends Renderer
{
    use DrawsTables;
    use DrawsHotkeys;

    public function __invoke(DataTable $prompt): string
    {
        $width = $prompt->terminal()->cols() - 2;
        $height = $prompt->terminal()->lines() - 6;

        $this->renderSearch($prompt);
        $this->renderJump($prompt);

        if ($this->output === '') {
            $this->newLine();
        }

        $selectedStyle = new TableCellStyle([
            'bg' => 'white',
            'fg' => 'black',
        ]);

        $columnLengths = collect(array_keys($prompt->headers))
            ->flatMap(fn ($key) => [
                $key => collect($prompt->rows)
                    ->pluck($key)
                    ->map(fn ($value) => mb_strwidth($value))
                    ->max(),
            ]);

        $rows = collect($prompt->visible())->map(
            fn ($row) => collect($row)
                ->map(fn ($value, $key) => mb_str_pad($value, $columnLengths[$key]))
                ->all(),
        )->all();

        $rows[$prompt->index] = collect($rows[$prompt->index])
            ->map(fn ($cell) => new TableCell($cell, ['style' => $selectedStyle]))
            ->all();

        $this->table($rows, $prompt->headers)->each($this->line(...));

        $this->newLine();

        $this->line($this->dim('Page ') . $prompt->page . $this->dim(' of ') . $prompt->totalPages);

        $this->newLine();

        match ($prompt->state) {
            'search' => $this->searchHotKeys(),
            'jump' => $this->jumpHotKeys(),
            default => $this->defaultHotKeys($prompt),
        };

        collect($this->hotkeys())->each($this->line(...));

        return $this;
    }

    protected function searchHotKeys()
    {
        $this->hotkey('Enter', 'Submit');
    }

    protected function jumpHotKeys()
    {
        $this->hotkey('Enter', 'Jump to Page');
    }

    protected function defaultHotKeys(DataTable $prompt)
    {
        $this->hotkey('↑ ↓', 'Navigate Records');
        $this->hotkey('←', 'Previous Page', $prompt->page > 1);
        $this->hotkey('→', 'Next Page', $prompt->page < $prompt->totalPages);
        $this->hotkey('/', 'Search');
        $this->hotkey('j', 'Jump to Page');
        $this->hotkey('q', 'Quit');
    }

    protected function renderSearch(DataTable $prompt): void
    {
        if ($prompt->state === 'search') {
            $this->line(' Search: ' . $prompt->queryValueWithCursor(60));

            return;
        }

        if ($prompt->query === '') {
            return;
        }

        $this->line($this->dim(' Search: ') . $prompt->query);
    }

    protected function renderJump(DataTable $prompt): void
    {
        if ($prompt->state !== 'jump') {
            return;
        }

        $this->line(' Jump to Page: ' . $prompt->jumpValueWithCursor(60));
    }
}
