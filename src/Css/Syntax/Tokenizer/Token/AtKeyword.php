<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class AtKeyword extends CharacterToken
{
    const TYPE = TokenTypes::AT_KEYWORD;

    public function __construct(string $value, int $position)
    {
        $this->representation = "@{$value}";
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
    }
}
