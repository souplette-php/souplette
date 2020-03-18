<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;

final class OpenElementsStack extends \SplStack
{
    private static $SCOPE_BUTTON;
    private static $SCOPE_LIST_ITEM;

    public function __construct()
    {
        if (!self::$SCOPE_BUTTON) {
            self::$SCOPE_BUTTON = array_merge_recursive(Elements::SCOPE_BASE, Elements::SCOPE_BUTTON);
        }
        if (!self::$SCOPE_LIST_ITEM) {
            self::$SCOPE_LIST_ITEM = array_merge_recursive(Elements::SCOPE_BASE, Elements::SCOPE_LIST_ITEM);
        }
    }

    public function clear()
    {
        while (!$this->isEmpty()) {
            $this->pop();
        }
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

    public function indexOf(\DOMElement $element): int
    {
        foreach ($this as $i => $node) {
            if ($node === $element) {
                return $i;
            }
        }

        return -1;
    }

    public function insert(int $offset, \DOMElement $element): void
    {
        $lastIndex = $this->count() - 1;
        if ($offset === 0 || $offset < -$lastIndex - 2) {
            $this->unshift($element);
            return;
        }
        if ($offset === -1 || $offset > $lastIndex) {
            $this->push($element);
            return;
        }
        if ($offset < 0) {
            $this->add(-$offset - 2, $element);
            return;
        }
        $this->add($lastIndex - $offset, $element);
    }

    public function contains(\DOMElement $element): bool
    {
        foreach ($this as $node) {
            if ($node === $element) {
                return true;
            }
        }

        return false;
    }

    public function containsTag(string $name): bool
    {
        foreach ($this as $node) {
            if ($node->localName === $name) {
                return true;
            }
        }

        return false;
    }

    public function popUntil(\DOMElement $element)
    {
        while (!$this->isEmpty()) {
            $node = $this->pop();
            if ($node === $element) {
                return $node;
            }
        }

        return null;
    }

    public function popUntilTag(string $tagName, string $namespace = Namespaces::HTML)
    {
        while (!$this->isEmpty()) {
            $node = $this->pop();
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) {
                return $node;
            }
        }

        return null;
    }

    public function popUntilOneOf(array $tagNames, string $namespace = Namespaces::HTML)
    {
        while (!$this->isEmpty()) {
            $node = $this->pop();
            if (in_array($node->localName, $tagNames, true) && $node->namespaceURI === $namespace) {
                return $node;
            }
        }

        return null;
    }

    public function hasElementInScope(\DOMElement $target): bool
    {
        return $this->hasElementInSpecificScope($target, Elements::SCOPE_BASE);
    }

    public function hasElementInListItemScope(\DOMElement $target): bool
    {
        return $this->hasElementInSpecificScope($target, self::$SCOPE_LIST_ITEM);
    }

    public function hasElementInButtonScope(\DOMElement $target): bool
    {
        return $this->hasElementInSpecificScope($target, self::$SCOPE_BUTTON);
    }

    public function hasElementInTableScope(\DOMElement $target): bool
    {
        return $this->hasElementInSpecificScope($target, Elements::SCOPE_TABLE);
    }

    public function hasElementInSelectScope(\DOMElement $target): bool
    {
        return !$this->hasElementInSpecificScope($target, Elements::SCOPE_SELECT);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-the-specific-scope
     *
     * @param \DOMElement $targetNode
     * @param array $scope
     * @return bool
     */
    private function hasElementInSpecificScope(\DOMElement $targetNode, array $scope): bool
    {
        foreach ($this as $i => $node) {
            // If node is the target node, terminate in a match state.
            if ($node === $targetNode) {
                return true;
            }
            // Otherwise, if node is one of the element types in list, terminate in a failure state.
            if (isset($scope[$node->namespaceURI][$node->localName])) {
                return false;
            }
        }

        return false;
    }

    public function hasTagsInScope(array $tagNames, string $namespace = Namespaces::HTML): bool
    {
        foreach ($tagNames as $tagName) {
            if ($this->hasTagInScope($tagName, $namespace)) {
                return true;
            }
        }
        return false;
    }

    public function hasTagInScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        return $this->hasTagInSpecificScope(Elements::SCOPE_BASE, $tagName, $namespace);
    }

    public function hasTagInListItemScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        return $this->hasTagInSpecificScope(self::$SCOPE_LIST_ITEM, $tagName, $namespace);
    }

    public function hasTagInButtonScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        return $this->hasTagInSpecificScope(self::$SCOPE_BUTTON, $tagName, $namespace);
    }

    public function hasTagInTableScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        return $this->hasTagInSpecificScope(Elements::SCOPE_TABLE, $tagName, $namespace);
    }

    public function hasTagsInTableScope(array $tagNames, string $namespace = Namespaces::HTML): bool
    {
        foreach ($tagNames as $tagName) {
            if ($this->hasTagInTableScope($tagName, $namespace)) {
                return true;
            }
        }
        return false;
    }

    public function hasTagInSelectScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        return !$this->hasTagInSpecificScope(Elements::SCOPE_SELECT, $tagName, $namespace);
    }

    private function hasTagInSpecificScope(array $scope, string $tagName, string $namespace = Namespaces::HTML): bool
    {
        foreach ($this as $i => $node) {
            // If node is the target node, terminate in a match state.
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) {
                return true;
            }
            // Otherwise, if node is one of the element types in list, terminate in a failure state.
            if (isset($scope[$node->namespaceURI][$node->localName])) {
                return false;
            }
        }

        return false;
    }
}
