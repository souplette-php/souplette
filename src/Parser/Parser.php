<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Tokenizer\InputPreprocessor;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

class Parser
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;
    /**
     * @var TreeBuilder
     */
    private $treeBuilder;
    /**
     * @var int
     */
    private $insertionMode;
    /**
     * @var int
     */
    private $originalInsertionMode;
    /**
     * @var RuleSet
     */
    private $rules;
    /**
     * @var RuleSet[]
     */
    private $ruleSets = [];
    /**
     * @var string
     */
    private $compatMode = CompatModes::NO_QUIRKS;
    /**
     * @var bool
     */
    private $isParsingFragment = false;
    /**
     * @var string
     */
    private $context;

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

    public function __construct()
    {
        $this->treeBuilder = new TreeBuilder(new \DOMImplementation());
        $this->ruleSets = $this->createRuleSets();
    }

    public function parse(string $input, ?string $encoding = null)
    {
        $this->isParsingFragment = false;
        $this->doParse($input, $encoding);
        return $this->treeBuilder->getDocument();
    }

    public function parseFragment(string $input, string $encoding = 'utf-8', string $contextElement = 'div')
    {
        $this->isParsingFragment = true;
        $this->context = strtolower($contextElement);
        $this->doParse($input, $encoding);
        return $this->treeBuilder->getFragment();
    }

    /**
     * @return RuleSet[]
     */
    private function createRuleSets(): array
    {
        $rules = [];
        foreach (self::RULE_SETS as $insertionMode => $ruleSet) {
            $rules[$insertionMode] = new $ruleSet($this, $this->treeBuilder);
        }
        return $rules;
    }

    private function doParse(string $input, string $encoding)
    {
        $input = InputPreprocessor::convertToUtf8($input);
        $input = InputPreprocessor::normalizeNewlines($input);
        $this->tokenizer = new Tokenizer($input);

        if ($this->isParsingFragment) {
            if (isset(Elements::CDATA_ELEMENTS[$this->context])) {
                $this->tokenizer->state = TokenizerStates::RCDATA;
            } elseif (isset(Elements::RCDATA_ELEMENTS[$this->context])) {
                $this->tokenizer->state = TokenizerStates::RAWTEXT;
            } elseif ($this->context === 'plaintext') {
                $this->tokenizer->state = TokenizerStates::PLAINTEXT;
            }
            $this->insertionMode = InsertionModes::BEFORE_HTML;
            $this->rules = $this->ruleSets[$this->insertionMode];
            $this->rules->insertHtmlElement();
            $this->resetInsertionModeAppropriately();
        } else {
            $this->insertionMode = InsertionModes::INITIAL;
            $this->rules = $this->ruleSets[$this->insertionMode];
        }
    }

    private function run()
    {
        
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
        $openElements = $this->treeBuilder->openElements;
        $openElements->rewind();
        $node = $openElements->current();
        while ($node) {
            $nodeName = $node->tagName;
            // 3. If node is the first node in the stack of open elements
            if ($node === $openElements->bottom()) {
                // then set last to true
                $last = true;
                // and if the parser was created as part of the HTML fragment parsing algorithm (fragment case)
                if ($this->isParsingFragment) {
                    // set node to the context element passed to that algorithm.
                    $nodeName = $this->context;
                }
            }
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
                // TODO: this is not good
                $this->insertionMode = InsertionModes::IN_TEMPLATE;
                break;
            }
            // 12. If node is a head element and last is false, then switch the insertion mode to "in head" and return.
            if (!$last && $nodeName === 'head') {
                $this->insertionMode = InsertionModes::IN_HEAD;
                break;
            }
            // 14. If node is a frameset element, then switch the insertion mode to "in frameset" and return. (fragment case)
            if ($this->isParsingFragment && $nodeName === 'frameset') {
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
            if ($last && $this->isParsingFragment) {
                $this->insertionMode = InsertionModes::IN_BODY;
            }
            // 17. Let node now be the node before node in the stack of open elements.
            $openElements->next();
            $node = $openElements->current();
        }
        $this->rules = $this->ruleSets[$this->insertionMode];
    }
}
