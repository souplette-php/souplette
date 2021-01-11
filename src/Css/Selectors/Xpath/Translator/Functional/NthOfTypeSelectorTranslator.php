<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator\Functional;

use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class NthOfTypeSelectorTranslator
{
    public function __invoke(NthOfType $selector, TranslationContext $context)
    {
        $type = $context->expr->getLocalName();
        if ($type !== '*') {
            throw new UnsupportedSelector($selector);
        }

        $a = $selector->anPlusB->a;
        $b = $selector->anPlusB->b;

        $predicate = NthTranslatorHelper::translateNth($a, $b, $type, false);
        $context->expr->predicate($predicate);
    }
}
