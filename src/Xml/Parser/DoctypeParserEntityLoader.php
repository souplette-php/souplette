<?php declare(strict_types=1);

namespace Souplette\Xml\Parser;

use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;

/**
 * XMLReader doesn't pass public & system id information to the handler,
 * so we have to hack the entity loading system.
 */
final class DoctypeParserEntityLoader implements ExternalEntityLoaderInterface
{
    private ?Document $document;

    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }

    public function __invoke(?string $publicId, string $systemId, array $context)
    {
        $doctype = new DocumentType($context['intSubName'], $publicId ?? '', $systemId);
        $this->document->parserInsertBefore($doctype, null);
        return null;
    }
}
