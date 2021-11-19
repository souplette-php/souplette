<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMText;
use Souplette\Encoding\EncodingLookup;
use Souplette\Html\Dom\Api\DocumentInterface;
use Souplette\Html\Dom\Api\ParentNodeInterface;
use Souplette\Html\Dom\DocumentModes;
use Souplette\Html\Dom\Internal\DomIdioms;
use Souplette\Html\Dom\Internal\ElementClasses;
use Souplette\Html\Dom\Internal\PropertyMaps;
use Souplette\Html\Dom\Traits\NodeTrait;
use Souplette\Html\Dom\Traits\ParentNodeTrait;
use Souplette\Html\Namespaces;

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
        $node = (new \DOMXPath($this))->query('/html/head/title')->item(0);
        return $node ? $node->nodeValue : '';
    }

    public function setTitle(string $title): void
    {
        $node = (new \DOMXPath($this))->query('/html/head/title')->item(0);
        if ($node) {
            $node->nodeValue = $title;
            return;
        }
        $this->getHead()->appendChild($this->createElement('title', $title));
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

    public function getElementById($elementId): ?DOMElement
    {
        $expr = "//*[@id = '{$elementId}' ]";
        return (new \DOMXPath($this))->query($expr)->item(0);
    }
}
