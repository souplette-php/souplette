<?php declare(strict_types=1);

namespace Souplette\Dom\Traversal;

use Souplette\Dom\Element;
use Souplette\Dom\Node;
use Souplette\Dom\ParentNode;

abstract class ElementTraversal extends Node
{
    public static function firstChild(?Node $parent, ?callable $filter = null): ?Element
    {
        if (!$parent) return null;
        for ($node = $parent->first; $node; $node = $node->next) {
            if ($node->nodeType === Node::ELEMENT_NODE && (!$filter || $filter($node))) {
                return $node;
            }
        }
        return null;
    }

    /**
     * @return iterable<Element>
     */
    public static function childrenOf(ParentNode $parent): iterable
    {
        for ($node = $parent->first; $node; $node = $node->next) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                yield $node;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function descendantsOf(ParentNode $parent): iterable
    {
        $node = $parent->first;
        while ($node) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                yield $node;
            }
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

    /**
     * @return iterable<Element>
     */
    public static function ancestorsOf(Node $node): iterable
    {
        while (($node = $node->parent) && $node->nodeType === Node::ELEMENT_NODE) {
            yield $node;
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function following(Node $node): iterable
    {
        while ($node = $node->next) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                yield $node;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function preceding(Node $node): iterable
    {
        while ($node = $node->prev) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                yield $node;
            }
        }
    }
}
