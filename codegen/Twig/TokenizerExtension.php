<?php declare(strict_types=1);

namespace Souplette\Codegen\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TokenizerExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('cc_in', [$this, 'currentCharIn']),
        ];
    }

    public function currentCharIn(string ...$chars): string
    {
        $exprs = [];
        foreach ($chars as $char) {
            if (ctype_cntrl($char)) {
                $repr = sprintf('"\x%02X"', ord($char));
            } else {
                $repr = var_export($char, true);
            }
            $exprs[] = sprintf('$cc === %s', $repr);
        }
        return implode(' || ', $exprs);
    }
}
