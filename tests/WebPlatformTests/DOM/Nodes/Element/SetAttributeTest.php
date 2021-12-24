<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM\Nodes\Element;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;

final class SetAttributeTest extends TestCase
{
    public function testSetAttributeIgnoresNamespace(): void
    {
        $doc = new Document();
        $el = $doc->createElement('p');
        $el->setAttributeNS('foo', 'x', 'first');
        $el->setAttributeNS('foo2', 'x', 'second');

        $el->setAttribute('x', 'changed');

        Assert::assertCount(2, $el->getAttributes());
        Assert::assertSame('changed', $el->getAttribute('x'));
        Assert::assertSame('changed', $el->getAttributeNS('foo', 'x'));
        Assert::assertSame('second', $el->getAttributeNS('foo2', 'x'));
    }

    public function testSetAttributeLowercaseInHTMLDocuments(): void
    {
        $doc = new Document();
        $el = $doc->createElement('p');
        $el->setAttribute('FOO', 'bar');

        Assert::assertSame('bar', $el->getAttribute('foo'));
        Assert::assertSame('bar', $el->getAttribute('FOO'));
        Assert::assertSame('bar', $el->getAttributeNS(null, 'foo'));
        Assert::assertSame(null, $el->getAttributeNS(null, 'FOO'));
    }
}
