<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

final class AttributeEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context): bool
    {
        $selector = $context->selector;
        assert($selector instanceof AttributeSelector);

        $element = $context->element;
        $attr = $selector->attribute;
        $op = $selector->operator;
        if (!$op) {
            return $element->hasAttribute($attr);
        }

        $expected = $selector->value;
        $actual = $element->getAttribute($attr);
        $caseInsensitive = match ($selector->forceCase) {
            'i' => true,
            's', null => false,
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
