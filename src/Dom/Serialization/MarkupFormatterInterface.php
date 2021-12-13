<?php declare(strict_types=1);

namespace Souplette\Dom\Serialization;

use Souplette\Dom\Attr;
use Souplette\Dom\CDATASection;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;

interface MarkupFormatterInterface
{
    public function getSerializationType(): SerializationType;

    public function formatStartMarkup(Node $node): string;
    public function formatEndMarkup(Element $node, string $localName, ?string $prefix = null): string;

    public function formatStartTagOpen(?string $prefix, string $localName): string;
    public function formatStartTagClose(Element $node): string;

    public function formatAttribute(string $prefix, string $localName, string $value): string;
    public function formatAttributeValue(string $value): string;
    public function formatAttributeAsHTML(Attr $attr, string $value): string;
    public function formatAttributeAsXMLWithoutNamespace(Attr $attr, string $value): string;

    public function formatText(Text $node): string;
    public function formatCDATASection(CDATASection $node): string;
    public function formatComment(Comment $node): string;

    public function formatDocumentType(DocumentType $node): string;
    public function formatProcessingInstruction(ProcessingInstruction $node): string;

    public function formatXMLDeclaration(Document $node): string;
}
