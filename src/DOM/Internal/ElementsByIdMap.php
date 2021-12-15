<?php declare(strict_types=1);

namespace Souplette\DOM\Internal;

use Souplette\DOM\Element;
use Souplette\DOM\ParentNode;
use Souplette\DOM\Traversal\ElementTraversal;

final class ElementsByIdMap extends TreeOrderedMap
{
    public function get(string $key, ParentNode $parent): ?Element
    {
        $entry = $this->entries[$key] ?? null;
        if (!$entry) return null;
        if ($entry->element) return $entry->element;
        // Iterate to find the node that matches.
        // Nothing will match if an element with children having duplicate IDs is being removed
        // -- the tree traversal will be over an updated tree not having that subtree.
        // In all other cases, a match is expected.
        foreach (ElementTraversal::descendantsOf($parent) as $element) {
            if ($element->getAttribute('id') === $key) {
                return $entry->element = $element;
            }
        }
        // Since we didn't find any elements for this key, remove the key from the map here.
        unset($this->entries[$key]);
        return null;
    }

    /**
     * @return Element[]
     */
    public function getAll(string $key, ParentNode $parent): array
    {
        $entry = $this->entries[$key] ?? null;
        if (!$entry) return [];
        if (!$entry->orderedList) {
            for (
                $element = $entry->element ?? $parent->getFirstElementChild();
                \count($entry->orderedList) < $entry->count;
                $element = $element->getNextElementSibling()
            ) {
                if ($element->getAttribute('id') === $key) {
                    $entry->orderedList[] = $element;
                }
            }
            if (!$entry->element) {
                $entry->element = $entry->orderedList[0] ?? null;
            }
        }
        return $entry->orderedList;
    }
}
