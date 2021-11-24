<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use JetBrains\PhpStorm\Pure;
use Souplette\Css\Selectors\Namespaces;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Query\AttributeMatcher;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

final class AttributeSelector extends SimpleSelector
{
    const OPERATOR_EQUALS = '=';
    const OPERATOR_INCLUDES = '~=';
    const OPERATOR_DASH_MATCH = '|=';
    const OPERATOR_PREFIX_MATCH = '^=';
    const OPERATOR_SUFFIX_MATCH = '$=';
    const OPERATOR_SUBSTRING_MATCH = '*=';

    const CASE_FORCE_INSENSITIVE = 'i';
    const CASE_FORCE_SENSITIVE = 's';

    #[Pure]
    public static function exists(string $attribute, ?string $namespace = null): self
    {
        return new self($attribute, $namespace);
    }

    #[Pure]
    public static function equals(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_EQUALS, $value, $forceCase);
    }

    #[Pure]
    public static function includes(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_INCLUDES, $value, $forceCase);
    }

    #[Pure]
    public static function dashMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_DASH_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function prefixMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_PREFIX_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function suffixMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_SUFFIX_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function substring(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_SUBSTRING_MATCH, $value, $forceCase);
    }

    public function __construct(
        public string $attribute,
        public ?string $namespace = null,
        public ?string $operator = null,
        public ?string $value = null,
        public ?string $forceCase = null
    )
    {
    }

    public function __toString(): string
    {
        $qname = match ($this->namespace) {
            Namespaces::NONE, Namespaces::DEFAULT => $this->attribute,
            default => "{$this->namespace}|{$this->attribute}",
        };
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

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $attr = $this->attribute;
        $hasAttribute = match ($this->namespace) {
            Namespaces::NONE, Namespaces::DEFAULT => $element->hasAttributeNS(null, $attr),
            Namespaces::ANY => (
                $element->hasAttribute($attr)
                || AttributeMatcher::hasAttributeInAnyNamespace($element, $attr)
            ),
            default => $element->hasAttributeNS($this->namespace, $attr),
        };

        if (!$this->operator) return $hasAttribute;
        if (!$hasAttribute || !$this->value) return false;

        $expected = $this->value;
        $actual = match ($this->namespace) {
            Namespaces::NONE, Namespaces::DEFAULT => $element->getAttributeNS(null, $attr),
            Namespaces::ANY => AttributeMatcher::getAttributeInAnyNamespace($element, $attr),
            default => $element->getAttributeNS($this->namespace, $attr),
        };
        $caseInsensitive = match ($this->forceCase) {
            AttributeSelector::CASE_FORCE_INSENSITIVE => true,
            AttributeSelector::CASE_FORCE_SENSITIVE, null => false,
        };

        return match ($this->operator) {
            AttributeSelector::OPERATOR_EQUALS
                => AttributeMatcher::equals($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_DASH_MATCH
                => AttributeMatcher::dashMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_INCLUDES
                => AttributeMatcher::includes($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_PREFIX_MATCH
                => AttributeMatcher::prefixMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_SUFFIX_MATCH
                => AttributeMatcher::suffixMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_SUBSTRING_MATCH
                => AttributeMatcher::substring($expected, $actual, $caseInsensitive),
        };
    }
}
