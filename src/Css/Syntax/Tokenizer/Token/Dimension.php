<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Dimension extends NumericToken
{
    public int $type = TokenTypes::DIMENSION;
    public string $unit;

    public function __construct(string $value, string $unit, int $position)
    {
        parent::__construct($value, $position);
        $this->unit = $unit;
        $this->representation = $value . $unit;
    }
}
