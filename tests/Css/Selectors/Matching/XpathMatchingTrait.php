<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Matching;

use Souplette\Css\Selectors\SelectorParser;
use Souplette\Css\Selectors\Xpath\Translator;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;

trait XpathMatchingTrait
{
    private static Translator $translator;

    /**
     * @beforeClass
     */
    public static function setUpTranslator()
    {
        self::$translator = new Translator();
    }

    private static function querySelector(\DOMDocument $document, string $selector): \DOMNodeList
    {
        $parser = new SelectorParser(new TokenStream(new Tokenizer($selector), 2));
        $ast = $parser->parseSelectorList();
        $expr = self::$translator->translate($ast);
        $xpath = new \DOMXPath($document);
        return $xpath->query($expr);
    }
}
