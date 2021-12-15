<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class EOF extends Token
{
    const TYPE = TokenType::EOF;

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
