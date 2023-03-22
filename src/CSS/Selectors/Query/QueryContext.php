<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Query;

use Souplette\CSS\Selectors\Node\ComplexSelector;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\DOM\Internal\DocumentMode;
use Souplette\DOM\ParentNode;

final class QueryContext
{
    public ?ParentNode $relativeLeftMostElement = null;
    /**
     * From the shortest argument selector match, we need to get the element that matches
     * the leftmost compound selector to mark the correct scope elements of :has() pseudo class
     * having the argument selectors starts with descendant combinator.
     *
     * <main id=main>
     *   <div id=d1>
     *     <div id=d2 class="a">
     *       <div id=d3 class="a">
     *         <div id=d4>
     *           <div id=d5 class="b">
     *           </div>
     *         </div>
     *       </div>
     *     </div>
     *   </div>
     * </div>
     * <script>
     *  main.querySelectorAll('div:has(.a .b)'); // Should return #d1, #d2
     * </script>
     *
     * In case of the above example, div#d5 element matches the argument selector '.a .b'.
     * Among the ancestors of the div#d5, the div#d3 and div#d4 is not the correct candidate scope element of ':has(.a .b)'
     * because those elements don't have .a element as its descendant.
     * So instead of marking ancestors of div#d5, we should mark ancestors of div#d3 to prevent incorrect marking.
     * In case of the shortest match for the argument selector '.a .b' on div#d5 element,
     * the div#d3 is the element that matches the leftmost compound selector '.a'.
     * So the MatchResult will return the div#d3 element for the matching operation.
     *
     * In case of matching none descendant relative argument selectors,
     * we can get the candidate leftmost compound matches while matching the argument selector.
     * To process the 'main.querySelectorAll("div:has(:scope > .a .b)")' on the above DOM tree,
     * selector checker will try to match the argument selector ':scope > .a .b' on the descendants of #d1 div
     * element with the :scope element as #d1.
     * When it matches the argument selector on #d5 element, the matching result is true
     * and it can get the element that matches the leftmost(except :scope) compound '.a' as #d2 element.
     * But while matching the argument selector on the #d5 element,
     * selector checker can also aware that the #d3 element can be a leftmost compound matching element
     * when the scope element is #d2 element.
     * So the selector checker will return the #d2 and #d3 element so that
     * #d1 and #d2 can be marked as matched with ':has(:scope > .a .b)'
     *
     * @var Element[]|null
     */
    public ?array $hasArgumentLeftMostCompoundMatches = null;
    public ?array $hasMatchedCache = null;

    public static function of(
        ParentNode $scopingRoot,
    ): self {
        $document = $scopingRoot->getDocumentNode();
        if ($document === null) {
            throw new \RuntimeException('HierarchyRequestError');
        }
        $isHtmlDoc = $document->isHTML;
        $isQuirksMode = $isHtmlDoc && $document->_mode === DocumentMode::QUIRKS;

        return new self(
            $document,
            $scopingRoot,
            isHtml: $isHtmlDoc,
            caseInsensitiveClasses: $isQuirksMode,
            caseInsensitiveIds: $isQuirksMode,
            caseInsensitiveTypes: $isHtmlDoc,
        );
    }

    private function __construct(
        public Document $document,
        public ParentNode $scopingRoot,
        public bool $isHtml = true,
        public bool $caseInsensitiveClasses = false,
        public bool $caseInsensitiveIds = false,
        public bool $caseInsensitiveTypes = true,
    ) {
    }

    public function withScope(Element $element): self
    {
        if ($element->getDocumentNode() !== $this->document) {
            throw new \RuntimeException('HierarchyRequestError');
        }
        $ctx = clone $this;
        $ctx->scopingRoot = $element;
        return $ctx;
    }

    public function getHasMatchedCache(ComplexSelector $selector): \SplObjectStorage
    {
        $this->hasMatchedCache ??= [];
        $key = (string)$selector;
        if ($cache = $this->hasMatchedCache[$key] ?? null) {
            return $cache;
        }
        return $this->hasMatchedCache[$key] = new \SplObjectStorage();
    }
}
