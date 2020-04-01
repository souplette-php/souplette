<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use DOMNodeList;
use JoliPotage\Html\Dom\Api\ChildNodeInterface;
use JoliPotage\Html\Dom\Api\HtmlElementInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\Traits\ChildNodeTrait;
use JoliPotage\Html\Dom\Traits\NonDocumentTypeChildNodeTrait;
use JoliPotage\Html\Parser\Parser;
use JoliPotage\Html\Serializer\Serializer;

/**
 * @property string $innerHTML
 * @property string $outerHTML
 * @property-read TokenList $classList
 */
class HtmlElement extends \DOMElement implements
    HtmlElementInterface,
    NonDocumentTypeChildNodeInterface,
    ChildNodeInterface
{
    use NonDocumentTypeChildNodeTrait;
    use ChildNodeTrait;

    private TokenList $internalClassList;

    public function __get($name)
    {
        $method = HtmlElementInterface::PROPERTIES_READ[$name] ?? null;
        $method ??= NonDocumentTypeChildNodeInterface::PROPERTIES_READ[$name] ?? null;
        if ($method) {
            return $this->{$method};
        }
    }

    public function __set($name, $value)
    {
        $method = HtmlElementInterface::PROPERTIES_WRITE[$name] ?? null;
        if ($method) {
            $this->{$method}($value);
        }
    }

    public function setAttribute($name, $value)
    {
        if ($name === 'class') {
            $attr = parent::setAttribute('class', $value);
            $this->getClassList()->setValue($attr->value);
            return $attr;
        }

        return parent::setAttribute($name, $value);
    }

    public function getClassList(): TokenList
    {
        if (!$this->internalClassList) {
            $this->internalClassList = new TokenList(
                parent::getAttribute('class'),
                fn(string $value) => $this->setAttribute('class', $value)
            );
        }
        return $this->internalClassList;
    }

    public function getElementsByClassName(string $classNames): DOMNodeList
    {
        return DomIdioms::getElementsByClassName($this->ownerDocument, $classNames, $this);
    }

    public function getInnerHTML(): string
    {
        $serializer = new Serializer();
        return $serializer->serialize($this);
    }

    public function setInnerHTML(string $html): void
    {
        $frag = $this->ownerDocument->createDocumentFragment();
        $parser = new Parser();
        $children = $parser->parseFragment($this, $html, $this->ownerDocument->encoding);
        foreach ($children as $child) {
            $this->ownerDocument->importNode($child, true);
            $frag->appendChild($child);
        }
        for ($i = $this->childNodes->length - 1; $i >= 0; $i--) {
            $this->removeChild($this->childNodes->item($i));
        }
        $this->appendChild($frag);
    }

    public function getOuterHTML(): string
    {
        // TODO: Implement getOuterHTML() method.
    }

    public function setOuterHTML(string $html): void
    {
        // TODO: Implement setOuterHTML() method.
    }
}
