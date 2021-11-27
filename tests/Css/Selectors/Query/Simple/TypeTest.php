<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Simple;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorAssert;
use Souplette\Tests\Dom\DomBuilder;

final class TypeTest extends TestCase
{
    /**
     * @dataProvider anyNamespaceProvider
     */
    public function testAnyNamespace(\DOMElement $element, TypeSelector $selector, bool $expected)
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
    public function testExplicitNamespace(\DOMElement $element, TypeSelector $selector, bool $expected)
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
    public function testNullNamespace(\DOMElement $element, TypeSelector $selector, bool $expected)
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
        $xml = <<<'XML'
        <html>
          <a/>
          <A/>
          <p>
            <A/>
            <a/>
          </p>
        </html>
        XML;

        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        SelectorAssert::assertQueryAll($doc, 'a', [
            '/html/a',
            '/html/p/a',
        ]);
    }
}
