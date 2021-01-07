<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class SemiColon extends SingleCharToken
{
    public int $type = TokenTypes::SEMICOLON;
    public string $value = ';';
    public string $representation = ';';
}
