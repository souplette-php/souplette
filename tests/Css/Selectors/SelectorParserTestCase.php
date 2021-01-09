<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\SelectorParser;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;

class SelectorParserTestCase extends TestCase
{
    protected static function parseSelectorList(string $input): SelectorList
    {
        $tokens = new TokenStream(new Tokenizer($input), 2);
        $parser = new SelectorParser($tokens);
        return $parser->parseSelectorList();
    }
}
