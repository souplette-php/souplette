<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\Token;

class EndTagTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('endTagProvider')]
    public function testEndTag(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function endTagProvider(): iterable
    {
        yield ['</foo>', [Token::endTag('foo')]];
        yield ['</foo >', [Token::endTag('foo')]];
    }
}
