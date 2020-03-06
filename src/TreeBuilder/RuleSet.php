<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Parser\Parser;
use ju1ius\HtmlParser\Tokenizer\Token;

abstract class RuleSet
{
    /**
     * @var Parser
     */
    protected $parser;
    /**
     * @var TreeBuilder
     */
    protected $treeBuilder;

    public function __construct(Parser $parser, TreeBuilder $treeBuilder)
    {
        $this->parser = $parser;
        $this->treeBuilder = $treeBuilder;
    }

    public function processEOF(Token $token) {}

    abstract public function processDoctype(Token $token);
    abstract public function processStartTag(Token $token);
    abstract public function processEndTag(Token $token);

    public function processComment(Token $token)
    {
        $this->treeBuilder->insertComment($token, $this->treeBuilder->openElements->top());
    }

    public function processCharacter(Token $token)
    {
        $this->treeBuilder->insertText($token->value);
    }
}
