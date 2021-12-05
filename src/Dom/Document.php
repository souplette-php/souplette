<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NonElementParentNodeInterface;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Exception\NotSupportedError;
use Souplette\Xml\QName;

/**
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read string $characterSet
 * @property-read string $contentType
 * @property-read ?DocumentType $doctype
 * @property-read ?Element $documentElement
 */
class Document extends ParentNode implements NonElementParentNodeInterface
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
            'doctype' => $this->getDoctype(),
            'documentElement' => $this->getFirstElementChild(),
            'implementation' => $this->implementation ??= new Implementation(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'textContent', 'nodeValue' => null,
            default => parent::__set($prop, $value),
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

    public function getDoctype(): ?DocumentType
    {
        for ($child = $this->first; $child; $child = $this->next) {
            if ($child->nodeType === Node::DOCUMENT_TYPE_NODE) {
                return $child;
            }
        }
        return null;
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
            throw new InvalidCharacterError(sprintf(
                'Provided attribute local name "%s" is not a valid name.',
                $localName
            ));
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
        if ($this->isHTML) {
            throw new NotSupportedError('CDATA sections are not supported for HTML documents.');
        }
        if (str_contains($data, ']]>')) {
            throw new InvalidCharacterError(
                'Data cannot contain "]]>" since that is the end delimiter of a CDATA section.'
            );
        }
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
        $node->document = $this;
        return $node;
    }

    public function getElementById(string $elementId): ?Element
    {
        // TODO: Implement getElementById() method.
        return null;
    }

    public function getTextContent(): ?string
    {
        return null;
    }

    public function setTextContent(string $value): void
    {
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

    protected function getDocumentNode(): ?Document
    {
        return $this;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if ($root = $this->getDocumentElement()) {
            return $root->locateNamespace($prefix);
        }
        return null;
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
        for ($child = $this->first; $child; $child = $child->next) {
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
            for ($child = $newChild->first; $child; $child = $child->next) {
                switch ($child->nodeType) {
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
