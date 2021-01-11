<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Translator\Functional;

final class NthTranslatorHelper
{
    public static function translateNth(int $a, int $b, string $type, bool $last = false): string
    {
        $siblings = self::getSiblingExpression($type, $last);
        if ($a === 0) {
            //return "position() = {$b}";
            return sprintf('count(%s) = %s', $siblings, $b - 1);
        }
        if (
            ($a > 0 && $b === 0)
            || ($a >= 1 && $b === $a)
        ) {
            return sprintf(
                '((count(%s) + 1) mod %s) = 0',
                $siblings,
                $a
            );
        }
        if ($a >= 1 && $b < $a) {
            return sprintf(
                '(count(%1$s) = (%3$s - 1)) or (((count(%1$s) - (%3$s - 1)) mod %2$s) = 0)',
                $siblings,
                $a,
                $b,
            );
        }
        if ($a >= 1 && $b > $a) {
            return sprintf(
                '(count(%1$s) = (%3$s - 1)) or (((count(%1$s) > %3$s) and (((count(%1$s) - (%3$s - 1)) mod %2$s) = 0)))',
                $siblings,
                $a,
                $b,
            );
        }
        if ($a > 0 && $b < 0) {
            $y = abs($b);
            if ($y > $a) {
                $y = $y % $a;
            }
            return self::translateNth($a, $a - $y, $type, $last);
        }
        if ($a < 0 && $b > 0) {
            $x = abs($a);
            if ($b <= $x) {
                //return "position() = {$b}";
                return sprintf('count(%s) = %s', $siblings, $b - 1);
            }
            $y = $b % $x;
            $expr = self::translateNth($x, $x - $y, $type, $last);
            return sprintf(
                '(%s) and (count(%s) < %s)',
                $expr,
                $siblings,
                $b,
            );
        }

        throw new \LogicException('Unreachable path.');
    }

    private static function getSiblingExpression(string $type, bool $last = false): string
    {
        return sprintf(
            '%s-sibling::%s',
            $last ? 'following' : 'preceding',
            $type === '*' ? '*' : "*[local-name() = '{$type}']",
        );
    }
}
