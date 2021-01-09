<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;
use Souplette\Xml\XpathIdioms;

final class ClassSelectorTranslator
{
    public function __invoke(ClassSelector $selector, TranslationContext $context)
    {
        // TODO: match must be case-insensitive in quirks mode
        $context->expr->predicate(XpathIdioms::attributeIncludes('class', $selector->class));
    }
}
