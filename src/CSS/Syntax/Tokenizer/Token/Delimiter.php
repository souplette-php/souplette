<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class Delimiter extends Token
{
    const TYPE = TokenType::DELIM;
    public string $value;

    public function __construct(string $value, int $position)
    {
        $this->representation = $value;
        $this->value = $value;
        $this->position = $position;
    }
}
