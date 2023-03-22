<?php declare(strict_types=1);

namespace Souplette\DOM\Internal;

use Souplette\DOM\Element;
use Souplette\DOM\ParentNode;

/**
 * TreeOrderedMap is a map from keys to elements,
 * which allows multiple values per key, maintained in tree order per key.
 * Tree walks are avoided when possible by retaining a cached, ordered array of matching nodes.
 * Adding or removing an element for a given key often clears the cache,
 * forcing a tree walk upon the next access.
 *
 * @internal
 */
abstract class TreeOrderedMap
{
    /**
     * @var array<string, TreeOrderedMapEntry>
     */
    protected array $entries = [];

    abstract public function get(string $key, ParentNode $parent): ?Element;

    public function add(string $key, Element $element): void
    {
        if ($entry = $this->entries[$key] ?? null) {
            $entry->element = null;
            $entry->count++;
            $entry->orderedList = [];
            return;
        }
        $this->entries[$key] = new TreeOrderedMapEntry($element);
    }

    public function remove(string $key, Element $element): void
    {
        $entry = $this->entries[$key] ?? null;
        if (!$entry) return;
        if ($entry->count === 1) {
            unset($this->entries[$key]);
            return;
        }
        if ($entry->element === $element) {
            $entry->element = \count($entry->orderedList) > 1 ? $entry->orderedList[1] : null;
        }
        $entry->count--;
        $entry->orderedList = [];
    }

    public function has(string $key): bool
    {
        return isset($this->entries[$key]);
    }

    public function hasMultiple(string $key): bool
    {
        if ($entry = $this->entries[$key] ?? null) {
            return $entry->count > 1;
        }
        return false;
    }

    /**
     * Don't use this unless the caller can know the internal state of TreeOrderedMap exactly.
     */
    public function getCacheFirstElementWithoutAccessingNodeTree(string $key): ?Element
    {
        if ($entry = $this->entries[$key] ?? null) {
            return $entry->element;
        }
        return null;
    }
}
