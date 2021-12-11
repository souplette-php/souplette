<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Dom\DocumentFragment;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\NoModificationAllowed;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Html\Parser as HtmlParser;
use Souplette\Html\Serializer as HtmlSerializer;
use Souplette\Xml\Parser as XmlParser;
use Souplette\Xml\Serializer as XmlSerializer;

/**
 * https://w3c.github.io/DOM-Parsing/#the-innerhtml-mixin
 * https://w3c.github.io/DOM-Parsing/#dom-element-outerhtml
 */
trait DomParsingTrait
{
    /**
     * @throws DomException
     */
    public function getInnerHTML(): string
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $serializer = new HtmlSerializer();
            return $serializer->serialize($this);
        }
        $serializer = new XmlSerializer();
        return $serializer->serializeFragment($this, true);
    }

    /**
     * @throws DomException
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
     * @throws DomException
     */
    public function getOuterHTML(): string
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $serializer = new HtmlSerializer();
            return $serializer->serializeElement($this);
        }
        $serializer = new XmlSerializer();
        return $serializer->serialize($this, true);
    }

    /**
     * @throws DomException
     */
    public function setOuterHTML(string $markup): void
    {
        $parent = $this->_parent;
        if (!$parent) return;
        if ($parent->nodeType === Node::DOCUMENT_NODE) {
            throw new NoModificationAllowed(sprintf(
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
     * @throws DomException
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
            throw new NoModificationAllowed(sprintf(
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
     * @throws DomException
     */
    private function parseFragment(string $markup, Element $contextElement): DocumentFragment
    {
        $contextDocument = $this->getDocumentNode();
        if ($contextDocument->isHTML) {
            $parser = new HtmlParser();
            $newChildren = $parser->parseFragment($contextElement, $markup, $this->_doc?->encoding ?? 'utf-8');
        } else {
            $parser = new XmlParser();
            $newChildren = $parser->parseFragment($markup, $contextElement);
        }
        $fragment = $contextDocument->createDocumentFragment();
        foreach ($newChildren as $newChild) {
            $fragment->parserInsertBefore($newChild, null);
        }
        return $fragment;
    }
}
