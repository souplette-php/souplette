<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html\Dom;

use JoliPotage\Html\Dom\Node\HtmlComment;
use JoliPotage\Html\Dom\Node\HtmlElement;
use JoliPotage\Html\Dom\Node\HtmlText;
use JoliPotage\Tests\Html\DomBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class HtmlElementTest extends TestCase
{
    public function testGetInnerHtml()
    {
        $doc = DomBuilder::create()->tag('html')
            ->comment(' inner html! ')
            ->tag('p')
                ->text('foo')
            ->getDocument();
        $html = '<!-- inner html! --><p>foo</p>';
        Assert::assertSame($html, $doc->documentElement->innerHTML);
    }

    public function testSetInnerHtml()
    {
        $doc = DomBuilder::create()->tag('html')->tag('body')->getDocument();
        /** @var HtmlElement $body */
        $body = $doc->documentElement->lastChild;
        $body->innerHTML = '<!-- foo --><p>bar</p>baz';
        //
        $comment = $body->firstChild;
        Assert::assertInstanceOf(HtmlComment::class, $comment);
        Assert::assertSame(' foo ', $comment->data);
        //
        $p = $comment->nextSibling;
        Assert::assertInstanceOf(HtmlElement::class, $p);
        Assert::assertSame('bar', $p->nodeValue);
        //
        $text = $p->nextSibling;
        Assert::assertInstanceOf(HtmlText::class, $text);
        Assert::assertSame('baz', $text->data);
    }

    public function testGetOuterHtml()
    {
        $doc = DomBuilder::create()->tag('html')
            ->comment(' outer html! ')
            ->tag('p')
                ->text('foo')
            ->getDocument();
        $html = '<html><!-- outer html! --><p>foo</p></html>';
        Assert::assertSame($html, $doc->documentElement->outerHTML);
    }

    public function testSetOuterHtml()
    {
        $doc = DomBuilder::create()->tag('html')->tag('body')
            ->tag('div')
            ->getDocument();
        $body = $doc->documentElement->lastChild;
        /** @var HtmlElement $div */
        $div = $body->firstChild;
        $div->outerHTML = '<article><!-- outer html! --><p>foo</p>bar</article>';
        //
        /** @var HtmlElement $div */
        $article = $body->firstChild;
        Assert::assertInstanceOf(HtmlElement::class, $article);
        Assert::assertSame('article', $article->tagName);
        //
        $comment = $article->firstChild;
        Assert::assertInstanceOf(HtmlComment::class, $comment);
        Assert::assertSame(' outer html! ', $comment->data);
        //
        $p = $comment->nextSibling;
        Assert::assertInstanceOf(HtmlElement::class, $p);
        Assert::assertSame('foo', $p->nodeValue);
        //
        $text = $p->nextSibling;
        Assert::assertInstanceOf(HtmlText::class, $text);
        Assert::assertSame('bar', $text->data);
    }

    public function testId()
    {
        $doc = DomBuilder::create()->tag('html')->getDocument();
        /** @var HtmlElement $html */
        $html = $doc->documentElement;
        Assert::assertSame('', $html->id);
        $html->id = 'foo';
        Assert::assertSame('foo', $html->getAttribute('id'));
        $html->setAttribute('id', 'bar');
        Assert::assertSame('bar', $html->id);
    }

    public function testClassName()
    {
        $doc = DomBuilder::create()->tag('html')->getDocument();
        /** @var HtmlElement $html */
        $html = $doc->documentElement;
        Assert::assertSame('', $html->className);
        $html->className = 'foo';
        Assert::assertSame('foo', $html->getAttribute('class'));
        $html->setAttribute('class', 'bar');
        Assert::assertSame('bar', $html->className);
    }
}
