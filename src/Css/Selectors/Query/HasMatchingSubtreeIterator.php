<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use Souplette\Dom\Element;

/**
 * Subtree traversal iterator for ':has' argument matching.
 * To solve the following problems, this traversal uses the right-to-left postorder tree traversal,
 * and provides a functionality to limit the traversal depth.
 *
 * 1. Prevent incorrect 'NotMatched' cache status marked in the ':has'
 * argument selector matching iteration.
 *
 * With the pre-order tree traversal, the previous ':has' matching logic
 * cannot guarantee that an element with 'NotMatched' status is actually
 * 'checked the :has selector on the element but not matched'.
 * To skip the duplicated argument selector matching on the descendant
 * subtree of an element, in the :has argument matching iteration,
 * SelectorChecker marks every descendant elements as 'NotMatched' if
 * the element status is not 'Matched'. This logic works when the subtree
 * doesn't have any argument matched element, or only 1 element. But
 * if the subtree has more than 2 argument matching elements and one of
 * them is an ancestor of the other, the pre-order tree traversal cannot
 * guarantee the 'NotMatched' status of the ancestor element because it
 * traverse root first before traversing it's descendants.
 * The right-to-left post-order traversal can guarantee the logic of
 * marking 'NotMatched' in the ':has' argument matching iteration
 * because it guarantee that the descendant subtree of the element and
 * the downward subtree(succeeding siblings and it's descendants) of the
 * element was already checked. (If any of the previous traversals have
 * matched the argument selector, the element marked as 'Matched' when
 * it was the :has scope element of the match)
 *
 * 2. Prevent unnecessary subtree traversal when it can be limited with
 * child combinator or direct sibling combinator.
 *
 * We can limit the tree traversal range when we count the leftmost
 * combinators of a ':has' argument selector. For example, when we have
 * 'div:has(:scope > .a > .b)', instead of traversing all the descendants
 * of div element, we can limit the traversal only for the elements at
 * depth 2 of the div element. When we have 'div:has(:scope + .a > .b)',
 * we can limit the traversal only for the child elements of the direct
 * adjacent sibling of the div element. To implement this, we need a
 * way to limit the traversal depth and a way to check whether the
 * iterator is currently at the fixed depth or not.
 */
final class HasMatchingSubtreeIterator implements \Iterator
{
    private ?Element $current = null;
    private ?Element $traversalEnd = null;
    private int $depth = 0;
    private int $depthLimit = 0;

    public function __construct(
        private Element $scopeElement,
        private HasMatchingContext $context,
    ) {
    }

    public function isAtFixedDepth(): bool
    {
        return $this->depth === $this->depthLimit;
    }

    public function isAtSiblingOfHasScope(): bool
    {
        return $this->depth === 0;
    }

    public function valid(): bool
    {
        return $this->current !== null;
    }

    public function current(): ?Element
    {
        return $this->current;
    }

    public function key(): int
    {
        return $this->depth;
    }

    public function next(): void
    {
        if ($this->current === $this->traversalEnd) {
            $this->current = null;
            return;
        }
        // Move to the previous element in DOM tree order within the depth limit.
        if ($next = $this->current->previousElementSibling) {
            $lastDescendant = $this->lastDescendantOf($next);
            $this->current = $lastDescendant ?? $next;
        } else {
            $this->depth--;
            $this->current = $this->current->parentNode;
        }
    }

    public function rewind(): void
    {
        $isAdjacentDistanceFixed = $this->context->adjacentTraversalDistance !== HasMatchingContext::INFINITE_ADJACENT_DISTANCE;
        $adjacentDistanceLimit = $isAdjacentDistanceFixed ? $this->context->adjacentTraversalDistance : PHP_INT_MAX;
        $isDepthFixed = $this->context->descendantTraversalDepth !== HasMatchingContext::INFINITE_DEPTH;
        $this->depthLimit = $isDepthFixed ? $this->context->descendantTraversalDepth : PHP_INT_MAX;
        $this->depth = 0;
        $this->current = $this->traversalEnd = null;

        if (!$isAdjacentDistanceFixed) {
            // Set the $traversalEnd as the next sibling of the :has scope element,
            // and move to the last sibling of the :has scope element,
            // and move again to the last descendant of the last sibling.
            $traversalEnd = $this->scopeElement->nextElementSibling;
            if (!$traversalEnd) return;
            $lastSibling = $this->scopeElement->parentNode->lastElementChild;
            $current = $this->lastDescendantOf($lastSibling);
            if (!$current) $current = $lastSibling;
        } else if ($adjacentDistanceLimit === 0) {
            // Set the $traversalEnd as the first child of the :has scope element,
            // and move to the last descendant of the :has scope element without exceeding the depth limit.
            $traversalEnd = $this->scopeElement->firstElementChild;
            if (!$traversalEnd) return;
            $current = $this->lastDescendantOf($this->scopeElement);
        } else {
            // Set the $traversalEnd as the element at the adjacent distance of the :has scope element,
            // and move to the last descendant of the element without exceeding the depth limit.
            $distance = 1;
            $traversalEnd = $this->scopeElement->nextElementSibling;
            while ($distance < $adjacentDistanceLimit && $traversalEnd) {
                $distance++;
                $traversalEnd = $traversalEnd->nextElementSibling;
            }
            if (!$traversalEnd) return;
            $current = $this->lastDescendantOf($traversalEnd);
            if (!$current) $current = $traversalEnd;
        }

        $this->current = $current;
        $this->traversalEnd = $traversalEnd;
    }

    private function lastDescendantOf(Element $element): ?Element
    {
        if ($this->depth === $this->depthLimit) return null;
        // Return the rightmost bottom element of the element without exceeding the depth limit.
        $lastDescendant = null;
        for ($descendant = $element->lastElementChild; $descendant; $descendant = $descendant->lastElementChild) {
            $lastDescendant = $descendant;
            if (++$this->depth === $this->depthLimit) break;
        }
        return $lastDescendant;
    }
}
