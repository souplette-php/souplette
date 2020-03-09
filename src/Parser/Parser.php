<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Tokenizer\InputPreprocessor;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
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

    public function __construct()
    {
        $this->treeBuilder = new TreeBuilder(new \DOMImplementation());
    }

    public function parse(string $input, ?string $encoding = null): \DOMDocument
    {
        $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($input);
        return $this->treeBuilder->buildDocument($tokenizer);
    }

    public function parseFragment(\DOMElement $contextElement, string $input, string $encoding = 'utf-8')
    {
        $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($input);
        return $this->treeBuilder->buildFragment($tokenizer, $contextElement);
    }

    private function preprocessInput(string $input, string $encoding)
    {
        $input = InputPreprocessor::convertToUtf8($input, $encoding);
        $input = InputPreprocessor::normalizeNewlines($input);

        return $input;
    }
}
