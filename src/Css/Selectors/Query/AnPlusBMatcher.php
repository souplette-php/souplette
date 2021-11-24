<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

final class AnPlusBMatcher
{
    public static function indexMatchesAnPlusB(int $index, int $a, int $b): bool
    {
        // servo implementation:
        // Is there a non-negative integer n such that An+B=index?
        //$an = $index - $b;
        //if ($a === 0) return $an === 0;
        //$n = intval($an / $a);
        //return $n >= 0 && $a * $n === $an;

        // chromium implementation:
        if ($a === 0) {
            return $index === $b;
        }
        if ($a > 0) {
            if ($index < $b) return false;
            return ($index - $b) % $a === 0;
        }
        if ($index > $b) return false;
        return ($b - $index) % -($a) === 0;
    }
}
