<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Node;

use DOMComment;
use DOMElement;
use DOMNodeList;
use DOMText;
use JoliPotage\Encoding\EncodingLookup;
use JoliPotage\Html\Dom\Api\HtmlDocumentInterface;
use JoliPotage\Html\Dom\Api\ParentNodeInterface;
use JoliPotage\Html\Dom\DocumentModes;
use JoliPotage\Html\Dom\DomIdioms;
use JoliPotage\Html\Dom\HtmlElementClasses;
use JoliPotage\Html\Dom\PropertyMaps;
use JoliPotage\Html\Dom\Traits\HtmlNodeTrait;
use JoliPotage\Html\Dom\Traits\ParentNodeTrait;
use JoliPotage\Html\Namespaces;

final class HtmlDocument extends \DOMDocument implements
    HtmlDocumentInterface,
    ParentNodeInterface
    //NonElementParentNodeInterface
{
    use HtmlNodeTrait;
    use ParentNodeTrait;

    const COMPAT_MODE_BACK = 'BackCompat';
    const COMPAT_MODE_CSS1 = 'CSS1Compat';

    private string $internalMode = DocumentModes::NO_QUIRKS;

    public function __construct()
    {
        parent::__construct('', EncodingLookup::UTF_8);
        $this->registerNodeClass(DOMText::class, HtmlText::class);
        $this->registerNodeClass(DOMComment::class, HtmlComment::class);
        $this->registerNodeClass(DOMElement::class, HtmlElement::class);
    }

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }

    public function __set($name, $value)
    {
        PropertyMaps::set($this, $name, $value);
    }

    public function createElement($name, $value = null)
    {
        return $this->createElementNS(Namespaces::HTML, $name, $value ?? '');
    }

    public function createElementNS($namespaceURI, $qualifiedName, $value = null)
    {
        if (isset(HtmlElementClasses::ELEMENTS[$namespaceURI][$qualifiedName])) {
            $class = HtmlElementClasses::ELEMENTS[$namespaceURI][$qualifiedName];
            $this->registerNodeClass(DOMElement::class, $class);
            $element = parent::createElementNS($namespaceURI, $qualifiedName, $value ?? '');
            $this->registerNodeClass(DOMElement::class, HtmlElement::class);
            return $element;
        }

        return parent::createElementNS($namespaceURI, $qualifiedName, $value ?? '');
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
     * @param string $mode
     */
    public function internalSetMode(string $mode): void
    {
        $this->internalMode = $mode;
    }

    public function getElementsByClassName(string $classNames): DOMNodeList
    {
        return DomIdioms::getElementsByClassName($this, $classNames, $this);
    }

    public function getElementById($elementId): ?DOMElement
    {
        $expr = "//*[@id = '{$elementId}' ]";
        return (new \DOMXPath($this))->query($expr)->item(0);
    }
}
