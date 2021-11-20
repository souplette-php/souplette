<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

final class AnPlusB extends CssValue
{
    public function __construct(
        public int $a,
        public int $b,
    ) {}

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#serializing-anb
     * @return string
     */
    public function __toString()
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
