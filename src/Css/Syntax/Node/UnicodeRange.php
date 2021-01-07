<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

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

    public function __toString()
    {
        if ($this->start === $this->end) {
            return sprintf('U+%X', $this->start);
        }
        return sprintf('U+%X-%X', $this->start, $this->end);
    }
}
