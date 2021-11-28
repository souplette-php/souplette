<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Simple;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

final class AttributeTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [AttributeSelector::exists('foo'), '[foo]'];
        yield [AttributeSelector::exists('foo', 'bar'), '[bar|foo]'];

        yield [AttributeSelector::equals('foo', 'bar'), '[foo="bar"]'];
        yield [AttributeSelector::equals('foo', 'bar', 'baz'), '[baz|foo="bar"]'];
        yield [AttributeSelector::equals('foo', 'bar', 'baz', 'i'), '[baz|foo="bar" i]'];
        yield [AttributeSelector::equals('foo', 'bar', 'baz', 's'), '[baz|foo="bar" s]'];

        yield [AttributeSelector::prefixMatch('foo', 'bar'), '[foo^="bar"]'];
        yield [AttributeSelector::prefixMatch('foo', 'bar', 'baz', 'i'), '[baz|foo^="bar" i]'];
        yield [AttributeSelector::suffixMatch('foo', 'bar'), '[foo$="bar"]'];
        yield [AttributeSelector::suffixMatch('foo', 'bar', 'baz', 's'), '[baz|foo$="bar" s]'];

        yield [AttributeSelector::includes('foo', 'bar'), '[foo~="bar"]'];
        yield [AttributeSelector::includes('foo', 'bar', 'baz', 'i'), '[baz|foo~="bar" i]'];

        yield [AttributeSelector::substring('foo', 'bar'), '[foo*="bar"]'];
        yield [AttributeSelector::substring('foo', 'bar', 'baz', 's'), '[baz|foo*="bar" s]'];

        yield [AttributeSelector::dashMatch('foo', 'bar'), '[foo|="bar"]'];
        yield [AttributeSelector::dashMatch('foo', 'bar', 'baz', 'i'), '[baz|foo|="bar" i]'];
    }

    public function specificityProvider(): iterable
    {
        yield [new AttributeSelector('foo'), new Specificity(0, 1, 0)];
    }

    /**
     * @dataProvider existsProvider
     */
    public function testExists(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function existsProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('bar', 'baz')->close()
            ->getDocument();

        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::exists('bar'),
            true,
        ];
        yield 'fails' => [
            $dom->firstElementChild,
            AttributeSelector::exists('nope'),
            false,
        ];
        yield 'attribute names are not case sensitive' => [
            $dom->firstElementChild,
            AttributeSelector::exists('BAR', '*'),
            true,
        ];
    }

    /**
     * @dataProvider equalsProvider
     */
    public function testEquals(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function equalsProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('foo', 'bar')
            ->getDocument();

        yield 'matches' => [
            $dom->documentElement,
            AttributeSelector::equals('foo', 'bar', '*'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->documentElement,
            AttributeSelector::equals('foo', 'BAR', '*', 'i'),
            true,
        ];
        yield 'fails case-sensitive' => [
            $dom->documentElement,
            AttributeSelector::equals('foo', 'BAR', '*', 's'),
            false,
        ];
        yield 'fails' => [
            $dom->documentElement,
            AttributeSelector::equals('foo', 'nope', '*'),
            false,
        ];
    }

    /**
     * @dataProvider dashMatchProvider
     */
    public function testDashMatch(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function dashMatchProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('lang', 'en')->close()
            ->tag('bar')->attr('lang', 'en-us')->close()
            ->getDocument();

        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::dashMatch('lang', 'en'),
            true,
        ];
        yield 'dash matches' => [
            $dom->lastElementChild,
            AttributeSelector::dashMatch('lang', 'en'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            AttributeSelector::dashMatch('lang', 'EN', '*', 'i'),
            true,
        ];
        yield 'dash matches case-insensitive' => [
            $dom->lastElementChild,
            AttributeSelector::dashMatch('lang', 'EN', '*', 'i'),
            true,
        ];
    }

    /**
     * @dataProvider includesProvider
     */
    public function testIncludes(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function includesProvider(): iterable
    {
        $doc = DomBuilder::create()
            ->tag('foo')->attr('rel', 'nofollow noopener noreferer')->close()
            ->getDocument();

        yield 'fails' => [
            $doc->firstElementChild,
            AttributeSelector::includes('rel', 'noope'),
            false,
        ];
        yield 'matches' => [
            $doc->firstElementChild,
            AttributeSelector::includes('rel', 'noopener'),
            true,
        ];
        yield 'matches at start' => [
            $doc->firstElementChild,
            AttributeSelector::includes('rel', 'nofollow'),
            true,
        ];
        yield 'matches at end' => [
            $doc->firstElementChild,
            AttributeSelector::includes('rel', 'noreferer'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $doc->firstElementChild,
            AttributeSelector::includes('rel', 'NoOpener', '*', 'i'),
            true,
        ];
        $doc = DomBuilder::create()
            ->tag('foo')->attr('title')->close()
            ->getDocument();
        yield 'empty value matches nothing' => [
            $doc->firstElementChild,
            AttributeSelector::includes('title', ''),
            false,
        ];
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testPrefix(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function prefixProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('bar', 'bazqux')->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            AttributeSelector::prefixMatch('bar', 'nope'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::prefixMatch('bar', 'baz'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            AttributeSelector::prefixMatch('bar', 'BAZ', '*', 'i'),
            true,
        ];
    }

    /**
     * @dataProvider suffixProvider
     */
    public function testSuffix(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function suffixProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('bar', 'bazqux')->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            AttributeSelector::suffixMatch('bar', 'nope'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::suffixMatch('bar', 'qux'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            AttributeSelector::suffixMatch('bar', 'QUX', '*', 'i'),
            true,
        ];
    }

    /**
     * @dataProvider substringProvider
     */
    public function testSubstring(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function substringProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('a')->attr('b', 'foobarbaz')->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            AttributeSelector::substring('b', 'nope'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::substring('b', 'bar'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            AttributeSelector::substring('b', 'BAR', '*', 'i'),
            true,
        ];
    }
}
