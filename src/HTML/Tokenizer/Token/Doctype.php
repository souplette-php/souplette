<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;

final class Doctype extends Token
{
    const KIND = TokenKind::Doctype;
    public string $name = '';
    public ?string $publicIdentifier = null;
    public ?string $systemIdentifier = null;
    public bool $forceQuirks = false;
}
