<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Legacy\Document;
use Souplette\Dom\Legacy\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/css/selectors/has-argument-with-explicit-scope.tentative.html
 */
final class HasArgumentWithExplicitScopeTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <meta charset="utf-8">
    <title>:has pseudo class behavior with explicit ':scope' in its argument</title>
    <link rel="author" title="Byungwoo Lee" href="mailto:blee@igalia.com">
    <link rel="help" href="https://drafts.csswg.org/selectors/#relational">
    <main>
      <div id=d01 class="a">
        <div id=scope1 class="b">
          <div id=d02 class="c">
            <div id=d03 class="c">
              <div id=d04 class="d"></div>
            </div>
          </div>
          <div id=d05 class="e"></div>
        </div>
      </div>
      <div id=d06>
        <div id=scope2 class="b">
          <div id=d07 class="c">
            <div id=d08 class="c">
              <div id=d09></div>
            </div>
          </div>
        </div>
       </div>
     </div>
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
    public function testQuerySelectorAll(string $scopeId, string $selector, array $expected)
    {
        $scope = self::$document->getElementById($scopeId);
        $actual = $scope->querySelectorAll($selector);
        Assert::assertEquals($expected, $this->formatElements($actual));
    }

    public function querySelectorAllProvider(): iterable
    {
        // descendants of a scope element cannot have the scope element as its descendant
        yield ['scope1', ':has(:scope)', []];
        yield ['scope1', ':has(:scope .c)', []];
        yield ['scope1', ':has(.a :scope)', []];

        yield ['scope1', '.a:has(:scope) .c', ['d02', 'd03']];
        yield ['scope2', '.a:has(:scope) .c', []];
        yield ['scope1', '.c:has(:is(:scope .d))', ['d02', 'd03']];
        yield ['scope2', '.c:has(:is(:scope .d))', []];
    }

    /**
     * @dataProvider compareSelectorAllProvider
     */
    public function testCompareSelectorAll(string $scopeId, string $selector1, string $selector2)
    {
        $scope = self::$document->getElementById($scopeId);
        $result1 = $scope->querySelectorAll($selector1);
        $result2 = $scope->querySelectorAll($selector2);
        Assert::assertEquals($this->formatElements($result1), $this->formatElements($result2));
    }

    public function compareSelectorAllProvider(): iterable
    {
        // there can be more simple and efficient alternative for a ':scope' in ':has'
        yield ['scope1', '.a:has(:scope) .c', ':is(.a :scope .c)'];
        yield ['scope2', '.a:has(:scope) .c', ':is(.a :scope .c)'];

        yield ['scope1', '.c:has(:is(:scope .d))', ':scope .c:has(.d)'];
        yield ['scope2', '.c:has(:is(:scope .d))', ':scope .c:has(.d)'];
        yield ['scope1', '.c:has(:is(:scope .d))', '.c:has(.d)'];
        yield ['scope2', '.c:has(:is(:scope .d))', '.c:has(.d)'];
    }
}
