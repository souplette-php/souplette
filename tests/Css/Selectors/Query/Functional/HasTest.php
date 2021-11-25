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

    /**
     * @dataProvider scopeProvider
     */
    public function testScope(\DOMDocument $doc, string $selector, array $matchingPaths)
    {
        SelectorAssert::assertQueryAll($doc, $selector, $matchingPaths, $doc->documentElement);
    }

    public function scopeProvider(): iterable
    {
        $doc = DomBuilder::create()->tag('main')
            ->tag('div')
                ->tag('div')->class('a')
                    ->tag('div')->class('a')
                        ->tag('div')
                            ->tag('div')->class('b')
            ->getDocument();
        yield 'div:has(.a .b)' => [
            $doc,
            'div:has(.a .b)',
            [
                '/main/div',
                '/main/div/div',
            ]
        ];
        $doc = DomBuilder::create()
            ->tag('main')->class('a')
                ->tag('div')->class('b')
                    ->tag('div')->class('a')
                        ->tag('div')->class('c')
                            ->tag('div')->class('d')
            ->getDocument();
        yield '.a:has(.b .c) .d' => [
            $doc,
            '.a:has(.b .c) .d',
            ['/main/div/div/div/div'],
        ];
        // examples from https://drafts.csswg.org/selectors-4/#relational
        $doc = DomBuilder::create()->tag('body')
            ->tag('a')->text('Nope')->close()
            ->tag('a')
                ->tag('img')
            ->close()
            ->tag('a')
                ->tag('b')
                    ->tag('img')
                ->close()
            ->close()
            ->getDocument();
        yield 'a:has(>img)' => [
            $doc,
            'a:has(>img)',
            ['/body/a[2]'],
        ];

        $doc = DomBuilder::create()->tag('body')
            ->tag('h1')->close()
            ->tag('h1')->close()
            ->tag('p')->close()
            ->getDocument();
        yield 'h1:has(+p)' => [
            $doc,
            'h1:has(+p)',
            ['/body/h1[2]'],
        ];

        $doc = DomBuilder::create()->tag(('body'))
            ->tag('section')
                ->tag('h1')->close()
            ->close()
            ->tag('section')
                ->tag('h2')->close()
            ->close()
            ->tag('section')
                ->tag('p')->close()
            ->close()
            ->getDocument();
        yield 'section:not(:has(h1, h2))' => [
            $doc,
            'section:not(:has(h1, h2))',
            ['/body/section[3]'],
        ];
    }
}
