<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\Token;

abstract class Tag extends Token
{
    public bool $selfClosing = false;
    public ?array $attributes = null;

    public function __construct(
        public string $name = '',
    ) {
    }
}
