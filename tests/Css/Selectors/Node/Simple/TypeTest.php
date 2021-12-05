<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Simple;

use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Namespaces;
use Souplette\Dom\XmlDocument;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

final class TypeTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new TypeSelector('foo', '*'), 'foo'];
        yield [new TypeSelector('foo', 'svg'), 'svg|foo'];
        yield [new TypeSelector('foo', null), '|foo'];
    }

    public function specificityProvider(): iterable
    {
        $spec = new Specificity(0, 0, 1);
        yield [new TypeSelector('foo', '*'), $spec];
        yield [new TypeSelector('foo', 'svg'), $spec];
        yield [new TypeSelector('foo', null), $spec];
    }

    /**
     * @dataProvider anyNamespaceProvider
     */
    public function testAnyNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function anyNamespaceProvider(): iterable
    {
        $dom = DomBuilder::create()
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

    /**
     * @dataProvider explicitNamespaceProvider
     */
    public function testExplicitNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        $this->markTestSkipped('Namespaces in CSS are a lie.');
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function explicitNamespaceProvider(): iterable
    {
        $dom = DomBuilder::create()
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

    /**
     * @dataProvider nullNamespaceProvider
     */
    public function testNullNamespace(Element $element, TypeSelector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function nullNamespaceProvider(): iterable
    {
        $dom = DomBuilder::create()
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
        $doc = DomBuilder::create('xml')->tag('html')
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
