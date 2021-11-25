<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\RelationType;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\HasMatchingContext;
use Souplette\Css\Selectors\Query\HasMatchingSubtreeIterator;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

final class Has extends FunctionalSelector
{
    public function __construct(public SelectorList $selectorList)
    {
        parent::__construct('has', [$this->selectorList]);
    }

    public function simpleSelectors(): iterable
    {
        yield $this;
        yield from $this->selectorList;
    }

    public function __toString(): string
    {
        return ":has({$this->selectorList})";
    }

    public function getSpecificity(): Specificity
    {
        return $this->selectorList->getSpecificity();
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        // unstable implementation ported from Blink
        $subContext = $context->withScope($element);
        /** @var ComplexSelector $selector */
        foreach ($this->selectorList as $selector) {
            // Get the cache item of matching ':has(<selector>)' on the element
            // to skip argument matching on the subtree elements
            //  - If the element was already marked as matched, return true.
            //  - If the element was already checked but not matched,
            //    move to the next argument selector.
            //  - Otherwise, mark the element as checked but not matched.
            $cache = $context->getHasMatchedCache($selector);
            if (isset($cache[$element])) {
                // was already marked as checked
                if ($cache[$element]) {
                    // was already marked as matched
                    return true;
                }
                continue;
            }
            $cache[$element] = false;

            $hasMatchingContext = new HasMatchingContext($selector->selector);
            $isDepthFixed = $hasMatchingContext->isDepthFixed();
            // To prevent incorrect 'NotChecked' status while matching ':has' pseudo
            // class, change the argument matching context scope when the ':has'
            // argument matching traversal cannot be fixed with a certain depth and
            // adjacent distance.
            //
            // For example, When we tries to match '.a:has(.b .c) .d' on below DOM,
            // <div id=d1 class="a">
            //  <div id=d2 class="b">
            //   <div id=d3 class="a">
            //    <div id=d4 class="c">
            //      <div id=d5 class="d"></div>
            //    </div>
            //   </div>
            //  </div>
            // </div>
            // the ':has(.b .c)' selector will be checked on the #d3 element first
            // because the selector '.a:has(.b .c) .d' will be matched upward from
            // the #d5 element.
            //  1) '.d' will be matched first on #d5
            //  2) move to the #d3 until the '.a' matched
            //  3) match the ':has(.b .c)' on the #d3
            //    3.1) match the argument selector '.b .c' on the descendants of #d3
            //  4) move to the #d1 until the '.a' matched
            //  5) match the ':has(.b .c)' on the #d1
            //    5.1) match the argument selector '.b .c' on the descendants of #d1
            //
            // The argument selector '.b .c' will not be matched on the #d4 at this
            // step if the argument matching scope is limited to #d3. But the '.b .c'
            // can be matched on the #d4 if the argument matching scope is #d1.
            // To prevent duplicated argument matching operation, the #d1 should be
            // marked as 'Matched' at the step 3.
            if (!$isDepthFixed) {
                // TODO: this should bee the root of the element's TreeScope
                $subContext->relativeLeftMostElement = $this->getRootElement($element);
            } else if ($hasMatchingContext->isAdjacentDistanceFixed()) {
                if ($parentNode = $element->parentNode) {
                    $subContext->relativeLeftMostElement = $parentNode->firstElementChild;
                } else {
                    $subContext->relativeLeftMostElement = $element;
                }
            } else {
                $subContext->relativeLeftMostElement = $element;
            }

            $selectorMatched = false;
            $iterator = new HasMatchingSubtreeIterator($element, $hasMatchingContext);
            foreach ($iterator as $current) {
                if ($isDepthFixed && $iterator->isAtFixedDepth()) {
                    continue;
                }
                $subContext->hasArgumentLeftMostCompoundMatches = [];

                $result = $selector->matches($subContext, $current);

                switch ($hasMatchingContext->leftMostRelation) {
                    case RelationType::RELATIVE_DESCENDANT:
                        $cache[$current] = false; // mark as checked
                        if ($subContext->hasArgumentLeftMostCompoundMatches) {
                            $node = $subContext->hasArgumentLeftMostCompoundMatches[0];
                            for ($node = $node->parentNode; $node; $node = $node->parentNode) {
                                $cache[$node] = true; // mark as matched
                                if ($node === $element) $selectorMatched = true;
                            }
                        }
                        break;
                    case RelationType::RELATIVE_CHILD:
                        foreach ($subContext->hasArgumentLeftMostCompoundMatches as $leftMost) {
                            $parent = $leftMost->parentNode;
                            $cache[$parent] = true; // mark as matched
                            if ($parent === $element) $selectorMatched = true;
                        }
                        break;
                    case RelationType::RELATIVE_ADJACENT:
                        if (!$isDepthFixed && !$iterator->isAtSiblingOfHasScope()) {
                            $cache[$current] = false; // mark as checked
                        }
                        foreach ($subContext->hasArgumentLeftMostCompoundMatches as $leftMost) {
                            if ($sibling = $leftMost->previousElementSibling) {
                                $cache[$sibling] = true; // mark as matched
                                if ($sibling === $element) $selectorMatched = true;
                            }
                        }
                        break;
                    case RelationType::RELATIVE_FOLLOWING:
                        if ($isDepthFixed) {
                            $cache[$current] = false; // mark as checked
                        }
                        foreach ($subContext->hasArgumentLeftMostCompoundMatches as $leftMost) {
                            for ($sibling = $leftMost->previousElementSibling; $sibling; $sibling = $sibling->previousElementSibling) {
                                $cache[$sibling] = true; // mark as matched
                                if ($sibling === $element) $selectorMatched = true;
                            }
                        }
                        break;
                    default:
                        break;
                }
                if ($selectorMatched) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getRootElement(\DOMElement $element): \DOMElement
    {
        $node = $element;
        while ($node->parentNode && $node->parentNode->nodeType === XML_ELEMENT_NODE) {
            $node = $node->parentNode;
        }

        return $node;
    }
}
