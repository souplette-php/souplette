<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Serializer;

use PHPUnit\Framework\Assert;
use Souplette\Dom\Node;
use Souplette\Html\Serializer;
use Souplette\Xml\Serializer as XmlSerializer;

final class SerializerAssert
{
    public static function assertSerializationEquals(Node $input, string $expected, ?string $xhtml = null)
    {
        $serializer = new Serializer();
        $result = $serializer->serialize($input);
        Assert::assertSame($expected, $result, 'Using HTML serialization');
        if ($xhtml !== null) {
            $serializer = new XmlSerializer();
            Assert::assertSame($xhtml, $serializer->serialize($input, false), 'Using XHTML serialization');
        }
    }
}
