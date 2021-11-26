<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/has-basic.html
 */
final class HasBasicTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <meta charset="utf-8">
    <title>Basic matching behavior of :has pseudo class</title>
    <link rel="author" title="Byungwoo Lee" href="mailto:blee@igalia.com">
    <link rel="help" href="https://drafts.csswg.org/selectors/#relational">
    
    <main id=main>
      <div id=a class="ancestor">
        <div id=b class="parent ancestor">
          <div id=c class="sibling descendant">
            <div id=d class="descendant"></div>
          </div>
          <div id=e class="target descendant"></div>
        </div>
        <div id=f class="parent ancestor">
          <div id=g class="target descendant"></div>
        </div>
        <div id=h class="parent ancestor">
          <div id=i class="target descendant"></div>
          <div id=j class="sibling descendant">
            <div id=k class="descendant"></div>
          </div>
        </div>
      </div>
    </main>
    HTML;

    private static Document $document;

    public static function setUpBeforeClass(): void
    {
        self::$document = Souplette::parseHtml(self::DOCUMENT);
    }

    /**
     * @param Element[] $elements
     * @return string[]
     */
    private function formatElements(array $elements): array
    {
        return array_map(fn($el) => $el->id, $elements);
    }

    /**
     * @dataProvider querySelectorAllProvider
     */
    public function testQuerySelectorAll(string $selector, array $expected)
    {
        /** @var Element $main */
        $main = self::$document->getElementsByTagName('main')->item(0);
        $actual = $main->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public function querySelectorAllProvider(): iterable
    {
        yield [':has(#a)', []];
        yield [':has(.ancestor)', ['a']];
        yield [':has(.target)', ['a', 'b', 'f', 'h']];
        yield [':has(.descendant)', ['a', 'b', 'c', 'f', 'h', 'j']];
        yield ['.parent:has(.target)', ['b', 'f', 'h']];
        yield [':has(.sibling ~ .target)', ['a', 'b']];
        yield ['.parent:has(.sibling ~ .target)', ['b']];
        yield [':has(:is(.target ~ .sibling .descendant))', ['a', 'h', 'j']];
        yield ['.parent:has(:is(.target ~ .sibling .descendant))', ['h']];
        yield ['.sibling:has(.descendant) ~ .target', ['e']];
        yield [':has(.sibling:has(.descendant) ~ .target)', ['a', 'b']];
        yield [
            ':has(.sibling:has(.descendant) ~ .target) ~ .parent > .descendant',
            ['g', 'i', 'j'],
        ];
        yield [':has(> .parent)', ['a']];
        yield [':has(> .target)', ['b', 'f', 'h']];
        yield [':has(> .parent, > .target)', ['a', 'b', 'f', 'h']];
        yield [':has(+ #h)', ['f']];
        yield ['.parent:has(~ #h)', ['b', 'f']];
    }

    /**
     * @dataProvider querySelectorProvider
     */
    public function testQuerySelector(string $selector, string $expected)
    {
        /** @var Element $main */
        $main = self::$document->getElementsByTagName('main')->item(0);
        $actual = $main->querySelector($selector);
        Assert::assertEquals($expected, $actual->id);
    }

    public function querySelectorProvider(): iterable
    {
        yield ['.sibling:has(.descendant)', 'c'];
    }

    /**
     * @dataProvider closestProvider
     */
    public function testClosest(string $subject, string $selector, string $expected)
    {
        $subject = self::$document->getElementById($subject);
        $actual = $subject->closest($selector);
        Assert::assertEquals($expected, $actual->id);
    }

    public function closestProvider(): iterable
    {
        yield ['k', '.ancestor:has(.descendant)', 'h'];
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(string $subject, string $selector, bool $expected)
    {
        $subject = self::$document->getElementById($subject);
        $actual = $subject->matches($selector);
        Assert::assertSame($expected, $actual);
    }

    public function matchesProvider(): iterable
    {
        yield ['h', ':has(.target ~ .sibling .descendant)', true];
    }
}
