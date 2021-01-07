<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

final class AnPlusB extends CssValue
{
    public int $a;
    public int $b;

    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#serializing-anb
     * @return string
     */
    public function __toString()
    {
        // 1.
        if ($this->a === 0) {
            return (string)$this->b;
        } elseif ($this->a === 2 && $this->b === 0) {
            return 'even';
        } elseif ($this->a === 2 && $this->b === 1) {
            return 'odd';
        }
        // 2.
        $result = '';
        // 3.
        if ($this->a === 1) {
            $result .= 'n';
        } elseif ($this->a === -1) {
            $result .= '-n';
        } else {
            $result .= "{$this->a}n";
        }
        // 4.
        if ($this->b > 0) {
            $result .= "+{$this->b}";
        } elseif ($this->b < 0) {
            $result .= $this->b;
        }
        // 5.
        return $result;
    }
}
