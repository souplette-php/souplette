<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class PseudoElementSelectorTranslator
{
    public function __invoke(PseudoElementSelector $selector, TranslationContext $context)
    {
        throw new UnsupportedSelector($selector);
    }
}
