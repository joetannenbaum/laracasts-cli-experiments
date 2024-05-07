<?php

namespace App;

use App\Renderers\DataTableRenderer;
use Chewie\Concerns\RegistersThemes;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Concerns\TypedValue;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

class DataTable extends Prompt
{
    use RegistersThemes;
    use TypedValue;

    public array $headers;

    public array $rows;

    public int $perPage = 10;

    public int $page = 1;

    public int $totalPages;

    public int $index = 0;

    public string $query = '';

    public string $jumpToPage = '';

    public bool|string $required = true;

    protected KeyPressListener $listener;

    public function __construct()
    {
        $this->registerTheme(DataTableRenderer::class);

        $this->headers = [
            'name'    => 'Name',
            'email'   => 'Email',
            'address' => 'Address',
        ];

        $this->rows = json_decode(file_get_contents(__DIR__ . '/../data/datatable.json'), true);

        $this->totalPages = $this->getTotalPages($this->rows);

        $this->listener = KeyPressListener::for($this);

        $this->browse();
    }

    protected function browse(): void
    {
        $this->state = 'browse';

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->onUp(fn () => $this->index = max(0, $this->index - 1))
            ->onDown(fn () => $this->index = min($this->perPage - 1, $this->index + 1))
            ->onRight(function () {
                $this->index = 0;
                $this->page = min($this->totalPages, $this->page + 1);
            })
            ->onLeft(function () {
                $this->index = 0;
                $this->page = max(1, $this->page - 1);
            })
            ->on('/', $this->search(...))
            ->on('j', $this->jump(...))
            ->on(Key::ENTER, $this->submit(...))
            ->listen();
    }

    protected function search(): void
    {
        $this->state = 'search';
        $this->index = 0;
        $this->page = 1;

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->listenToInput($this->query, $this->cursorPosition)
            ->on(
                Key::ENTER,
                function () {
                    if (count($this->visible()) === 0) {
                        return;
                    }

                    $this->browse();
                },
            )
            ->listen();
    }

    protected function jump(): void
    {
        $this->state = 'jump';
        $this->index = 0;

        $this->listener
            ->clearExisting()
            ->listenForQuit()
            ->listenToInput($this->jumpToPage, $this->cursorPosition)
            ->on(
                Key::ENTER,
                function () {
                    if ($this->jumpToPage === '') {
                        $this->browse();
                        return;
                    }

                    if (!is_numeric($this->jumpToPage)) {
                        return;
                    }

                    if ($this->jumpToPage < 1 || $this->jumpToPage > $this->totalPages) {
                        return;
                    }

                    $this->page = (int) $this->jumpToPage;
                    $this->jumpToPage = '';
                    $this->browse();
                },
            )
            ->listen();
    }

    public function queryValueWithCursor(int $maxWidth): string
    {
        return $this->getValueWithCursor($this->query, $maxWidth);
    }

    public function jumpValueWithCursor(int $maxWidth): string
    {
        return $this->getValueWithCursor($this->jumpToPage, $maxWidth);
    }

    protected function getValueWithCursor(string $value, int $maxWidth): string
    {
        if ($value === '') {
            return $this->dim($this->addCursor('', 0, $maxWidth));
        }

        return $this->addCursor($value, $this->cursorPosition, $maxWidth);
    }

    protected function getTotalPages(array $records): int
    {
        return (int) ceil(count($records) / $this->perPage);
    }

    public function visible(): array
    {
        if ($this->query === '') {
            $this->totalPages = $this->getTotalPages($this->rows);

            return array_slice($this->rows, ($this->page - 1) * $this->perPage, $this->perPage);
        }

        $filtered = array_filter(
            $this->rows,
            fn ($row) => str_contains(
                mb_strtolower(implode(' ', $row)),
                mb_strtolower($this->query)
            ),
        );

        $this->totalPages = $this->getTotalPages($filtered);

        $results = array_slice($filtered, 0, $this->perPage);

        if (count($results) > 0) {
            return $results;
        }

        return [
            [
                'name' => 'No results',
                'email' => '',
                'address' => '',
            ],
        ];
    }

    public function value(): mixed
    {
        return $this->visible()[$this->index];
    }
}
