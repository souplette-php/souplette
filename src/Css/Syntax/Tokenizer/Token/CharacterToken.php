<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\Token;

abstract class CharacterToken extends Token
{
    public string $value;

    public function __construct(string $value, int $position)
    {
        $this->representation = $value;
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
    }
}
