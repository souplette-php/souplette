<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;
use Souplette\Xml\XpathIdioms;

final class IdSelectorTranslator
{
    public function __invoke(IdSelector $selector, TranslationContext $context)
    {
        $context->expr->predicate(
            XpathIdioms::attributeEquals('id', $selector->id)
        );
    }
}
