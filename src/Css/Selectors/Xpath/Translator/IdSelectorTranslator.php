<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Xpath\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class IdSelectorTranslator
{
    public function __invoke(IdSelector $selector, TranslationContext $context)
    {
        $context->expr->predicate(
            AttributeMatchHelper::attributeEquals('id', $selector->id)
        );
    }
}
