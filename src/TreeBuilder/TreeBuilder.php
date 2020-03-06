<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Tokenizer\Token;
use SplStack;

final class TreeBuilder
{
    /**
     * @var SplStack
     */
    public $openElements;

    public function __construct()
    {
        $this->openElements = new SplStack();
    }

    public function insertRoot(Token $token) {}
    public function insertDoctype(Token $token) {}
    public function insertComment(Token $token, $parent = null) {}
    public function createElement(Token $token) {}
    public function insertElement(Token $token) {}
    public function insertText(string $data, $parent = null) {}
}
