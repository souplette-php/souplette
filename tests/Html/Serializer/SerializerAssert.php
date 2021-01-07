<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Serializer;

use Souplette\Html\Serializer\Serializer;
use PHPUnit\Framework\Assert;

final class SerializerAssert
{
    public static function assertSerializationEquals(\DOMDocument $input, string $expected)
    {
        $serializer = new Serializer();
        $result = $serializer->serialize($input);
        Assert::assertSame($expected, $result);
    }
}
