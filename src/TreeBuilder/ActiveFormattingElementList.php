<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-list-of-active-formatting-elements
 */
final class ActiveFormattingElementList extends \SplStack
{
    public static function areNodesEqual(\DOMElement $node, \DOMElement $other): bool
    {
        if ($node->tagName !== $other->tagName) {
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
     * @param string $tagName
     * @return \DOMElement|false
     */
    public function contains(string $tagName)
    {
        foreach ($this as $i => $entry) {
            if ($entry === null) {
                break;
            }
            if ($entry->tagName === $tagName) {
                return $entry;
            }
        }
        return false;
    }
}
