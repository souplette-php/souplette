<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm;

final class AnPlusB extends CssValue
{
    public int $a;
    public int $b;

    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
