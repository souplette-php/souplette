<?php declare(strict_types=1);

namespace Souplette\Exception;

final class UndefinedProperty extends \RuntimeException
{
    public static function forRead(object $object, string $property): self
    {
        $message = sprintf(
            'Undefined property `%s::$%s`',
            get_debug_type($object),
            $property,
        );
        return new self($message);
    }

    public static function forWrite(object $object, string $property): self
    {
        $message = sprintf(
            'Undefined or read-only property `%s::$%s`',
            get_debug_type($object),
            $property,
        );
        return new self($message);
    }
}
