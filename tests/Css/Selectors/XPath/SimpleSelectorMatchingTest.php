<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\XPath;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Tests\Html\DomBuilder;

final class SimpleSelectorMatchingTest extends TestCase
{
    use XpathMatchingTrait;

    /**
     * @dataProvider typeSelectorProvider
     */
    public function testTypeSelectors(\DOMDocument $document, string $type)
    {
        $nodes = self::querySelector($document, $type);
        Assert::assertGreaterThan(0, $nodes->length);
        foreach ($nodes as $node) {
            Assert::assertSame($type, $node->localName);
        }
    }

    public function typeSelectorProvider(): \Generator
    {
        $doc = DomBuilder::create()->tag('html')->tag('body')
            ->tag('div')
                ->tag('ul')
                    ->tag('li')
                    ->tag('li')
            ->getDocument()
        ;
        yield [$doc, 'html'];
        yield [$doc, 'div'];
        yield [$doc, 'li'];
    }

    /**
     * @dataProvider idSelectorProvider
     */
    public function testIdSelectors(\DOMDocument $document, string $input, string $expected)
    {
        $nodes = self::querySelector($document, $input);
        Assert::assertSame(1, $nodes->length);
        foreach ($nodes as $node) {
            Assert::assertSame($expected, $node->getAttribute('id'));
        }
    }

    public function idSelectorProvider(): \Generator
    {
        $doc = DomBuilder::create()->tag('html')->tag('body')
            ->tag('div')->attr('id', 'root')
                ->tag('ul')
                    ->tag('li')->attr('id', 'item-1')->close()
                    ->tag('li')->attr('id', 'item-2')->close()
                    ->tag('li')->attr('id', 'item-3')->close()
            ->getDocument()
        ;
        yield [$doc, '#root', 'root'];
        yield [$doc, '#item-2', 'item-2'];
    }

    /**
     * @dataProvider classSelectorsProvider
     */
    public function testClassSelectors(\DOMDocument $document, string $input, array $expected)
    {
        $nodes = self::querySelector($document, $input);
        $classes = array_map(fn($node) => $node->getAttribute('class'), iterator_to_array($nodes));
        Assert::assertEquals($expected, $classes);
    }

    public function classSelectorsProvider(): \Generator
    {
        $doc = DomBuilder::create()->tag('html')->tag('body')
            ->tag('ul')->attr('class', 'list')
                ->tag('li')->attr('class', 'item odd')->close()
                ->tag('li')->attr('class', 'item even')->close()
                ->tag('li')->attr('class', 'item odd')->close()
                ->tag('li')->attr('class', 'item even')->close()
            ->getDocument()
        ;
        yield [$doc, '.list', ['list']];
        yield [$doc, '.item', ['item odd', 'item even', 'item odd', 'item even']];
        yield [$doc, '.item.odd', ['item odd', 'item odd']];
    }

    /**
     * @dataProvider attributeSelectorsProvider
     */
    public function testAttributeSelectors(\DOMDocument $document, string $input, array $expected)
    {
        $nodes = self::querySelector($document, $input);
        $attrs = array_map(fn($node) => $node->getAttribute('test'), iterator_to_array($nodes));
        Assert::assertEquals($expected, $attrs);
    }

    public function attributeSelectorsProvider(): \Generator
    {
        $builder = DomBuilder::create()->tag('html')->tag('body');
        $builder
            ->tag('b')->attr('test', 'a')->close()
            ->tag('b')->attr('test', 'A')->close()
            ->tag('b')->attr('test', 'ab')->close()
            ->tag('b')->attr('test', 'AB')->close()
            ->tag('b')->attr('test', 'xyx')->close()
            ->tag('b')->attr('test', 'XYX')->close()
        ;
        $builder
            ->tag('b')->attr('test', 'c c d')->close()
            ->tag('b')->attr('test', 'c d c')->close()
            ->tag('b')->attr('test', 'd c c')->close()
            ->tag('b')->attr('test', 'C C D')->close()
            ->tag('b')->attr('test', 'C D C')->close()
            ->tag('b')->attr('test', 'D C C')->close()
        ;
        $builder
            ->tag('b')->attr('test', 'en')->close()
            ->tag('b')->attr('test', 'en-us')->close()
            ->tag('b')->attr('test', 'EN')->close()
            ->tag('b')->attr('test', 'EN-US')->close()
        ;
        $doc = $builder->getDocument();
        //yield ['[test]', ['a', 'b', 'c']];
        yield [$doc, '[test=a]', ['a']];
        yield [$doc, '[test=A]', ['A']];
        yield [$doc, '[test=a i]', ['a', 'A']];
        yield [$doc, '[test=A i]', ['a', 'A']];
        yield [$doc, '[test^=a]', ['a', 'ab']];
        yield [$doc, '[test^=a i]', ['a', 'A', 'ab', 'AB']];
        yield [$doc, '[test$=b]', ['ab']];
        yield [$doc, '[test$=b i]', ['ab', 'AB']];
        yield [$doc, '[test*=y]', ['xyx']];
        yield [$doc, '[test*=y i]', ['xyx', 'XYX']];
        yield [$doc, '[test~=d]', ['c c d', 'c d c', 'd c c']];
        yield [$doc, '[test~=d i]', ['c c d', 'c d c', 'd c c', 'C C D', 'C D C', 'D C C']];
        yield [$doc, '[test|=en]', ['en', 'en-us']];
        yield [$doc, '[test|=en i]', ['en', 'en-us', 'EN', 'EN-US']];
    }
}
