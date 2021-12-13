<?php declare(strict_types=1);

namespace Souplette\Dom\Serialization;

use Souplette\Dom\Attr;
use Souplette\Dom\CDATASection;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\InvalidStateError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Xml\QName;

final class NodeValidator implements NodeValidatorInterface
{
    private const VALID_TEXT = <<<'REGEXP'
    /
        ^
        [\x{09}\x{0A}\x{0D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}] *
        $
    /Sux
    REGEXP;

    private const VALID_DOCTYPE_PUBLIC_ID = <<<'REGEXP'
    ~
        ^
        [\x{20}\x{0D}\x{0A}A-Za-z0-9'()+,./:=?;!*#@$_%-]*
        $  
    ~x
    REGEXP;

    private const VALID_DOCTYPE_SYSTEM_ID = <<<'REGEXP'
    /
        ^
        [\x{09}\x{0A}\x{0D}\x{20}\x{21}\x{23}-\x{26}\x{28}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}] *
        $
    /Sux
    REGEXP;

    public function validateNode(Node $node): void
    {
        match ($node->nodeType) {
            Node::DOCUMENT_NODE => $this->validateDocument($node),
            Node::DOCUMENT_TYPE_NODE => $this->validateDocumentType($node),
            Node::ELEMENT_NODE => $this->validateElement($node),
            Node::ATTRIBUTE_NODE => $this->validateAttribute($node),
            Node::TEXT_NODE => $this->validateText($node),
            Node::CDATA_SECTION_NODE => $this->validateCDATASection($node),
            Node::COMMENT_NODE => $this->validateComment($node),
            Node::PROCESSING_INSTRUCTION_NODE => $this->validateProcessingInstruction($node),
            default => null,
        };
    }

    public function validateDocument(Document $node): void
    {
        if (!$node->getDocumentElement()) {
            throw new InvalidStateError('Missing document element node.');
        }
    }

    public function validateDocumentType(DocumentType $node): void
    {
        if ($node->publicId && !preg_match(self::VALID_DOCTYPE_PUBLIC_ID, $node->publicId)) {
            throw new SyntaxError('Doctype public ID contains invalid characters.');
        }
        if ($node->systemId && (!preg_match(self::VALID_DOCTYPE_SYSTEM_ID, $node->systemId))) {
            throw new SyntaxError('Doctype system ID contains invalid characters.');
        }
    }

    public function validateElement(Element $node): void
    {
        if (str_contains($node->localName, ':')
            || !preg_match(QName::NAME_PATTERN, $node->localName)
        ) {
            throw new SyntaxError(sprintf(
                'Element local name contains invalid characters: "%s".',
                $node->localName,
            ));
        }
        if ($node->prefix === 'xmlns') {
            throw new NamespaceError('Element has an xmlns prefix.');
        }

        $attributeSet = [];
        foreach ($node->_attrs as $attr) {
            if (isset($attributeSet[$attr->namespaceURI][$attr->localName])) {
                throw new InvalidStateError(sprintf(
                    'Duplicate attribute "%s".',
                    $attr->name,
                ));
            }
            $attributeSet[$attr->namespaceURI][$attr->localName] = true;
        }
    }

    public function validateAttribute(Attr $attr): void
    {
        if (str_contains($attr->localName, ':')
            || !preg_match(QName::NAME_PATTERN, $attr->localName)
        ) {
            throw new SyntaxError(sprintf(
                'Invalid attribute name: "%s"',
                $attr->localName,
            ));
        }
        if ($attr->localName === 'xmlns' && !$attr->namespaceURI) {
            throw new NamespaceError(
                'Attribute having local name "xmlns" must be in the XMLNS namespace.'
            );
        }
        if ($attr->namespaceURI === Namespaces::XMLNS) {
            // 3.5.2.2
            if ($attr->_value === Namespaces::XMLNS) {
                throw new NamespaceError('The XMLNS namespace is reserved.');
            }
            // 3.5.2.3
            if (!$attr->_value) {
                throw new NamespaceError(
                    'Namespace prefix declarations cannot be used to undeclare a namespace'
                    . ' (use a default namespace declaration instead).'
                );
            }
        }

        $this->validateAttributeValue($attr->_value);
    }

    public function validateAttributeValue(string $value): void
    {
        if (!$value) return;

        if (!preg_match(self::VALID_TEXT, $value)) {
            throw new SyntaxError(sprintf(
                'Attribute value contains illegal characters: "%s".',
                $value,
            ));
        }
    }

    public function validateText(Text $node): void
    {
        if (!preg_match(self::VALID_TEXT, $node->_value)) {
            throw new SyntaxError('Text node contains invalid characters.');
        }
    }

    public function validateCDATASection(CDATASection $node): void
    {
        if (!$node->_value) return;

        if (str_contains($node->_value, ']]>')) {
            throw new SyntaxError('CDATA section contains "]]>".');
        }
        if (!preg_match(self::VALID_TEXT, $node->_value)) {
            throw new SyntaxError('CDATA section contains invalid characters.');
        }
    }

    public function validateComment(Comment $node): void
    {
        if (!$node->_value) return;

        if (!preg_match(self::VALID_TEXT, $node->_value)
            || preg_match('/--|-$/', $node->_value)
        ) {
            throw new SyntaxError('Comment node contains invalid characters.');
        }
    }

    public function validateProcessingInstruction(ProcessingInstruction $node): void
    {
        if (str_contains($node->target, ':')) {
            throw new SyntaxError('Processing instruction target contains invalid characters.');
        }
        if (strcasecmp($node->target, 'xml') === 0) {
            throw new SyntaxError('Processing instruction target cannot be "xml".');
        }
        if (str_contains($node->_value, '?>') || !preg_match(self::VALID_TEXT, $node->_value)) {
            throw new SyntaxError('Processing instruction data contains invalid characters.');
        }
    }
}
