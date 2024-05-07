<?php

namespace App;

use App\Prong\Ball;
use App\Prong\Paddle;
use App\Prong\Title;
use App\Renderers\ProngRenderer;
use Chewie\Art;
use Chewie\Concerns\Loops;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Concerns\SetsUpAndResets;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

class Prong extends Prompt
{
    use RegistersRenderers;
    use SetsUpAndResets;
    use Loops;

    public Title $title;

    protected KeyPressListener $listener;

    public int $countdown;

    public Ball $ball;

    public Paddle $player1;

    public Paddle $computer;

    public int $gameHeight = 26;

    public int $gameWidth = 100;

    public ?int $winner = null;

    public function __construct()
    {
        $this->registerRenderer(ProngRenderer::class);

        Art::setDirectory(__DIR__ . '/../art/characters');

        $this->state = 'title';

        $this->title = new Title();

        $this->listener = KeyPressListener::for($this);
    }

    public function play(): void
    {
        $this->setup($this->showTitle(...));
    }

    protected function showTitle(): void
    {
        $this->registerLoopable($this->title);

        $this->render();

        $this->listener->clearExisting()->listenForQuit()->on(Key::ENTER, fn () => false)->listenNow();

        $this->title->hide();

        $this->loop(function () {

            $this->render();

            return $this->title->value->current() > 0;
        });

        $this->startGame();
    }

    protected function startGame(): void
    {
        $this->countdown = 3;
        $this->winner = null;

        $this->ball = new Ball($this);
        $this->player1 = new Paddle($this);
        $this->computer = new Paddle($this);

        $this->state = 'playing';

        $this->clearRegisteredLoopables();

        $this->playGame();
    }

    protected function playGame(): void
    {
        $this->listener->clearExisting()->listenForQuit();

        while ($this->countdown > 0) {
            $this->render();
            $this->listener->once();
            $this->countdown--;
            sleep(1);
        }

        $this->registerLoopables($this->ball, $this->player1, $this->computer);

        $this->ball->onDirectionChange($this->calculateComputerPosition(...));
        $this->ball->onDirectionChange($this->determineWinner(...));

        $this->ball->start();

        $this->listener
            ->onUp($this->player1->moveUp(...))
            ->onDown($this->player1->moveDown(...));

        $this->loop(function () {
            $this->render();

            if ($this->winner !== null) {
                return false;
            }

            $this->listener->once();
        }, 25_000);

        $this->listener->clearExisting()->listenForQuit()->on('r', $this->startGame(...))->listenNow();
    }

    protected function calculateComputerPosition()
    {
        if ($this->ball->direction !== 1) {
            return;
        }

        $nextY = collect($this->ball->steps)->last();
        $chances = collect([1, 1, 1, 1, 0]);

        if ($chances->random() === 0) {
            $nextY -= $this->computer->height + 1;
        }

        $this->computer->value->to($nextY);
    }

    protected function determineWinner()
    {
        if ($this->ball->directionChangeCount === 0) {
            return;
        }

        $winner = match ($this->ball->x->current()) {
            0 => $this->getWinner($this->player1, 2),
            default => $this->getWinner($this->computer, 1),
        };

        if ($winner !== null) {
            $this->state = 'winner';
            $this->winner = $winner;
        }
    }

    protected function getWinner(Paddle $player, int $winnerNumber)
    {
        $paddleStart = $player->value->current();
        $paddleEnd = $player->value->current() + $player->height;

        $isHittingPaddle = $this->ball->y >= $paddleStart && $this->ball->y <= $paddleEnd;

        return $isHittingPaddle ? null : $winnerNumber;
    }

    public function value(): mixed
    {
        return null;
    }
}
