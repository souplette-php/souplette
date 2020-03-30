<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser;

use JoliPotage\Encoding\Encoding;
use JoliPotage\Encoding\EncodingLookup;
use JoliPotage\Html\Parser\EncodingSniffer;
use JoliPotage\Encoding\Exception\EncodingChanged;
use JoliPotage\Html\Parser\Tokenizer\Tokenizer;
use JoliPotage\Html\Parser\TreeBuilder\TreeBuilder;

final class Parser
{
    /**
     * @var TreeBuilder
     */
    private $treeBuilder;

    public function __construct(bool $scriptingEnabled = false)
    {
        $this->treeBuilder = new TreeBuilder(new \DOMImplementation(), $scriptingEnabled);
    }

    public function parse(string $input, ?string $encoding = null): \DOMDocument
    {
        $encoding = $this->detectEncoding($input, $encoding);
        $converted = $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($converted);
        try {
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        } catch (EncodingChanged $err) {
            $encoding = $err->getEncoding();
            $converted = $this->preprocessInput($input, $encoding);
            $tokenizer = new Tokenizer($converted);
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        }
    }

    public function parseFragment(\DOMElement $contextElement, string $input, ?string $encoding = null)
    {
        $encoding = $this->detectEncoding($input, $encoding);
        $converted = $this->preprocessInput($input, $encoding);
        $tokenizer = new Tokenizer($converted);
        try {
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        } catch (EncodingChanged $err) {
            $encoding = $err->getEncoding();
            $converted = $this->preprocessInput($input, $encoding);
            $tokenizer = new Tokenizer($converted);
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        }
    }

    private function detectEncoding(string $input, ?string $override = null): Encoding
    {
        if ($override) {
            return Encoding::certain($override);
        }
        if ($sniffed = EncodingSniffer::sniff($input)) {
            return Encoding::tentative($sniffed);
        }

        return Encoding::default();
    }

    private function preprocessInput(string $input, Encoding $encoding): string
    {
        if ($encoding->getName() !== EncodingLookup::UTF_8) {
            $input = InputPreprocessor::convertToUtf8($input, $encoding->getName());
        }
        $input = InputPreprocessor::removeBOM($input);
        $input = InputPreprocessor::normalizeNewlines($input);

        return $input;
    }
}
