<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Comment;
use Souplette\Dom\Element;
use Souplette\Dom\Text;

final class ElementTest extends TestCase
{
    public function testGetInnerHtml()
    {
        $doc = DomBuilder::html()->tag('html')
            ->comment(' inner html! ')
            ->tag('p')
                ->text('foo')
            ->getDocument();
        $html = '<!-- inner html! --><p>foo</p>';
        Assert::assertSame($html, $doc->documentElement->innerHTML);
    }

    public function testSetInnerHtml()
    {
        $doc = DomBuilder::html()
            ->tag('body')
                ->tag('remove')->text('this!')
            ->getDocument();
        /** @var Element $body */
        $body = $doc->documentElement;
        $body->innerHTML = '<!-- foo --><p>bar</p>baz';
        //
        $comment = $body->firstChild;
        Assert::assertInstanceOf(Comment::class, $comment);
        Assert::assertSame(' foo ', $comment->data);
        //
        $p = $comment->nextSibling;
        Assert::assertInstanceOf(Element::class, $p);
        Assert::assertSame('bar', $p->textContent);
        //
        $text = $p->nextSibling;
        Assert::assertInstanceOf(Text::class, $text);
        Assert::assertSame('baz', $text->data);
    }

    public function testGetOuterHtml()
    {
        $doc = DomBuilder::html()->tag('html')
            ->comment(' outer html! ')
            ->tag('p')
                ->text('foo')
            ->getDocument();
        $html = '<html><!-- outer html! --><p>foo</p></html>';
        Assert::assertSame($html, $doc->documentElement->outerHTML);
    }

    public function testSetOuterHtml()
    {
        $doc = DomBuilder::html()->tag('html')->tag('body')
            ->tag('div')
            ->getDocument();
        /** @var Element $body */
        $body = $doc->documentElement->lastChild;
        /** @var Element $div */
        $div = $body->firstChild;
        $div->outerHTML = '<article><!-- outer html! --><p>foo</p>bar</article>';
        //
        $article = $body->firstChild;
        Assert::assertInstanceOf(Element::class, $article);
        Assert::assertSame('article', $article->localName);
        //
        $comment = $article->firstChild;
        Assert::assertInstanceOf(Comment::class, $comment);
        Assert::assertSame(' outer html! ', $comment->data);
        //
        $p = $comment->nextSibling;
        Assert::assertInstanceOf(Element::class, $p);
        Assert::assertSame('foo', $p->textContent);
        //
        $text = $p->nextSibling;
        Assert::assertInstanceOf(Text::class, $text);
        Assert::assertSame('bar', $text->data);
    }

    public function testId()
    {
        $doc = DomBuilder::html()->tag('html')->getDocument();
        /** @var Element $html */
        $html = $doc->documentElement;
        Assert::assertSame('', $html->id);
        $html->id = 'foo';
        Assert::assertSame('foo', $html->getAttribute('id'));
        $html->setAttribute('id', 'bar');
        Assert::assertSame('bar', $html->id);
    }

    public function testClassName()
    {
        $doc = DomBuilder::html()->tag('html')->getDocument();
        /** @var Element $html */
        $html = $doc->documentElement;
        Assert::assertSame('', $html->className);
        $html->className = 'foo';
        Assert::assertSame('foo', $html->getAttribute('class'));
        $html->setAttribute('class', 'bar');
        Assert::assertSame('bar', $html->className);
    }

    public function testHasAttributeIsCaseInsensitive()
    {
        $doc = DomBuilder::html()
            ->tag('foo')->attr('bar', 'baz')
            ->getDocument();
        $el = $doc->documentElement;
        Assert::assertTrue($el->hasAttribute('BAR'));
    }

    public function testGetAttributeIsCaseInsensitive()
    {
        $doc = DomBuilder::html()
            ->tag('foo')->attr('bar', 'baz')
            ->getDocument();
        $el = $doc->documentElement;
        Assert::assertSame('baz', $el->getAttribute('BAR'));
    }
}
