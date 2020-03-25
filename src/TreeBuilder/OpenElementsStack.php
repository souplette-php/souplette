<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;

final class OpenElementsStack extends Stack
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
        parent::__construct();
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

    /**
     * Used in thr rules for parsing a token in foreign content.
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inforeign
     * The algorithm in the spec is not quite the same, but Blink and html5lib use this one.
     */
    public function popUntilForeignContentScopeMarker(): void
    {
        while (true) {
            $node = $this->top();
            if (Elements::isMathMlTextIntegrationPoint($node)
                || Elements::isHtmlIntegrationPoint($node)
                || $node->namespaceURI === Namespaces::HTML
            ) {
                return;
            }
            $this->pop();
        }
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
        return $this->hasElementInSpecificScope($target, Elements::SCOPE_SELECT, true);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-the-specific-scope
     *
     * @param \DOMElement $targetNode
     * @param array $scope
     * @param bool $invert
     * @return bool
     */
    private function hasElementInSpecificScope(\DOMElement $targetNode, array $scope, bool $invert = false): bool
    {
        foreach ($this as $node) {
            // If node is the target node, terminate in a match state.
            if ($node === $targetNode) {
                return true;
            }
            // Otherwise, if node is one of the element types in list, terminate in a failure state.
            $inScope = isset($scope[$node->namespaceURI][$node->localName]);
            if ($invert ^ $inScope) {
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
        return $this->hasTagInSpecificScope(Elements::SCOPE_SELECT, $tagName, $namespace, true);
    }

    private function hasTagInSpecificScope(
        array $scope,
        string $tagName,
        string $namespace = Namespaces::HTML,
        bool $invert = false
    ): bool {
        foreach ($this as $node) {
            // If node is the target node, terminate in a match state.
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) {
                return true;
            }
            // Otherwise, if node is one of the element types in list, terminate in a failure state.
            $inScope = isset($scope[$node->namespaceURI][$node->localName]);
            //if (($invert && !$inScope) || (!$invert && $inScope)) {
            if ($invert ^ $inScope) {
                return false;
            }
        }

        return false;
    }
}
