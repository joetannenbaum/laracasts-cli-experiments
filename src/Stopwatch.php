<?php

namespace App;

use App\Renderers\StopwatchRenderer;
use Chewie\Concerns\CreatesAnAltScreen;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

class Stopwatch extends Prompt
{
    use RegistersRenderers;
    use CreatesAnAltScreen;

    public int $elapsedTime = 0;

    public array $laps = [];

    public bool $started = false;

    protected KeyPressListener $listener;

    public function __construct()
    {
        $this->registerRenderer(StopwatchRenderer::class);

        $this->createAltScreen();

        $this->listener = KeyPressListener::for($this);

        $this->listener
            ->listenForQuit()
            ->on(Key::SPACE, function () {
                $this->started = true;
                $this->listener->clearExisting();
                $this->start();
            })
            ->listen();
    }

    public function start(): void
    {
        $this->listener
            ->listenForQuit()
            ->on(Key::SPACE, fn () => $this->laps[] = $this->elapsedTime)
            ->on('r', function () {
                $this->laps = [];
                $this->elapsedTime = 0;
            });

        while (true) {
            usleep(1000);

            $this->elapsedTime++;

            $this->render();

            $this->listener->once();
        }
    }

    public function value(): mixed
    {
        return null;
    }
}
