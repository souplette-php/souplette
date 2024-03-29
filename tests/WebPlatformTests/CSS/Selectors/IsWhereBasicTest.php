<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\CSS\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/is-where-basic.html
 */
final class IsWhereBasicTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <title>Basic :is/:where matching behavior</title>
    <link rel="help" href="https://drafts.csswg.org/selectors-4/#matches">
    <main id=main>
      <div id=a><div id=d></div></div>
      <div id=b><div id=e></div></div>
      <div id=c><div id=f></div></div>
    </main>
    HTML;

    private static Document $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = Souplette::parseHTML(self::DOCUMENT);
    }

    /**
     * @param Element[] $elements
     * @return string
     */
    private function formatElements(array $elements): string
    {
        return implode(',', array_map(fn($el) => $el->id, $elements));
    }

    #[DataProvider('querySelectorAllProvider')]
    public function testQuerySelectorAll(string $selector, string $expected)
    {
        /** @var Element $main */
        $main = self::$document->getElementsByTagName('main')[0];
        $actual = $main->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public static function querySelectorAllProvider(): iterable
    {
        yield [':is()', ''];
        yield [':is(#a)', 'a'];
        yield [':is(#a, #f)', 'a,f'];
        yield [':is(#a, #c) :where(#a #d, #c #f)', 'd,f'];
        yield ['#c > :is(#c > #f)', 'f'];
        yield ['#c > :is(#b > #f)', ''];
        yield ['#a div:is(#d)', 'd'];
        yield [':is(div) > div', 'd,e,f'];
        yield [':is(*) > div', 'a,d,b,e,c,f'];
        yield [':is(*) div', 'a,d,b,e,c,f'];
        yield ['div > :where(#e, #f)', 'e,f'];
        yield ['div > :where(*)', 'd,e,f'];
        yield [':is(*) > :where(*)', 'a,d,b,e,c,f'];
        yield [':is(#a + #b) + :is(#c)', 'c'];
        yield [':is(#a, #b) + div', 'b,c'];
    }
}
