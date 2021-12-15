<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class CDO extends Token
{
    const TYPE = TokenType::CDO;
    public string $representation = '<!--';

    public function __construct(int $pos)
    {
        $this->position = $pos;
    }
}
