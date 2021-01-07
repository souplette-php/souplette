<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Exception;

final class UnexpectedValue extends ParseError
{
    public static function expecting(string $actual, string $expected): self
    {
        return new self(sprintf('"%s", expected %s', $actual, $expected));
    }
}
