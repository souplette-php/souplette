<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

final class Whitespace extends Token
{
    const TYPE = TokenType::WHITESPACE;
    public string $representation = ' ';

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
