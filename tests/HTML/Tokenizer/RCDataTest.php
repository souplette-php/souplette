<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\TokenizerState;

class RCDataTest extends TestCase
{
    /**
     * @dataProvider rcdataProvider
     * @param string $input
     * @param array $expected
     */
    public function testRCData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected, null, TokenizerState::RCDATA);
    }

    public function rcdataProvider(): iterable
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