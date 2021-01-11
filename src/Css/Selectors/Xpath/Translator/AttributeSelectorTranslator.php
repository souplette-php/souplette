<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Xpath\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class AttributeSelectorTranslator
{
    public function __invoke(AttributeSelector $selector, TranslationContext $context)
    {
        // TODO: handle namespaces
        // TODO: handle caseInsensitivity wrt document modes
        $caseInsensitive = $selector->forceCase === 'i';
        $predicate = match($selector->operator) {
            AttributeSelector::OPERATOR_EQUALS =>
                AttributeMatchHelper::attributeEquals($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_PREFIX_MATCH =>
                AttributeMatchHelper::attributeStartsWith($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_SUFFIX_MATCH =>
                AttributeMatchHelper::attributeEndsWith($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_INCLUDES =>
                AttributeMatchHelper::attributeIncludes($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_SUBSTRING_MATCH =>
                AttributeMatchHelper::attributeContains($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_DASH_MATCH =>
                AttributeMatchHelper::attributeDashMatches($selector->attribute, $selector->value, $caseInsensitive),
            default =>
                "@{$selector->attribute}",
        };

        $context->expr->predicate($predicate);
    }
}
