<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator\Functional;

use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\Helper\NthTranslatorHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class NthLastOfTypeSelectorTranslator
{
    public function __invoke(NthLastOfType $selector, TranslationContext $context)
    {
        $type = $context->expr->getLocalName();
        if ($type !== '*') {
            throw new UnsupportedSelector($selector);
        }

        $a = $selector->anPlusB->a;
        $b = $selector->anPlusB->b;
        $context->expr->predicate(
            NthTranslatorHelper::translateNth($a, $b, $type, true)
        );
    }
}
