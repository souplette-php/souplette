<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TokenizerExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('cc_in', [$this, 'currentCharIn']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('indent', [$this, 'indent']),
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

    public function indent(string $input, int $level = 0): string
    {
        if (!$level) {
            return $input;
        }
        $indent = str_repeat('    ', $level);
        $lines = explode("\n", $input);
        $output = [];
        foreach ($lines as $line) {
            if (!ltrim($line)) {
                $output[] = '';
            } else {
                $output[] = $indent . $line;
            }
        }

        return implode("\n", $output);
    }
}
