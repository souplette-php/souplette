<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html\Serializer;

use JoliPotage\Html\Serializer\Serializer;
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
