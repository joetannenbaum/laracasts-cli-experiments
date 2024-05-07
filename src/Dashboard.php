<?php

namespace App;

use App\Dashboard\Chat;
use App\Dashboard\HalPulse;
use App\Dashboard\RandomValue;
use App\Renderers\DashboardRenderer;
use Chewie\Concerns\Loops;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Concerns\SetsUpAndResets;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Concerns\TypedValue;
use Laravel\Prompts\Prompt;

class Dashboard extends Prompt
{
    use RegistersRenderers;
    use SetsUpAndResets;
    use Loops;
    use TypedValue;

    public HalPulse $halPulse;

    public RandomValue $health;

    public RandomValue $bar1;

    public RandomValue $bar2;

    public RandomValue $bar3;

    public Chat $chat;

    public function __construct()
    {
        $this->registerRenderer(DashboardRenderer::class);

        $this->halPulse = new HalPulse;

        $this->health = new RandomValue(
            lowerLimit: 25,
            upperLimit: 75,
            initialValue: 50,
            pauseAfter: 10,
        );

        $this->bar1 = new RandomValue(lowerLimit: 10, upperLimit: 90);
        $this->bar2 = new RandomValue(lowerLimit: 10, upperLimit: 90);
        $this->bar3 = new RandomValue(lowerLimit: 10, upperLimit: 90);

        $this->chat = new Chat;

        $this->registerLoopables(
            $this->halPulse,
            $this->health,
            $this->bar1,
            $this->bar2,
            $this->bar3,
            $this->chat,
        );
    }

    public function run(): void
    {
        $this->setup($this->showDashboard(...));
    }

    public function valueWithCursor(int $maxWidth): string
    {
        if ($this->chat->currentMessage === '') {
            return $this->dim($this->addCursor('Chat with HAL', 0, $maxWidth));
        }

        return $this->addCursor($this->chat->currentMessage, strlen($this->chat->currentMessage), $maxWidth);
    }

    protected function showDashboard(): void
    {
        $listener = KeyPressListener::for($this)->listenForQuit();

        $this->loop(function () use ($listener) {
            $this->render();
            $listener->once();
        }, 100_000);
    }

    public function value(): mixed
    {
        return null;
    }
}
