<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Tokenizer\InputPreprocessor;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
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
     * @var RuleSet[]
     */
    private $rules = [];

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
        $this->treeBuilder = new TreeBuilder();
        $this->rules = $this->createRules();
    }

    public function parse(string $input, ?string $encoding = null)
    {
        $input = InputPreprocessor::convertToUtf8($input);
        $input = InputPreprocessor::normalizeNewlines($input);
        $this->tokenizer = new Tokenizer($input);
        $this->insertionMode = InsertionModes::INITIAL;
    }

    /**
     * @return RuleSet[]
     */
    private function createRules(): array
    {
        $rules = [];
        foreach (self::RULE_SETS as $insertionMode => $ruleSet) {
            $rules[$insertionMode] = new $ruleSet($this, $this->treeBuilder);
        }
        return $rules;
    }
}
