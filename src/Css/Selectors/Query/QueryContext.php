<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

use DOMDocument;
use DOMElement;
use Souplette\Dom\Internal\DomIdioms;
use Souplette\Dom\Node\HtmlDocument;

final class QueryContext
{
    public static function of(
        \DOMParentNode $scopingRoot,
    ): self {
        $document = DomIdioms::getOwnerDocument($scopingRoot);
        if ($document === null) {
            throw new \RuntimeException('HierarchyRequestError');
        }
        $isHtml = self::isHtmlDocument($document);
        $isQuirksMode = $isHtml && self::isQuirksMode($document);

        return new self(
            $document,
            $scopingRoot,
            caseInsensitiveClasses: $isQuirksMode,
            caseInsensitiveIds: $isQuirksMode,
            caseInsensitiveTypes: $isHtml,
        );
    }

    private function __construct(
        public DOMDocument $document,
        public \DOMParentNode $scopingRoot,
        public bool $caseInsensitiveClasses = false,
        public bool $caseInsensitiveIds = false,
        public bool $caseInsensitiveTypes = true,
    ) {
    }

    public function withScope(DOMElement $element): self
    {
        if ($element->ownerDocument !== $this->document) {
            throw new \RuntimeException('HierarchyRequestError');
        }
        $ctx = clone $this;
        $ctx->scopingRoot = $element;
        return $ctx;
    }

    private static function isQuirksMode(DOMDocument $document): bool
    {
        if ($document instanceof HtmlDocument) {
            return $document->getCompatMode() === HtmlDocument::COMPAT_MODE_BACK;
        }
        return true;
    }

    private static function isHtmlDocument(DOMDocument $document): bool
    {
        if ($document instanceof HtmlDocument) {
            return true;
        }
        return match ($document->nodeType) {
            XML_HTML_DOCUMENT_NODE => true,
            XML_DOCUMENT_NODE => false,
        };
    }
}
