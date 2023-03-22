<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\TokenizerState;

class RCDataTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('rcdataProvider')]
    public function testRCData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected, null, TokenizerState::RCDATA);
    }

    public static function rcdataProvider(): iterable
    {
        yield [
            'Foo & Bar', [
                'Foo ',
                '&',
                ' Bar',
            ]
        ];
    }
}
