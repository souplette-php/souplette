<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Dom\Namespaces;

final class OpenElementsStack extends Stack
{
    private const SCOPE_BASE = [
        Namespaces::HTML => [
            'applet' => true,
            'caption' => true,
            'html' => true,
            'table' => true,
            'td' => true,
            'th' => true,
            'marquee' => true,
            'object' => true,
            'template' => true,
        ],
        Namespaces::MATHML => [
            'mi' => true,
            'mo' => true,
            'mn' => true,
            'ms' => true,
            'mtext' => true,
            'annotation-xml' => true,
        ],
        Namespaces::SVG => [
            'foreignObject' => true,
            'desc' => true,
            'title' => true,
        ],
    ];

    private const SCOPE_LIST_ITEM = [
        Namespaces::HTML => [
            'ol' => true,
            'ul' => true,
        ],
    ];

    private const SCOPE_BUTTON = [
        Namespaces::HTML => [
            'button' => true,
        ],
    ];

    private const SCOPE_TABLE = [
        Namespaces::HTML => [
            'html' => true,
            'table' => true,
            'template' => true,
        ],
    ];

    private const SCOPE_SELECT = [
        Namespaces::HTML => [
            'optgroup' => true,
            'option' => true,
        ],
    ];

    private static array $SCOPE_BUTTON;
    private static array $SCOPE_LIST_ITEM;

    public function __construct()
    {
        self::$SCOPE_BUTTON ??= array_merge_recursive(self::SCOPE_BASE, self::SCOPE_BUTTON);
        self::$SCOPE_LIST_ITEM ??= array_merge_recursive(self::SCOPE_BASE, self::SCOPE_LIST_ITEM);
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
            if (\in_array($node->localName, $tagNames, true) && $node->namespaceURI === $namespace) {
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
            if ($node->namespaceURI === Namespaces::HTML
                || Elements::isMathMlTextIntegrationPoint($node)
                || Elements::isHtmlIntegrationPoint($node)
            ) {
                return;
            }
            $this->pop();
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-scope
     *
     * @param \DOMElement $target
     * @return bool
     */
    public function hasElementInScope(\DOMElement $target): bool
    {
        $scope = self::SCOPE_BASE;
        foreach ($this as $node) {
            // If node is the target node, terminate in a match state.
            if ($node === $target) return true;
            // Otherwise, if node is one of the element types in list, terminate in a failure state.
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }
        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-scope
     *
     * @param string[] $tagNames
     * @param string $namespace
     * @return bool
     */
    public function hasTagsInScope(array $tagNames, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::SCOPE_BASE;
        foreach ($this as $node) {
            if (\in_array($node->localName, $tagNames, true) && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-scope
     *
     * @param string $tagName
     * @param string $namespace
     * @return bool
     */
    public function hasTagInScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::SCOPE_BASE;
        foreach ($this as $node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-list-item-scope
     *
     * @param string $tagName
     * @param string $namespace
     * @return bool
     */
    public function hasTagInListItemScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::$SCOPE_LIST_ITEM;
        foreach ($this as $node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-button-scope
     *
     * @param string $tagName
     * @param string $namespace
     * @return bool
     */
    public function hasTagInButtonScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::$SCOPE_BUTTON;
        foreach ($this as $node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-table-scope
     *
     * @param string $tagName
     * @param string $namespace
     * @return bool
     */
    public function hasTagInTableScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::SCOPE_TABLE;
        foreach ($this as $node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-table-scope
     *
     * @param string[] $tagNames
     * @param string $namespace
     * @return bool
     */
    public function hasTagsInTableScope(array $tagNames, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::SCOPE_TABLE;
        foreach ($this as $node) {
            if (\in_array($node->localName, $tagNames, true) && $node->namespaceURI === $namespace) return true;
            if (isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-select-scope
     *
     * @param string $tagName
     * @param string $namespace
     * @return bool
     */
    public function hasTagInSelectScope(string $tagName, string $namespace = Namespaces::HTML): bool
    {
        $scope = self::SCOPE_SELECT;
        foreach ($this as $node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespace) return true;
            if (!isset($scope[$node->namespaceURI][$node->localName])) return false;
        }

        return false;
    }

    /**
     * Helper method for adoption agency algorithm, section 4.7
     * @return array{\DOMElement, int}|null
     */
    public function furthestBlockForFormattingElement(\DOMElement $formattingElement): ?array
    {
        // 4.7 Let furthest block be the topmost node in the stack of open elements
        //     that is lower in the stack than formatting element, and is an element in the special category.
        //     There might not be one.
        $index = 0;
        $furthestBlock = null;
        foreach ($this as $node) {
            if ($node === $formattingElement) {
                return $furthestBlock ? [$furthestBlock, $index] : null;
            } else if (isset(Elements::SPECIAL[$node->namespaceURI][$node->localName])) {
                $furthestBlock = $node;
            }
            $index++;
        }
        return null;
    }

    /**
     * This method is inlined in the more specific public methods and is kept here as a reference.
     * @codeCoverageIgnore
     *
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

    /**
     * This method is inlined in the more specific public methods and is kept here as a reference.
     * @codeCoverageIgnore
     *
     * @see https://html.spec.whatwg.org/multipage/parsing.html#has-an-element-in-the-specific-scope
     *
     * @param array $scope
     * @param string $tagName
     * @param string $namespace
     * @param bool $invert
     * @return bool
     */
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
            // invert is needed for the select scope case which contains any element except <option> and <optgroup>
            if ($invert ^ $inScope) {
                return false;
            }
        }

        return false;
    }
}
