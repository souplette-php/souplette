<?php declare(strict_types=1);

namespace Souplette\Tests\Xml;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Namespaces;
use Souplette\Xml\XmlNameEscaper;

final class XmlNameEscaperTest extends TestCase
{
    /**
     * @dataProvider escapeElementNameProvider
     * @param string $input
     * @param string $expected
     */
    public function testEscapeElementName(string $input, string $expected)
    {
        $name = XmlNameEscaper::escape($input);
        Assert::assertSame($expected, $name);
        // Should not throw exception
        $element = new \DOMElement($name, Namespaces::HTML);
    }

    public function escapeElementNameProvider()
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
        Assert::assertSame($expected, XmlNameEscaper::unescape($input));
    }

    public function unescapeElementNameProvider()
    {
        yield ['divU00003Cdiv', 'div<div'];
        yield ['fooU00003Ebar', 'foo>bar'];
        yield ['fooU000026bar', 'foo&bar'];
        yield ['U00003666evil', '666evil'];
    }
}
