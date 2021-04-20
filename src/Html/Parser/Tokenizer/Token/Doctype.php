<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;

final class Doctype extends Token
{
    const TYPE = TokenTypes::DOCTYPE;
    public string $name = '';
    public ?string $publicIdentifier = null;
    public ?string $systemIdentifier = null;
    public bool $forceQuirks = false;
}
