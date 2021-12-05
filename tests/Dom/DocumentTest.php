<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Element;

final class DocumentTest extends TestCase
{
    public function testGetAndSetTitle()
    {
        $titleText = 'This is the title';
        $doc = DomBuilder::create()->tag('html')
            ->tag('head')
                ->tag('title')->text($titleText)
            ->getDocument();
        Assert::assertSame($titleText, $doc->title);
        $doc->title = $titleText = 'I changed the title!';
        Assert::assertSame($titleText, $doc->title);
    }

    public function testGetElementById()
    {
        $doc = DomBuilder::create()->tag('html')
            ->tag('div')->id('foo')->close()
            ->tag('div')->id('bar')->close()
            ->getDocument();

        $foo = $doc->getElementById('foo');
        Assert::assertInstanceOf(Element::class, $foo);

        $bar = $doc->getElementById('bar');
        Assert::assertInstanceOf(Element::class, $bar);
    }

    public function testGetElementsByClassName()
    {
        $doc = DomBuilder::create()->tag('html')
            ->tag('div')->class('foo')->close()
            ->tag('div')->class('bar')->close()
            ->tag('div')->class('bar baz')->close()
            ->getDocument();

        $rs = $doc->getElementsByClassName('foo');
        Assert::assertCount(1, $rs);
        Assert::assertInstanceOf(Element::class, $rs[0]);
        Assert::assertSame($doc->documentElement->firstChild, $rs[0]);

        $rs = $doc->getElementsByClassName('bar baz');
        Assert::assertCount(1, $rs);
        Assert::assertInstanceOf(Element::class, $rs[0]);
        Assert::assertSame($doc->documentElement->lastChild, $rs[0]);
    }
}
