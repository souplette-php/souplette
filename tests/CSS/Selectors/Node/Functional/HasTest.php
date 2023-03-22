<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Functional\Has;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;
use Souplette\Tests\DOM\DOMBuilder;

final class HasTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [
            new Has(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new TypeSelector('bar', '*'),
            ])),
            ':has(foo, bar)',
        ];
    }

    public static function specificityProvider(): iterable
    {
        yield [
            new Has(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ])),
            new Specificity(1, 0, 0),
        ];
    }
    #[DataProvider('matchesProvider')]
    public function testMatches(Element $element, string $selectorText, bool $expected)
    {
        $selector = SelectorUtils::parseSelectorList($selectorText);
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function matchesProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
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
        $root = $dom->documentElement;
        foreach (['b', 'c', 'd', 'e'] as $i => $tagName) {
            $selector = ":has({$tagName})";
            yield "node {$i} matches {$selector}" => [
                $root->childNodes[$i],
                $selector,
                true,
            ];
        }
        yield "node 0 matches :has(a, b)" => [
            $root->childNodes[0],
            ':has(a, b)',
            true,
        ];
        yield "node 1 does not matches :has(a, b)" => [
            $root->childNodes[1],
            ':has(a, b)',
            false,
        ];
    }

    #[DataProvider('scopeProvider')]
    public function testScope(Document $doc, string $selector, array $matchingPaths)
    {
        SelectorAssert::assertQueryAll($doc, $selector, $matchingPaths, $doc->documentElement);
    }

    public static function scopeProvider(): iterable
    {
        $doc = DOMBuilder::html()->tag('main')
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
        $doc = DOMBuilder::html()
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
        $doc = DOMBuilder::html()->tag('body')
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

        $doc = DOMBuilder::html()->tag('body')
            ->tag('h1')->close()
            ->tag('h1')->close()
            ->tag('p')->close()
            ->getDocument();
        yield 'h1:has(+p)' => [
            $doc,
            'h1:has(+p)',
            ['/body/h1[2]'],
        ];

        $doc = DOMBuilder::html()->tag(('body'))
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
