<?php declare(strict_types=1);

namespace Souplette\Html;

use JetBrains\PhpStorm\Pure;
use Souplette\Dom\Node\Document;
use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Implementation;
use Souplette\Dom\Node\Node;
use Souplette\Encoding\Encoding;
use Souplette\Encoding\EncodingLookup;
use Souplette\Encoding\Exception\EncodingChanged;
use Souplette\Encoding\Utf8Converter;
use Souplette\Html\Parser\EncodingSniffer;
use Souplette\Html\Parser\InputPreprocessor;
use Souplette\Html\Tokenizer\Tokenizer;

final class Parser
{
    private TreeBuilder $treeBuilder;

    public function __construct(bool $scriptingEnabled = false)
    {
        $this->treeBuilder = new TreeBuilder(new Implementation(), $scriptingEnabled);
    }

    public function parse(string $input, ?string $encoding = null): Document
    {
        $encoding = $this->detectEncoding($input, $encoding);
        $converted = $this->preprocessInput($input, $encoding);
        $tokenizer = $this->createTokenizer($converted);
        try {
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        } catch (EncodingChanged $err) {
            $encoding = $err->encoding;
            $converted = $this->preprocessInput($input, $encoding);
            $tokenizer = $this->createTokenizer($converted);
            return $this->treeBuilder->buildDocument($tokenizer, $encoding);
        }
    }

    /**
     * @return Node[]
     */
    public function parseFragment(Element $contextElement, string $input, ?string $encoding = null): array
    {
        $encoding = $this->detectEncoding($input, $encoding);
        $converted = $this->preprocessInput($input, $encoding);
        $tokenizer = $this->createTokenizer($converted);
        try {
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        } catch (EncodingChanged $err) {
            $encoding = $err->encoding;
            $converted = $this->preprocessInput($input, $encoding);
            $tokenizer = $this->createTokenizer($converted);
            return $this->treeBuilder->buildFragment($tokenizer, $encoding, $contextElement);
        }
    }

    #[Pure]
    private function createTokenizer(string $input): Tokenizer
    {
        return new Tokenizer($input);
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
        if ($encoding->name !== EncodingLookup::UTF_8) {
            $input = Utf8Converter::convert($input, $encoding);
        }
        $input = InputPreprocessor::removeBOM($input);
        $input = InputPreprocessor::normalizeNewlines($input);

        return $input;
    }
}
