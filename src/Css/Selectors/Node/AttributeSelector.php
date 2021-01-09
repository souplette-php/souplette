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
        private string $attribute,
        private ?string $namespace = null,
        private ?string $operator = null,
        private ?string $value = null,
        private ?string $forceCase = null
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
