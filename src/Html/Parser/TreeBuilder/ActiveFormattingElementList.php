<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder;

use JoliPotage\Html\Namespaces;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-list-of-active-formatting-elements
 */
final class ActiveFormattingElementList extends Stack
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

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#push-onto-the-list-of-active-formatting-elements
     * @param \DOMElement|null $value
     */
    public function push($value)
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
     * @param \DOMElement $value
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
    public function containsTag(string $tagName, string $namespace = Namespaces::HTML): \DOMElement|false
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
