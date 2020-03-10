<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use SplStack;

final class TreeBuilder
{
    private const RULE_SETS = [
        InsertionModes::INITIAL => RuleSet\Initial::class,
        InsertionModes::BEFORE_HTML => RuleSet\BeforeHtml::class,
        InsertionModes::BEFORE_HEAD => RuleSet\BeforeHead::class,
        InsertionModes::IN_HEAD => RuleSet\InHead::class,
        InsertionModes::IN_HEAD_NOSCRIPT => RuleSet\InHeadNoscript::class,
        InsertionModes::AFTER_HEAD => RuleSet\AfterHead::class,
        InsertionModes::IN_BODY => RuleSet\InBody::class,
        InsertionModes::TEXT => RuleSet\Text::class,
        InsertionModes::IN_TABLE => RuleSet\InTable::class,
        InsertionModes::IN_TABLE_TEXT => RuleSet\InTableText::class,
        InsertionModes::IN_CAPTION => RuleSet\InCaption::class,
        InsertionModes::IN_COLUMN_GROUP => RuleSet\InColumnGroup::class,
        InsertionModes::IN_TABLE_BODY => RuleSet\InTableBody::class,
        InsertionModes::IN_ROW => RuleSet\InRow::class,
        InsertionModes::IN_CELL => RuleSet\InCell::class,
        InsertionModes::IN_SELECT => RuleSet\InSelect::class,
        InsertionModes::IN_SELECT_IN_TABLE => RuleSet\InSelectInTable::class,
        InsertionModes::IN_TEMPLATE => RuleSet\InTemplate::class,
        InsertionModes::AFTER_BODY => RuleSet\AfterBody::class,
        InsertionModes::IN_FRAMESET => RuleSet\InFrameset::class,
        InsertionModes::AFTER_FRAMESET => RuleSet\AfterFrameset::class,
        InsertionModes::AFTER_AFTER_BODY => RuleSet\AfterAfterBody::class,
        InsertionModes::AFTER_AFTER_FRAMESET => RuleSet\AfterAfterFrameset::class,
    ];
    /**
     * @var Tokenizer
     */
    public $tokenizer;
    /**
     * @var \DOMImplementation
     */
    private $dom;
    /**
     * @var \DOMDocument
     */
    public $document;
    /**
     * @var string
     */
    public $compatMode = CompatModes::NO_QUIRKS;
    /**
     * @var int
     */
    public $insertionMode;
    /**
     * @var int
     */
    public $originalInsertionMode;
    /**
     * @var OpenElementsStack
     */
    public $openElements;
    /**
     * @var ActiveFormattingElementList
     */
    public $activeFormattingElements;
    /**
     * @var bool
     */
    private $isBuildingFragment = false;
    /**
     * @var \DOMElement
     */
    private $contextElement;
    /**
     * @var RuleSet
     */
    private $rules;
    /**
     * @var RuleSet[]
     */
    private $ruleSets = [];
    public $headElement;
    public $formElement;
    private $insertFromTable = false;
    private $fosterParenting = false;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#frameset-ok-flag
     * @var bool
     */
    public $framesetOK = true;
    /**
     * @var Token
     */
    private $currentToken;

    public function __construct(\DOMImplementation $dom)
    {
        $this->dom = $dom;
        $this->ruleSets = $this->createRuleSets();
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing
     * @param Tokenizer $tokenizer
     * @return \DOMDocument
     */
    public function buildDocument(Tokenizer $tokenizer): \DOMDocument
    {
        $this->tokenizer = $tokenizer;
        $this->reset();
        $this->run();
        return $this->document;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-html-fragments
     * @param Tokenizer $tokenizer
     * @param \DOMElement $contextElement
     * @return \DOMNode[]
     */
    public function buildFragment(Tokenizer $tokenizer, \DOMElement $contextElement): array
    {
        $this->tokenizer = $tokenizer;
        $this->reset();
        $this->isBuildingFragment = true;
        $this->contextElement = $contextElement;

        $tagName = $this->contextElement->tagName;
        if (isset(Elements::CDATA_ELEMENTS[$tagName])) {
            $this->tokenizer->state = TokenizerStates::RCDATA;
        } elseif (isset(Elements::RCDATA_ELEMENTS[$tagName])) {
            $this->tokenizer->state = TokenizerStates::RAWTEXT;
        } elseif ($tagName === 'plaintext') {
            $this->tokenizer->state = TokenizerStates::PLAINTEXT;
        }
        $this->insertionMode = InsertionModes::BEFORE_HTML;
        $this->rules = $this->ruleSets[$this->insertionMode];
        $this->rules->insertHtmlElement();
        $this->resetInsertionModeAppropriately();

        $this->run();

        return iterator_to_array($this->document->documentElement->childNodes);
    }

    private function reset(): void
    {
        $this->compatMode = CompatModes::NO_QUIRKS;
        $this->isBuildingFragment = false;
        $this->openElements = new OpenElementsStack();
        $this->activeFormattingElements = new ActiveFormattingElementList();
        $this->contextElement = null;
        $this->fosterParenting = false;
        $this->framesetOK = true;
        $this->insertionMode = InsertionModes::INITIAL;
        $this->rules = $this->ruleSets[$this->insertionMode];
        $this->document = $this->dom->createDocument();
    }

    private function run()
    {
        foreach ($this->tokenizer->tokenize() as $token) {
            $this->processToken($token);
        }
    }

    public function processToken(Token $token, ?int $insertionMode = null)
    {
        $this->currentToken = $token;
        if ($insertionMode !== null) {
            return $this->ruleSets[$insertionMode]->process($token, $this);
        }
        return $this->rules->process($token, $this);
    }

    public function setInsertionMode(int $mode): void
    {
        $this->insertionMode = $mode;
        $this->rules = $this->ruleSets[$mode];
    }

    /**
     * @return RuleSet[]
     */
    private function createRuleSets(): array
    {
        $rules = [];
        foreach (self::RULE_SETS as $insertionMode => $ruleSet) {
            $rules[$insertionMode] = new $ruleSet();
        }
        return $rules;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#reset-the-insertion-mode-appropriately
     */
    private function resetInsertionModeAppropriately()
    {
        // Shortcut for steps 6, 7, 8, 9, 10 & 13
        $nextModes = [
            'tr' => InsertionModes::IN_ROW,
            'tbody' => InsertionModes::IN_TABLE_BODY,
            'thead' => InsertionModes::IN_TABLE_BODY,
            'tfoot' => InsertionModes::IN_TABLE_BODY,
            'caption' => InsertionModes::IN_CAPTION,
            'colgroup' => InsertionModes::IN_COLUMN_GROUP,
            'table' => InsertionModes::IN_TABLE,
            'body' => InsertionModes::IN_BODY,
        ];
        // 1. Let last be false.
        $last = false;
        // 2. Let node be the last node in the stack of open elements.
        $openElements = $this->openElements;
        $openElements->rewind();
        $node = $openElements->current();
        while ($node) {
            // 3. If node is the first node in the stack of open elements
            if ($node === $openElements->bottom()) {
                // then set last to true
                $last = true;
                // and if the parser was created as part of the HTML fragment parsing algorithm (fragment case)
                if ($this->isBuildingFragment) {
                    // set node to the context element passed to that algorithm.
                    $node = $this->contextElement;
                }
            }
            $nodeName = $node->tagName;
            // 4. If node is a select element, run these substeps:
            if ($nodeName === 'select') {
                // 4.1 If last is true, jump to the step below labeled done.
                if (!$last) {
                    // 4.2 Let ancestor be node
                    $ancestor = $node;
                    // 4.3 Loop: If ancestor is the first node in the stack of open elements, jump to the step below labeled done.
                    while ($ancestor && $ancestor !== $openElements->bottom()) {
                        // 4.4 Let ancestor be the node before ancestor in the stack of open elements.
                        $openElements->next();
                        $ancestor = $openElements->current();
                        if (!$ancestor) break;
                        // 4.5 If ancestor is a template node, jump to the step below labeled done.
                        if ($ancestor->tagName === 'template') break;
                        // 4.6 If ancestor is a table node, switch the insertion mode to "in select in table" and return.
                        if ($ancestor->tagName === 'table') {
                            $this->insertionMode = InsertionModes::IN_SELECT_IN_TABLE;
                            break 2;
                        }
                        // 4.7 Jump back to the step labeled loop.
                    }
                }
                // 4.8 Done: Switch the insertion mode to "in select" and return.
                $this->insertionMode = InsertionModes::IN_SELECT;
                break;
            }
            // 5. If node is a td or th element and last is false, then switch the insertion mode to "in cell" and return.
            if (!$last && ($nodeName === 'td' || $nodeName === 'th')) {
                $this->insertionMode = InsertionModes::IN_CELL;
                break;
            }
            // Covers steps 6, 7, 8, 9, 10, 13
            if (isset($nextModes[$nodeName])) {
                $this->insertionMode = $nextModes[$nodeName];
                break;
            }
            // 11. If node is a template element, then switch the insertion mode to the current template insertion mode and return.
            if ($nodeName === 'template') {
                // FIXME: this is not good
                $this->insertionMode = InsertionModes::IN_TEMPLATE;
                break;
            }
            // 12. If node is a head element and last is false, then switch the insertion mode to "in head" and return.
            if (!$last && $nodeName === 'head') {
                $this->insertionMode = InsertionModes::IN_HEAD;
                break;
            }
            // 14. If node is a frameset element, then switch the insertion mode to "in frameset" and return. (fragment case)
            if ($this->isBuildingFragment && $nodeName === 'frameset') {
                $this->insertionMode = InsertionModes::IN_FRAMESET;
                break;
            }
            // 15. If node is an html element, run these substeps:
            if ($nodeName === 'html') {
                // 15.1 If the head element pointer is null, switch the insertion mode to "before head" and return. (fragment case)
                // 15.2 Otherwise, the head element pointer is not null, switch the insertion mode to "after head" and return.
                break;
            }
            // 16. If last is true, then switch the insertion mode to "in body" and return. (fragment case)
            if ($last && $this->isBuildingFragment) {
                $this->insertionMode = InsertionModes::IN_BODY;
            }
            // 17. Let node now be the node before node in the stack of open elements.
            $openElements->next();
            $node = $openElements->current();
        }
        $this->rules = $this->ruleSets[$this->insertionMode];
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#closing-elements-that-have-implied-end-tags
     * @param string $excluded
     * @param bool $thoroughly
     */
    public function generateImpliedEndTags(?string $excluded = null, bool $thoroughly = false)
    {
        $impliedTags = $thoroughly ? Elements::END_TAG_IMPLIED_THOROUGH : Elements::END_TAG_IMPLIED;
        while (true) {
            $node = $this->openElements->top();
            if (!$node || $node->tagName === $excluded) {
                return;
            }
            if (isset($impliedTags[$node->tagName])) {
                $this->openElements->pop();
                continue;
            }
            return;
        }
    }

    public function getAdjustedCurrentNode(): \DOMElement
    {
        if ($this->isBuildingFragment && $this->openElements->count() === 1) {
            return $this->contextElement;
        }
        return $this->openElements->top();
    }

    public function appropriatePlaceForInsertingANode(?\DOMElement $overrideTarget = null): InsertionLocation
    {
        // @see https://html.spec.whatwg.org/multipage/parsing.html#creating-and-inserting-nodes
        /** @var \DOMElement $target */
        $target = $overrideTarget ?: $this->openElements->top();
        if ($this->fosterParenting && isset(Elements::TABLE_INSERT_MODE_ELEMENTS[$target->tagName])) {
            // TODO: Run these substeps:
            // 1. Let last template be the last template element in the stack of open elements, if any.
            // 2. Let last table be the last table element in the stack of open elements, if any.
            $lastTemplate = null;
            $lastTemplatePosition = null;
            $lastTable = null;
            $lastTablePosition = null;
            foreach ($this->openElements as $pos => $element) {
                if ($element->tagName === 'template') {
                    $lastTemplate = $element;
                    $lastTemplatePosition = $pos;
                    break;
                }
                if ($element->tagName === 'table') {
                    $lastTable = $element;
                    $lastTablePosition = $pos;
                    break;
                }
            }
            if ($lastTemplate && (!$lastTable || $lastTemplatePosition < $lastTablePosition)) {
                // 3. If there is a last template and either there is no last table,
                // or there is one, but last template is lower (more recently added) than last table in the stack of open elements,
                // then: let adjusted insertion location be inside last template's template contents, after its last child (if any),
                // and abort these steps.
                // FIXME: nogood !
                $adjustedInsertionLocation = new InsertionLocation($lastTemplate, $lastTemplate->lastChild);
            } elseif ($this->isBuildingFragment && !$lastTable) {
                // 4. If there is no last table,
                // then let adjusted insertion location be inside the first element in the stack of open elements (the html element),
                // after its last child (if any),
                // and abort these steps. (fragment case)
                $parent = $this->openElements->top();
                $adjustedInsertionLocation = new InsertionLocation($parent, $parent->lastChild);
            } elseif ($lastTable && $lastTable->parentNode) {
                // 5. If last table has a parent node,
                // then let adjusted insertion location be inside last table's parent node,
                // immediately before last table, and abort these steps.
                $adjustedInsertionLocation = new InsertionLocation($lastTable->parentNode, $lastTable->previousSibling);
            } else {
                // 6. Let previous element be the element immediately above last table in the stack of open elements.
                $previousElement = $this->openElements[$lastTablePosition - 1];
                // 7. Let adjusted insertion location be inside previous element, after its last child (if any).
                $adjustedInsertionLocation = new InsertionLocation($previousElement, $previousElement->lastChild);
            }
        } else {
            $adjustedInsertionLocation = new InsertionLocation($target, $target->lastChild);
        }
        // 3. TODO: If the adjusted insertion location is inside a template element,
        // let it instead be inside the template element's template contents, after its last child (if any).
        // 4. Return the adjusted insertion location.
        return $adjustedInsertionLocation;
    }

    public function createDoctype(Token\Doctype $token)
    {
        return $this->dom->createDocumentType($token->name, $token->publicIdentifier ?: '', $token->publicIdentifier ?: '');
    }

    public function createElement(Token\Tag $token, string $namespace, \DOMNode $intendedParent): \DOMElement
    {
        // 1. Let document be intended parent's node document.
        if ($intendedParent instanceof \DOMDocument) {
            $doc = $intendedParent;
        } else {
            $doc = $intendedParent->ownerDocument;
        }
        // 2. Let local name be the tag name of the token.
        $localName = $token->name;
        // 7. Let element be the result of creating an element given document, localName, given namespace, null, and is.
        // If will execute script is true, set the synchronous custom elements flag; otherwise, leave it unset.
        $qname = sprintf('%s:%s', Namespaces::PREFIXES[$namespace], $localName);
        $element = $doc->createElementNS($namespace, $qname);
        // 8. Append each attribute in the given token to element.
        if ($token->attributes) {
            foreach ($token->attributes as [$name, $value]) {
                if (!$element->hasAttribute($name)) {
                    $element->setAttribute($name, $value);
                }
            }
        }

        return $element;
    }

    public function insertCharacter(Token\Character $token, ?string $data = null)
    {
        // 1. Let data be the characters passed to the algorithm, or,
        // if no characters were explicitly specified, the character of the character token being processed.
        if ($data === null) {
            $data = $token->data;
        }
        // 2. Let the adjusted insertion location be the appropriate place for inserting a node.
        $location = $this->appropriatePlaceForInsertingANode();
        // 3. If the adjusted insertion location is in a Document node, then return.
        if ($location->parent->nodeType === XML_HTML_DOCUMENT_NODE) {
            return;
        }
        $target = $location->target;
        if ($target && $target->previousSibling && $target->previousSibling->nodeType === XML_TEXT_NODE) {
            // 4. If there is a Text node immediately before the adjusted insertion location,
            // then append data to that Text node's data.
            $target->previousSibling->nodeValue .= $data;
        } else {
            // Otherwise, create a new Text node whose data is data
            // and whose node document is the same as that of the element in which the adjusted insertion location finds itself,
            $doc = $location->parent->ownerDocument;
            $node = $doc->createTextNode($data);
            // and insert the newly created node at the adjusted insertion location.
            $location->insert($node);
        }
    }

    public function insertComment(Token\Comment $token, ?InsertionLocation $position = null)
    {
        // 1. Let data be the data given in the comment token being processed.
        $data = $token->data;
        // 2. If position was specified, then let the adjusted insertion location be position.
        // Otherwise, let adjusted insertion location be the appropriate place for inserting a node.
        $location = $position ?: $this->appropriatePlaceForInsertingANode();
        // 3. Create a Comment node whose data attribute is set to data
        // and whose node document is the same as that of the node in which the adjusted insertion location finds itself.
        $node = $location->parent->ownerDocument->createComment($data);
        // 4. Insert the newly created node at the adjusted insertion location.
        $location->insert($node);
    }

    public function insertElement(Token\Tag $token, string $namespace = Namespaces::HTML)
    {
        // 1. Let the adjusted insertion location be the appropriate place for inserting a node.
        $location = $this->appropriatePlaceForInsertingANode();
        // 2. Let element be the result of creating an element for the token in the given namespace,
        // with the intended parent being the element in which the adjusted insertion location finds itself.
        $element = $this->createElement($token, $namespace, $location->parent);
        // 3. TODO: If it is possible to insert element at the adjusted insertion location, then:
        if (true) {
            // 3.1 If the parser was not created as part of the HTML fragment parsing algorithm,
            if (!$this->isBuildingFragment) {
                // then push a new element queue onto element's relevant agent's custom element reactions stack.
            }
            // 3.2 Insert element at the adjusted insertion location.
            $location->insert($element);
            // 3.3 If the parser was not created as part of the HTML fragment parsing algorithm,
            if (!$this->isBuildingFragment) {
                // then pop the element queue from element's relevant agent's custom element reactions stack,
                // and invoke custom element reactions in that queue.
            }
        }
        // Note: If the adjusted insertion location cannot accept more elements,
        // e.g. because it's a Document that already has an element child, then element is dropped on the floor.

        // 4. Push element onto the stack of open elements so that it is the new current node.
        $this->openElements->push($element);

        // 5. Return element.
        return $element;
    }

    public function acknowledgeSelfClosingFlag(Token\StartTag $token)
    {
        // @see https://html.spec.whatwg.org/multipage/parsing.html#acknowledge-self-closing-flag
        if ($token->selfClosing && !isset(Elements::VOID_ELEMENTS[$token->name])) {
            // When a start tag token is emitted with its self-closing flag set,
            // if the flag is not acknowledged when it is processed by the tree construction stage,
            // TODO: that is a non-void-html-element-start-tag-with-trailing-solidus parse error.
        }
    }

    public function followTheGenericTextElementParsingAlgoithm(Token\StartTag $token, bool $rawtext = false)
    {
        // @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-elements-that-contain-only-text
        $this->insertElement($token);
        $this->tokenizer->state = $rawtext ? TokenizerStates::RAWTEXT : TokenizerStates::RCDATA;
        $this->originalInsertionMode = $this->insertionMode;
        $this->setInsertionMode(InsertionModes::TEXT);
    }

    public function adjustSvgAttributes(Token\StartTag $token)
    {
        foreach ($token->attributes as $i => [$name, $value]) {
            if (isset(Attributes::ADJUSTED_SVG_ATTRIBUTES[$name])) {
                $name = Attributes::ADJUSTED_SVG_ATTRIBUTES[$name];
                $token->attributes[$i] = [$name, $value];
            }
        }
    }

    public function adjustMathMlAttributes(Token\StartTag $token)
    {
        foreach ($token->attributes as $i => [$name, $value]) {
            if (isset(Attributes::ADJUSTED_MATHML_ATTRIBUTES[$name])) {
                $name = Attributes::ADJUSTED_MATHML_ATTRIBUTES[$name];
                $token->attributes[$i] = [$name, $value];
            }
        }
    }

    public function adjustForeignAttributes(Token\StartTag $token)
    {
        foreach ($token->attributes as $i => [$name, $value]) {
            if (isset(Attributes::ADJUSTED_FOREIGN_ATTRIBUTES[$name])) {
                [$qname, $prefix, $localName, $ns] = Attributes::ADJUSTED_FOREIGN_ATTRIBUTES[$name];
                $attr = $this->document->createAttributeNS($ns, $qname);
                $attr->value = $value;
                $token->attributes[$i] = $attr;
            }
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#reconstruct-the-active-formatting-elements
     */
    public function reconstructTheListOfActiveElements()
    {
        // 1. If there are no entries in the list of active formatting elements, then there is nothing to reconstruct;
        // stop this algorithm.
        if ($this->activeFormattingElements->isEmpty()) {
            return;
        }
        // 3. Let entry be the last (most recently added) element in the list of active formatting elements.
        $i = $this->activeFormattingElements->count() - 1;
        $entry = $this->activeFormattingElements->top();
        // 2. If the last (most recently added) entry in the list of active formatting elements is a marker,
        // or if it is an element that is in the stack of open elements, then there is nothing to reconstruct; stop this algorithm.
        if ($entry === null || $this->openElements->contains($entry)) {
            return;
        }
        // 6. If entry is neither a marker nor an element that is also in the stack of open elements,
        // go to the step labeled rewind.
        while ($entry !== null && !$this->openElements->contains($entry)) {
            if ($i === 0) {
                $i = -1;
                break;
            }
            $i--;
            // 5. Let entry be the entry one earlier than entry in the list of active formatting elements.
            $entry = $this->activeFormattingElements[$i];
        }

        while (true) {
            // 7. Advance: Let entry be the element one later than entry in the list of active formatting elements.
            $i++;
            $entry = $this->activeFormattingElements[$i];
            // 8. Create: Insert an HTML element for the token for which the element entry was created, to obtain new element.
            $token = new Token\StartTag($entry->tagName);
            foreach ($entry->attributes as $attr) {
                $token->attributes[] = [$attr->nodeName, $attr->nodeValue];
            }
            $element = $this->insertElement($token, $entry->namespaceURI);
            // 9. Replace the entry for entry in the list with an entry for new element.
            $this->activeFormattingElements[$i] = $element;
            // 10. If the entry for new element in the list of active formatting elements is not the last entry in the list,
            // return to the step labeled advance.
            if ($element === $this->activeFormattingElements[$i - 1]) {
                break;
            }
        }
    }
}