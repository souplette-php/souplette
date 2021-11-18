<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Simple;

use Souplette\Html\Namespaces;
use Souplette\Tests\Css\Selectors\Query\SelectorQueryTestCase;
use Souplette\Tests\Html\DomBuilder;

final class TypeSelectorTest extends SelectorQueryTestCase
{
    /**
     * @dataProvider anyNamespaceProvider
     */
    public function testAnyNamespace(\DOMDocument $doc, string $selector, array $matches)
    {
        self::assertQueryAll($doc, $selector, $matches);
    }

    public function anyNamespaceProvider(): \Generator
    {
        $doc = DomBuilder::create()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('p')
                ->tag('A')->close()
                ->tag('b')->close()
                ->tag('A')->close()
            ->close()
            ->getDocument();

        yield [
            $doc,
            'a',
            [
                '/a[1]',
                '/a[2]',
                '/p/A[1]',
                '/p/A[2]',
            ],
        ];
        yield [
            $doc,
            'A',
            [
                '/a[1]',
                '/a[2]',
                '/p/A[1]',
                '/p/A[2]',
            ],
        ];
    }

    public function testXmlDocumentIsCaseSensitive()
    {
        $doc = self::loadXml(<<<HTML
        <html>
          <a/>
          <A/>
          <p>
            <A/>
            <a/>
          </p>
        </html>
        HTML);

        self::assertQueryAll($doc, 'a', [
            '/html/a',
            '/html/p/a',
        ]);
    }
}
