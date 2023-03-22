<?php declare(strict_types=1);

namespace Souplette\XML;

use Souplette\DOM\Document;
use Souplette\DOM\DocumentType;
use Souplette\DOM\Element;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Node;
use Souplette\DOM\Text;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\DOM\XMLDocument;
use Souplette\XML\Exception\ParseError;
use Souplette\XML\Parser\DoctypeParserEntityLoader;
use Souplette\XML\Parser\EntityLoaderChain;
use Souplette\XML\Parser\ExternalEntityLoaderInterface;
use Souplette\XML\Parser\HTMLEntityLoader;
use XMLReader;

/**
 * @see https://html.spec.whatwg.org/multipage/xhtml.html#xml-parser
 */
final class XMLParser
{
    private EntityLoaderChain $entityLoader;
    private DoctypeParserEntityLoader $doctypeParser;

    public function __construct()
    {
        $this->entityLoader = new EntityLoaderChain([
            $this->doctypeParser = new DoctypeParserEntityLoader(),
            new HTMLEntityLoader(),
        ]);
    }

    public function withExternalEntityLoader(ExternalEntityLoaderInterface ...$loaders): self
    {
        foreach ($loaders as $loader) {
            $this->entityLoader->add($loader);
        }
        return $this;
    }

    /**
     * @throws DOMException|ParseError
     */
    public function parse(string $xml): XMLDocument
    {
        $declaration = $this->parseXMLDeclaration($xml);

        libxml_set_external_entity_loader($this->entityLoader);
        $internalErrors = libxml_use_internal_errors(true);

        $reader = new XMLReader();
        $reader->xml($xml);
        $reader->setParserProperty(XMLReader::LOADDTD, true);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

        try {
            $doc = $this->read($reader);
            if ($declaration) {
                $this->handleXMLDeclaration($doc, $declaration['version'], $declaration['encoding'], $declaration['standalone']);
            }
            return $doc;
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
     * @throws DOMException
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
     * @throws DOMException|ParseError
     */
    private function read(XMLReader $reader): XMLDocument
    {
        $document = new XMLDocument();
        $openElements = new \SplStack();
        $openElements->push($document);
        $this->doctypeParser->setDocument($document);

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
                default => throw ParseError::unsupportedNodeType($reader->nodeType),
            };
        }
        $openElements->pop();
        return $document;
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

    private function handleXMLDeclaration(Document $document, string $version, ?string $encoding, ?string $standalone): void
    {
        $document->_hasXMLDeclaration = true;
        $document->_xmlVersion = $version;
        if ($encoding) $document->inputEncoding = $encoding;
        if ($standalone) $document->_xmlStandalone = $standalone === 'yes';
    }

    /**
     * @throws DOMException
     */
    private function handleElement(XMLReader $reader, Node $parent, Document $document, \SplStack $openElements): void
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
     * @throws DOMException
     */
    private function handleDocumentType(XMLReader $reader, Node $parent, Document $document): void
    {
        if ($document->_doctype) return;
        $document->parserInsertBefore(new DocumentType($reader->name), null);
    }

    /**
     * @throws DOMException
     */
    private function handleText(XMLReader $reader, Node $parent, Document $document): void
    {
        if (($prev = $parent->_last) && $prev instanceof Text) {
            $prev->appendData($reader->value);
        } else {
            $parent->appendChild($document->createTextNode($reader->value));
        }
    }

    /**
     * @throws DOMException
     */
    private function handleCdataSection(XMLReader $reader, Node $parent, Document $document): void
    {
        $parent->appendChild($document->createCDATASection($reader->value));
    }

    /**
     * @throws DOMException
     */
    private function handleComment(XMLReader $reader, Node $parent, Document $document): void
    {
        $parent->appendChild($document->createComment($reader->value));
    }

    /**
     * @throws DOMException
     */
    private function handleProcessingInstruction(XMLReader $reader, Node $parent, Document $document): void
    {
        $parent->appendChild($document->createProcessingInstruction($reader->name, $reader->value));
    }

    private const XML_DECL_PATTERN = <<<'REGEXP'
    /
    ^
    <\?xml \s+ version = (?<q1>["']) (?<version> 1\.[0-9]+ ) \k<q1>
        (?:
            \s+ encoding =  (?<q2>["']) (?<encoding> [A-Za-z] ([A-Za-z0-9._-])* ) \k<q2>
            | \s+ standalone =  (?<q3>["']) (?<standalone> yes | no ) \k<q3>
        )*
        \s*
    \?>
    /Jx
    REGEXP;

    private function parseXMLDeclaration(string $xml): array
    {
        if (preg_match(self::XML_DECL_PATTERN, $xml, $m, \PREG_UNMATCHED_AS_NULL)) {
            return [
                'version' => $m['version'],
                'encoding' => $m['encoding'],
                'standalone' => $m['standalone'],
            ];
        }
        return [];
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
