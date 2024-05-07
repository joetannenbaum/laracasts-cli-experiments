<?php

namespace App;

use App\Renderers\BigTextRenderer;
use Chewie\Art;
use Chewie\Concerns\CreatesAnAltScreen;
use Chewie\Concerns\RegistersThemes;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;

class BigText extends Prompt
{
    use RegistersThemes;
    use CreatesAnAltScreen;

    public string $message = "Type a message,\nenter to clear";

    public function __construct()
    {
        $this->registerTheme(BigTextRenderer::class);

        $this->createAltScreen();

        Art::setDirectory(__DIR__ . '/../art/characters');

        $validCharacters = array_merge(
            range('a', 'z'),
            range('A', 'Z'),
            [
                ' ',
                '.',
                ',',
                '?',
                '!',
                "'",
            ],
        );

        KeyPressListener::for($this)
            ->on($validCharacters, fn ($key) => $this->message .= $key)
            ->on(Key::ENTER, fn () => $this->message = '')
            ->on(Key::BACKSPACE, fn () => $this->message = substr($this->message, 0, -1))
            ->on(Key::CTRL_C, fn () => $this->terminal()->exit())
            ->listen();
    }

    public function value(): mixed
    {
        return null;
    }
}
