<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Node;

use DOMNodeList;
use JoliPotage\Html\Dom\Api\ChildNodeInterface;
use JoliPotage\Html\Dom\Api\HtmlElementInterface;
use JoliPotage\Html\Dom\Api\HtmlNodeInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\Api\ParentNodeInterface;
use JoliPotage\Html\Dom\DomIdioms;
use JoliPotage\Html\Dom\PropertyMaps;
use JoliPotage\Html\Dom\TokenList;
use JoliPotage\Html\Dom\Traits\ChildNodeTrait;
use JoliPotage\Html\Dom\Traits\HtmlNodeTrait;
use JoliPotage\Html\Dom\Traits\NonDocumentTypeChildNodeTrait;
use JoliPotage\Html\Dom\Traits\ParentNodeTrait;
use JoliPotage\Html\Parser\Parser;
use JoliPotage\Html\Serializer\Serializer;

class HtmlElement extends \DOMElement implements
    HtmlNodeInterface,
    ParentNodeInterface,
    NonDocumentTypeChildNodeInterface,
    ChildNodeInterface,
    HtmlElementInterface
{
    use HtmlNodeTrait;
    use ParentNodeTrait;
    use NonDocumentTypeChildNodeTrait;
    use ChildNodeTrait;

    private TokenList $internalClassList;

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }

    public function __set($name, $value)
    {
        PropertyMaps::set($this, $name, $value);
    }

    public function getId(): string
    {
        return $this->getAttribute('id');
    }

    public function setId(string $id): void
    {
        $this->setAttribute('id', $id);
    }

    public function getClassName(): string
    {
        return $this->getAttribute('class');
    }

    public function setClassName(string $className): void
    {
        $this->setAttribute('class', $className);
    }

    public function getClassList(): TokenList
    {
        if (!isset($this->internalClassList)) {
            $this->internalClassList = new TokenList($this, 'class');
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
        $parser = new Parser();
        $children = $parser->parseFragment($this, $html, $this->ownerDocument->encoding);
        while ($child = $this->firstChild) {
            $this->removeChild($child);
        }
        $this->append(...$children);
    }

    public function getOuterHTML(): string
    {
        $serializer = new Serializer();
        return $serializer->serializeElement($this);
    }

    public function setOuterHTML(string $html): void
    {
        $parser = new Parser();
        $children = $parser->parseFragment($this, $html, $this->ownerDocument->encoding);
        $this->replaceWith(...$children);
    }
}
