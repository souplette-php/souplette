<?php declare(strict_types=1);

namespace Souplette\Dom\Traversal;

use Souplette\Dom\Element;
use Souplette\Dom\Node;
use Souplette\Dom\ParentNode;

abstract class ElementTraversal
{
    public static function firstChild(?Node $parent, ?callable $filter = null): ?Element
    {
        if (!$parent) return null;
        for ($node = $parent->_first; $node; $node = $node->_next) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                return $node;
            }
        }
        return null;
    }

    /**
     * @return iterable<Element>
     */
    public static function childrenOf(ParentNode $parent, ?callable $filter = null): iterable
    {
        for ($node = $parent->_first; $node; $node = $node->_next) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                yield $node;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function descendantsOf(ParentNode $parent, ?callable $filter = null): iterable
    {
        $node = $parent->_first;
        while ($node) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                yield $node;
            }
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
     * @return iterable<Element>
     */
    public static function ancestorsOf(Node $node, ?callable $filter = null): iterable
    {
        while ($node = $node->_parent) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                yield $node;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function following(Node $node, ?callable $filter = null): iterable
    {
        while ($node = $node->_next) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                yield $node;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function preceding(Node $node, ?callable $filter = null): iterable
    {
        while ($node = $node->_prev) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                yield $node;
            }
        }
    }
}
