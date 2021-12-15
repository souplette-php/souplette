<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;
use Souplette\CSS\Syntax\Utils;

final class Str extends CharacterToken
{
    const TYPE = TokenType::STRING;

    public function __construct(string $value, int $position)
    {
        $this->representation = sprintf('"%s"', $value);
        $this->value = Utils::unescapeString($value);
        $this->position = $position;
    }
}
