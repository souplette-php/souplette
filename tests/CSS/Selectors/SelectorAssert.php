<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors;

use Souplette\DOM\Document;
use Souplette\DOM\Element;
use PHPUnit\Framework\Assert;
use Souplette\CSS\Selectors\Node\ComplexSelector;
use Souplette\CSS\Selectors\Node\Selector;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\SelectorQuery;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\Utils as TestUtils;

final class SelectorAssert
{
    public static function selectorEquals(ComplexSelector $expected, ComplexSelector $actual)
    {
        Assert::assertEquals($expected, $actual);
    }

    public static function selectorListEquals(SelectorList $expected, SelectorList $actual)
    {
        Assert::assertSame(
            \count($expected),
            \count($actual),
        );
        foreach ($expected->selectors as $i => $expectedSelector) {
            $actualSelector = $actual->selectors[$i];
            self::selectorEquals($expectedSelector, $actualSelector);
        }
    }

    public static function specificityEquals(Selector $selector, Specificity $expected)
    {
        $actual = $selector->getSpecificity();
        Assert::assertSame((string)$expected, (string)$actual);
    }

    public static function assertQueryAll(
        Document $doc,
        string $selectorText,
        array $expectedPaths,
        ?Element $root = null,
    ) {
        if (!$root) $root = $doc;
        $results = SelectorQuery::all($root, $selectorText) ?? [];
        $actualPaths = array_map(fn($el) => TestUtils::elementPath($el), $results);
        Assert::assertEquals($expectedPaths, $actualPaths);
    }
}
