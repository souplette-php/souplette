<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\HtmlElement;

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
        $doc = DomBuilder::create()
            ->tag('div')->attr('id', 'foo')->close()
            ->tag('div')->attr('ID', 'bar')->close()
            ->getDocument();

        $foo = $doc->getElementById('foo');
        Assert::assertInstanceOf(HtmlElement::class, $foo);

        $bar = $doc->getElementById('bar');
        Assert::assertInstanceOf(HtmlElement::class, $bar);
    }

    public function testGetElementsByClassName()
    {
        $doc = DomBuilder::create()
            ->tag('div')->attr('class', 'foo')->close()
            ->tag('div')->attr('class', 'bar')->close()
            ->tag('div')->attr('class', 'bar baz')->close()
            ->getDocument();

        $rs = $doc->getElementsByClassName('foo');
        Assert::assertCount(1, $rs);
        Assert::assertInstanceOf(HtmlElement::class, $rs[0]);
        Assert::assertSame($doc->firstChild, $rs[0]);

        $rs = $doc->getElementsByClassName('bar baz');
        Assert::assertCount(1, $rs);
        Assert::assertInstanceOf(HtmlElement::class, $rs[0]);
        Assert::assertSame($doc->lastChild, $rs[0]);
    }
}
