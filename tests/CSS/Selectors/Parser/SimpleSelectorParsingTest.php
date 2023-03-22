<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Exception\UndeclaredNamespacePrefix;
use Souplette\CSS\Selectors\Node\PseudoClass\FirstChildPseudo;
use Souplette\CSS\Selectors\Node\PseudoClass\RootPseudo;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\SelectorAssert;
use Souplette\Tests\CSS\Selectors\SelectorParserTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class SimpleSelectorParsingTest extends SelectorParserTestCase
{
    #[DataProvider('parseSelectorListWithSimpleSelectorsProvider')]
    public function testParseSelectorListWithSimpleSelectors(string $input, $expected, array $namespaces = [])
    {
        $selector = SelectorUtils::parseSelectorList($input, $namespaces);
        $expected = new SelectorList([
            SelectorUtils::simpleToComplex($expected),
        ]);
        SelectorAssert::selectorListEquals($expected, $selector);
    }

    public static function parseSelectorListWithSimpleSelectorsProvider(): iterable
    {
        // Type selectors
        yield from SimpleSelectorProvider::typeSelectors();
        yield from SimpleSelectorProvider::namespacedTypeSelectors();
        // ID
        yield '#id' => ['#foo', new IdSelector('foo')];
        // class
        yield '.class' => ['.bar', new ClassSelector('bar')];
        // Attributes
        yield from SimpleSelectorProvider::attributeSelectors();
        // pseudo-classes
        yield ':root' => [':root', new RootPseudo('root')];
        yield ':first-child' => [':first-child', new FirstChildPseudo('first-child')];
        yield ':unknown' => [':unknown', new PseudoClassSelector('unknown')];
        // functional pseudo-classes
        yield from SimpleSelectorProvider::simpleFunctionalPseudoClasses();
        // TODO: :is() :not() :has() :where()
    }

    #[DataProvider('undeclaredNamespacePrefixesProvider')]
    public function testUndeclaredNamespacePrefixes(string $input)
    {
        $this->expectException(UndeclaredNamespacePrefix::class);
        SelectorUtils::parseSelectorList($input);
    }

    public static function undeclaredNamespacePrefixesProvider(): iterable
    {
        foreach (SimpleSelectorProvider::namespacedTypeSelectors() as $key => [$input,]) {
            yield $key => [$input];
        }
        yield 'undeclared attribute namespace' => ['[foo|bar="baz"]'];
    }
}
