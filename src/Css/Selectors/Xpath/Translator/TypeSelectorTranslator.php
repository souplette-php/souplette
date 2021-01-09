<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class TypeSelectorTranslator
{
    public function __invoke(TypeSelector $selector, TranslationContext $context)
    {
        $context->expr->element('', $selector->tagName, $selector->namespace);
    }
}
