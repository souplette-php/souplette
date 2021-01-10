<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;
use Souplette\Xml\XpathIdioms;

final class AttributeSelectorTranslator
{
    public function __invoke(AttributeSelector $selector, TranslationContext $context)
    {
        // TODO: handle namespaces
        // TODO: handle caseInsensitivity wrt document modes
        $caseInsensitive = $selector->forceCase === 'i';
        $predicate = match($selector->operator) {
            AttributeSelector::OPERATOR_EQUALS =>
                XpathIdioms::attributeEquals($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_PREFIX_MATCH =>
                XpathIdioms::attributeStartsWith($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_SUFFIX_MATCH =>
                XpathIdioms::attributeEndsWith($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_INCLUDES =>
                XpathIdioms::attributeIncludes($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_SUBSTRING_MATCH =>
                XpathIdioms::attributeContains($selector->attribute, $selector->value, $caseInsensitive),
            AttributeSelector::OPERATOR_DASH_MATCH =>
                XpathIdioms::attributeDashMatches($selector->attribute, $selector->value, $caseInsensitive),
            default =>
                "@{$selector->attribute}",
        };

        $context->expr->predicate($predicate);
    }
}
