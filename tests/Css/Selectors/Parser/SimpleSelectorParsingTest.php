<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Exception\UndeclaredNamespacePrefix;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\SelectorParserTestCase;

final class SimpleSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseSelectorListWithSimpleSelectorsProvider
     */
    public function testParseSelectorListWithSimpleSelectors(string $input, $expected, array $namespaces = [])
    {
        $selector = self::parseSelectorList($input, $namespaces);
        $expected = new SelectorList([new ComplexSelector(new CompoundSelector([$expected]))]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseSelectorListWithSimpleSelectorsProvider(): \Generator
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
        yield ':root' => [':root', new PseudoClassSelector('root')];
        yield ':first-child' => [':first-child', new PseudoClassSelector('first-child')];
        // functional pseudo-classes
        yield from SimpleSelectorProvider::simpleFunctionalPseudoClasses();
        // TODO: :is() :not() :has() :where()
    }

    /**
     * @dataProvider undeclaredNamespacePrefixesProvider
     */
    public function testUndeclaredNamespacePrefixes(string $input)
    {
        $this->expectException(UndeclaredNamespacePrefix::class);
        self::parseSelectorList($input);
    }

    public function undeclaredNamespacePrefixesProvider(): \Generator
    {
        foreach (SimpleSelectorProvider::namespacedTypeSelectors() as $key => [$input,]) {
            yield $key => [$input];
        }
        yield 'undeclared attribute namespace' => ['[foo|bar="baz"]'];
    }
}
