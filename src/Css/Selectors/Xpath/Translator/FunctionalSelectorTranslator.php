<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class FunctionalSelectorTranslator
{
    public function __invoke(FunctionalSelector $selector, TranslationContext $context)
    {
        throw new UnsupportedSelector($selector);
    }
}
