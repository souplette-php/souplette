<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class RightCurly extends SingleCharToken
{
    public int $type = TokenTypes::RCURLY;
    public string $value = '}';
    public string $representation = '}';
}
