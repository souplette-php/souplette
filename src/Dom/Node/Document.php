<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node\Traits\ParentNodeTrait;
use Souplette\Xml\QName;

/**
 * @property-read string $compatMode
 * @property-read string $characterSet
 * @property-read string $contentType
 */
final class Document extends Node
{
    use ParentNodeTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly bool $isHTML;
    private Implementation $implementation;

    public function __construct(string $type)
    {
        $this->nodeType = Node::DOCUMENT_NODE;
        $this->nodeName = '#document';
        $this->isHTML = $type !== 'xml';
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'implementation' => $this->implementation ??= new Implementation(),
            'textContent' => $this->getTextContent(),
            'children' => $this->getChildren(),
            'firstElementChild' => $this->getFirstElementChild(),
            'lastElementChild' => $this->getLastElementChild(),
            'childElementCount' => $this->getChildElementCount(),
            default => parent::__get($prop),
        };
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

    protected function getDocument(): ?Document
    {
        return $this;
    }
}
