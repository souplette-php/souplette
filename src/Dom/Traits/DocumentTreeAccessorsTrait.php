<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Internal\Idioms;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Traversal\ElementTraversal;

trait DocumentTreeAccessorsTrait
{
    /**
     * https://html.spec.whatwg.org/multipage/dom.html#the-head-element-2
     */
    public function getHead(): ?Element
    {
        if ($html = $this->getHtmlElement()) {
            return ElementTraversal::firstChild(
                $html,
                fn(Element $el) => $el->isHTML && $el->localName === 'head',
            );
        }
        return null;
    }

    /**
     * https://html.spec.whatwg.org/multipage/dom.html#the-body-element-2
     */
    public function getBody(): ?Element
    {
        if ($html = $this->getHtmlElement()) {
            return ElementTraversal::firstChild(
                $html,
                fn(Element $el) => $el->isHTML && ($el->localName === 'body' || $el->localName === 'frameset'),
            );
        }
        return null;
    }

    /**
     * https://html.spec.whatwg.org/multipage/dom.html#dom-document-body
     * @throws DomException
     */
    public function setBody(Element $body): void
    {
        if (!$body->isHTML || ($body->localName !== 'body' && $body->localName !== 'frameset')) {
            throw new HierarchyRequestError(sprintf(
                'The body element must be either a "body" or "frameset" element, got "%s".',
                $body->localName,
            ));
        }
        $oldBody = $this->getBody();
        if ($oldBody === $body) return;
        if ($oldBody) {
            $oldBody->replaceWith($body);
            return;
        }
        $parent = $this->getDocumentElement();
        if (!$parent) {
            throw new HierarchyRequestError('The document has no document element.');
        }
        $parent->appendChild($body);
    }

    /**
     * https://html.spec.whatwg.org/multipage/dom.html#document.title
     */
    public function getTitle(): string
    {
        $root = $this->getDocumentElement();
        $namespace = Namespaces::HTML;
        if ($root->localName === 'svg' && $root->namespaceURI === Namespaces::SVG) {
            $namespace = Namespaces::SVG;
        }
        $title = $this->getTitleElement($namespace, $root);

        if (!$title) return '';
        return Idioms::stripAndCollapseAsciiWhitespace($title->getTextContent());
    }

    /**
     * https://html.spec.whatwg.org/multipage/dom.html#document.title
     */
    public function setTitle(?string $value = ''): void
    {
        $root = $this->getDocumentElement();
        if ($root->localName === 'svg' && $root->namespaceURI === Namespaces::SVG) {
            $title = $this->getTitleElement(Namespaces::SVG, $root);;
            if (!$title) {
                $title = $this->createElementNS(Namespaces::SVG, 'title');
                $root->appendChild($title);
            }
            $title->setTextContent($value);
        } else if ($root->isHTML) {
            $title = $this->getTitleElement(Namespaces::HTML, $root);
            $head = $this->getHead();
            if (!$title && !$head) return;
            if (!$title) {
                $title = $this->createElementNS(Namespaces::HTML, 'title');
                $head->appendChild($title);
            }
            $title->setTextContent($value);
        }
    }

    private function getTitleElement(string $namespace = Namespaces::HTML, ?Element $root = null): ?Element
    {
        return match ($namespace) {
            Namespaces::HTML => ElementTraversal::firstDescendant(
                $root ?? $this->getDocumentElement(),
                fn(Element $el) => $el->localName === 'title' && $el->namespaceURI === $namespace,
            ),
            Namespaces::SVG => ElementTraversal::firstChild(
                $root ?? $this->getDocumentElement(),
                fn(Element $el) => $el->localName === 'title' && $el->namespaceURI === $namespace,
            ),
            default => null,
        };
    }

    private function getHtmlElement(): ?Element
    {
        $el = $this->getDocumentElement();
        if ($el?->isHTML && $el->localName === 'html') {
            return $el;
        }
        return null;
    }
}
