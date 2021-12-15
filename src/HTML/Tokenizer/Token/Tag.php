<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;

abstract class Tag extends Token
{
    public bool $selfClosing = false;
    public ?array $attributes = null;

    public function __construct(
        public string $name = '',
    ) {
    }
}
