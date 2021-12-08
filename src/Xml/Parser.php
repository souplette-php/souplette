<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Internal\BaseNode;
use Souplette\Dom\Node;
use Souplette\Dom\Text;
use Souplette\Dom\XmlDocument;
use XMLReader;

final class Parser extends BaseNode
{
    protected ?Document $document;
    /** @var \SplStack<Node> */
    private \SplStack $openElements;

    public function parse(string $xml): Document
    {
        $reader = new XMLReader();
        $reader->xml($xml);
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

        $this->document = new XmlDocument();
        $this->openElements = new \SplStack();
        $this->openElements->push($this->document);
        while ($reader->read()) {
            match ($reader->nodeType) {
                XMLReader::ELEMENT => $this->handleElement($reader),
                XMLReader::END_ELEMENT => $this->openElements->pop(),
                XMLReader::TEXT, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE
                    => $this->handleText($reader),
                XMLReader::CDATA => $this->handleCdataSection($reader),
                XMLReader::COMMENT => $this->handleComment($reader),
                XMLReader::DOC_TYPE => $this->handleDocumentType($reader),
                XMLReader::PI => $this->handleProcessingInstruction($reader),
                default => null,
            };
        }
        $this->openElements->pop();
        $reader->close();
        return $this->document;
    }

    private function handleElement(XMLReader $reader)
    {
        $isBlank = $reader->isEmptyElement;
        if ($ns = $reader->namespaceURI) {
            $element = $this->document->createElementNS($ns, $reader->name);
        } else {
            $element = $this->document->createElement($reader->localName);
        }
        for ($hasAttr = $reader->moveToFirstAttribute(); $hasAttr; $hasAttr = $reader->moveToNextAttribute()) {
            if ($ns = $reader->namespaceURI) {
                $element->setAttributeNS($ns, $reader->name, $reader->value);
            } else {
                $element->setAttribute($reader->localName, $reader->value);
            }
        }
        $parent = $this->openElements->top();
        $parent->appendChild($element);
        if (!$isBlank) {
            $this->openElements->push($element);
        }
    }

    private function handleDocumentType(XMLReader $reader)
    {
        $node = $this->parseDoctype($reader->readOuterXml());
        if (!$node) {
            $node = new DocumentType($reader->name);
        }
        $parent = $this->openElements->top();
        $node->document = $this->document;
        $parent->appendChild($node);
    }

    private function handleText(XMLReader $reader)
    {
        $parent = $this->openElements->top();
        if (($prev = $parent->last) && $prev instanceof Text) {
            $prev->appendData($reader->value);
        } else {
            $parent->appendChild($this->document->createTextNode($reader->value));
        }
    }

    private function handleCdataSection(XMLReader $reader)
    {
        $parent = $this->openElements->top();
        $node = $this->document->createCDATASection($reader->value);
        $parent->appendChild($node);
    }

    private function handleComment(XMLReader $reader)
    {
        $parent = $this->openElements->top();
        $node = $this->document->createComment($reader->value);
        $parent->appendChild($node);
    }

    private function handleProcessingInstruction(XMLReader $reader)
    {
        $parent = $this->openElements->top();
        $node = $this->document->createProcessingInstruction($reader->name, $reader->value);
        $parent->appendChild($node);
    }

    private const DOCTYPE_PATTERN = <<<'REGEXP'
    ~
    (?(DEFINE)
        (?<InternalSubset> \[ (?: [^\[\]] | (?&InternalSubset) )* ] )
    )
    ^
        <!DOCTYPE \s+ (?<name>\w+)
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
}
