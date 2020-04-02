<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html\Dom;

use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\Node\HtmlDocument;
use JoliPotage\Html\Dom\Node\HtmlElement;
use JoliPotage\Tests\Html\DomBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class NonDocumentChildNodeTest extends TestCase
{
    /**
     * @dataProvider previousElementSiblingProvider
     * @param HtmlDocument $doc
     * @param NonDocumentTypeChildNodeInterface $test
     * @param HtmlElement|null $target
     */
    public function testPreviousElementSibling(HtmlDocument $doc, NonDocumentTypeChildNodeInterface $test, ?HtmlElement $target)
    {
        Assert::assertSame($target, $test->previousElementSibling);
    }

    public function previousElementSiblingProvider()
    {
        yield 'works on text nodes' => [
            $doc = DomBuilder::create()
                ->tag('target')->close()
                ->text('foo')
                ->text('test')
                ->getDocument(),
            $doc->lastChild,
            $doc->firstChild,
        ];
        yield 'works on comment nodes' => [
            $doc = DomBuilder::create()
                ->tag('target')->close()
                ->text('foo')
                ->comment('test')
                ->getDocument(),
            $doc->lastChild,
            $doc->firstChild,
        ];
        yield 'works on element nodes' => [
            $doc = DomBuilder::create()
                ->tag('target')->close()
                ->text('foo')
                ->tag('test')
                ->getDocument(),
            $doc->lastChild,
            $doc->firstChild,
        ];
        yield 'returns null when no previous element sibling' => [
            $doc = DomBuilder::create()
                ->text('foo')
                ->comment('bar')
                ->tag('test')->close()
                ->getDocument(),
            $doc->lastChild,
            null,
        ];
    }
    /**
     * @dataProvider nextElementSiblingProvider
     * @param HtmlDocument $doc
     * @param NonDocumentTypeChildNodeInterface $test
     * @param HtmlElement|null $target
     */
    public function testNextElementSibling(HtmlDocument $doc, NonDocumentTypeChildNodeInterface $test, ?HtmlElement $target)
    {
        Assert::assertSame($target, $test->nextElementSibling);
    }

    public function nextElementSiblingProvider()
    {
        yield 'works on text nodes' => [
            $doc = DomBuilder::create()
                ->text('test')
                ->text('foo')
                ->tag('target')
                ->getDocument(),
            $doc->firstChild,
            $doc->lastChild,
        ];
        yield 'works on comment nodes' => [
            $doc = DomBuilder::create()
                ->comment('test')
                ->text('foo')
                ->tag('target')
                ->getDocument(),
            $doc->firstChild,
            $doc->lastChild,
        ];
        yield 'works on element nodes' => [
            $doc = DomBuilder::create()
                ->tag('test')->close()
                ->text('foo')
                ->tag('target')
                ->getDocument(),
            $doc->firstChild,
            $doc->lastChild,
        ];
        yield 'returns null when no previous element sibling' => [
            $doc = DomBuilder::create()
                ->tag('test')->close()
                ->text('foo')
                ->comment('bar')
                ->getDocument(),
            $doc->firstChild,
            null,
        ];
    }
}
