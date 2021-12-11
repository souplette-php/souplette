<?php declare(strict_types=1);

namespace Souplette\Html;

use Souplette\Dom\Attr;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Implementation;
use Souplette\Dom\Internal\DocumentMode;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\Text;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Encoding\Encoding;
use Souplette\Encoding\EncodingLookup;
use Souplette\Encoding\Exception\EncodingChanged;
use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\Token\StartTag;
use Souplette\Html\Tokenizer\Token\Tag;
use Souplette\Html\Tokenizer\Tokenizer;
use Souplette\Html\Tokenizer\TokenizerState;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder\ActiveFormattingElementList;
use Souplette\Html\TreeBuilder\Attributes;
use Souplette\Html\TreeBuilder\Elements;
use Souplette\Html\TreeBuilder\InsertionLocation;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\OpenElementsStack;
use Souplette\Html\TreeBuilder\RuleSet;
use Souplette\Html\TreeBuilder\RuleSet\InForeignContent;
use SplStack;

final class TreeBuilder
{
    /**
     * @var array<string, RuleSet>
     */
    private const RULES = [
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

    public Tokenizer $tokenizer;
    public Encoding $encoding;
    private Implementation $dom;
    public Document $document;

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#the-insertion-mode
     */
    public int $insertionMode;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#original-insertion-mode
     */
    public int $originalInsertionMode;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#the-stack-of-open-elements
     */
    public OpenElementsStack $openElements;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#the-list-of-active-formatting-elements
     */
    public ActiveFormattingElementList $activeFormattingElements;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#stack-of-template-insertion-modes
     */
    public SplStack $templateInsertionModes;
    /**
     * @var Token\Character[]
     */
    public array $pendingTableCharacterTokens = [];
    public bool $isBuildingFragment = false;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#concept-frag-parse-context
     */
    private ?Element $contextElement = null;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#head-element-pointer
     */
    public ?Element $headElement = null;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#form-element-pointer
     */
    public ?Element $formElement = null;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#foster-parent
     */
    public bool $fosterParenting = false;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#frameset-ok-flag
     */
    public bool $framesetOK = true;
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#scripting-flag
     */
    public bool $scriptingEnabled = false;
    public bool $shouldSkipNextNewLine = false;

    public function __construct(Implementation $dom, bool $scriptingEnabled = false)
    {
        $this->dom = $dom;
        $this->scriptingEnabled = $scriptingEnabled;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing
     */
    public function buildDocument(Tokenizer $tokenizer, Encoding $encoding): Document
    {
        $this->tokenizer = $tokenizer;
        $this->encoding = $encoding;
        $this->reset();
        $this->run(TokenizerState::DATA);
        return $this->document;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-html-fragments
     * @return Node[]
     */
    public function buildFragment(Tokenizer $tokenizer, Encoding $encoding, Element $contextElement): array
    {
        $this->tokenizer = $tokenizer;
        $this->encoding = $encoding;
        $this->reset();
        $this->isBuildingFragment = true;
        $this->contextElement = $contextElement;
        // 2. If the node document of the context element is in quirks mode, then let the Document be in quirks mode.
        // Otherwise, the node document of the context element is in limited-quirks mode,
        // then let the Document be in limited-quirks mode.
        // Otherwise, leave the Document in no-quirks mode.
        $this->document->_mode = match ($contextElement->ownerDocument?->_mode) {
            DocumentMode::QUIRKS => DocumentMode::QUIRKS,
            DocumentMode::LIMITED_QUIRKS => DocumentMode::LIMITED_QUIRKS,
            default => DocumentMode::NO_QUIRKS,
        };
        // 4. Set the state of the HTML parser's tokenization stage as follows, switching on the context element:
        $contextTag = $this->contextElement->localName;
        $contextNS = $this->contextElement->namespaceURI;
        if (isset(Elements::CDATA_ELEMENTS[$contextNS][$contextTag])) {
            $tokenizerState = TokenizerState::RCDATA;
        } else if (isset(Elements::RCDATA_ELEMENTS[$contextNS][$contextTag])) {
            $tokenizerState = TokenizerState::RAWTEXT;
        } else if ($contextTag === 'script') {
            $tokenizerState = TokenizerState::SCRIPT_DATA;
        } else if ($this->scriptingEnabled && $contextTag === 'noscript') {
            $tokenizerState = TokenizerState::RAWTEXT;
        } else if (isset(Elements::PLAINTEXT_ELEMENTS[$contextNS][$contextTag])) {
            $tokenizerState = TokenizerState::PLAINTEXT;
        } else {
            $tokenizerState = TokenizerState::DATA;
        }
        // 5. Let root be a new html element with no attributes.
        $root = new Element('html', Namespaces::HTML);
        // 6. Append the element root to the Document node created above.
        $this->document->parserInsertBefore($root, null);
        // 7. Set up the parser's stack of open elements so that it contains just the single element root.
        $this->openElements->push($root);
        $this->insertionMode = InsertionModes::BEFORE_HEAD;
        // 8. If the context element is a template element, push "in template" onto the stack of template insertion modes
        // so that it is the new current template insertion mode.
        if ($contextTag === 'template' && $contextNS === Namespaces::HTML) {
            $this->templateInsertionModes->push(InsertionModes::IN_TEMPLATE);
        }
        // 9. Create a start tag token whose name is the local name of context and whose attributes are the attributes of context.
        // Let this start tag token be the start tag token of the context node,
        // e.g. for the purposes of determining if it is an HTML integration point.
        // --> not needed. $this->getAdjustedCurrentNode() will do the trick.
        // 10. Reset the parser's insertion mode appropriately.
        $this->resetInsertionModeAppropriately();
        // 11. Set the parser's form element pointer to the nearest node to the context element that is a form element
        // (going straight up the ancestor chain, and including the element itself, if it is a form element), if any.
        // (If there is no such form element, the form element pointer keeps its initial value, null.)
        foreach (ElementTraversal::inclusiveAncestorsOf($contextElement) as $node) {
            if ($node->localName === 'form') {
                $this->formElement = $node;
                break;
            }
        }
        // 12. Place the input into the input stream for the HTML parser just created.
        // The encoding confidence is irrelevant.
        $encoding->makeIrrelevant();
        // 13. Start the parser and let it run until it has consumed all the characters just inserted into the input stream.
        $this->run($tokenizerState);
        // 14. Return the child nodes of root, in tree order.
        // TODO: change this when we implement NodeList
        return $root->getChildNodes();
    }

    private function reset(): void
    {
        $this->framesetOK = true;
        $this->isBuildingFragment = false;
        $this->openElements = new OpenElementsStack();
        $this->activeFormattingElements = new ActiveFormattingElementList();
        $this->templateInsertionModes = new SplStack();
        $this->contextElement = null;
        $this->insertionMode = $this->originalInsertionMode = InsertionModes::INITIAL;
        $this->document = $this->createDocument();
        $this->headElement = null;
        $this->formElement = null;
        $this->fosterParenting = false;
        $this->shouldSkipNextNewLine = false;
        $this->pendingTableCharacterTokens = [];

        $this->tokenizer->allowCdata = function() {
            $adjustedCurrentNode = $this->getAdjustedCurrentNode();
            return $adjustedCurrentNode && $adjustedCurrentNode->namespaceURI !== Namespaces::HTML;
        };
    }

    private function run(TokenizerState $tokenizerState)
    {
        $previousToken = null;
        foreach ($this->tokenizer->tokenize($tokenizerState) as $token) {
            $this->shouldSkipNextNewLine = (
                $previousToken
                && $previousToken::TYPE === TokenType::START_TAG
                && ($previousToken->name === 'pre' || $previousToken->name === 'listing' || $previousToken->name === 'textarea')
            );
            // Tree construction dispatcher
            // @see https://html.spec.whatwg.org/multipage/parsing.html#tree-construction-dispatcher
            $adjustedCurrentNode = $this->getAdjustedCurrentNode();
            if (
                !$adjustedCurrentNode
                || $adjustedCurrentNode->isHTML
                || (
                    Elements::isMathMlTextIntegrationPoint($adjustedCurrentNode) && (
                        (
                            $token::TYPE === TokenType::START_TAG
                            && $token->name !== 'mglyph'
                            && $token->name !== 'malignmark'
                        ) || (
                            $token::TYPE === TokenType::CHARACTER
                        )
                    )
                ) || (
                    $adjustedCurrentNode->localName === 'annotation-xml'
                    && $adjustedCurrentNode->namespaceURI === Namespaces::MATHML
                    && $token::TYPE === TokenType::START_TAG
                    && $token->name === 'svg'
                ) || (
                    Elements::isHtmlIntegrationPoint($adjustedCurrentNode) && (
                        $token::TYPE === TokenType::START_TAG
                        || $token::TYPE === TokenType::CHARACTER
                    )
                )
                || $token::TYPE === TokenType::EOF
            ) {
                (self::RULES[$this->insertionMode])::process($token, $this);
            } else {
                InForeignContent::process($token, $this);
            }

            $previousToken = $token;
        }
    }

    public function processToken(Token $token)
    {
        return (self::RULES[$this->insertionMode])::process($token, $this);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#reset-the-insertion-mode-appropriately
     */
    public function resetInsertionModeAppropriately()
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
            $nodeName = $node->localName;
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
                        if ($ancestor->localName === 'template') break;
                        // 4.6 If ancestor is a table node, switch the insertion mode to "in select in table" and return.
                        if ($ancestor->localName === 'table') {
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
                $this->insertionMode = $this->templateInsertionModes->top();
                break;
            }
            // 12. If node is a head element and last is false, then switch the insertion mode to "in head" and return.
            if (!$last && $nodeName === 'head') {
                $this->insertionMode = InsertionModes::IN_HEAD;
                break;
            }
            // 14. If node is a frameset element, then switch the insertion mode to "in frameset" and return. (fragment case)
            if (/*$this->isBuildingFragment && */$nodeName === 'frameset') {
                $this->insertionMode = InsertionModes::IN_FRAMESET;
                break;
            }
            // 15. If node is an html element, run these substeps:
            if ($nodeName === 'html') {
                // 15.1 If the head element pointer is null, switch the insertion mode to "before head" and return. (fragment case)
                if ($this->headElement === null) {
                    $this->insertionMode = InsertionModes::BEFORE_HEAD;
                    return;
                }
                // 15.2 Otherwise, the head element pointer is not null, switch the insertion mode to "after head" and return.
                $this->insertionMode = InsertionModes::AFTER_HEAD;
                return;
            }
            // 16. If last is true, then switch the insertion mode to "in body" and return. (fragment case)
            if ($last/* && $this->isBuildingFragment*/) {
                $this->insertionMode = InsertionModes::IN_BODY;
            }
            // 17. Let node now be the node before node in the stack of open elements.
            $openElements->next();
            $node = $openElements->current();
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#closing-elements-that-have-implied-end-tags
     */
    public function generateImpliedEndTags(?string $excluded = null, bool $thoroughly = false)
    {
        $impliedTags = $thoroughly ? Elements::END_TAG_IMPLIED_THOROUGH : Elements::END_TAG_IMPLIED;
        while (true) {
            $node = $this->openElements->top();
            if (!$node || $node->localName === $excluded) {
                return;
            }
            if (isset($impliedTags[$node->localName])) {
                $this->openElements->pop();
                continue;
            }
            return;
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#adjusted-current-node
     */
    public function getAdjustedCurrentNode(): ?Element
    {
        $openElementsCount = $this->openElements->count();
        if (!$openElementsCount) {
            return null;
        }
        if ($this->isBuildingFragment && $openElementsCount === 1) {
            return $this->contextElement;
        }
        return $this->openElements->top();
    }

    public function appropriatePlaceForInsertingANode(?Element $overrideTarget = null): InsertionLocation
    {
        // @see https://html.spec.whatwg.org/multipage/parsing.html#creating-and-inserting-nodes
        // 1. If there was an override target specified, then let target be the override target.
        // Otherwise, let target be the current node.
        /** @var Element $target */
        $target = $overrideTarget ?: $this->openElements->top();
        // 2. Determine the adjusted insertion location using the first matching steps from the following list:
        if ($this->fosterParenting && isset(Elements::TABLE_INSERT_MODE_ELEMENTS[$target->localName])) {
            // If foster parenting is enabled and target is a table, tbody, tfoot, thead, or tr element
            // Run these substeps:
            // 1. Let last template be the last template element in the stack of open elements, if any.
            // 2. Let last table be the last table element in the stack of open elements, if any.
            $lastTemplate = null;
            $lastTemplatePosition = null;
            $lastTable = null;
            $lastTablePosition = null;
            $pos = 0;
            foreach ($this->openElements as $element) {
                if (!$lastTemplate && $element->localName === 'template') {
                    $lastTemplate = $element;
                    $lastTemplatePosition = $pos;
                } else if (!$lastTable && $element->localName === 'table') {
                    $lastTable = $element;
                    $lastTablePosition = $pos;
                }
                $pos++;
            }
            if ($lastTemplate && (!$lastTable || $lastTemplatePosition < $lastTablePosition)) {
                // 3. If there is a last template and either there is no last table,
                // or there is one, but last template is lower (more recently added) than last table in the stack of open elements,
                // then: let adjusted insertion location be inside last template's template contents, after its last child (if any),
                // and abort these steps.
                // NOTE: we can't support the template->content property,
                // because of the PHP DOM implementation details.
                //$adjustedInsertionLocation = new InsertionLocation($lastTemplate->content);
                $adjustedInsertionLocation = new InsertionLocation($lastTemplate);
            } else if (!$lastTable) {
                // 4. If there is no last table,
                // then let adjusted insertion location be inside the first element in the stack of open elements (the html element),
                // after its last child (if any),
                // and abort these steps. (fragment case)
                $parent = $this->openElements->bottom();
                $adjustedInsertionLocation = new InsertionLocation($parent);
            } else if ($lastTable && $lastTable->parentNode) {
                // 5. If last table has a parent node,
                // then let adjusted insertion location be inside last table's parent node,
                // immediately before last table, and abort these steps.
                $adjustedInsertionLocation = new InsertionLocation($lastTable->parentNode, $lastTable, true);
            } else {
                // 6. Let previous element be the element immediately above last table in the stack of open elements.
                $previousElement = $this->openElements[$lastTablePosition + 1];
                // 7. Let adjusted insertion location be inside previous element, after its last child (if any).
                $adjustedInsertionLocation = new InsertionLocation($previousElement);
            }
        } else {
            // Otherwise
            // Let adjusted insertion location be inside target, after its last child (if any).
            $adjustedInsertionLocation = new InsertionLocation($target);
        }
        // 3. If the adjusted insertion location is inside a template element,
        //    let it instead be inside the template element's template contents, after its last child (if any).
        // NOTE: we can't support the template->content property,
        // so we have to skip this step
        //if ($template = $adjustedInsertionLocation->closestAncestor('template')) {
        //    $adjustedInsertionLocation = new InsertionLocation($template->content);
        //}
        // 4. Return the adjusted insertion location.
        return $adjustedInsertionLocation;
    }

    private function createDocument(): Document
    {
        return $this->dom->createDocument();
    }

    public function createDoctype(Token\Doctype $token): DocumentType
    {
        return $this->dom->createDocumentType($token->name, $token->publicIdentifier ?: '', $token->systemIdentifier ?: '');
    }

    public function insertCharacter(Token\Character $token, ?string $data = null)
    {
        // 1. Let data be the characters passed to the algorithm, or,
        // if no characters were explicitly specified, the character of the character token being processed.
        $data ??= $token->data;
        // 2. Let the adjusted insertion location be the appropriate place for inserting a node.
        $location = $this->appropriatePlaceForInsertingANode();
        // 3. If the adjusted insertion location is in a Document node, then return.
        if ($location->parent->nodeType === Node::DOCUMENT_NODE) {
            return;
        }
        // 4. If there is a Text node immediately before the adjusted insertion location,
        // then append data to that Text node's data.
        $target = $location->target;
        if ($target?->nodeType === Node::TEXT_NODE) {
            $target->appendData($data);
        } else if ($location->beforeTarget && $target?->_prev?->nodeType === Node::TEXT_NODE) {
            $target->_prev->appendData($data);
        } else {
            // Otherwise, create a new Text node whose data is data
            // and whose node document is the same as that of the element in which the adjusted insertion location finds itself,
            // and insert the newly created node at the adjusted insertion location.
            $location->insert(new Text($data));
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
        $node = $location->document->createComment($data);
        // 4. Insert the newly created node at the adjusted insertion location.
        $location->insert($node);
    }

    public function insertElement(
        Token\Tag $token,
        string $namespace = Namespaces::HTML,
        bool $inForeignContent = false
    ): Element {
        // 1. Let the adjusted insertion location be the appropriate place for inserting a node.
        $location = $this->appropriatePlaceForInsertingANode();
        // 2. Let element be the result of creating an element for the token in the given namespace,
        // with the intended parent being the element in which the adjusted insertion location finds itself.
        $element = $this->createElement($token, $namespace, $location->parent, $inForeignContent);
        // 3. TODO: If it is possible to insert element at the adjusted insertion location, then:
        $canInsert = match ($location->parent) {
            $this->document => !$this->document->documentElement,
            default => true,
        };
        if ($canInsert) {
            // 3.1 If the parser was not created as part of the HTML fragment parsing algorithm,
            // then push a new element queue onto element's relevant agent's custom element reactions stack.
            // 3.2 Insert element at the adjusted insertion location.
            $location->insert($element);
            // 3.3 If the parser was not created as part of the HTML fragment parsing algorithm,
            // then pop the element queue from element's relevant agent's custom element reactions stack,
            // and invoke custom element reactions in that queue.
        }
        // Note: If the adjusted insertion location cannot accept more elements,
        // e.g. because it's a Document that already has an element child, then element is dropped on the floor.

        // 4. Push element onto the stack of open elements so that it is the new current node.
        $this->openElements->push($element);

        // 5. Return element.
        return $element;
    }

    public function createElement(
        Tag $token,
        string $namespace,
        Node $intendedParent,
        bool $inForeignContent = false,
    ): Element {
        // 3. Let document be intended parent's node document.
        $doc = match ($intendedParent->nodeType) {
            Node::DOCUMENT_NODE => $intendedParent,
            default => $intendedParent->_doc,
        };
        // 4. Let local name be the tag name of the token.
        $localName = $token->name;
        // 9. Let element be the result of creating an element given document, localName, given namespace, null, and is.
        // If will execute script is true, set the synchronous custom elements flag; otherwise, leave it unset.
        $element = new Element($localName, $namespace);
        $element->_doc = $doc;
        // 10. Append each attribute in the given token to element.
        if ($token->attributes) {
            foreach ($token->attributes as $name => $value) {
                if ($value instanceof Attr) {
                    $element->_attrs[] = $value;
                    $value->_parent = $element;
                } else {
                    $attr = new Attr((string)$name);
                    $attr->_value = $value;
                    $attr->_doc = $doc;
                    $attr->_parent = $element;
                    $element->_attrs[] = $attr;
                }
            }
        }

        return $element;
    }

    public function mergeAttributes(StartTag $fromToken, Element $toElement): void
    {
        // For each attribute on the token, check to see if the attribute is already present on the element.
        // If it is not, add the attribute and its corresponding value to that element.
        foreach ($toElement->_attrs as $attr) {
            unset($fromToken->attributes[$attr->name]);
        }
        foreach ($fromToken->attributes as $name => $value) {
            $attr = new Attr((string)$name);
            $attr->_value = $value;
            $attr->_doc = $toElement->_doc;
            $attr->_parent = $toElement;
            $toElement->_attrs[] = $attr;
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#acknowledge-self-closing-flag
     */
    public function acknowledgeSelfClosingFlag(Token\StartTag $token)
    {
        if ($token->selfClosing && !isset(Elements::VOID_ELEMENTS[$token->name])) {
            // When a start tag token is emitted with its self-closing flag set,
            // if the flag is not acknowledged when it is processed by the tree construction stage,
            // TODO: that is a non-void-html-element-start-tag-with-trailing-solidus parse error.
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-elements-that-contain-only-text
     */
    public function followTheGenericTextElementParsingAlgorithm(Token\StartTag $token, bool $rawtext = false)
    {
        $this->insertElement($token);
        $this->tokenizer->state = $rawtext ? TokenizerState::RAWTEXT : TokenizerState::RCDATA;
        $this->originalInsertionMode = $this->insertionMode;
        $this->insertionMode = InsertionModes::TEXT;
    }

    public function adjustSvgTagName(Token\StartTag $token)
    {
        if (isset(Elements::NORMALIZED_SVG_TAGS[$token->name])) {
            $token->name = Elements::NORMALIZED_SVG_TAGS[$token->name];
        }
    }

    public function adjustSvgAttributes(Token\StartTag $token)
    {
        if (!$token->attributes) return;
        foreach ($token->attributes as $name => $value) {
            if (isset(Attributes::ADJUSTED_SVG_ATTRIBUTES[$name])) {
                unset($token->attributes[$name]);
                $name = Attributes::ADJUSTED_SVG_ATTRIBUTES[$name];
                $token->attributes[$name] = $value;
            }
        }
    }

    public function adjustMathMlAttributes(Token\StartTag $token)
    {
        if (!$token->attributes) return;
        foreach ($token->attributes as $name => $value) {
            if (isset(Attributes::ADJUSTED_MATHML_ATTRIBUTES[$name])) {
                unset($token->attributes[$name]);
                $name = Attributes::ADJUSTED_MATHML_ATTRIBUTES[$name];
                $token->attributes[$name] = $value;
            }
        }
    }

    public function adjustForeignAttributes(Token\StartTag $token)
    {
        if (!$token->attributes) return;
        foreach ($token->attributes as $qname => $value) {
            if (isset(Attributes::ADJUSTED_FOREIGN_ATTRIBUTES[$qname])) {
                [$prefix, $localName, $ns] = Attributes::ADJUSTED_FOREIGN_ATTRIBUTES[$qname];
                $attr = new Attr($localName, $ns, $prefix);
                $attr->_doc = $this->document;
                $attr->_value = $value;
                $token->attributes[$qname] = $attr;
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
        $i = 0;
        $entry = $this->activeFormattingElements->top();
        // 2. If the last (most recently added) entry in the list of active formatting elements is a marker,
        // or if it is an element that is in the stack of open elements, then there is nothing to reconstruct; stop this algorithm.
        if ($entry === null || $this->openElements->contains($entry)) {
            return;
        }
        // 4. Rewind: If there are no entries before entry in the list of active formatting elements,
        // then jump to the step labeled create.
        REWIND:
        if ($i === $this->activeFormattingElements->count() - 1) {
            goto CREATE;
        }
        // 5. Let entry be the entry one earlier than entry in the list of active formatting elements.
        $entry = $this->activeFormattingElements[++$i];
        // 6. If entry is neither a marker nor an element that is also in the stack of open elements,
        // go to the step labeled rewind.
        if ($entry !== null && !$this->openElements->contains($entry)) {
            goto REWIND;
        }
        // 7. Advance: Let entry be the element one later than entry in the list of active formatting elements.
        ADVANCE:
        $entry = $this->activeFormattingElements[--$i];
        // 8. Create: Insert an HTML element for the token for which the element entry was created, to obtain new element.
        CREATE:
        $token = new Token\StartTag($entry->localName);
        foreach ($entry->attributes as $attr) {
            $token->attributes[$attr->nodeName] = $attr->nodeValue;
        }
        $element = $this->insertElement($token, $entry->namespaceURI);
        // 9. Replace the entry for entry in the list with an entry for new element.
        $this->activeFormattingElements[$i] = $element;
        // 10. If the entry for new element in the list of active formatting elements is not the last entry in the list,
        // return to the step labeled advance.
        if ($element !== $this->activeFormattingElements->top()) {
            goto ADVANCE;
        }
    }

    public function changeTheEncoding(string $label)
    {
        $currentEncoding = $this->encoding->name;
        // 1. If the encoding that is already being used to interpret the input stream is a UTF-16 encoding,
        // then set the confidence to certain and return.
        // The new encoding is ignored; if it was anything but the same encoding, then it would be clearly incorrect.
        if ($currentEncoding === EncodingLookup::UTF_16LE || $currentEncoding === EncodingLookup::UTF_16BE) {
            $this->encoding->makeCertain();
            return;
        }
        // 2. If the new encoding is a UTF-16 encoding, then change it to UTF-8.
        if ($label === EncodingLookup::UTF_16LE || $label === EncodingLookup::UTF_16BE) {
            $label = EncodingLookup::UTF_8;
        }
        // 3. If the new encoding is x-user-defined, then change it to windows-1252.
        if ($label === EncodingLookup::X_USER_DEFINED) {
            $label = EncodingLookup::WINDOWS_1252;
        }
        // 4. If the new encoding is identical or equivalent to the encoding
        // that is already being used to interpret the input stream,
        // then set the confidence to certain and return.
        // This happens when the encoding information found in the file
        // matches what the encoding sniffing algorithm determined to be the encoding,
        // and in the second pass through the parser if the first pass found that the encoding sniffing algorithm
        // described in the earlier section failed to find the right encoding.
        if ($label === $currentEncoding) {
            $this->encoding->makeCertain();
            return;
        }
        // 5. If all the bytes up to the last byte converted by the current decoder have the same Unicode interpretations
        // in both the current encoding and the new encoding,
        // and if the user agent supports changing the converter on the fly,
        // then the user agent may change to the new converter for the encoding on the fly.
        // Set the document's character encoding and the encoding used to convert the input stream to the new encoding,
        // set the confidence to certain, and return.

        // 6. Otherwise, navigate to the document again, with replacement enabled, and using the same source browsing context,
        // but this time skip the encoding sniffing algorithm and instead just set the encoding
        // to the new encoding and the confidence to certain.
        // Whenever possible, this should be done without actually contacting the network layer
        // (the bytes should be re-parsed from memory), even if, e.g., the document is marked as not being cacheable.
        // If this is not possible and contacting the network layer would involve repeating a request
        // that uses a method other than `GET`, then instead set the confidence to certain and ignore the new encoding.
        // The resource will be misinterpreted.
        // User agents may notify the user of the situation, to aid in application development.
        throw new EncodingChanged(Encoding::certain($label));
    }
}
