<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Node;
use Souplette\Dom\Text;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Dom\XmlDocument;
use Souplette\Xml\Exception\ParseError;
use XMLReader;

/**
 * @see https://html.spec.whatwg.org/multipage/xhtml.html#xml-parser
 */
final class Parser
{
    private const HTML_PUBLIC_IDS = [
        '-//W3C//DTD XHTML 1.0 Transitional//EN' => true,
        '-//W3C//DTD XHTML 1.1//EN' => true,
        '-//W3C//DTD XHTML 1.0 Strict//EN' => true,
        '-//W3C//DTD XHTML 1.0 Frameset//EN' => true,
        '-//W3C//DTD XHTML Basic 1.0//EN' => true,
        '-//W3C//DTD XHTML 1.1 plus MathML 2.0//EN' => true,
        '-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN' => true,
        '-//W3C//DTD MathML 2.0//EN' => true,
        '-//WAPFORUM//DTD XHTML Mobile 1.0//EN' => true,
    ];

    /**
     * @throws DomException|ParseError
     */
    public function parse(string $xml): Document
    {
        libxml_set_external_entity_loader($this->resolveEntities(...));
        $internalErrors = libxml_use_internal_errors(true);

        $reader = new XMLReader();
        $reader->xml($xml);
        $reader->setParserProperty(XMLReader::LOADDTD, true);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

        try {
            return $this->read($reader);
        } finally {
            $reader->close();
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
            libxml_set_external_entity_loader(null);
        }
    }

    /**
     * https://html.spec.whatwg.org/multipage/xhtml.html#xml-fragment-parsing-algorithm
     * @return Node[]
     * @throws DomException
     */
    public function parseFragment(string $xml, Element $context): array
    {
        $xml = $this->preprocessFragment($xml, $context);
        $internalErrors = libxml_use_internal_errors(true);
        $reader = new XMLReader();
        $reader->xml($xml);

        try {
            $document = $this->read($reader);
            return $document->getDocumentElement()?->getChildNodes() ?? [];
        } finally {
            $reader->close();
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
            libxml_set_external_entity_loader(null);
        }
    }

    /**
     * @throws DomException|ParseError
     */
    private function read(XMLReader $reader): XmlDocument
    {
        $document = new XmlDocument();
        $openElements = new \SplStack();
        $openElements->push($document);

        while ($this->readWithErrorHandling($reader)) {
            $parent = $openElements->top();
            match ($reader->nodeType) {
                XMLReader::ELEMENT => $this->handleElement($reader, $parent, $document, $openElements),
                XMLReader::END_ELEMENT => $openElements->pop(),
                XMLReader::TEXT, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE
                    => $this->handleText($reader, $parent, $document),
                XMLReader::CDATA => $this->handleCdataSection($reader, $parent, $document),
                XMLReader::COMMENT => $this->handleComment($reader, $parent, $document),
                XMLReader::DOC_TYPE => $this->handleDocumentType($reader, $parent, $document),
                XMLReader::PI => $this->handleProcessingInstruction($reader, $parent, $document),
                XMLReader::DOC => null,
                default => throw ParseError::unsupportedNodeType($reader->nodeType),
            };
        }
        $openElements->pop();
        return $document;
    }

    private function resolveEntities(string $publicId, string $systemId, array $context): ?string
    {
        if (isset(self::HTML_PUBLIC_IDS[$publicId])) {
            return __DIR__ . '/Parser/html.dtd';
        }
        return null;
    }

    /**
     * @throws ParseError
     */
    private function readWithErrorHandling(XMLReader $reader): bool
    {
        $result = $reader->read();
        if (!$result) {
            $errors = libxml_get_errors();
            foreach ($errors as $err) {
                throw ParseError::fromLibXML($err);
            }
        }
        return $result;
    }

    /**
     * @throws DomException
     */
    private function handleElement(XMLReader $reader, Node $parent, Document $document, \SplStack $openElements)
    {
        $isBlank = $reader->isEmptyElement;
        if ($ns = $reader->namespaceURI) {
            $element = $document->createElementNS($ns, $reader->name);
        } else {
            $element = $document->createElement($reader->localName);
        }
        for ($hasAttr = $reader->moveToFirstAttribute(); $hasAttr; $hasAttr = $reader->moveToNextAttribute()) {
            if ($ns = $reader->namespaceURI) {
                $element->setAttributeNS($ns, $reader->name, $reader->value);
            } else {
                $element->setAttribute($reader->localName, $reader->value);
            }
        }
        $parent->appendChild($element);
        if (!$isBlank) {
            $openElements->push($element);
        }
    }

    /**
     * @throws DomException
     */
    private function handleDocumentType(XMLReader $reader, Node $parent, Document $document)
    {
        $node = $this->parseDoctype($reader->readOuterXml());
        if (!$node) {
            $node = new DocumentType($reader->name);
        }
        $node->_doc = $document;
        $parent->appendChild($node);
    }

    /**
     * @throws DomException
     */
    private function handleText(XMLReader $reader, Node $parent, Document $document)
    {
        if (($prev = $parent->_last) && $prev instanceof Text) {
            $prev->appendData($reader->value);
        } else {
            $parent->appendChild($document->createTextNode($reader->value));
        }
    }

    /**
     * @throws DomException
     */
    private function handleCdataSection(XMLReader $reader, Node $parent, Document $document)
    {
        $parent->appendChild($document->createCDATASection($reader->value));
    }

    /**
     * @throws DomException
     */
    private function handleComment(XMLReader $reader, Node $parent, Document $document)
    {
        $parent->appendChild($document->createComment($reader->value));
    }

    /**
     * @throws DomException
     */
    private function handleProcessingInstruction(XMLReader $reader, Node $parent, Document $document)
    {
        $parent->appendChild($document->createProcessingInstruction($reader->name, $reader->value));
    }

    private const DOCTYPE_PATTERN = <<<'REGEXP'
    ~
    (?(DEFINE)
        (?<InternalSubset> \[ (?: [^\[\]] | (?&InternalSubset) )* ] )
    )
    ^
        <!DOCTYPE \s+ (?<name>\S+)
        (?: \s+ (?:
            SYSTEM \s+ (?<q>["']) (?<systemId> (?!\k<q>).* ) \k<q>
            |
            PUBLIC \s+ (?<q>["']) (?<publicId> (?!\k<q>).* ) \k<q> \s+ (?<q>["']) (?<systemId> (?!\k<q>).* ) \k<q>
        ) )?
        \s* (?: (?&InternalSubset) \s* )?
        >
    $
    ~Jx
    REGEXP;

    private function parseDoctype(string $xml): ?DocumentType
    {
        if (preg_match(self::DOCTYPE_PATTERN, $xml, $m)) {
            return new DocumentType($m['name'], $m['publicId'] ?? '', $m['systemId'] ?? '');
        }
        return null;
    }

    private function preprocessFragment(string $xml, Element $context): string
    {
        $declarations = [];
        $defaultNamespace = $this->collectNamespaceDeclarations($context, $declarations);
        $attributes = [];
        if ($defaultNamespace) {
            $attributes[] = sprintf('xmlns="%s"', $defaultNamespace);
        }
        foreach ($declarations as $prefix => $namespace) {
            $attributes[] = sprintf('xmlns:%s="%s"', $prefix, $namespace);
        }
        return sprintf(
            '<%1$s %2$s>%3$s</%1$s>',
            $context->qualifiedName,
            implode(' ', $attributes),
            $xml,
        );
    }

    /**
     * Step 2 of https://html.spec.whatwg.org/C/#xml-fragment-parsing-algorithm
     * The following code collects prefix-namespace mapping in scope on `$element`.
     *
     * @return string|null The default namespace
     */
    private function collectNamespaceDeclarations(Element $element, array &$map): ?string
    {
        $stack = new \SplStack();
        $stack->push($element);
        foreach (ElementTraversal::ancestorsOf($element) as $parent) {
            $stack->push($parent);
        }
        if ($stack->isEmpty()) return null;
        $defaultNamespace = null;
        /** @var Element $element */
        foreach ($stack as $element) {
            // According to https://dom.spec.whatwg.org/#locate-a-namespace,
            // a namespace from the element name should have higher priority.
            // So we check xmlns attributes first, then overwrite the map with the namespace of the element name.
            foreach ($element->_attrs as $attr) {
                if ($attr->localName === 'xmlns') {
                    $defaultNamespace = $attr->_value;
                } else if ($attr->prefix === 'xmlns') {
                    $map[$attr->localName] = $attr->_value;
                }
            }
            if ($element->namespaceURI === null) continue;
            if (!$element->prefix) {
                $defaultNamespace = $element->namespaceURI;
            } else {
                $map[$element->prefix] = $element->namespaceURI;
            }
        }

        return $defaultNamespace;
    }
}
