<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Functional;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\Has;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorAssert;
use Souplette\Tests\Css\Selectors\Utils;
use Souplette\Tests\Dom\DomBuilder;

final class HasTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(\DOMElement $element, string $selectorText, bool $expected)
    {
        $selector = Utils::parseSelectorList($selectorText);
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('a')
                ->tag('b')->close()
            ->close()
            ->tag('a')
                ->tag('c')->close()
            ->close()
            ->tag('a')
                ->tag('d')->close()
            ->close()
            ->tag('a')
                ->tag('foo')
                    ->tag('e')->close()
                ->close()
            ->close()
            ->getDocument();
        foreach (['b', 'c', 'd', 'e'] as $i => $tagName) {
            $selector = ":has({$tagName})";
            yield "node {$i} matches {$selector}" => [
                $dom->childNodes->item($i),
                $selector,
                true,
            ];
        }
        yield "node 0 matches :has(a, b)" => [
            $dom->childNodes->item(0),
            ':has(a, b)',
            true,
        ];
        yield "node 1 does not matches :has(a, b)" => [
            $dom->childNodes->item(1),
            ':has(a, b)',
            false,
        ];
    }

    public function testScope()
    {
        $doc = DomBuilder::create()->tag('main')
            ->tag('div')->id('d1')
                ->tag('div')->id('d2')->class('a')
                    ->tag('div')->id('d3')->class('a')
                        ->tag('div')->id('d4')
                            ->tag('div')->id('d5')->class('b')
            ->getDocument();
        SelectorAssert::assertQueryAll($doc, 'div:has(.a .b)', [
            '/main/div', // #d1
            '/main/div/div', // #d2
        ], $doc->documentElement);
    }

    public function testItAcceptsRelativeSelectors()
    {
        $this->markTestIncomplete('Not implemented');
    }
}
