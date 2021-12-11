<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-list-of-active-formatting-elements
 * @extends Stack<Element>
 */
final class ActiveFormattingElementList extends Stack
{
    public static function areNodesEqual(Element $node, Element $other): bool
    {
        if ($node->localName !== $other->localName) {
            return false;
        }
        if ($node->namespaceURI !== $other->namespaceURI) {
            return false;
        }
        if (\count($node->_attrs) !== \count($other->_attrs)) {
            return false;
        }
        foreach ($node->_attrs as $attr) {
            $otherAttr = $other->getAttributeNode($attr->name);
            if (!$otherAttr) {
                return false;
            }
            if ($attr->namespaceURI !== $otherAttr->namespaceURI) {
                return false;
            }
            if ($attr->_value !== $otherAttr->_value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#push-onto-the-list-of-active-formatting-elements
     * @param Element|null $value
     */
    public function push($value): void
    {
        $equalCount = 0;
        if ($value !== null) {
            $i = 0;
            foreach ($this as $entry) {
                if ($entry === null) {
                    break;
                }
                if (self::areNodesEqual($entry, $value)) {
                    $equalCount++;
                }
                if ($equalCount === 3) {
                    $this->offsetUnset($i);
                }
                $i++;
            }
        }
        parent::push($value);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#clear-the-list-of-active-formatting-elements-up-to-the-last-marker
     */
    public function clearUpToLastMarker(): void
    {
        while (!$this->isEmpty()) {
            $entry = $this->pop();
            if ($entry === null) {
                break;
            }
        }
    }

    /**
     * Check if an element exists between the end of the active formatting elements and the last marker.
     * If it does, return it, else return false
     *
     * @param Element $value
     * @return bool
     */
    public function contains($value): bool
    {
        foreach ($this as $entry) {
            if ($entry === null) {
                break;
            }
            if ($entry === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if an element exists between the end of the active formatting elements and the last marker.
     * If it does, return it, else return false
     */
    public function containsTag(string $tagName, string $namespace = Namespaces::HTML): Element|false
    {
        foreach ($this as $entry) {
            if ($entry === null) {
                break;
            }
            if ($entry->localName === $tagName && $entry->namespaceURI === $namespace) {
                return $entry;
            }
        }
        return false;
    }
}
