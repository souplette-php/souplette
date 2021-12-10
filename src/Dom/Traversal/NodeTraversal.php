<?php declare(strict_types=1);

namespace Souplette\Dom\Traversal;

use Souplette\Dom\Node;

abstract class NodeTraversal
{
    /**
     * Does a pre-order traversal of the tree to find the next node after this one
     * This uses the same order that tags appear in the source file.
     * If the `$within` argument is non-null, the traversal will stop once the specified node is reached.
     * This can be used to restrict traversal to a particular subtree.
     */
    public static function next(Node $current, ?Node $within = null): ?Node
    {
        if ($current->_first) return $current->_first;
        if ($within && $current === $within) return null;
        if ($current->_next) return $current->_next;
        foreach (self::ancestorsOf($current) as $parent) {
            if ($within && $parent === $within) return null;
            if ($parent->_next) return $parent->_next;
        }
        return null;
    }

    /**
     * Does a reverse pre-order traversal to find the node that comes before the current one in document order.
     * If the `$within` argument is non-null, the traversal will stop once the specified node is reached.
     * This can be used to restrict traversal to a particular subtree.
     */
    public static function previous(Node $current, ?Node $within = null): ?Node
    {
        if ($within && $current === $within) return null;
        if ($prev = $current->_prev) {
            while ($child = $prev->_last) $prev = $child;
            return $prev;
        }
        return $current->_parent;
    }

    /**
     * Like next, but visits parents after their children.
     */
    public static function nextPostOrder(Node $current, ?Node $within = null): ?Node
    {
        if ($within && $current === $within) return null;
        if (!$current->_next) return $current->_parent;
        $next = $current->_next;
        while ($child = $next->_first) $next = $child;
        return $next;
    }

    /**
     * Like previous, but visits parents before their children.
     */
    public static function previousPostOrder(Node $current, ?Node $within = null): ?Node
    {
        if ($current->_last) return $current->_last;
        if ($within && $current === $within) return null;
        if ($current->_prev) return $current->_prev;
        foreach (self::ancestorsOf($current) as $parent) {
            if ($within && $parent === $within) return null;
            if ($parent->_prev) return $parent->_prev;
        }
        return null;
    }

    /**
     * Yields all child nodes of a given parent, in tree order.
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
    public static function descendantsOf(Node $parent): iterable
    {
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

    /**
     * Yields all ancestors of a given node, in reverse tree order.
     * @return iterable<Node>
     */
    public static function ancestorsOf(Node $node): iterable
    {
        while ($node = $node->_parent) {
            yield $node;
        }
    }

    public static function commonAncestor(Node $a, Node $b): ?Node
    {
        if ($a === $b) return $a;
        if ($a->_doc !== $b->_doc) return null;
        $aDepth = $bDepth = 0;
        for ($node = $a; $node; $node = $node->_parent) {
            if ($node === $b) return $b;
            $aDepth++;
        }
        for ($node = $b; $node; $node = $node->_parent) {
            if ($node === $a) return $a;
            $bDepth++;
        }
        $aParent = $a;
        $bParent = $b;
        if ($aDepth > $bDepth) {
            for ($i = $aDepth; $i > $bDepth; $i--) $aParent = $aParent->_parent;
        } else if ($bDepth > $aDepth) {
            for ($i = $bDepth; $i > $aDepth; $i--) $bParent = $bParent->_parent;
        }
        while ($aParent) {
            if ($aParent === $bParent) return $aParent;
            $aParent = $aParent->_parent;
            $bParent = $bParent->_parent;
        }

        assert($bParent === null);
        return null;
    }
}
