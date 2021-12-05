<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Node\PseudoClass\ScopePseudo;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Element;
use Souplette\Dom\Node;
use Souplette\Dom\Traversal\ElementTraversal;
use Traversable;

final class ComplexSelector extends Selector implements \IteratorAggregate
{
    public function __construct(
        public SimpleSelector $selector,
    ) {
    }

    /**
     * @return Traversable<SimpleSelector>
     */
    public function getIterator(): Traversable
    {
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            yield $selector;
        }
    }

    public function __toString(): string
    {
        $css = '';
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            [$selector, $compound] = $this->serializeCompound($selector);
            if (!$selector) return $compound . $css;
            $css = RelationType::toCss($selector->relationType) . $selector . $css;
        }
        return $css;
    }

    public function getSpecificity(): Specificity
    {
        $spec = new Specificity();
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            $spec = $spec->add($selector->getSpecificity());
        }
        return $spec;
    }

    public function matches(QueryContext $context, Element $element): bool
    {
        return $this->matchSelector($this->selector, $context, $element);
    }

    private function matchSelector(SimpleSelector $selector, QueryContext $context, Element $element): bool
    {
        if (!$selector->matches($context, $element)) {
            return false;
        }
        if (!$selector->next) {
            return true;
        }

        return match ($selector->relationType) {
            RelationType::COLUMN => false, // not supported
            RelationType::SUB => $this->matchSelector($selector->next, $context, $element),
            default => $this->matchRelation($selector, $context, $element),
        };
    }

    private function matchRelation(SimpleSelector $selector, QueryContext $context, Element $element): bool
    {
        $relationType = $selector->relationType;
        $nextSelector = $selector->next;
        switch ($relationType) {
            case RelationType::RELATIVE_CHILD:
                $context->hasArgumentLeftMostCompoundMatches[] = $element;
            // fallthrough
            case RelationType::CHILD:
                if ($this->isLeftMostScopeForFragment($context, $selector)) return true;
                $parent = $element->parentElement;
                if (!$parent) return false;
                return $this->matchSelector($nextSelector, $context, $parent);
            case RelationType::RELATIVE_DESCENDANT:
                $context->hasArgumentLeftMostCompoundMatches[] = $element;
            // fallthrough
            case RelationType::DESCENDANT:
                if ($this->isLeftMostScopeForFragment($context, $selector)) {
                    return true;
                }
                foreach (ElementTraversal::ancestorsOf($element) as $ancestor) {
                    if ($this->matchSelector($nextSelector, $context, $ancestor)) {
                        return true;
                    }
                }
                return false;
            case RelationType::RELATIVE_ADJACENT:
                $context->hasArgumentLeftMostCompoundMatches[] = $element;
            // fallthrough
            case RelationType::ADJACENT:
                $previous = $element->previousElementSibling;
                if (!$previous) return false;
                return $this->matchSelector($nextSelector, $context, $previous);
            case RelationType::RELATIVE_FOLLOWING:
                $context->hasArgumentLeftMostCompoundMatches[] = $element;
            // fallthrough
            case RelationType::FOLLOWING:
                foreach (ElementTraversal::preceding($element) as $previous) {
                    if ($this->matchSelector($nextSelector, $context, $previous)) {
                        return true;
                    }
                }
                return false;
            default:
                break;
        }
        return false;
    }

    private function isLeftMostScopeForFragment(QueryContext $context, SimpleSelector $selector): bool
    {
        return (
            $selector instanceof ScopePseudo
            && !$selector->next
            && $context->scopingRoot->nodeType === Node::DOCUMENT_FRAGMENT_NODE
        );
    }

    /**
     * @param SimpleSelector $selector
     * @return array{SimpleSelector|null, string}
     */
    private function serializeCompound(SimpleSelector $selector): array
    {
        $css = '';
        for ($current = $selector; $current; $current = $current->next) {
            $css .= $current;
            if ($current->relationType !== RelationType::SUB) {
                return [$current, $css];
            }
        }
        return [$current, $css];
    }
}
