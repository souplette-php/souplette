<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator\Functional;

use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\Helper\NthTranslatorHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class NthChildSelectorTranslator
{
    public function __invoke(NthChild $selector, TranslationContext $context)
    {
        if ($selector->selectorList) {
            throw new UnsupportedSelector($selector);
        }
        $a = $selector->anPlusB->a;
        $b = $selector->anPlusB->b;

        $predicate = NthTranslatorHelper::translateNth($a, $b, '*', false);
        $context->expr->predicate($predicate);
    }
}
