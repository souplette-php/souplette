<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;

abstract class NumericToken extends Token
{
    public int|float $value;
    public string $representation;
    public bool $isInteger;

    public function __construct(string $value, int $position)
    {
        $this->representation = $value;
        $this->position = $position;
        $this->value = (0 + $value);
        $this->isInteger = \is_integer($this->value);
    }
}
