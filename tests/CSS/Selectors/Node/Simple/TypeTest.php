<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;
use Souplette\DOM\Namespaces;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class TypeTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [new TypeSelector('foo', '*'), 'foo'];
        yield [new TypeSelector('foo', 'svg'), 'svg|foo'];
        yield [new TypeSelector('foo', null), '|foo'];
    }

    public static function specificityProvider(): iterable
    {
        $spec = new Specificity(0, 0, 1);
        yield [new TypeSelector('foo', '*'), $spec];
        yield [new TypeSelector('foo', 'svg'), $spec];
        yield [new TypeSelector('foo', null), $spec];
    }

    #[DataProvider('anyNamespaceProvider')]
    public function testAnyNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function anyNamespaceProvider(): iterable
    {
        $dom = DOMBuilder::html()
            ->tag('g', Namespaces::SVG)->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            new TypeSelector('nope', '*'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            new TypeSelector('g', '*'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            new TypeSelector('G', '*'),
            true,
        ];
    }

    #[DataProvider('explicitNamespaceProvider')]
    public function testExplicitNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        $this->markTestSkipped('Namespaces in CSS are a lie.');
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function explicitNamespaceProvider(): iterable
    {
        $dom = DOMBuilder::html()
            ->tag('svg:g', Namespaces::SVG)->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            new TypeSelector('g', 'html'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            new TypeSelector('g', 'svg'),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            new TypeSelector('G', 'svg'),
            true,
        ];
    }

    #[DataProvider('nullNamespaceProvider')]
    public function testNullNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function nullNamespaceProvider(): iterable
    {
        $dom = DOMBuilder::html()
            ->tag('foo', '')->close()
            ->getDocument();

        yield 'fails' => [
            $dom->firstElementChild,
            new TypeSelector('foo', 'html'),
            false,
        ];
        yield 'matches' => [
            $dom->firstElementChild,
            new TypeSelector('foo', null),
            true,
        ];
        yield 'matches case-insensitive' => [
            $dom->firstElementChild,
            new TypeSelector('FOO', null),
            true,
        ];
    }

    public function testXmlDocumentIsCaseSensitive()
    {
        $doc = DOMBuilder::xml()->tag('html')
            ->tag('a')->close()
            ->tag('A')->close()
            ->tag('p')
                ->tag('A')->close()
                ->tag('a')->close()
            ->close()
            ->getDocument();
        SelectorAssert::assertQueryAll($doc, 'a', [
            '/html/a',
            '/html/p/a',
        ]);
    }
}
