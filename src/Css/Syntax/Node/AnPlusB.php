<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

final class AnPlusB extends CssValue
{
    public function __construct(
        public int $a,
        public int $b,
    ) {}

    /**
     * Returns whether the provided (1-based) list index matches this An+B.
     * In other words whether there is  a non-negative integer n such that An+B === index.
     */
    public function matchesIndex(int $index): bool
    {
        // servo implementation:
        //$an = $index - $this->b;
        //if ($this->a === 0) return $an === 0;
        //$n = intval($an / $this->a);
        //return $n >= 0 && $this->a * $n === $an;

        // chromium implementation:
        if ($this->a === 0) {
            return $index === $this->b;
        }
        if ($this->a > 0) {
            if ($index < $this->b) return false;
            return ($index - $this->b) % $this->a === 0;
        }
        if ($index > $this->b) return false;
        return ($this->b - $index) % -($this->a) === 0;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#serializing-anb
     */
    public function __toString(): string
    {
        // 1.
        if ($this->a === 0) {
            return (string)$this->b;
        } else if ($this->a === 2 && $this->b === 0) {
            return 'even';
        } else if ($this->a === 2 && $this->b === 1) {
            return 'odd';
        }
        // 2.
        $result = '';
        // 3.
        if ($this->a === 1) {
            $result .= 'n';
        } else if ($this->a === -1) {
            $result .= '-n';
        } else {
            $result .= "{$this->a}n";
        }
        // 4.
        if ($this->b > 0) {
            $result .= "+{$this->b}";
        } else if ($this->b < 0) {
            $result .= $this->b;
        }
        // 5.
        return $result;
    }
}
