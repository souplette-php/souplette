<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Namespaces;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

final class AttributeEvaluator implements EvaluatorInterface
{
    public function __construct(
        public string $attribute,
        public ?string $operator = null,
        public ?string $value = null,
        public ?string $namespace = null,
        public ?string $forceCase = null,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $attr = $this->attribute;
        $hasAttribute = match ($this->namespace) {
            Namespaces::NONE, Namespaces::DEFAULT => $element->hasAttributeNS(null, $attr),
            Namespaces::ANY => $element->hasAttribute($attr) || self::hasAttributeInAnyNamespace($element, $attr),
            default => $element->hasAttributeNS($this->namespace, $attr),
        };

        if (!$this->operator) return $hasAttribute;
        if (!$hasAttribute || !$this->value) return false;

        $expected = $this->value;
        $actual = match ($this->namespace) {
            Namespaces::NONE, Namespaces::DEFAULT => $element->getAttributeNS(null, $attr),
            Namespaces::ANY => self::getAttributeInAnyNamespace($element, $attr),
            default => $element->getAttributeNS($this->namespace, $attr),
        };
        $caseInsensitive = match ($this->forceCase) {
            AttributeSelector::CASE_FORCE_INSENSITIVE => true,
            AttributeSelector::CASE_FORCE_SENSITIVE, null => false,
        };

        return match ($this->operator) {
            AttributeSelector::OPERATOR_EQUALS
                => AttributeMatchHelper::equals($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_DASH_MATCH
                => AttributeMatchHelper::dashMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_INCLUDES
                => AttributeMatchHelper::includes($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_PREFIX_MATCH
                => AttributeMatchHelper::prefixMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_SUFFIX_MATCH
                => AttributeMatchHelper::suffixMatch($expected, $actual, $caseInsensitive),
            AttributeSelector::OPERATOR_SUBSTRING_MATCH
                => AttributeMatchHelper::substring($expected, $actual, $caseInsensitive),
        };
    }

    private static function hasAttributeInAnyNamespace(\DOMElement $element, string $localName): bool
    {
        foreach ($element->attributes as $attribute) {
            if ($attribute->localName === $localName) return true;
        }
        return false;
    }

    private static function getAttributeInAnyNamespace(\DOMElement $element, string $localName): ?string
    {
        foreach ($element->attributes as $attribute) {
            if ($attribute->localName === $localName) return $attribute->value;
        }
        return null;
    }
}
