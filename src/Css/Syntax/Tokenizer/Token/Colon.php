<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class Colon extends SingleCharToken
{
    const TYPE = TokenTypes::COLON;
    public string $value = ':';
    public string $representation = ':';
}
