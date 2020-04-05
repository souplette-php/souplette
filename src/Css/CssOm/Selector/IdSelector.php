<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class IdSelector extends AttributeSelector
{
    public function __construct(string $value)
    {
        parent::__construct('id', '*', self::OPERATOR_EQUALS, $value);
    }
}
