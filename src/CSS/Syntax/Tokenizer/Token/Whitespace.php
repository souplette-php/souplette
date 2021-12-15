<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class Whitespace extends Token
{
    const TYPE = TokenType::WHITESPACE;
    public string $representation = ' ';

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
