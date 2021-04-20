<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

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
        $op = $this->operator;
        if (!$op) {
            return $element->hasAttribute($attr);
        }

        $expected = $this->value;
        $actual = $element->getAttribute($attr);
        $caseInsensitive = match ($this->forceCase) {
            AttributeSelector::CASE_FORCE_INSENSITIVE => true,
            AttributeSelector::CASE_FORCE_SENSITIVE, null => false,
        };

        return match ($op) {
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
}
