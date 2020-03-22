<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen\Twig;

use IntlChar;
use ju1ius\HtmlParser\Codegen\Utils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class CodeGeneratorExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('indent', [$this, 'indent']),
            new TwigFilter('repr', [$this, 'repr']),
            new TwigFilter('repr_str', [$this, 'reprString']),
            new TwigFilter('repr_bytes', [$this, 'reprBytes']),
            new TwigFilter('repr_array', [$this, 'reprArray']),
        ];
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

    public function repr($value): string
    {
        if (is_string($value)) {
            return $this->reprString($value);
        } elseif (is_array($value)) {
            return $this->reprArray($value);
        }
        return (string)$value;
    }

    public function reprArray(array $input, bool $multiline = false): string
    {
        $entries = [];
        $isSequential = key($input) === 0 && end($a) === count($a) - 1;
        foreach ($input as $key => $value) {
            if ($isSequential) {
                $entries[] = $this->repr($value);
            } else {
                $entries[] = sprintf('%s => %s', $this->repr($key), $this->repr($value));
            }
        }

        if ($multiline) {
            return sprintf("[\n%s\n]", implode(",\n", $entries));
        }

        return sprintf('[%s]', implode(', ', $entries));
    }

    public function reprString(string $input): string
    {
        $output = '';
        $quoteChar = $this->isUnicodePrintable($input) ? "'" : '"';
        foreach (Utils::iterateCodepoints($input) as $char) {
            $cp = IntlChar::ord($char);
            if ($cp < 128) {
                if (ctype_cntrl($char)) {
                    $output .= Utils::escapeAsciiControl($char);
                } elseif ($char === '$' && $quoteChar === '"') {
                    $output .= "\\$";
                } elseif ($char === $quoteChar) {
                    $output .= "\\{$char}";
                } else {
                    $output .= $char;
                }
            } elseif (!$this->isCodepointPrintable($cp)) {
                $output .= sprintf('\u{%X}', $cp);
            } else {
                $output .= $char;
            }
        }

        return "{$quoteChar}{$output}{$quoteChar}";
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

    private function isUnicodePrintable(string $input): bool
    {
        foreach (Utils::iterateCodepoints($input) as $char) {
            if (!$this->isCodepointPrintable(IntlChar::ord($char))) {
                return false;
            }
        }

        return true;
    }

    private function isCodepointPrintable(int $cp): bool
    {
        if (IntlChar::iscntrl($cp) || !IntlChar::isprint($cp) || !IntlChar::isgraph($cp)) {
            return false;
        }
        return true;
    }
}