<?php declare(strict_types=1);

namespace Souplette\Dom\Traversal;

use Souplette\Dom\Node;

abstract class NodeTraversal
{
    public static function nextPostOrder(Node $current, ?Node $bounds = null): ?Node
    {
        if ($current === $bounds) return null;
        if (!$current->_next) return $current->_parent;
        $next = $current->_next;
        while ($child = $next->_first) $next = $child;
        return $next;
    }

    /**
     * @return iterable<Node>
     */
    public static function childrenOf(Node $parent): iterable
    {
        for ($child = $parent->_first; $child; $child = $child->_next) {
            yield $child;
        }
    }

    /**
     * Yields all descendants of a given parent, in tree order.
     * @return iterable<Node>
     */
    public static function descendantsOf(Node $parent, bool $inclusive = false): iterable
    {
        if ($inclusive) {
            yield $parent;
        }
        $node = $parent->_first;
        while ($node) {
            yield $node;
            if ($node->_first) {
                $node = $node->_first;
                continue;
            }
            while ($node) {
                if ($node === $parent) {
                    break 2;
                }
                if ($node->_next) {
                    $node = $node->_next;
                    continue 2;
                }
                $node = $node->_parent;
            }
        }
    }
}
