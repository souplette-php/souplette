<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\TokenTypes;

final class StartTag extends Tag
{
    const TYPE = TokenTypes::START_TAG;
}
