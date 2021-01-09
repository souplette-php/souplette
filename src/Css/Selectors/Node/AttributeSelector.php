<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class AttributeSelector extends SimpleSelector
{
    const OPERATOR_EQUALS = '=';
    const OPERATOR_INCLUDES = '~=';
    const OPERATOR_DASH_MATCH = '|=';
    const OPERATOR_PREFIX_MATCH = '^=';
    const OPERATOR_SUFFIX_MATCH = '$=';
    const OPERATOR_SUBSTRING_MATCH = '*=';

    public function __construct(
        public string $attribute,
        public ?string $namespace = null,
        public ?string $operator = null,
        public ?string $value = null,
        public ?string $forceCase = null
    ) {
    }

    public function __toString()
    {
        $qname = $this->namespace ? "{$this->namespace}|{$this->attribute}" : $this->attribute;
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

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
