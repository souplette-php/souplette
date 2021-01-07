<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Parser\Tokenizer;

use Souplette\Html\Parser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class EndTagTest extends TestCase
{
    /**
     * @dataProvider endTagProvider
     * @param string $input
     * @param array $expected
     */
    public function testEndTag(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function endTagProvider()
    {
        yield ['</foo>', [Token::endTag('foo')]];
        yield ['</foo >', [Token::endTag('foo')]];
    }
}
