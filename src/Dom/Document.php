<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NonElementParentNodeInterface;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Exception\NotSupportedError;
use Souplette\Dom\Internal\CompatMode;
use Souplette\Dom\Internal\DocumentMode;
use Souplette\Dom\Internal\ElementsByIdMap;
use Souplette\Dom\Internal\NodeFlags;
use Souplette\Dom\Traits\GetElementsByClassNameTrait;
use Souplette\Dom\Traits\GetElementsByTagNameTrait;
use Souplette\Dom\Traits\DocumentTreeAccessorsTrait;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Xml\QName;

/**
 * @property-read Implementation $implementation
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read string $characterSet
 * @property-read string $contentType
 * @property-read ?DocumentType $doctype
 * @property-read ?Element $documentElement
 * @property-read ?Element $head
 * @property ?Element $body
 * @property string $title
 */
class Document extends ParentNode implements NonElementParentNodeInterface
{
    use GetElementsByTagNameTrait;
    use GetElementsByClassNameTrait;
    use DocumentTreeAccessorsTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly bool $isHTML;

    public string $encoding = 'UTF-8';

    protected string $type = 'html';
    private ?ElementsByIdMap $elementsById = null;

    /** @internal */
    public Implementation $_implementation;
    /** @internal  */
    public DocumentMode $_mode = DocumentMode::NO_QUIRKS;
    /** @internal */
    public ?DocumentType $_doctype = null;
    /** @internal */
    public string $_contentType = 'application/xml';

    public function __construct() {
        $this->nodeType = Node::DOCUMENT_NODE;
        $this->nodeName = '#document';
        $this->_flags |= NodeFlags::IS_CONTAINER|NodeFlags::IS_CONNECTED;
        if ($this->isHTML = $this->type !== 'xml') {
            $this->_flags |= NodeFlags::NS_TYPE_HTML;
        }
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'implementation' => $this->getImplementation(),
            'mode' => $this->getMode(),
            'compatMode' => $this->getCompatMode(),
            'contentType' => $this->getContentType(),
            'doctype' => $this->getDoctype(),
            'documentElement' => $this->getDocumentElement(),
            'head' => $this->getHead(),
            'body' => $this->getBody(),
            'title' => $this->getTitle(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'textContent', 'nodeValue' => null,
            'body' => $this->setBody($value),
            'title' => $this->setTitle($value),
            default => parent::__set($prop, $value),
        };
    }

    public function getImplementation(): Implementation
    {
        return $this->_implementation ??= new Implementation();
    }

    /**
     * @see https://dom.spec.whatwg.org/#concept-document-mode
     */
    public function getMode(): string
    {
        return $this->_mode->value;
    }

    public function getCompatMode(): string
    {
        return match ($this->_mode) {
            DocumentMode::QUIRKS => CompatMode::BACK->value,
            default => CompatMode::CSS1->value,
        };
    }

    public function getContentType(): string
    {
        return $this->_contentType;
    }

    public function getDoctype(): ?DocumentType
    {
        return $this->_doctype;
    }

    public function getDocumentElement(): ?Element
    {
        return $this->getFirstElementChild();
    }

    public function createDocumentFragment(): DocumentFragment
    {
        $frag = new DocumentFragment();
        $frag->_doc = $this;
        return $frag;
    }

    /**
     * @throws InvalidCharacterError
     */
    public function createElement(string $localName): Element
    {
        if (!QName::isValidName($localName)) {
            throw new InvalidCharacterError(sprintf(
                'Provided element local name "%s" is not a valid name.',
                $localName
            ));
        }
        $namespace = null;
        if ($this->isHTML) {
            $localName = strtolower($localName);
            $namespace = Namespaces::HTML;
        }
        $node = new Element($localName, $namespace);
        $node->_doc = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError|NamespaceError
     */
    public function createElementNS(?string $namespace, string $qualifiedName): Element
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $node = new Element($localName, $namespace, $prefix);
        $node->_doc = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError
     */
    public function createAttribute(string $localName): Attr
    {
        if (!QName::isValidName($localName)) {
            throw new InvalidCharacterError(sprintf(
                'Provided attribute local name "%s" is not a valid name.',
                $localName
            ));
        }
        if ($this->isHTML) {
            $localName = strtolower($localName);
        }
        $node = new Attr($localName);
        $node->_doc = $this;
        return $node;
    }

    /**
     * @throws InvalidCharacterError|NamespaceError
     */
    public function createAttributeNS(?string $namespace, string $qualifiedName): Attr
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $node = new Attr($localName, $namespace, $prefix);
        $node->_doc = $this;
        return $node;
    }

    public function createTextNode(string $data): Text
    {
        $node = new Text($data);
        $node->_doc = $this;
        return $node;
    }

    public function createCDATASection(string $data): CDATASection
    {
        if ($this->isHTML) {
            throw new NotSupportedError('CDATA sections are not supported for HTML documents.');
        }
        if (str_contains($data, ']]>')) {
            throw new InvalidCharacterError(
                'Data cannot contain "]]>" since that is the end delimiter of a CDATA section.'
            );
        }
        $node = new CDATASection($data);
        $node->_doc = $this;
        return $node;
    }

    public function createComment(string $data): Comment
    {
        $node = new Comment($data);
        $node->_doc = $this;
        return $node;
    }

    public function createProcessingInstruction(string $target, string $data): ProcessingInstruction
    {
        if (!QName::isValidName($target)) {
            throw new InvalidCharacterError(sprintf(
                'Provided target "%s" is not a valid name.',
                $target,
            ));
        }
        if (str_contains($data, '?>')) {
            throw new InvalidCharacterError(
                'Data cannot contain "?>" since that is the end delimiter of a processing instruction.'
            );
        }
        $node = new ProcessingInstruction($target, $data);
        $node->_doc = $this;
        return $node;
    }

    public function getTextContent(): ?string
    {
        return null;
    }

    public function setTextContent(?string $value): void
    {
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-document-importnode
     * @throws NotSupportedError
     */
    public function importNode(Node $node, bool $deep = false): Node
    {
        // 1. If node is a document or shadow root, then throw a "NotSupportedError" DOMException.
        if ($node->nodeType === Node::DOCUMENT_NODE) {
            throw new NotSupportedError(
                'The node provided is of type `#document`, which may not be adopted.'
            );
        }
        // 2. Return a clone of node, with this and the clone children flag set if deep is true.
        return $node->clone($this, $deep);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-document-adoptnode
     * @throws NotSupportedError
     */
    public function adoptNode(Node $node): ?Node
    {
        // 1. If node is a document, then throw a "NotSupportedError" DOMException.
        if ($node->nodeType === Node::DOCUMENT_NODE) {
            throw new NotSupportedError(
                'The node provided is of type `#document`, which may not be adopted.'
            );
        }
        // 2. If node is a shadow root, then throw a "HierarchyRequestError" DOMException.
        // 3. TODO: If node is a DocumentFragment node whose host is non-null, then return.
        // 4. Adopt node into this.
        $this->adopt($node);
        return $node;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace) return null;
        if ($root = $this->getDocumentElement()) {
            return $root->locateNamespacePrefix($namespace);
        }
        return null;
    }

    public function getRootNode(array $options = []): Node
    {
        return $this;
    }

    public function getDocumentNode(): ?Document
    {
        return $this;
    }

    public function getElementById(string $elementId): ?Element
    {
        if (!$elementId) return null;
        return $this->elementsById?->get($elementId, $this);
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        // TODO: encoding, contentType, URL, origin, mode
        $copy = new self($this->type);
        if ($deep) {
            for ($child = $this->_first; $child; $child = $this->_next) {
                $childCopy = $child->clone($this,true);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if ($root = $this->getDocumentElement()) {
            return $root->locateNamespace($prefix);
        }
        return null;
    }


    // ==============================================================
    // Internal caches
    // ==============================================================

    /**
     * @internal
     */
    public function addElementById(string $newId, Element $element): void
    {
        $this->elementsById ??= new ElementsByIdMap();
        $this->elementsById->add($newId, $element);
    }

    /**
     * @internal
     */
    public function removeElementById(string $newId, Element $element): void
    {
        $this->elementsById ??= new ElementsByIdMap();
        $this->elementsById->remove($newId, $element);
    }

    // ==============================================================
    // Mutation algorithms
    // ==============================================================

    protected const VALID_CHILD_TYPES = ParentNode::VALID_CHILD_TYPES + [
        Node::DOCUMENT_TYPE_NODE => true,
    ];

    protected function ensurePreInsertionValidity(Node $node, ?Node $child): void
    {
        parent::ensurePreInsertionValidity($node, $child);
        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        $this->canAcceptChild($node, $child, null);
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
        $this->canAcceptChild($node, null, $child);
    }

    private function canAcceptChild(Node $newChild, ?Node $next, ?Node $oldChild): bool
    {
        if ($oldChild?->nodeType === $newChild->nodeType) {
            return true;
        }
        $numDoctypes = 0;
        $numElements = 0;
        $hasDoctypeAfterReferenceNode = false;
        $hasElementAfterReferenceNode = false;
        // First, check how many doctypes and elements we have, not counting the child we're about to remove.
        $sawReferenceNode = false;
        for ($child = $this->_first; $child; $child = $child->_next) {
            if ($oldChild && $oldChild === $child) {
                $sawReferenceNode = true;
                continue;
            }
            if ($child === $next) {
                $sawReferenceNode = true;
            }
            switch ($child->nodeType) {
                case Node::DOCUMENT_TYPE_NODE:
                    $numDoctypes++;
                    $hasDoctypeAfterReferenceNode = $sawReferenceNode;
                    break;
                case Node::ELEMENT_NODE:
                    $numElements++;
                    $hasElementAfterReferenceNode = $sawReferenceNode;
                    break;
                default:
                    break;
            }
        }
        // Then, see how many doctypes and elements might be added by the new child.
        if ($newChild->nodeType === Node::DOCUMENT_FRAGMENT_NODE) {
            for ($child = $newChild->_first; $child; $child = $child->_next) {
                switch ($child->nodeType) {
                    case Node::ATTRIBUTE_NODE:
                    case Node::CDATA_SECTION_NODE:
                    case Node::DOCUMENT_FRAGMENT_NODE:
                    case Node::DOCUMENT_NODE:
                    case Node::TEXT_NODE:
                        throw new HierarchyRequestError(sprintf(
                            'Nodes of type `%s` may not be inserted inside nodes of type `%s`.',
                            $child->getDebugType(),
                            $this->getDebugType(),
                        ));
                    case Node::DOCUMENT_TYPE_NODE:
                        $numDoctypes++;
                        break;
                    case Node::ELEMENT_NODE:
                        $numElements++;
                        if ($hasDoctypeAfterReferenceNode) {
                            throw new HierarchyRequestError(
                                'Cannot insert an element before a doctype.'
                            );
                        }
                        break;
                    default:
                        break;
                }
            }
        } else {
            switch ($newChild->nodeType) {
                case Node::ATTRIBUTE_NODE:
                case Node::CDATA_SECTION_NODE:
                case Node::DOCUMENT_FRAGMENT_NODE:
                case Node::DOCUMENT_NODE:
                case Node::TEXT_NODE:
                    throw new HierarchyRequestError(sprintf(
                        'Nodes of type `%s` may not be inserted inside nodes of type `%s`.',
                        $newChild->getDebugType(),
                        $this->getDebugType(),
                    ));
                case Node::COMMENT_NODE:
                case Node::PROCESSING_INSTRUCTION_NODE:
                    return true;
                case Node::DOCUMENT_TYPE_NODE:
                    $numDoctypes++;
                    if ($numElements > 0 && !$hasElementAfterReferenceNode) {
                        throw new HierarchyRequestError(
                            'Cannot insert a doctype before the document element.'
                        );
                    }
                    break;
                case Node::ELEMENT_NODE:
                    $numElements++;
                    if ($hasDoctypeAfterReferenceNode) {
                        throw new HierarchyRequestError(
                            'Cannot insert an element before a doctype.'
                        );
                    }
                    break;
            }
        }
        if ($numElements > 1 || $numDoctypes > 1) {
            throw new HierarchyRequestError(sprintf(
                'Document only allows one %s child.',
                $numElements ? 'element' : 'doctype',
            ));
        }
        return true;
    }
}
