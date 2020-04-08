<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Exception;

final class UnexpectedValue extends ParseError
{
    public static function expecting(string $actual, string $expected): self
    {
        return new self(sprintf('"%s", expected %s', $actual, $expected));
    }
}
