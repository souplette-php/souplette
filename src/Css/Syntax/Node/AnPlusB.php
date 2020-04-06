<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Node;

final class AnPlusB extends CssValue
{
    public int $a;
    public int $b;

    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function __toString()
    {
        if ($this->a === 2 && $this->b === 0) {
            return 'even';
        } elseif ($this->a === 2 && $this->b === 1) {
            return 'odd';
        } elseif ($this->a === 0) {
            return (string)$this->b;
        }

        if ($this->a === 1) {
            $a = 'n';
        } elseif ($this->a === -1) {
            $a = '-n';
        } else {
            $a = "{$this->a}n";
        }

        if ($this->b === 0) {
            $b = '';
        } elseif ($this->b > 0) {
            $b = "+{$this->b}";
        } else {
            $b = (string)$this->b;
        }

        return "{$a}{$b}";
    }
}
