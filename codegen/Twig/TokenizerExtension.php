<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen\Twig;

use ju1ius\HtmlParser\Codegen\Utils;
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
            new TwigFilter('repr_bytes', [$this, 'reprBytes']),
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

    public function reprBytes(string $bytes)
    {
        $output = [];
        $quoteChar = ctype_print($bytes) ? "'" : '"';
        for ($i = 0; $i < strlen($bytes); $i++) {
            $byte = $bytes[$i];
            if (ctype_cntrl($byte)) {
                $output[] = Utils::escapeAsciiControl($byte);
            } elseif ($byte === '$' && $quoteChar === '"') {
                $output[] = '\\$';
            } elseif ($byte === $quoteChar) {
                $output[] = '\\'.$byte;
            } else {
                $output[] = $byte;
            }
        }

        return sprintf('%1$s%2$s%1$s', $quoteChar, implode('', $output));
    }
}
