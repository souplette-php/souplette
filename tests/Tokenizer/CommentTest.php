<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * @dataProvider commentsProvider
     * @param string $input
     * @param array $expected
     */
    public function testComments(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function commentsProvider()
    {
        yield ['<!-- comment -->', [Token::comment(' comment ')]];
        yield ['<!-- XXX - XXX -->', [Token::comment(' XXX - XXX ')]];
    }
}
