<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Tokenizer;

use PHPUnit\Framework\TestCase;
use Souplette\Html\Tokenizer\Token;

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
