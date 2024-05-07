<?php

namespace App;

use App\Renderers\DirectoryWatcherRenderer;
use Chewie\Concerns\CreatesAnAltScreen;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Concerns\SetsUpAndResets;
use Chewie\Input\KeyPressListener;
use Illuminate\Support\Collection;
use Laravel\Prompts\Prompt;

class DirectoryWatcher extends Prompt
{
    use RegistersRenderers;
    use SetsUpAndResets;
    use CreatesAnAltScreen;

    public string $total = '';

    public Collection $items;

    public Collection $versions;

    public function __construct(public string $path)
    {
        $this->registerRenderer(DirectoryWatcherRenderer::class);

        $this->items = collect();
        $this->versions = collect();

        $this->createAltScreen();
    }

    public function watch()
    {
        $this->setup($this->watchDirectory(...));
    }

    protected function watchDirectory(): void
    {
        $listener = KeyPressListener::for($this)->listenForQuit();

        while (true) {
            $output = shell_exec('ls -lAh ' . $this->path);

            $items = collect(explode(PHP_EOL, $output));

            $this->total = str_replace('total ', '', $items->shift());

            $this->versions->push($this->items);
            $this->versions = $this->versions->take(-20);

            $this->items = $items->map($this->parseItem(...))->filter(fn ($item) => count($item) > 0);

            $this->render();
            $listener->once();
            usleep(100_000);
        }
    }

    protected function parseItem(string $item): array
    {
        $parts = preg_split('/\s+/', $item);

        if (count($parts) <= 1) {
            return [];
        }

        $fields = [
            'permissions',
            'hardLinks',
            'owner',
            'group',
            'size',
            'month',
            'day',
            'time',
        ];

        $data = [];

        foreach ($fields as $field) {
            $data[$field] = array_shift($parts);
        }

        $data['name'] = implode(' ', $parts);
        $data['date'] = "{$data['month']} {$data['day']} {$data['time']}";
        $data['is_dir'] = substr($data['permissions'], 0, 1) === 'd';

        return $data;
    }

    public function value(): mixed
    {
        return null;
    }
}
