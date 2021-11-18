<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

final class CDC extends Token
{
    const TYPE = TokenType::CDC;
    public string $representation = '-->';

    public function __construct(int $pos)
    {
        $this->position = $pos;
    }
}
