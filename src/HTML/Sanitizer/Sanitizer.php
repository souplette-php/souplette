<?php declare(strict_types=1);

namespace Souplette\HTML\Sanitizer;

use Souplette\DOM\Document;
use Souplette\DOM\DocumentFragment;
use Souplette\DOM\Element;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Node;
use Souplette\DOM\ParentNode;
use Souplette\DOM\Traversal\NodeTraversal;
use Souplette\HTML\Custom\CustomElement;

final class Sanitizer
{
    private static ?self $defaultInstance = null;

    private SanitizerConfig $config;

    private function __construct(SanitizerConfig $config)
    {
        $this->config = $config;
    }

    public static function of(SanitizerConfig $config): self
    {
        return new self($config);
    }

    public static function default(): self
    {
        return self::$defaultInstance ??= new self(SanitizerConfig::default());
    }

    public function sanitize(Document|DocumentFragment $input): DocumentFragment
    {
        $fragment = $this->prepareFragment($input);
        $this->doSanitize($fragment);
        return $fragment;
    }

    public function sanitizeFor(string $localName, string $markup): ?Element
    {
        $name = strtolower($localName);
        if (Baseline::DROP_ELEMENTS[$name] ?? false) {
            return null;
        }
        $doc = new Document();
        $element = $doc->createElement($localName);
        $element->setInnerHTML($markup);
        $this->doSanitize($element);
        // TODO Edge case: The template element treatment also applies to the newly created element in .sanitizeFor.
        //if ($name === 'template') {
        //    $this->doSanitize($element->content);
        //}

        return $element;
    }

    /**
     * @internal
     */
    public function setHTML(Element $element, string $markup): void
    {
        $newElement = $this->sanitizeFor($element->localName, $markup);
        if (!$newElement) {
            return;
        }
        while ($child = $element->_first) {
            $element->removeChild($child);
        }
        while ($newChild = $newElement->_first) {
            $element->appendChild($newChild);
        }
    }

    /**
     * https://wicg.github.io/sanitizer-api/#create-a-document-fragment
     */
    private function prepareFragment(Document|DocumentFragment $input): DocumentFragment
    {
        if ($input instanceof Document) {
            $fragment = $input->createDocumentFragment();
            $nodes = $input->getBody()->getChildNodes();
            foreach ($nodes as $node) {
                $fragment->parserInsertBefore($node->cloneNode(true), null);
            }
            return $fragment;
        }
        return $input;
    }

    private function doSanitize(ParentNode $fragment): void
    {
        $node = $fragment->_first;
        while ($node) {
            $node = match ($node->nodeType) {
                Node::ELEMENT_NODE => $this->sanitizeElement($node, $fragment),
                // Text node: Keep (by skipping over it).
                Node::TEXT_NODE => NodeTraversal::next($node, $fragment),
                // Comment: Drop (unless allowed by config).
                Node::COMMENT_NODE => $this->config->shouldAllowComments()
                    ? NodeTraversal::next($node, $fragment)
                    : $this->drop($node, $fragment),
                // Document & DocumentFragment: Drop (unless it's the root).
                Node::DOCUMENT_NODE, Node::DOCUMENT_FRAGMENT_NODE => !$node->_parent
                    ? NodeTraversal::next($node, $fragment)
                    : $this->drop($node, $fragment),
                // Default: Drop anything not explicitly handled.
                default => $this->drop($node, $fragment),
            };
        }
    }

    private function sanitizeElement(Element $node, ParentNode $fragment): ?Node
    {
        $name = strtolower($node->qualifiedName);
        $ns = $node->namespaceURI;
        // 1. Let kind be element’s element kind.
        // 2. If kind is regular and element does not match any name in the baseline element allow list: Return drop.
        if (Baseline::DROP_ELEMENTS[$name] ?? false) {
            return $this->drop($node, $fragment);
        }
        // 3. If element is of custom kind and if config’s allow custom elements option does not exist
        // or if |config|[allowCustomElements] is false: Return drop.
        $isCustom = CustomElement::isValidName($name);
        if ($isCustom && !$this->config->shouldAllowCustomElements()) {
            return $this->drop($node, $fragment);
        }
        // 4. If element matches any name in config’s element drop list: Return drop.
        if ($this->config->shouldDropElement($name, $ns)) {
            return $this->drop($node, $fragment);
        }
        // 5. If element matches any name in config’s element block list: Return block.
        if ($this->config->shouldBlockElement($name, $ns)) {
            return $this->block($node, $fragment);
        }
        // 6. If element allow list exists in config:
        //    1. Then : Let allow list be |config|["allowElements"].
        //    2. Otherwise: Let allow list be the default configuration's element allow list.
        // 7. If element matches any name in allow list: Return block.
        if (!$this->config->shouldAllowElement($name, $ns)) {
            return $this->block($node, $fragment);
        }
        $this->handleFunkyElements($node, $name, $fragment);
        // 8. Return keep.
        return $this->keepElement($node, $name, $fragment);
    }

    /**
     * If the current element needs to be dropped,
     * remove current element entirely and proceed to its next sibling.
     */
    private function drop(Node $node, ParentNode $fragment): ?Node
    {
        $next = NodeTraversal::nextSkippingChildren($node, $fragment);
        $node->unlink();
        return $next;
    }

    /**
     * If the current element should be blocked,
     * append its children after current node to parent node,
     * remove current element and proceed to the next node.
     */
    private function block(Node $node, ParentNode $fragment): ?Node
    {
        $parent = $node->_parent;
        $next = $node->_next;
        while ($child = $node->_first) {
            try {
                $parent->insertBefore($child, $next);
            } catch (DOMException) {
                return null;
            }
        }
        $next = NodeTraversal::next($node, $fragment);
        $node->unlink();
        return $next;
    }

    /**
     * Remove any attributes to be dropped from the current element,
     * and proceed to the next node (preorder, depth-first traversal).
     */
    private function keepElement(Element $element, string $name, ParentNode $fragment): Node
    {
        if ($this->config->shouldAllowAttribute('*', $name)) {
            // ???
        } else if ($this->config->shouldDropAttribute('*', $name)) {
            foreach ($element->getAttributeNames() as $attributeName) {
                $element->removeAttribute($attributeName);
            }
        } else {
            foreach ($element->getAttributeNames() as $attributeName) {
                // Attributes in drop list or not in allow list while allow list exists will be dropped.
                $shouldDrop = (
                    isset(Baseline::DROP_ATTRIBUTES[$attributeName])
                    || $this->config->shouldDropAttribute($attributeName, $name)
                    || !$this->config->shouldAllowAttribute($attributeName, $name)
                );
                if ($shouldDrop) {
                    $element->removeAttribute($attributeName);
                }
            }
        }
        return NodeTraversal::next($element);
    }

    /**
     * https://wicg.github.io/sanitizer-api/#handle-funky-elements
     */
    private function handleFunkyElements(Element $element, string $name, ParentNode $fragment): void
    {
        if (!$element->isHTML) return;
        switch ($name) {
            // 1. If element’s element interface is HTMLTemplateElement:
            case 'template':
                // TODO: templates
                // 1. Run the steps of the sanitize a document fragment algorithm on element’s content attribute,
                // and replace element’s content attribute with the result.
                // 2. Drop all child nodes of element.
                break;
            // 2. If element’s element interface has a HTMLHyperlinkElementUtils mixin,
            case 'a':
            case 'area':
                // and if element’s protocol property is "javascript:":
                // Remove the href attribute from element.
                if ($this->isJavascriptURL($element->getAttribute('href'))) {
                    $element->removeAttribute('href');
                }
                break;
            // 3. if element’s element interface is HTMLFormElement,
            case 'form':
                // and if element’s action attribute is a [URL] with javascript: protocol:
                // Remove the action attribute from element.
                if ($this->isJavascriptURL($element->getAttribute('action'))) {
                    $element->removeAttribute('action');
                }
                break;
            // 4. if element’s element interface is HTMLInputElement or HTMLButtonElement,
            case 'input':
            case 'button':
                // and if element’s formaction attribute is a [URL] with javascript: protocol:
                // Remove the formaction attribute from element.
                if ($this->isJavascriptURL($element->getAttribute('formaction'))) {
                    $element->removeAttribute('formaction');
                }
                break;
        }
    }

    private function isJavascriptURL(?string $url): bool
    {
        if (!$url) return false;
        $protocol = parse_url(ltrim($url), \PHP_URL_SCHEME);
        return strcasecmp('javascript', $protocol) === 0;
    }
}
