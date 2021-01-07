<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class LeftCurly extends SingleCharToken
{
    public int $type = TokenTypes::LCURLY;
    public string $value = '{';
    public string $representation = '{';
}
