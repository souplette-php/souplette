<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

final class AttributeSelector extends SimpleSelector
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

    public function __toString()
    {
        $qname = $this->namespace === '*' ? $this->attribute : "{$this->namespace}:{$this->attribute}";
        if (!$this->operator) {
            return "[{$qname}]";
        }
        return sprintf(
            '[%s%s"%s"%s]',
            $qname,
            $this->operator,
            $this->value,
            $this->forceCase ? " {$this->forceCase}" : ''
        );
    }
}
