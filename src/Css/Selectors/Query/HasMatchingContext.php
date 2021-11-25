<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use Souplette\Css\Selectors\Node\RelationType;
use Souplette\Css\Selectors\Node\SimpleSelector;

final class HasMatchingContext
{
    const INFINITE_DEPTH = -1;
    const INFINITE_ADJACENT_DISTANCE = -1;

    // Indicate the :has argument relative type and subtree traversal scope.
    // If '$adjacentTraversalDistance' is greater than 0, then it means that
    // it is enough to traverse the adjacent subtree at that distance.
    // If it is -1, it means that all the adjacent subtree need to be traversed.
    // If '$descendantTraversalDepth' is greater than 0, then it means that
    // it is enough to traverse elements at the certain depth. If it is -1,
    // it means that all the descendant subtree need to be traversed.
    //
    // Case 1:  (DESCENDANT, 0, -1)
    //   - Argument selector conditions
    //     - Starts with descendant combinator.
    //   - E.g. ':has(.a)', ':has(:scope .a)', ':has(.a ~ .b > .c)'
    //   - Traverse all descendants of the :has scope element.
    // Case 2:  (CHILD, 0, -1)
    //   - Argument selector conditions
    //     - Starts with child combinator.
    //     - At least one descendant combinator.
    //   - E.g. ':has(:scope > .a .b)', ':has(:scope > .a ~ .b .c)'
    //   - Traverse all descendants of the :has scope element.
    // Case 3:  (CHILD, 0, n)
    //   - Argument selector conditions
    //     - Starts with child combinator.
    //     - n number of child combinator. (n > 0)
    //     - No descendant combinator.
    //   - E.g.
    //     - ':has(:scope > .a)'            : (CHILD, 0, 1)
    //     - ':has(:scope > .a ~ .b > .c)'  : (CHILD, 0, 2)
    //   - Traverse the depth n descendants of the :has scope element.
    // Case 4:  (FOLLOWING, -1, -1)
    //   - Argument selector conditions
    //     - Starts with subsequent-sibling combinator.
    //     - At least one descendant combinator.
    //   - E.g. ':has(:scope ~ .a .b)', ':has(:scope ~ .a + .b > .c ~ .d .e)'
    //   - Traverse all the subsequent sibling subtrees of the :has scope element.
    //     (all subsequent siblings and it's descendants)
    // Case 5:  (FOLLOWING, -1, 0)
    //   - Argument selector conditions
    //     - Starts with subsequent-sibling combinator.
    //     - No descendant/child combinator.
    //   - E.g. ':has(:scope ~ .a)', ':has(:scope ~ .a + .b ~ .c)'
    //   - Traverse all subsequent siblings of the :has scope element.
    // Case 6:  (FOLLOWING, -1, n)
    //   - Argument selector conditions
    //     - Starts with subsequent-sibling combinator.
    //     - n number of child combinator. (n > 0)
    //     - No descendant combinator.
    //   - E.g.
    //     - ':has(:scope ~ .a > .b)'                 : (FOLLOWING, -1, 1)
    //     - ':has(:scope ~ .a + .b > .c ~ .d > .e)'  : (FOLLOWING, -1, 2)
    //   - Traverse depth n elements of all subsequent sibling subtree of the
    //     :has scope element.
    // Case 7:  (ADJACENT, -1, -1)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - At least one subsequent-sibling combinator to the left of every
    //       descendant or child combinator.
    //     - At least 1 descendant combinator.
    //   - E.g. ':has(:scope + .a ~ .b .c)', ':has(:scope + .a ~ .b > .c + .e .f)'
    //   - Traverse all the subsequent sibling subtrees of the :has scope element.
    //     (all subsequent siblings and it's descendants)
    // Case 8:  (ADJACENT, -1, 0)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - At least one subsequent-sibling combinator.
    //     - No descendant/child combinator.
    //   - E.g. ':has(:scope + .a ~ .b)', ':has(:scope + .a + .b ~ .c)'
    //   - Traverse all subsequent siblings of the :has scope element.
    // Case 9:  (ADJACENT, -1, n)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - At least one subsequent-sibling combinator to the left of every
    //       descendant or child combinator.
    //     - n number of child combinator. (n > 0)
    //     - No descendant combinator.
    //   - E.g.
    //     - ':has(:scope + .a ~ .b > .c)'            : (ADJACENT, -1, 1)
    //     - ':has(:scope + .a ~ .b > .c + .e >.f)'   : (ADJACENT, -1, 2)
    //   - Traverse depth n elements of all subsequent sibling subtree of the
    //     :has scope element.
    // Case 10:  (ADJACENT, n, -1)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - n number of next-sibling combinator to the left of the leftmost
    //       child(or descendant) combinator. (n > 0)
    //     - No subsequent-sibling combinator to the left of the leftmost child
    //       (or descendant) combinator.
    //     - At least 1 descendant combinator.
    //   - E.g.
    //     - ':has(:scope + .a .b)'            : (ADJACENT, 1, -1)
    //     - ':has(:scope + .a > .b + .c .d)'  : (ADJACENT, 1, -1)
    //     - ':has(:scope + .a + .b > .c .d)'  : (ADJACENT, 2, -1)
    //   - Traverse the distance n sibling subtree of the :has scope element.
    //     (sibling element at distance n, and it's descendants).
    // Case 11:  (ADJACENT, n, 0)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - n number of next-sibling combinator. (n > 0)
    //     - No child/descendant/subsequent-sibling combinator.
    //   - E.g.
    //     - ':has(:scope + .a)'            : (ADJACENT, 1, 0)
    //     - ':has(:scope + .a + .b + .c)'  : (ADJACENT, 3, 0)
    //   - Traverse the distance n sibling element of the :has scope element.
    // Case 12:  (ADJACENT, n, m)
    //   - Argument selector conditions
    //     - Starts with next-sibling combinator.
    //     - n number of next-sibling combinator to the left of the leftmost
    //       child combinator. (n > 0)
    //     - No subsequent-sibling combinator to the left of the leftmost child
    //       combinator.
    //     - n number of child combinator. (n > 0)
    //     - No descendant combinator.
    //   - E.g.
    //     - ':has(:scope + .a > .b)'                 : (ADJACENT, 1, 1)
    //     - ':has(:scope + .a + .b > .c ~ .d > .e)'  : (ADJACENT, 2, 2)
    //   - Traverse the depth m elements of the distance n sibling subtree of
    //     the :has scope element. (elements at depth m of the descendant subtree
    //     of the sibling element at distance n)
    public RelationType $leftMostRelation = RelationType::SUB;
    public int $descendantTraversalDepth = 0;
    public int $adjacentTraversalDistance = 0;

    public function __construct(SimpleSelector $selector)
    {
        for (
            [$relation, $selector] = $this->getCurrentRelationAndNextCompound($selector);
            $selector;
            [$relation, $selector] = $this->getCurrentRelationAndNextCompound($selector)
        ) {
            switch ($relation) {
                case RelationType::RELATIVE_DESCENDANT:
                    $this->leftMostRelation = $relation;
                // fallthrough
                case RelationType::DESCENDANT:
                    $this->descendantTraversalDepth = self::INFINITE_DEPTH;
                    $this->adjacentTraversalDistance = 0;
                    break;
                case RelationType::RELATIVE_CHILD:
                    $this->leftMostRelation = $relation;
                // fallthrough
                case RelationType::CHILD:
                    if ($this->descendantTraversalDepth !== self::INFINITE_DEPTH) {
                        $this->descendantTraversalDepth++;
                        $this->adjacentTraversalDistance = 0;
                    }
                    break;
                case RelationType::RELATIVE_ADJACENT:
                    $this->leftMostRelation = $relation;
                // fallthrough
                case RelationType::ADJACENT:
                    if ($this->adjacentTraversalDistance !== self::INFINITE_ADJACENT_DISTANCE) {
                        $this->adjacentTraversalDistance++;
                    }
                    break;
                case RelationType::RELATIVE_FOLLOWING:
                    $this->leftMostRelation = $relation;
                // fallthrough
                case RelationType::FOLLOWING:
                    $this->adjacentTraversalDistance = self::INFINITE_ADJACENT_DISTANCE;
                    break;
                default:
                    break;
            }
        }
    }

    public function isDepthFixed(): bool
    {
        return $this->descendantTraversalDepth !== self::INFINITE_DEPTH;
    }

    public function isAdjacentDistanceFixed(): bool
    {
        return $this->adjacentTraversalDistance !== self::INFINITE_ADJACENT_DISTANCE;
    }

    private function getCurrentRelationAndNextCompound(SimpleSelector $selector): array
    {
        $relationType = RelationType::SUB;
        for (; $selector; $selector = $selector->next) {
            $relationType = $selector->relationType;
            if ($relationType !== RelationType::SUB) {
                return [$relationType, $selector->next];
            }
        }
        return [$relationType, null];
    }
}
