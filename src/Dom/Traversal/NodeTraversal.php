<?php declare(strict_types=1);

namespace Souplette\Dom\Traversal;

use Souplette\Dom\Internal\BaseNode;
use Souplette\Dom\Node;

abstract class NodeTraversal extends BaseNode
{
    public static function nextPostOrder(Node $current, ?Node $bounds = null): ?Node
    {
        if ($current === $bounds) return null;
        if (!$current->next) return $current->parent;
        $next = $current->next;
        while ($child = $next->first) $next = $child;
        return $next;
    }

    /**
     * @return iterable<Node>
     */
    public static function childrenOf(Node $parent): iterable
    {
        for ($child = $parent->first; $child; $child = $child->next) {
            yield $child;
        }
    }

    /**
     * @return iterable<Node>
     */
    public static function descendantsOf(Node $parent): iterable
    {
        $node = $parent->first;
        while ($node) {
            yield $node;
            if ($node->first) {
                $node = $node->first;
                continue;
            }
            while ($node) {
                if ($node === $parent) {
                    break 2;
                }
                if ($node->next) {
                    $node = $node->next;
                    continue 2;
                }
                $node = $node->parent;
            }
        }
    }
}
