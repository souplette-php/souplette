<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Html\Dom\Api\ChildNodeInterface;
use Souplette\Html\Dom\Api\ElementInterface;
use Souplette\Html\Dom\Api\NodeInterface;
use Souplette\Html\Dom\Api\ParentNodeInterface;
use Souplette\Html\Dom\Internal\DomIdioms;
use Souplette\Html\Dom\Internal\PropertyMaps;
use Souplette\Html\Dom\TokenList;
use Souplette\Html\Dom\Traits\ChildNodeTrait;
use Souplette\Html\Dom\Traits\NodeTrait;
use Souplette\Html\Dom\Traits\ParentNodeTrait;
use Souplette\Html\Namespaces;
use Souplette\Html\Parser\Parser;
use Souplette\Html\Serializer\Serializer;

class Element extends \DOMElement implements
    NodeInterface,
    ParentNodeInterface,
    ChildNodeInterface,
    ElementInterface
{
    use NodeTrait;
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

    public function getElementsByClassName(string $classNames): array
    {
        return DomIdioms::getElementsByClassName($this, $classNames);
    }

    public function matches(string $selector): bool
    {
        return SelectorQuery::matches($this, $selector);
    }

    public function closest(string $selector): ?Element
    {
        return SelectorQuery::closest($this, $selector);
    }

    public function hasAttribute($qualifiedName): bool
    {
        if (parent::hasAttribute($qualifiedName)) {
            return true;
        }
        return DomIdioms::getAttributeByName($this, $qualifiedName) !== null;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-getattribute
     */
    public function getAttribute(string $qualifiedName): string
    {
        // https://dom.spec.whatwg.org/#dom-element-getattribute
        if (parent::hasAttribute($qualifiedName)) {
            return parent::getAttribute($qualifiedName);
        }
        $node = DomIdioms::getAttributeByName($this, $qualifiedName);
        return $node ? $node->nodeValue : '';
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-setattribute
     */
    public function setAttribute(string $qualifiedName, string $value): \DOMAttr|false
    {
        // 1. If qualifiedName does not match the Name production in XML,
        // then throw an "InvalidCharacterError" DOMException.
        // 2. If this is in the HTML namespace and its node document is an HTML document,
        // then set qualifiedName to qualifiedName in ASCII lowercase.
        if ($this->namespaceURI === Namespaces::HTML && $this->ownerDocument->nodeType === XML_HTML_DOCUMENT_NODE) {
            $qualifiedName = strtolower($qualifiedName);
        }
        return parent::setAttribute($qualifiedName, $value);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-removeattribute
     */
    public function removeAttribute(string $qualifiedName): bool
    {
        if (parent::removeAttribute($qualifiedName)) return true;
        if ($node = DomIdioms::getAttributeByName($this, $qualifiedName)) {
            $this->removeChild($node);
            return true;
        }
        return false;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-toggleattribute
     */
    public function toggleAttribute(string $qualifiedName, bool $force = null): bool
    {
        // TODO: 1. If qualifiedName does not match the Name production in XML,
        // then throw an "InvalidCharacterError" DOMException.

        // 2. If this is in the HTML namespace and its node document is an HTML document,
        // then set qualifiedName to qualifiedName in ASCII lowercase.
        if ($this->namespaceURI === Namespaces::HTML && $this->ownerDocument->nodeType === XML_HTML_DOCUMENT_NODE) {
            $qualifiedName = strtolower($qualifiedName);
        }
        // 3. Let attribute be the first attribute in this’s attribute list whose qualified name is qualifiedName,
        // and null otherwise.
        $attr = null;
        foreach ($this->attributes as $attribute) {
            if ($attribute->nodeName === $qualifiedName) {
                $attr = $attribute;
                break;
            }
        }
        // 4. If attribute is null, then:
        if (!$attr) {
            // 1. If force is not given or is true, create an attribute whose local name is qualifiedName,
            // value is the empty string, and node document is this’s node document,
            // then append this attribute to this, and then return true.
            if ($force || $force === null) {
                $attr = $this->ownerDocument->createAttribute($qualifiedName);
                $this->appendChild($attr);
                return true;
            }
            // 2.
            return false;
        }
        // 5. Otherwise, if force is not given or is false,
        // remove an attribute given qualifiedName and this, and then return false.
        if (!$force) {
            $this->removeChild($attr);
        }
        // 6.
        return true;
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
