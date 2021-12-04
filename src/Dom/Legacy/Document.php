<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMText;
use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Api\DocumentInterface;
use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\DocumentModes;
use Souplette\Dom\Internal\PropertyMaps;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Traits\NodeTrait;
use Souplette\Dom\Traits\ParentNodeTrait;
use Souplette\Encoding\EncodingLookup;

/**
 * @property-read Implementation $implementation
 * @property-read Element|null $documentElement
 *
 * @method DocumentFragment|false createDocumentFragment()
 * @method Element|false createElementNS(?string $namespace, string $qualifiedName, string $value = '')
 * @method Attr|false createAttribute(string $localName)
 * @method Attr|false createAttributeNS(?string $namespace, string $qualifiedName)
 * @method Comment|false createComment(string $data)
 * @method Text|false createTextNode(string $data)
 * @method NodeInterface|false importNode(DOMNode $node, bool $deep = false)
 */
final class Document extends \DOMDocument implements DocumentInterface
{
    use NodeTrait;
    use ParentNodeTrait;

    const COMPAT_MODE_BACK = 'BackCompat';
    const COMPAT_MODE_CSS1 = 'CSS1Compat';

    private string $internalMode = DocumentModes::NO_QUIRKS;

    public function __construct()
    {
        parent::__construct('', EncodingLookup::UTF_8);
        parent::registerNodeClass(DOMDocument::class, self::class);
        parent::registerNodeClass(DOMDocumentType::class, DocumentType::class);
        parent::registerNodeClass(DOMDocumentFragment::class, DocumentFragment::class);
        parent::registerNodeClass(DOMText::class, Text::class);
        parent::registerNodeClass(DOMComment::class, Comment::class);
        parent::registerNodeClass(DOMElement::class, Element::class);
        parent::registerNodeClass(DOMAttr::class, Attr::class);

        // Force $this->nodeType to XML_HTML_DOCUMENT_NODE
        parent::loadHTML('<!doctype html>');
        parent::removeChild($this->doctype);
    }

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }

    public function __set($name, $value)
    {
        PropertyMaps::set($this, $name, $value);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-document-createelement
     */
    public function createElement(string $localName, string $value = ''): Element|false
    {
        return $this->createElementNS(Namespaces::HTML, strtolower($localName), $value);
    }

    public function getHead(): ?Element
    {
        return $this->getElementsByTagName('head')->item(0);
    }

    public function getBody(): ?Element
    {
        return $this->getElementsByTagName('body')->item(0);
    }

    public function getTitle(): string
    {
        $node = $this->getElementsByTagName('title')->item(0);
        return $node ? $node->nodeValue : '';
    }

    public function setTitle(string $title): void
    {
        $node = $this->getElementsByTagName('title')->item(0);
        if ($node) {
            $node->nodeValue = $title;
            return;
        }
        if ($head = $this->getHead()) {
            $head->appendChild($this->createElement('title', $title));
        }
    }

    public function getMode(): string
    {
        return $this->internalMode;
    }

    public function getCompatMode(): string
    {
        return $this->internalMode === DocumentModes::QUIRKS ? self::COMPAT_MODE_BACK : self::COMPAT_MODE_CSS1;
    }

    /**
     * @internal
     */
    public function internalSetMode(string $mode): void
    {
        $this->internalMode = $mode;
    }

    public function getElementById(string $elementId): ?Element
    {
        return SelectorQuery::byId($this, $elementId);
    }

    /**
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array
    {
        return SelectorQuery::byClassNames($this, $classNames);
    }

    private ?\DOMXPath $_xpath = null;

    private function xpath(): \DOMXPath
    {
        if ($this->_xpath) return $this->_xpath;

        $xpath = new \DOMXPath($this, true);
        $xpath->registerNamespace('_', Namespaces::HTML);
        $xpath->registerNamespace('svg', Namespaces::SVG);
        $xpath->registerNamespace('math', Namespaces::MATHML);
        return $this->_xpath = $xpath;
    }
}
