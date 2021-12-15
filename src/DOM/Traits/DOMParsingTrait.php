<?php declare(strict_types=1);

namespace Souplette\DOM\Traits;

use Souplette\DOM\DocumentFragment;
use Souplette\DOM\Element;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Exception\NoModificationAllowedError;
use Souplette\DOM\Exception\SyntaxError;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\HTML\HTMLParser;
use Souplette\HTML\HTMLSerializer;
use Souplette\XML\XMLParser;
use Souplette\XML\XMLSerializer;

/**
 * https://w3c.github.io/DOM-Parsing/#the-innerhtml-mixin
 * https://w3c.github.io/DOM-Parsing/#dom-element-outerhtml
 */
trait DOMParsingTrait
{
    /**
     * @throws DOMException
     */
    public function getInnerHTML(): string
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $serializer = new HTMLSerializer();
            return $serializer->serializeFragment($this);
        }
        $serializer = new XMLSerializer();
        return $serializer->serializeFragment($this, true);
    }

    /**
     * @throws DOMException
     */
    public function setInnerHTML(?string $markup): void
    {
        if (!$markup) {
            $this->replaceAllWithNode(null);
            return;
        }
        $fragment = $this->parseFragment($markup, $this);
        // TODO: If the context object is a template element,
        // then let context object be the template's template contents (a DocumentFragment).
        $this->replaceAllWithNode($fragment);
    }

    /**
     * @throws DOMException
     */
    public function getOuterHTML(): string
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $serializer = new HTMLSerializer();
            return $serializer->serialize($this);
        }
        $serializer = new XMLSerializer();
        return $serializer->serialize($this, true);
    }

    /**
     * @throws DOMException
     */
    public function setOuterHTML(string $markup): void
    {
        $parent = $this->_parent;
        if (!$parent) return;
        if ($parent->nodeType === Node::DOCUMENT_NODE) {
            throw new NoModificationAllowedError(sprintf(
                'Failed to execute %s: The element has no parent.',
                __METHOD__,
            ));
        }
        if (!$markup) {
            $parent->removeChild($this);
            return;
        }
        if ($parent->nodeType === Node::DOCUMENT_FRAGMENT_NODE) {
            $parent = new Element('body', Namespaces::HTML);
            $parent->_doc = $this->_doc;
        }
        $fragment = $this->parseFragment($markup, $parent);
        $this->_parent->replaceChildWithNode($this, $fragment);
    }


    /**
     * https://w3c.github.io/DOM-Parsing/#dom-element-insertadjacenthtml
     * @throws DOMException
     */
    public function insertAdjacentHTML(string $position, string $html): void
    {
        $position = strtolower($position);
        $context = match ($position) {
            'beforebegin', 'afterend' => $this->_parent,
            'afterbegin', 'beforeend' => $this,
            default => throw new SyntaxError(sprintf(
                'Failed to execute %s: The value provided ("%s") is not one of "beforebegin", "afterend", "afterbegin", or "beforeend".',
                __METHOD__,
                $position
            )),
        };
        if (!$context || $context === $this->_doc) {
            throw new NoModificationAllowedError(sprintf(
                'Failed to execute %s: The element has no parent.',
                __METHOD__,
            ));
        }
        if ($context->nodeType !== Node::ELEMENT_NODE || (
            $context->_doc?->isHTML
            && $context->isHTML
            && $context->localName === 'html'
        )) {
            $context = new Element('body', Namespaces::HTML);
            $context->_doc = $this->_doc;
        }
        $fragment = $this->parseFragment($html, $context);
        match ($position) {
            'beforebegin' => $this->_parent->insertBefore($fragment, $this),
            'afterbegin' => $this->insertBefore($fragment, $this->_first),
            'beforeend' => $this->appendChild($fragment),
            'afterend' => $this->_parent->insertBefore($fragment, $this->_next),
        };
    }

    /**
     * @throws DOMException
     */
    private function parseFragment(string $markup, Element $contextElement): DocumentFragment
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $parser = new HTMLParser();
            $newChildren = $parser->parseFragment($contextElement, $markup, $this->_doc?->characterSet ?? 'utf-8');
        } else {
            $parser = new XMLParser();
            $newChildren = $parser->parseFragment($markup, $contextElement);
        }
        $fragment = $contextDocument->createDocumentFragment();
        foreach ($newChildren as $newChild) {
            $fragment->parserInsertBefore($newChild, null);
        }
        return $fragment;
    }
}
