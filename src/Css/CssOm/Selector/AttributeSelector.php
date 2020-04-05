<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

class AttributeSelector extends Selector
{
    const OPERATOR_EXISTS = '';
    const OPERATOR_EQUALS = '=';
    const OPERATOR_INCLUDES = '~=';
    const OPERATOR_DASH_MATCH = '|=';
    const OPERATOR_PREFIX_MATCH = '⁼';
    const OPERATOR_SUFFIX_MATCH = '$=';
    const OPERATOR_SUBSTRING_MATCH = '*=';

    protected string $attribute;
    protected string $namespace;
    protected ?string $operator;
    protected ?string $value;
    protected ?string $forceCase;

    public function __construct(
        string $attribute,
        string $namespace = '*',
        ?string $operator = null,
        ?string $value = null,
        ?string $forceCase = null
    ) {
        $this->attribute = $attribute;
        $this->namespace = $namespace;
        $this->operator = $operator;
        $this->value = $value;
        $this->forceCase = $forceCase;
    }
}
