<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class Hash extends CharacterToken
{
    const TYPE = TokenType::HASH;
    public bool $isId;

    public function __construct(string $value, int $position, bool $isId = false)
    {
        $this->representation = "#{$value}";
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
        $this->isId = $isId;
    }
}
