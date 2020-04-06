<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm;

final class UnicodeRange extends CssValue
{
    private int $start;
    private int $end;

    public function __construct(int $start, int $end)
    {
        if ($end > 0x10FFFF) {
            throw new \OutOfBoundsException("Unicode range end is greater than maximum allowed code point.");
        }
        if ($start > $end) {
            throw new \OutOfRangeException("Unicode range start must be lower than range end.");
        }
        $this->start = $start;
        $this->end = $end;
    }
}
