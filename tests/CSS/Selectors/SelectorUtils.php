<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors;

use Souplette\CSS\Selectors\Node\ComplexSelector;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\CSS\Selectors\SelectorParser;
use Souplette\CSS\Syntax\Tokenizer\Tokenizer;
use Souplette\CSS\Syntax\TokenStream\TokenStream;

final class SelectorUtils
{
    public static function parseSelectorList(string $input, array $namespaces = []): SelectorList
    {
        $tokens = new TokenStream(new Tokenizer($input), 2);
        $parser = new SelectorParser($tokens, $namespaces);
        return $parser->parseSelectorList();
    }

    /**
     * @param SimpleSelector[] $selectors
     * @return ComplexSelector
     */
    public static function compoundToComplex(array $selectors): ComplexSelector
    {
        $compound = null;
        foreach ($selectors as $selector) {
            $compound = $compound ? $compound->append($selector) : $selector;
        }
        return new ComplexSelector($compound);
    }

    public static function simpleToComplex(SimpleSelector $selector): ComplexSelector
    {
        return new ComplexSelector($selector);
    }

    public static function toSelectorList(array $selectors): SelectorList
    {
        $arguments = [];
        foreach ($selectors as $selector) {
            if ($selector instanceof SimpleSelector) {
                $arguments[] = self::simpleToComplex($selector);
            } else if (\is_array($selector)) {
                $arguments[] = self::compoundToComplex($selector);
            } else {
                $arguments[] = $selector;
            }
        }
        return new SelectorList($arguments);
    }
}
