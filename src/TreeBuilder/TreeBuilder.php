<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Tokenizer\Token;
use SplStack;

final class TreeBuilder
{
    /**
     * @var SplStack<\DOMElement>
     */
    public $openElements;
    /**
     * @var \DOMImplementation
     */
    private $dom;
    /**
     * @var \DOMDocument
     */
    private $document;
    private $activeFormattingElements;
    private $headElement;
    private $formElement;
    private $insertFromTable = false;

    public function __construct(\DOMImplementation $dom)
    {
        $this->dom = $dom;
        $this->openElements = new SplStack();
    }

    public function getDocument(): \DOMDocument
    {
        return $this->document;
    }

    public function getFragment(): ?\DOMDocumentFragment
    {
        return null;
    }

    public function insertRoot(Token $token) {}
    public function insertDoctype(Token $token) {}
    public function insertComment(Token $token, $parent = null) {}
    public function createElement(Token $token) {}
    public function insertElement(Token $token) {}
    public function insertText(string $data, $parent = null) {}

    public function reset(): void
    {
        $this->openElements = new SplStack();
        $this->document = $this->dom->createDocument();
    }
}
