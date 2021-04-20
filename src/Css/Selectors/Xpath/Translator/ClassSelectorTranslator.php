<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Xpath\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class ClassSelectorTranslator
{
    public function __invoke(ClassSelector $selector, TranslationContext $context)
    {
        // TODO: match must be case-insensitive in quirks mode
        $context->expr->predicate(
            AttributeMatchHelper::attributeIncludes('class', $selector->class)
        );
    }
}
