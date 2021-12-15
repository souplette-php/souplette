<?php declare(strict_types=1);

namespace Souplette\Tests\XML;

use Souplette\DOM\Element;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Namespaces;
use Souplette\XML\XMLNameEscaper;

final class XMLNameEscaperTest extends TestCase
{
    /**
     * @dataProvider escapeElementNameProvider
     * @param string $input
     * @param string $expected
     */
    public function testEscapeElementName(string $input, string $expected)
    {
        $name = XMLNameEscaper::escape($input);
        Assert::assertSame($expected, $name);
        // Should not throw exception
        $element = new Element($name, Namespaces::HTML);
    }

    public function escapeElementNameProvider(): iterable
    {
        yield ['div<div', 'divU00003Cdiv'];
        yield ['foo>bar', 'fooU00003Ebar'];
        yield ['foo&bar', 'fooU000026bar'];
        yield ['rdar:', 'rdarU00003A'];
        yield ['666evil', 'U00003666evil'];
    }

    /**
     * @dataProvider unescapeElementNameProvider
     * @param string $input
     * @param string $expected
     */
    public function testUnescapeElementName(string $input, string $expected)
    {
        Assert::assertSame($expected, XMLNameEscaper::unescape($input));
    }

    public function unescapeElementNameProvider(): iterable
    {
        yield ['divU00003Cdiv', 'div<div'];
        yield ['fooU00003Ebar', 'foo>bar'];
        yield ['fooU000026bar', 'foo&bar'];
        yield ['U00003666evil', '666evil'];
    }
}
