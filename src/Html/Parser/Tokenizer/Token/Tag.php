<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\Token;

abstract class Tag extends Token
{
    public string $name = '';
    public bool $selfClosing = false;
    public ?array $attributes = null;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}
