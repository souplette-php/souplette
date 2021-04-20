<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Query\Evaluator\Simple\TypeEvaluator;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Html\Namespaces;
use Souplette\Tests\Html\DomBuilder;

final class TypeEvaluatorTest extends TestCase
{
    private static function assertMatches(\DOMElement $element, TypeSelector $selector, bool $expected)
    {
        $ctx = new QueryContext($element);
        $ctx->selector = $selector;
        $evaluator = new TypeEvaluator();
        Assert::assertSame($expected, $evaluator->matches($ctx));
    }

    /**
     * @dataProvider anyNamespaceProvider
     */
    public function testAnyNamespace(\DOMElement $element, TypeSelector $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function anyNamespaceProvider(): \Generator
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
        self::assertMatches($element, $selector, $expected);
    }

    public function explicitNamespaceProvider(): \Generator
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
        self::assertMatches($element, $selector, $expected);
    }

    public function nullNamespaceProvider(): \Generator
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
}
