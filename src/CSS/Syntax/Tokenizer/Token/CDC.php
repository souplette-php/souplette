<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class CDC extends Token
{
    const TYPE = TokenType::CDC;
    public string $representation = '-->';

    public function __construct(int $pos)
    {
        $this->position = $pos;
    }
}
