<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Implementation;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Node-isEqualNode.html
 */
final class NodeIsEqualNodeTest extends TestCase
{
    /**
     * @dataProvider documentTypeProvider
     */
    public function testDocumentType(DocumentType $doctype, DocumentType $other, bool $expected)
    {
        Assert::assertSame($expected, $doctype->isEqualNode($other));
    }

    public function documentTypeProvider(): iterable
    {
        $impl = new Implementation();
        $doctype1 = $impl->createDocumentType('qname', 'pubId', 'sysId');
        $doctype2 = $impl->createDocumentType('qname', 'pubId', 'sysId');
        $doctype3 = $impl->createDocumentType('qname2', 'pubId', 'sysId');
        $doctype4 = $impl->createDocumentType('qname', 'pubId2', 'sysId');
        $doctype5 = $impl->createDocumentType('qname', 'pubId', 'sysId3');

        yield 'self-comparison' => [$doctype1, $doctype1, true];
        yield 'same properties' => [$doctype1, $doctype2, true];
        yield 'different name' => [$doctype1, $doctype3, false];
        yield 'different publicId' => [$doctype1, $doctype4, false];
        yield 'different systemId' => [$doctype1, $doctype5, false];
    }
}
