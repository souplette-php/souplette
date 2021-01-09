<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator;

use Souplette\Css\Selectors\Node\Combinators;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Xpath\Exception\UnsupportedCombinator;
use Souplette\Css\Selectors\Xpath\TranslationContext;

final class ComplexSelectorTranslator
{
    public function __invoke(ComplexSelector $selector, TranslationContext $context)
    {
        $context->visit($selector->lhs);
        if (!$selector->combinator) {
            return;
        }
        // since the selectors parse-tree is left-associative,
        // the right-hand side of the selector is always a single compound selector.
        // Therefore the current context expression is guaranteed to be correct.
        $context->visit($selector->rhs);
        switch ($selector->combinator) {
            case Combinators::CHILD:
                break;
            case Combinators::DESCENDANT:
                $context->expr->axis('descendant-or-self::*/');
                break;
            case Combinators::NEXT_SIBLING:
                $context->expr->axis('following-sibling::')->predicate('position() = 1');
                break;
            case Combinators::SUBSEQUENT_SIBLING:
                $context->expr->axis('following-sibling::');
                break;
            default:
                throw new UnsupportedCombinator($selector->combinator);
        }
    }
}
