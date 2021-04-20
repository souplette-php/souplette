<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\TokenTypes;

final class EndTag extends Tag
{
    const TYPE = TokenTypes::END_TAG;
}
