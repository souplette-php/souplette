<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use DOMElement;
use DOMNodeList;
use JoliPotage\Encoding\EncodingLookup;
use JoliPotage\Html\Dom\Api\HtmlDocumentInterface;
use JoliPotage\Html\Dom\Api\ParentNodeInterface;
use JoliPotage\Html\Dom\Traits\ParentNodeTrait;
use JoliPotage\Html\Namespaces;
use JoliPotage\Html\Parser\TreeBuilder\CompatModes;

final class HtmlDocument extends \DOMDocument implements HtmlDocumentInterface, ParentNodeInterface
{
    use ParentNodeTrait;

    const COMPAT_MODE_BACK = 'BackCompat';
    const COMPAT_MODE_CSS1 = 'CSS1Compat';

    private string $internalMode;

    public function __construct()
    {
        parent::__construct('', EncodingLookup::UTF_8);
        $this->registerNodeClass(\DOMNode::class, HtmlNode::class);
        $this->registerNodeClass(DOMElement::class, HtmlElement::class);
    }

    public function __get($name)
    {
        $methods = PropertyMaps::READ;
        $method = $methods[HtmlDocumentInterface::class][$name] ?? null;
        $method ??= $methods[ParentNodeInterface::class][$name] ?? null;
        if ($method) {
            return $this->{$method}();
        }
    }

    public function __set($name, $value)
    {
        $methods = PropertyMaps::WRITE;
        $method = $methods[HtmlDocumentInterface::class][$name] ?? null;
        if ($method) {
            $this->{$method}($value);
        }
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
        return $this->internalMode === CompatModes::QUIRKS ? self::COMPAT_MODE_BACK : self::COMPAT_MODE_CSS1;
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
}
