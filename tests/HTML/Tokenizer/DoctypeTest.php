<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\Token;

class DoctypeTest extends TestCase
{

    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('doctypeProvider')]
    public function testDoctype(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function doctypeProvider(): iterable
    {
        yield ['<!DOCTYPE html>', [Token::doctype('html')]];
        yield [
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            [Token::doctype('html', '-//W3C//DTD HTML 4.01 Transitional//EN', 'http://www.w3.org/TR/html4/loose.dtd')]
        ];
        yield [
            '<!DOCTYPE foo SYSTEM "http://www.example.com/foo.dtd">',
            [Token::doctype('foo', null, 'http://www.example.com/foo.dtd')]
        ];
    }

}
