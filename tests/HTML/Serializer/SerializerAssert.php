<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Serializer;

use PHPUnit\Framework\Assert;
use Souplette\DOM\Node;
use Souplette\HTML\HTMLSerializer;
use Souplette\XML\XMLSerializer as XmlSerializer;

final class SerializerAssert
{
    public static function assertSerializationEquals(Node $input, string $expected, ?string $xhtml = null)
    {
        $serializer = new HTMLSerializer();
        $result = $serializer->serializeFragment($input);
        Assert::assertSame($expected, $result, 'Using HTML serialization');
        if ($xhtml !== null) {
            $serializer = new XMLSerializer();
            Assert::assertSame($xhtml, $serializer->serialize($input), 'Using XHTML serialization');
        }
    }
}
