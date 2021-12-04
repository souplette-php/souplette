<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Legacy\Document;
use Souplette\Dom\Legacy\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/is-where-not.html
 */
final class IsWhereNotTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <title>:is() inside :not()</title>
    <link rel="help" href="https://drafts.csswg.org/selectors-4/#matches">
    <link rel="help" href="https://drafts.csswg.org/selectors/#negation">
    <main id=main>
      <div id=a><div id=d></div></div>
      <div id=b><div id=e></div></div>
      <div id=c><div id=f></div></div>
    </main>
    HTML;

    private static Document $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = Souplette::parseHtml(self::DOCUMENT);
    }

    /**
     * @param Element[] $elements
     * @return string
     */
    private function formatElements(array $elements): string
    {
        return implode(',', array_map(fn($el) => $el->id, $elements));
    }

    /**
     * @dataProvider querySelectorAllProvider
     */
    public function testQuerySelectorAll(string $selector, string $expected)
    {
        /** @var Element $main */
        $main = self::$document->getElementsByTagName('main')->item(0);
        $actual = $main->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public function querySelectorAllProvider(): iterable
    {
        yield [':not(:is(#a))', 'd,b,e,c,f'];
        yield [':not(:where(#b))', 'a,d,e,c,f'];
        yield [':not(:where(:root #c))', 'a,d,b,e,f'];
        yield [':not(:is(#a, #b))', 'd,e,c,f'];
        yield [':not(:is(#b div))', 'a,d,b,c,f'];
        yield [':not(:is(#a div, div + div))', 'a,e,f'];
        yield [':not(:is(span))', 'a,d,b,e,c,f'];
        yield [':not(:is(div))', ''];
        yield [':not(:is(*|div))', ''];
        yield [':not(:is(*|*))', ''];
        yield [':not(:is(*))', ''];
        yield [':not(:is(svg|div))', 'a,d,b,e,c,f'];
        yield [':not(:is(:not(div)))', 'a,d,b,e,c,f'];
        yield [':not(:is(span, b, i))', 'a,d,b,e,c,f'];
        yield [':not(:is(span, b, i, div))', ''];
        yield [':not(:is(#b ~ div div, * + #c))', 'a,d,b,e'];
        yield [':not(:is(div > :not(#e)))', 'a,b,e,c'];
        yield [':not(:is(div > :not(:where(#e, #f))))', 'a,b,e,c,f'];

    }
}
