<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use Souplette\Dom\Document;
use Souplette\Dom\Namespaces;
use Souplette\Html\TreeBuilder\Elements;

final class DomBuilder
{
    protected Document $document;
    protected \SplStack $openElements;

    private function __construct()
    {
        $this->document = new Document();
        $this->openElements = new \SplStack();
    }

    public static function create(): self
    {
        return new self();
    }

    public function getDocument(): Document
    {
        while (!$this->openElements->isEmpty()) {
            $this->openElements->pop();
        }
        return $this->document;
    }

    public function tag(string $name, string $namespace = Namespaces::HTML): self
    {
        $element = $this->document->createElementNS($namespace, $name);
        $this->getParent()->appendChild($element);
        if (!isset(Elements::VOID_ELEMENTS[$name])) {
            $this->openElements->push($element);
        }

        return $this;
    }

    public function close(?string $untilTag = null): self
    {
        if ($untilTag === null) {
            $this->openElements->pop();
            return $this;
        }
        while (!$this->openElements->isEmpty() && $this->openElements->top()->tagName !== $untilTag) {
            $this->openElements->pop();
        }
        return $this;
    }

    public function attr(string $name, string $value, ?string $namespace = null): self
    {
        $element = $this->openElements->top();
        if ($namespace) {
            $element->setAttributeNS($namespace, $name, $value);
        } else {
            $element->setAttribute($name, $value);
        }
        return $this;
    }

    public function id(string $id): self
    {
        return $this->attr('id', $id);
    }

    public function class(string $className): self
    {
        return $this->attr('class', $className);
    }

    public function comment(string $data): self
    {
        $node = $this->document->createComment($data);
        $this->getParent()->appendChild($node);
        return $this;
    }

    public function text(string $data): self
    {
        $node = $this->document->createTextNode($data);
        $this->getParent()->appendChild($node);
        return $this;
    }

    public function doctype(string $name, string $pub = '', string $sys = ''): self
    {
        $node = $this->document->implementation->createDocumentType($name, $pub, $sys);
        $this->document->appendChild($node);
        return $this;
    }

    private function getParent(): \DOMNode
    {
        return $this->openElements->isEmpty() ? $this->document : $this->openElements->top();
    }
}
