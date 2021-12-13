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

interface NodeValidatorInterface
{
    public function validateNode(Node $node): void;

    public function validateDocument(Document $node): void;

    public function validateDocumentType(DocumentType $node): void;

    public function validateElement(Element $node): void;

    public function validateAttribute(Attr $attr): void;

    public function validateAttributeValue(string $value): void;

    public function validateText(Text $node): void;

    public function validateCDATASection(CDATASection $node): void;

    public function validateComment(Comment $node): void;

    public function validateProcessingInstruction(ProcessingInstruction $node): void;
}
