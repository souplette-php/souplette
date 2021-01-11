<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator\Functional;

use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class NthLastChildSelectorTranslator
{
    public function __invoke(NthLastChild $selector, TranslationContext $context)
    {
        if ($selector->selectorList) {
            throw new UnsupportedSelector($selector);
        }
        $a = $selector->anPlusB->a;
        $b = $selector->anPlusB->b;

        $predicate = NthTranslatorHelper::translateNth($a, $b, '*', true);
        $context->expr->predicate($predicate);
    }
}
