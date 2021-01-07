<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class EOF extends Token
{
    public int $type = TokenTypes::EOF;

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
