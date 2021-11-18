<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class RightCurly extends SingleCharToken
{
    const TYPE = TokenType::RCURLY;
    public string $value = '}';
    public string $representation = '}';
}
