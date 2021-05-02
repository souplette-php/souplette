<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\SelectorParser;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;

class SelectorParserTestCase extends TestCase
{
    protected static function parseSelectorList(string $input, array $namespaces = []): SelectorList
    {
        $tokens = new TokenStream(new Tokenizer($input), 2);
        $parser = new SelectorParser($tokens, $namespaces);
        return $parser->parseSelectorList();
    }

    protected static function simpleToComplex(SimpleSelector $selector): ComplexSelector
    {
        return new ComplexSelector(new CompoundSelector([$selector]));
    }
}
