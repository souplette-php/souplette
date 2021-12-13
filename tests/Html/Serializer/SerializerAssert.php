<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Serializer;

use PHPUnit\Framework\Assert;
use Souplette\Dom\Node;
use Souplette\Html\HtmlSerializer;
use Souplette\Xml\XmlSerializer as XmlSerializer;

final class SerializerAssert
{
    public static function assertSerializationEquals(Node $input, string $expected, ?string $xhtml = null)
    {
        $serializer = new HtmlSerializer();
        $result = $serializer->serializeFragment($input);
        Assert::assertSame($expected, $result, 'Using HTML serialization');
        if ($xhtml !== null) {
            $serializer = new XmlSerializer();
            Assert::assertSame($xhtml, $serializer->serialize($input), 'Using XHTML serialization');
        }
    }
}
