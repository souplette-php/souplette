<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-list-of-active-formatting-elements
 */
final class ActiveFormattingElementList extends \SplStack
{
    public static function areNodesEqual(\DOMElement $node, \DOMElement $other): bool
    {
        if ($node->localName !== $other->localName) {
            return false;
        }
        if ($node->namespaceURI !== $other->namespaceURI) {
            return false;
        }
        if ($node->attributes->length !== $other->attributes->length) {
            return false;
        }
        foreach ($node->attributes as $attr) {
            $otherAttr = $other->attributes->getNamedItem($attr->name);
            if (!$otherAttr) {
                return false;
            }
            if ($attr->namespaceURI !== $otherAttr->namespaceURI) {
                return false;
            }
            if ($attr->nodeValue !== $otherAttr->nodeValue) {
                return false;
            }
        }

        return true;
    }

    public function clear()
    {
        while (!$this->isEmpty()) {
            $this->pop();
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#push-onto-the-list-of-active-formatting-elements
     * @param \DOMElement|null $value
     */
    public function push($value)
    {
        $equalCount = 0;
        if ($value !== null) {
            foreach ($this as $i => $entry) {
                if ($entry === null) {
                    break;
                }
                if (self::areNodesEqual($entry, $value)) {
                    $equalCount++;
                }
                if ($equalCount === 3) {
                    $this->offsetUnset($i);
                }
            }
        }
        parent::push($value);
    }

    public function indexOf(\DOMElement $element): int
    {
        foreach ($this as $i => $entry) {
            if ($entry === $element) {
                return $i;
            }
        }

        return -1;
    }

    public function insert(int $offset, \DOMElement $element)
    {
        $stack = iterator_to_array($this);
        array_splice($stack, $offset, 0, $element);
        $this->clear();
        foreach ($stack as $entry) {
            $this->push($entry);
        }
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
     * @param \DOMElement $element
     * @return bool
     */
    public function contains(\DOMElement $element): bool
    {
        foreach ($this as $i => $entry) {
            if ($entry === null) {
                break;
            }
            if ($entry === $element) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if an element exists between the end of the active formatting elements and the last marker.
     * If it does, return it, else return false
     *
     * @param string $tagName
     * @param string $namespace
     * @return \DOMElement|false
     */
    public function containsTag(string $tagName, string $namespace = Namespaces::HTML)
    {
        foreach ($this as $i => $entry) {
            if ($entry === null) {
                break;
            }
            if ($entry->localName === $tagName && $entry->namespaceURI === $namespace) {
                return $entry;
            }
        }
        return false;
    }

    public function remove(\DOMElement $element): bool
    {
        foreach ($this as $i => $node) {
            if ($node === $element) {
                $this->offsetUnset($i);
                return true;
            }
        }
        return false;
    }

    public function replace(\DOMElement $old, \DOMElement $new): bool
    {
        foreach ($this as $i => $node) {
            if ($node === $old) {
                $this->offsetSet($i, $new);
                return true;
            }
        }
        return false;
    }
}
