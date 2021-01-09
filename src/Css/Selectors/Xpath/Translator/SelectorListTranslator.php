<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class SelectorListTranslator
{
    public function __invoke(SelectorList $selector, TranslationContext $context)
    {
        foreach ($selector->selectors as $child) {
            $context->visit($child);
            $context->expr->end();
        }
    }
}
