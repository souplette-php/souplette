<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use DOMNodeList;
use Souplette\Html\Dom\Api\ChildNodeInterface;
use Souplette\Html\Dom\Api\HtmlElementInterface;
use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Html\Dom\Api\ParentNodeInterface;
use Souplette\Html\Dom\DomIdioms;
use Souplette\Html\Dom\PropertyMaps;
use Souplette\Html\Dom\TokenList;
use Souplette\Html\Dom\Traits\ChildNodeTrait;
use Souplette\Html\Dom\Traits\HtmlNodeTrait;
use Souplette\Html\Dom\Traits\ParentNodeTrait;
use Souplette\Html\Parser\Parser;
use Souplette\Html\Serializer\Serializer;

class HtmlElement extends \DOMElement implements
    HtmlNodeInterface,
    ParentNodeInterface,
    ChildNodeInterface,
    HtmlElementInterface
{
    use HtmlNodeTrait;
    use ParentNodeTrait;
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
