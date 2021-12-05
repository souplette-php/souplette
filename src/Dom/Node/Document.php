<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\DocumentModes;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Namespaces;
use Souplette\Xml\QName;

/**
 * @property-read string $compatMode
 * @property-read string $characterSet
 * @property-read string $contentType
 * @property-read ?Element $documentElement
 */
class Document extends ParentNode
{
    const COMPAT_MODE_BACK = 'BackCompat';
    const COMPAT_MODE_CSS1 = 'CSS1Compat';

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly bool $isHTML;

    protected string $mode = DocumentModes::NO_QUIRKS;
    private Implementation $implementation;

    public function __construct(
        public readonly string $type,
    ) {
        $this->nodeType = Node::DOCUMENT_NODE;
        $this->nodeName = '#document';
        $this->isHTML = $type !== 'xml';
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'mode' => $this->mode,
            'compatMode' => $this->getCompatMode(),
            'documentElement' => $this->getFirstElementChild(),
            'implementation' => $this->implementation ??= new Implementation(),
            default => parent::__get($prop),
        };
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getCompatMode(): string
    {
        return $this->mode === DocumentModes::QUIRKS ? self::COMPAT_MODE_BACK : self::COMPAT_MODE_CSS1;
    }

    public function getDocumentElement(): ?Element
    {
        return $this->getFirstElementChild();
    }

    public function createDocumentFragment(): DocumentFragment
    {
        $frag = new DocumentFragment();
        $frag->document = $this;
        return $frag;
    }

    /**
     * @throws InvalidCharacterError
     */
    public function createElement(string $localName): Element
    {
        if (!QName::isValidName($localName)) {
            throw new InvalidCharacterError();
        }
        $namespace = null;
        if ($this->isHTML) {
            $localName = strtolower($localName);
            $namespace = Namespaces::HTML;
        }
        $node = new Element($localName, $namespace);
        $node->document = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError|NamespaceError
     */
    public function createElementNS(?string $namespace, string $qualifiedName): Element
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $node = new Element($localName, $namespace, $prefix);
        $node->document = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError
     */
    public function createAttribute(string $localName): Attr
    {
        if (!QName::isValidName($localName)) {
            throw new InvalidCharacterError();
        }
        if ($this->isHTML) {
            $localName = strtolower($localName);
        }
        $node = new Attr($localName);
        $node->document = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError|NamespaceError
     */
    public function createAttributeNS(?string $namespace, string $qualifiedName): Attr
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $node = new Attr($localName, $namespace, $prefix);
        $node->document = $this;
        return $node;
    }

    public function createTextNode(string $data): Text
    {
        $node = new Text($data);
        $node->document = $this;
        return $node;
    }

    public function createCDATASection(string $data): CDATASection
    {
        $node = new CDATASection($data);
        $node->document = $this;
        return $node;
    }

    public function createComment(string $data): Comment
    {
        $node = new Comment($data);
        $node->document = $this;
        return $node;
    }

    public function cloneNode(bool $deep = false): static
    {
        // TODO: encoding, contentType, URL, origin, mode
        $copy = new self($this->type);
        $copy->document = $this->document;
        if ($deep) {
            for ($child = $this->first; $child; $child = $this->next) {
                $childCopy = $child->cloneNode(true);
                $copy->adopt($childCopy);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }

    protected function getDocument(): ?Document
    {
        return $this;
    }

    protected const VALID_CHILD_TYPES = ParentNode::VALID_CHILD_TYPES + [
        Node::DOCUMENT_TYPE_NODE => true,
    ];

    protected function ensurePreInsertionValidity(Node $node, ?Node $child): void
    {
        parent::ensurePreInsertionValidity($node, $child);
        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        switch ($node->nodeType) {
            case Node::TEXT_NODE:
            case Node::CDATA_SECTION_NODE:
                // first half of step 5: If node is a Text node and parent is a document
                throw new HierarchyRequestError();
            case Node::DOCUMENT_FRAGMENT_NODE:
                // If node has more than one element child or has a Text node child.
                $nodeChildElementCount = $node->getChildElementCount();
                if ($nodeChildElementCount > 1 || $node->hasChildOfType(self::TEXT_NODE)) {
                    throw new HierarchyRequestError();
                }
                // Otherwise, if node has one element child and either parent has an element child,
                //  child is a doctype, or child is non-null and a doctype is following child.
                if ($nodeChildElementCount === 1
                    && (
                        $this->getChildElementCount() > 0
                        || ($child && $child->nodeType === self::DOCUMENT_TYPE_NODE)
                        || ($child && $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE))
                    )
                ) {
                    throw new HierarchyRequestError();
                }
                break;
            case Node::ELEMENT_NODE:
                // parent has an element child, child is a doctype, or child is non-null and a doctype is following child.
                if ($this->getChildElementCount() > 0
                    || ($child && $child->nodeType === self::DOCUMENT_TYPE_NODE)
                    || ($child && $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE))
                ) {
                    throw new HierarchyRequestError();
                }
                break;
            case Node::DOCUMENT_TYPE_NODE:
                // parent has a doctype child, child is non-null and an element is preceding child,
                // or child is null and parent has an element child.
                if ($this->hasChildOfType(self::DOCUMENT_TYPE_NODE)
                    || ($child && $child->hasPrecedingSiblingOfType(self::ELEMENT_NODE))
                    || (!$child && $this->hasChildOfType(self::ELEMENT_NODE))
                ) {
                    throw new HierarchyRequestError();
                }
                break;
            default:
                break;
        }
    }

    /**
     * https://dom.spec.whatwg.org/#concept-node-replace
     *
     * @throws HierarchyRequestError
     * @throws NotFoundError
     */
    protected function ensureReplacementValidity(Node $child, Node $node): void
    {
        parent::ensurePreInsertionValidity($node, $child);
        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        switch ($node->nodeType) {
            case self::DOCUMENT_FRAGMENT_NODE:
                // If node has more than one element child or has a Text node child.
                // Otherwise, if node has one element child and either parent has an element child
                // that is not child or a doctype is following child.
                $nodeChildElementCount = $node->getChildElementCount();
                if ($nodeChildElementCount > 1
                    || $node->hasChildOfType(self::TEXT_NODE)
                    || ($nodeChildElementCount === 1 && (
                        $this->hasChildOfTypeThatIsNotChild(self::ELEMENT_NODE, $child)
                        || $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE)
                    ))
                ) {
                    throw new HierarchyRequestError();
                }
                break;
            case self::ELEMENT_NODE:
                // parent has an element child that is not child or a doctype is following child.
                if ($this->hasChildOfTypeThatIsNotChild(self::ELEMENT_NODE, $child)
                    || $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE)
                ) {
                    throw new HierarchyRequestError();
                }
                break;
            case self::DOCUMENT_TYPE_NODE:
                // parent has a doctype child that is not child, or an element is preceding child.
                if ($this->hasChildOfTypeThatIsNotChild(self::DOCUMENT_TYPE_NODE, $child)
                    || $child->hasPrecedingSiblingOfType(self::ELEMENT_NODE)
                ) {
                    throw new HierarchyRequestError();
                }
                break;
        }
    }
}
