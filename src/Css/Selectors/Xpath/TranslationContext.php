<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath;

use Souplette\Css\Selectors\Node\Selector;

final class TranslationContext
{
    public function __construct(
        public Translator $translator,
        public ExpressionBuilder $expr,
    )
    {
    }

    public function visit(Selector $node)
    {
        $this->translator->visit($node);
    }
}
