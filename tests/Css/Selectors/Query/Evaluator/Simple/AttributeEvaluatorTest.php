<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Simple;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class AttributeEvaluatorTest extends TestCase
{
    /**
     * @dataProvider existsProvider
     */
    public function testExists(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function existsProvider(): \Generator
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
    }

    /**
     * @dataProvider equalsProvider
     */
    public function testEquals(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function equalsProvider(): \Generator
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

    public function dashMatchProvider(): \Generator
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

    public function includesProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('rel', 'nofollow noopener noreferer')->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            AttributeSelector::includes('rel', 'noope'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            AttributeSelector::includes('rel', 'noopener'),
            true,
        ];
        yield 'matches at start' => [
            $dom->firstElementChild,
            AttributeSelector::includes('rel', 'nofollow'),
            true,
        ];
        yield 'matches at end' => [
            $dom->firstElementChild,
            AttributeSelector::includes('rel', 'noreferer'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            AttributeSelector::includes('rel', 'NoOpener', '*', 'i'),
            true,
        ];
    }

    /**
     * @dataProvider prefixProvider
     */
    public function testPrefix(\DOMElement $element, AttributeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function prefixProvider(): \Generator
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

    public function suffixProvider(): \Generator
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

    public function substringProvider(): \Generator
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
