<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMText;
use Souplette\Dom\Api\DocumentInterface;
use Souplette\Dom\Api\ParentNodeInterface;
use Souplette\Dom\DocumentModes;
use Souplette\Dom\Internal\DomIdioms;
use Souplette\Dom\Internal\ElementClasses;
use Souplette\Dom\Internal\PropertyMaps;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Traits\NodeTrait;
use Souplette\Dom\Traits\ParentNodeTrait;
use Souplette\Encoding\EncodingLookup;

final class HtmlDocument extends \DOMDocument implements
    DocumentInterface,
    ParentNodeInterface
    //NonElementParentNodeInterface
{
    use NodeTrait;
    use ParentNodeTrait;

    const COMPAT_MODE_BACK = 'BackCompat';
    const COMPAT_MODE_CSS1 = 'CSS1Compat';

    private string $internalMode = DocumentModes::NO_QUIRKS;

    public function __construct()
    {
        parent::__construct('', EncodingLookup::UTF_8);
        $this->registerNodeClass(DOMDocument::class, self::class);
        $this->registerNodeClass(DOMDocumentFragment::class, HtmlDocumentFragment::class);
        $this->registerNodeClass(DOMText::class, Text::class);
        $this->registerNodeClass(DOMComment::class, Comment::class);
        $this->registerNodeClass(DOMElement::class, HtmlElement::class);
        $this->registerNodeClass(DOMAttr::class, Attr::class);

        // Force $this->nodeType to XML_HTML_DOCUMENT_NODE
        parent::loadHTML('<!doctype html>');
        $this->removeChild($this->doctype);
    }

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }

    public function __set($name, $value)
    {
        PropertyMaps::set($this, $name, $value);
    }

    public function createElement($localName, $value = null): bool|Element
    {
        return $this->createElementNS(Namespaces::HTML, $localName, $value ?? '');
    }

    public function createElementNS($namespace, $qualifiedName, $value = null): bool|Element
    {
        $class = ElementClasses::ELEMENTS[$namespace][$qualifiedName] ?? null;
        if (!$class) {
            $class = ElementClasses::BASES[$namespace] ?? Element::class;
        }
        $this->registerNodeClass(DOMElement::class, $class);
        return parent::createElementNS($namespace, $qualifiedName, $value ?? '');
    }

    public function getHead(): ?DOMElement
    {
        return $this->getElementsByTagName('head')->item(0);
    }

    public function getBody(): ?DOMElement
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

    /**
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array
    {
        return DomIdioms::getElementsByClassName($this, $classNames);
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
