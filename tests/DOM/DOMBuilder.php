<?php declare(strict_types=1);

namespace Souplette\Tests\DOM;

use Souplette\DOM\Document;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\DOM\XMLDocument;
use Souplette\HTML\TreeBuilder\Elements;

/**
 * @template T of Document
 */
final class DOMBuilder
{
    /** @var T  */
    protected Document $document;
    protected \SplStack $openElements;

    /**
     * @param T $document
     */
    private function __construct(Document $document)
    {
        $this->document = $document;
        $this->openElements = new \SplStack();
    }

    /**
     * @return DOMBuilder<Document>
     */
    public static function html(): self
    {
        return new self(new Document());
    }

    /**
     * @return DOMBuilder<XMLDocument>
     */
    public static function xml(): self
    {
        return new self(new XMLDocument());
    }

    /**
     * @return T
     */
    public function getDocument(): Document
    {
        while (!$this->openElements->isEmpty()) {
            $this->openElements->pop();
        }
        return $this->document;
    }

    public function tag(string $name, ?string $namespace = Namespaces::HTML): self
    {
        $this->closeVoidElements();
        $element = $this->document->createElementNS($namespace, $name);
        $this->getParent()->appendChild($element);
        $this->openElements->push($element);

        return $this;
    }

    public function close(?string $untilTag = null): self
    {
        $this->closeVoidElements();
        if ($untilTag === null) {
            $this->openElements->pop();
            return $this;
        }
        while (!$this->openElements->isEmpty() && $this->openElements->top()->tagName !== $untilTag) {
            $this->openElements->pop();
        }
        return $this;
    }

    public function attr(string $name, string $value = '', ?string $namespace = null): self
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
        $this->closeVoidElements();
        $node = $this->document->createComment($data);
        $this->getParent()->appendChild($node);
        return $this;
    }

    public function text(string $data): self
    {
        $this->closeVoidElements();
        $node = $this->document->createTextNode($data);
        $this->getParent()->appendChild($node);
        return $this;
    }

    public function doctype(string $name, string $pub = '', string $sys = ''): self
    {
        $this->closeVoidElements();
        $node = $this->document->implementation->createDocumentType($name, $pub, $sys);
        $this->document->appendChild($node);
        return $this;
    }

    private function getParent(): Node
    {
        return $this->openElements->isEmpty() ? $this->document : $this->openElements->top();
    }

    private function closeVoidElements()
    {
        if (!$this->document->isHTML || $this->openElements->isEmpty()) {
            return;
        }
        $node = $this->openElements->top();
        if (isset(Elements::VOID_ELEMENTS[$node->localName]) && $node->namespaceURI === Namespaces::HTML) {
            $this->openElements->pop();
        }
    }
}
