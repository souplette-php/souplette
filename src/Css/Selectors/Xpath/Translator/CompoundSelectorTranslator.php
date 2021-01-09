<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Node\UniversalSelector;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class CompoundSelectorTranslator
{
    public function __invoke(CompoundSelector $selector, TranslationContext $context)
    {
        $type = $selector->selectors[0];
        if (!$type instanceof TypeSelector) {
            $context->visit(new UniversalSelector('*'));
        }
        foreach ($selector as $child) {
            $context->visit($child);
        }
    }
}
