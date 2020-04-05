<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class ClassSelector extends AttributeSelector
{
    public function __construct(string $value)
    {
        parent::__construct('class', '*', self::OPERATOR_INCLUDES, $value);
    }
}
