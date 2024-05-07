<?php

namespace App;

use App\DancingBars\Bar;
use App\Renderers\DancingBarsRenderer;
use Chewie\Concerns\Loops;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Concerns\SetsUpAndResets;
use Chewie\Input\KeyPressListener;
use Illuminate\Support\Collection;
use Laravel\Prompts\Prompt;

class DancingBars extends Prompt
{
    use RegistersRenderers;
    use SetsUpAndResets;
    use Loops;

    public Collection $bars;

    public int $barCount = 0;

    public int $maxBarHeight = 0;

    public function __construct()
    {
        $this->registerRenderer(DancingBarsRenderer::class);
    }

    public function run(): void
    {
        $this->render();

        $this->bars = collect(range(1, $this->barCount))
            ->map(fn () => new Bar($this->maxBarHeight));

        $this->registerLoopables(...$this->bars);

        $this->setup($this->dance(...));
    }

    protected function dance(): void
    {
        $listener = KeyPressListener::for($this)->listenForQuit();

        $this->loop(function () use ($listener) {
            $this->render();
            $listener->once();
        });
    }

    public function value(): mixed
    {
        return null;
    }
}
