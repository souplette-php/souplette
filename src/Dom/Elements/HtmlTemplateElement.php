<?php declare(strict_types=1);

namespace Souplette\Dom\Elements;

use DOMDocumentFragment;
use Souplette\Dom\Api\HtmlTemplateElementInterface;
use Souplette\Dom\DocumentFragment;
use Souplette\Dom\HtmlElement;
use Souplette\Html\Parser;

/**
 * @property-read DOMDocumentFragment|null $content
 */
final class HtmlTemplateElement extends HtmlElement implements HtmlTemplateElementInterface
{
    private static ?\SplObjectStorage $instances = null;
    private static ?\WeakMap $storage = null;

    public function getContent(): ?DOMDocumentFragment
    {
        return $this->getFragment();
    }

    public function setInnerHTML(string $html): void
    {
        $parser = new Parser();
        $children = $parser->parseFragment($this, $html, $this->ownerDocument?->encoding);
        $fragment = $this->getFragment();
        foreach ($fragment->childNodes as $child) {
            $fragment->removeChild($child);
        }
        foreach ($children as $child) {
            $fragment->appendChild($child);
        }
    }

    public function prepend(...$nodes): void
    {
    }

    public function append(...$nodes): void
    {
    }

    public function replaceChildren(...$nodes): void
    {
    }

    public function insertBefore(\DOMNode $node, ?\DOMNode $child = null)
    {
    }

    public function appendChild(\DOMNode $node)
    {
    }

    private function getFragment(): DocumentFragment
    {
        if (!self::$storage) self::$storage = new \WeakMap();
        self::$storage[$this->ownerDocument] ??= new \SplObjectStorage();
        if (!isset(self::$storage[$this->ownerDocument][$this])) {
            $content = $this->ownerDocument->createDocumentFragment();
            self::$storage[$this->ownerDocument]->attach($this, $content);
        }
        return self::$storage[$this->ownerDocument][$this];
    }
}
