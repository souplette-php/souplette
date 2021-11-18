<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class Dimension extends NumericToken
{
    const TYPE = TokenType::DIMENSION;
    public string $unit;

    public function __construct(string $value, string $unit, int $position)
    {
        parent::__construct($value, $position);
        $this->unit = $unit;
        $this->representation = $value . $unit;
    }
}
