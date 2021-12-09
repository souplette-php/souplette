<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes\Document;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Souplette;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Document-getElementById.html
 *
 * @todo namespaced id attributes
 * @todo SVG + MathML elements
 */
final class GetElementByIdTest extends TestCase
{
    const DOCUMENT = <<<'HTML'
    <!DOCTYPE html>
    <meta charset=utf-8>
    <title>Document.getElementById</title>
    <link rel="author" title="Tetsuharu OHZEKI" href="mailto:saneyuki.snyk@gmail.com">
    <link rel=help href="https://dom.spec.whatwg.org/#dom-document-getelementbyid">
    <body>
      <div id="log"></div>

      <!-- test 0 -->
      <div id=""></div>

      <!-- test 1 -->
      <div id="test1"></div>

      <!-- test 5 -->
      <div id="test5" data-name="1st">
        <p id="test5" data-name="2nd">P</p>
        <input id="test5" type="submit" value="Submit" data-name="3rd">
      </div>

      <!-- test 15 -->
      <div id="outer">
        <div id="middle">
          <div id="inner"></div>
        </div>
      </div>
    </body>
    HTML;

    private static Document $doc;

    public static function setUpBeforeClass(): void
    {
        self::$doc = Souplette::parseHtml(self::DOCUMENT);
    }

    public function testItMatchesNothingWhenCalledWithEmptyString()
    {
        Assert::assertNull(self::$doc->getElementById(''));
    }

    public function testItWorks()
    {
        $el = self::$doc->getElementById('test1');
        Assert::assertNotNull($el);
        Assert::assertInstanceOf(Element::class, $el);
        Assert::assertSame('div', $el->localName);
    }

    public function testInsertedElement()
    {
        $body = self::$doc->body;
        $test = self::$doc->createElement('div');
        $testId = 'test2';
        $test->setAttribute('id', $testId);
        $body->appendChild($test);

        try {
            Assert::assertSame($test, self::$doc->getElementById($testId));
        } finally {
            $body->removeChild($test);
        }

        Assert::assertNull(self::$doc->getElementById($testId));
    }

    public function testUpdateIdViaSetAttribute()
    {
        $body = self::$doc->body;
        $test = self::$doc->createElement('div');
        $testId = 'test3';
        $test->setAttribute('id', $testId);
        $body->appendChild($test);

        $updatedId = 'test3-updated';
        $test->setAttribute('id', $updatedId);
        Assert::assertSame($test, self::$doc->getElementById($updatedId));
        Assert::assertNull(self::$doc->getElementById($testId));

        $test->removeAttribute('id');
        Assert::assertNull(self::$doc->getElementById($updatedId));

        $body->removeChild($test);
    }

    public function testItReturnsTheFirstElementInTreeOrder()
    {
        $body = self::$doc->body;
        $testId = 'test5';
        $target = self::$doc->getElementById($testId);
        Assert::assertSame(
            '1st',
            $target->getAttribute('data-name'),
            'returns the first element in tree order'
        );

        $element4 = self::$doc->createElement('div');
        $element4->setAttribute('id', $testId);
        $element4->setAttribute('data-name', '4th');
        $body->appendChild($element4);
        // should still be 1st
        Assert::assertSame(
            $target,
            self::$doc->getElementById($testId),
            'returns the first element after adding another element with same id'
        );
        //
        $target->remove();
        Assert::assertSame(
            $element4,
            self::$doc->getElementById($testId),
            'returns the new element after removing first.'
        );
    }

    public function testNonConnectedElement()
    {
        $testId = 'test6';
        $test = self::$doc->createElement('div');
        $test->setAttribute('id', $testId);
        // tests that the internal id cache is not updated if appended node is not connected.
        self::$doc->createElement('div')->appendChild($test);
        Assert::assertNull(self::$doc->getElementById($testId));
    }

    public function testUpdateViaAttributeSetValue()
    {
        $body = self::$doc->body;
        $testId = 'test7';
        $test = self::$doc->createElement('div');
        $test->setAttribute('id', $testId);
        $body->appendChild($test);

        Assert::assertSame($test, self::$doc->getElementById($testId));
        $test->attributes[0]->setValue("{$testId}-updated");
        Assert::assertNull(self::$doc->getElementById($testId));
        Assert::assertSame($test, self::$doc->getElementById("{$testId}-updated"));
    }

    public function testUpdateViaInnerHTML()
    {
        $body = self::$doc->body;
        $testId = 'test8';
        $test = self::$doc->createElement('div');
        $test->setAttribute('id', "{$testId}-fixture");
        $body->appendChild($test);

        $test->innerHTML = sprintf('<div id="%s"></div>', $testId);
        Assert::assertSame($test->firstChild, self::$doc->getElementById($testId));
    }

    public function testRemoveViaInnerHTML()
    {
        $body = self::$doc->body;
        $testId = 'test9';
        $fixture = self::$doc->createElement('div');
        $fixture->setAttribute('id', "{$testId}-fixture");
        $body->appendChild($fixture);

        $test = self::$doc->createElement('div');
        $test->setAttribute('id', $testId);
        $fixture->appendChild($test);
        Assert::assertSame($test, self::$doc->getElementById($testId));

        $fixture->innerHTML = sprintf('<div id="%s"></div>', $testId);
        Assert::assertSame($fixture->firstChild, self::$doc->getElementById($testId));

        $fixture->innerHTML = '';
        Assert::assertNull(self::$doc->getElementById($testId));
    }

    public function testUpdateViaOuterHTML()
    {
        $body = self::$doc->body;
        $testId = 'test10';
        $test = self::$doc->createElement('div');
        $test->setAttribute('id', "{$testId}-fixture");
        $body->appendChild($test);

        $test->outerHTML = sprintf('<div id="%s"></div>', $testId);
        Assert::assertSame($body->lastElementChild, self::$doc->getElementById($testId));
    }

    public function testRemoveViaOuterHTML()
    {
        $body = self::$doc->body;
        $testId = 'test11';
        $test = self::$doc->createElement('div');
        $test->setAttribute('id', $testId);
        $body->appendChild($test);
        Assert::assertSame($test, self::$doc->getElementById($testId));

        $test->outerHTML = '<div></div>';
        Assert::assertNull(self::$doc->getElementById($testId));
    }

    public function testUpdateViaIdProperty()
    {
        $body = self::$doc->body;
        $testId = 'test12';
        $test = self::$doc->createElement('div');
        $test->id = $testId;
        $body->appendChild($test);
        Assert::assertSame($test, self::$doc->getElementById($testId));

        $test->id = "{$testId}-updated";
        Assert::assertSame($test, self::$doc->getElementById("{$testId}-updated"));
        Assert::assertNull(self::$doc->getElementById($testId));

        $test->id = '';
        Assert::assertNull(self::$doc->getElementById("{$testId}-updated"));
    }

    public function testWhenTreeOrderIsDifferentFromInsertionOrder()
    {
        $testId = 'test13';
        $factory = function(int $order) use($testId) {
            $el = self::$doc->createElement('div');
            $el->setAttribute('id', $testId);
            $el->setAttribute('data-order', (string)$order);
            return $el;
        };
        $fixture = self::$doc->createElement('div');
        $fixture->setAttribute('id', "{$testId}-fixture");
        self::$doc->body->appendChild($fixture);

        $el1 = $factory(1);
        $el2 = $factory(2);
        $el3 = $factory(3);
        $el4 = $factory(4);
        // append element: 2 -> 4 -> 3 -> 1
        $fixture->appendChild($el2);
        $fixture->appendChild($el4);
        $fixture->insertBefore($el3, $el4);
        $fixture->insertBefore($el1, $el2);

        Assert::assertSame($el1, self::$doc->getElementById($testId));
        $fixture->removeChild($el1);
        Assert::assertSame($el2, self::$doc->getElementById($testId));
        $fixture->removeChild($el2);
        Assert::assertSame($el3, self::$doc->getElementById($testId));
        $fixture->removeChild($el3);
        Assert::assertSame($el4, self::$doc->getElementById($testId));
        $fixture->removeChild($el4);
    }

    public function testInsertingViaParentNode()
    {
        $testId = 'test14';
        $a = self::$doc->createElement('a');
        $b = self::$doc->createElement('b');
        $a->appendChild($b);
        $b->id = $testId;
        Assert::assertNull(self::$doc->getElementById($testId));
        self::$doc->body->appendChild($a);
        Assert::assertSame($b, self::$doc->getElementById($testId));
    }

    public function testItMustNotReturnNodesNotPresentInDocument()
    {
        $testId = 'test15';
        $outer = self::$doc->getElementById('outer');
        $middle = self::$doc->getElementById('middle');
        $inner = self::$doc->getElementById('inner');
        $outer->removeChild($middle);

        $test = self::$doc->createElement('h1');
        $test->id = 'heading';
        $inner->appendChild($test);
        // the new element is not part of the document since
        // "middle" element was removed previously
        Assert::assertNull(self::$doc->getElementById('heading'));
    }
}
