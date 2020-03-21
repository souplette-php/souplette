<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Encoding\Encoding;
use ju1ius\HtmlParser\Encoding\EncodingLookup;
use ju1ius\HtmlParser\Encoding\EncodingSniffer;
use ju1ius\HtmlParser\Encoding\Exception\EncodingChanged;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

final class Parser
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
        [$input, $encoding] = $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($input);
        try {
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        } catch (EncodingChanged $err) {
            $encoding = $err->getEncoding();
            if ($encoding->getName() !== EncodingLookup::UTF_8) {
                $input = InputPreprocessor::convertToUtf8($input, $encoding->getName());
                $tokenizer = new Tokenizer($input);
            }
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        }
    }

    public function parseFragment(\DOMElement $contextElement, string $input, ?string $encoding = null)
    {
        [$input, $encoding] = $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($input);
        try {
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        } catch (EncodingChanged $err) {
            $encoding = $err->getEncoding();
            if ($encoding->getName() !== EncodingLookup::UTF_8) {
                $input = InputPreprocessor::convertToUtf8($input, $encoding->getName());
                $tokenizer = new Tokenizer($input);
            }
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        }
    }

    private function preprocessInput(string $input, ?string $encoding = null)
    {
        if ($encoding !== null) {
            $encoding = Encoding::certain($encoding);
        } else {
            if ($sniffed = EncodingSniffer::sniff($input)) {
                $encoding = Encoding::tentative($encoding);
            } else {
                $encoding = Encoding::unknown();
            }
        }
        if ($encoding->getName() !== EncodingLookup::UTF_8) {
            $input = InputPreprocessor::convertToUtf8($input, $encoding->getName());
        }
        $input = InputPreprocessor::removeBOM($input);
        $input = InputPreprocessor::normalizeNewlines($input);

        return [$input, $encoding];
    }
}
