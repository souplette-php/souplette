<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\CDATASection;
use Souplette\Dom\CharacterData;
use Souplette\Dom\Comment;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;

final class CharacterDataTest extends TestCase
{
    /**
     * @dataProvider itIsConstructedWithValueAndLengthProvider
     */
    public function testItIsConstructedWithValueAndLength(CharacterData $node, string $data, int $expectedLength)
    {
        Assert::assertSame($data, $node->getData());
        Assert::assertSame($expectedLength, $node->getLength());
        // aliases
        Assert::assertSame($data, $node->getTextContent(), 'getTextContent method');
        Assert::assertSame($data, $node->getNodeValue(), 'getNodeValue method');
        // properties
        Assert::assertSame($expectedLength, $node->length, '$length property');
        Assert::assertSame($data, $node->data, '$data property');
        Assert::assertSame($data, $node->textContent, '$textContent property');
        Assert::assertSame($data, $node->nodeValue, '$nodeValue property');
    }

    public function itIsConstructedWithValueAndLengthProvider(): iterable
    {
        yield 'empty text node' => [new Text(), '', 0];
        yield 'empty comment node' => [new Comment(), '', 0];
        yield 'empty cdata node' => [new CDATASection(), '', 0];
        yield 'empty PI node' => [new ProcessingInstruction(''), '', 0];
        //
        yield '#text - wide unicode char' => [new Text('💩'), '💩', 1];
        yield '#comment - wide unicode char' => [new Comment('💩'), '💩', 1];
        yield '#cdata - wide unicode char' => [new CDATASection('💩'), '💩', 1];
        yield '#PI - wide unicode char' => [new ProcessingInstruction('', '💩'), '💩', 1];
    }
}
