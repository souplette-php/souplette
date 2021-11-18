<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Node\Combinator;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Exception\UnsupportedCombinator;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#match-a-complex-selector-against-an-element
 */
final class ComplexEvaluator implements EvaluatorInterface
{
    public function __construct(
        public EvaluatorInterface $lhs,
        public string $combinator,
        public EvaluatorInterface $rhs,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        if (!$this->rhs->matches($context, $element)) {
            return false;
        }

        switch ($this->combinator) {
            case Combinator::CHILD:
                $parent = $element->parentNode;
                if (!$parent) return false;
                return $this->lhs->matches($context, $parent);
            case Combinator::DESCENDANT:
                $parent = $element->parentNode;
                while ($parent) {
                    if ($this->lhs->matches($context, $parent)) return true;
                    $parent = $parent->parentNode;
                }
                return false;
            case Combinator::NEXT_SIBLING:
                $previous = $element->previousElementSibling;
                if (!$previous) return false;
                return $this->lhs->matches($context, $previous);
            case Combinator::SUBSEQUENT_SIBLING:
                $previous = $element->previousElementSibling;
                while ($previous) {
                    if ($this->lhs->matches($context, $previous)) return true;
                    $previous = $previous->previousElementSibling;
                }
                return false;
            default:
                throw new UnsupportedCombinator($this->combinator);
        }
    }
}
