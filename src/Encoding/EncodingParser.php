<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Encoding;

final class EncodingParser
{
    private const WHITESPACE = "\t\n\f\r ";
    private const ASCII_UPPER = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private const ASCII_ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

    private $input;
    private $position;
    private $end;
    private $encoding;

    public function parse(string $input, int $maxLength = 1024): ?string
    {
        $this->input = $input;
        $this->position = 0;
        $this->end = min(strlen($input), $maxLength);
        $this->encoding = null;

        while ($this->position < $this->end) {
            $carryOn = true;
            if ($this->matches('<!--')) {
                $carryOn = $this->handleComment();
            } elseif ($this->matches('<meta')) {
                $carryOn = $this->handleMeta();
            } elseif ($this->matches('</')) {
                $carryOn = $this->handlePossibleEndTag();
            } elseif ($this->matches('<!')) {
                $carryOn = $this->handleMarkup();
            } elseif ($this->matches('<?')) {
                $carryOn = $this->handleMarkup();
            } elseif ($this->matches('<')) {
                $carryOn = $this->handlePossibleStartTag();
            }
            if (!$carryOn) break;
            $this->next();
        }

        return $this->encoding;
    }

    private function handleComment()
    {
        return $this->jumpTo('-->');
    }

    private function handleMarkup()
    {
        return $this->jumpTo('>');
    }

    private function handleMeta()
    {
        if (!$this->isOneOf(self::WHITESPACE)) {
            return true;
        }
        $hasPragma = false;
        $pendingEncoding = null;
        while (true) {
            $attr = $this->getAnAttribute();
            if ($attr === null) {
                return true;
            }
            [$name, $value] = $attr;
            if ($name === 'http-equiv') {
                $hasPragma = $value === 'content-type';
                if ($hasPragma && $pendingEncoding) {
                    $this->encoding = $pendingEncoding;
                    return false;
                }
            } elseif ($name === 'charset') {
                $label = strtolower(trim($value));
                $encoding = EncodingLookup::LABELS[$label] ?? null;
                if ($encoding) {
                    $this->encoding = $encoding;
                    return false;
                }
            } elseif ($name === 'content') {
                $encoding = self::extractFromMetaContentAttribute($value);
                if ($encoding) {
                    if ($hasPragma) {
                        $this->encoding = $encoding;
                        return false;
                    }
                    $pendingEncoding = $encoding;
                }
            }
        }
    }

    private function handlePossibleStartTag()
    {
        return $this->handlePossibleTag(false);
    }

    private function handlePossibleEndTag()
    {
        ++$this->position;
        return $this->handlePossibleTag(true);
    }

    private function handlePossibleTag(bool $isEndTag)
    {
        if (!$this->isOneOf(self::ASCII_ALPHA)) {
            if ($isEndTag) {
                --$this->position;
                $this->handleMarkup();
            }
            return true;
        }
        $this->skipUntil(self::WHITESPACE . "<>");
        if ($this->current() === '<') {
            --$this->position;
        } else {
            do {
                $attr = $this->getAnAttribute();
            } while ($attr !== null);
        }
        return true;
    }

    private function getAnAttribute(): ?array
    {
        $this->skipWhile(self::WHITESPACE . "/");
        $cc = $this->current();
        if ($cc === '>' || $cc === null) {
            return null;
        }
        $name = $value = '';
        while (true) {
            if ($name && $cc === '=') {
                break;
            } elseif ($this->isOneOf(self::WHITESPACE)) {
                $this->skipWhile(self::WHITESPACE);
                break;
            } elseif ($this->isOneOf('/>')) {
                return [$name, ''];
            } elseif ($this->isOneOf(self::ASCII_UPPER)) {
                $name .= strtolower($cc);
            } elseif ($cc === null) {
                return null;
            } else {
                $name .= $cc;
            }
            $cc = $this->next();
        }
        if ($cc !== '=') {
            --$this->position;
            return [$name, ''];
        }
        $this->next();
        $this->skipWhile(self::WHITESPACE);
        $cc = $this->current();
        if ($this->isOneOf('"\'')) {
            $quoteChar = $cc;
            while (true) {
                $cc = $this->next();
                if ($cc === $quoteChar) {
                    $this->next();
                    return [$name, $value];
                } elseif ($this->isOneOf(self::ASCII_UPPER)) {
                    $value .= strtolower($cc);
                } elseif ($cc === null) {
                    return null;
                } else {
                    $value .= $cc;
                }
            }
        } elseif ($cc === '>') {
            return [$name, ''];
        } elseif ($this->isOneOf(self::ASCII_UPPER)) {
            $value .= strtolower($cc);
        } elseif ($cc === null) {
            return null;
        } else {
            $value .= $cc;
        }
        while (true) {
            $cc = $this->next();
            if ($this->isOneOf(self::WHITESPACE . '<>')) {
                return [$name, $value];
            } elseif ($this->isOneOf(self::ASCII_UPPER)) {
                $value .= strtolower($cc);
            } elseif ($cc === null) {
                return null;
            } else {
                $value .= $cc;
            }
        }
    }

    private function current(): ?string
    {
        return $this->input[$this->position] ?? null;
    }

    private function next(): ?string
    {
        return $this->input[++$this->position] ?? null;
    }

    private function matches(string $bytes, bool $caseInsensitive = false): bool
    {
        if (0 === substr_compare($this->input, $bytes, $this->position, strlen($bytes), $caseInsensitive)) {
            $this->position += strlen($bytes);
            return true;
        }
        return false;
    }

    private function jumpTo(string $bytes)
    {
        $pos = strpos($this->input, $bytes, $this->position);
        if ($pos) {
            $this->position = $pos + strlen($bytes);
            return true;
        }
        return false;
    }

    public function isOneOf(string $bytes): bool
    {
        return strspn($this->input, $bytes, $this->position, 1) === 1;
    }

    private function skipUntil(string $bytes)
    {
        $this->position += strcspn($this->input, $bytes, $this->position);
    }

    private function skipWhile(string $bytes)
    {
        $this->position += strspn($this->input, $bytes, $this->position);
    }

    private const META_CHARSET_PATTERN = <<<'REGEXP'
@
   charset \s* = \s*
   (?>
        " (?P<value> [^"]+ ) "
        | ' (?P<value> [^']+ ) '
        | (?P<value> [^\t\n\f\r ;]+ ) 
   ) 
@Jix
REGEXP;

    /**
     * @see https://html.spec.whatwg.org/multipage/urls-and-fetching.html#algorithm-for-extracting-a-character-encoding-from-a-meta-element
     *
     * @param string $input
     * @return string|null
     */
    public static function extractFromMetaContentAttribute(string $input): ?string
    {
        // NOTE: This method has been inlined in self::sniff()
        // Please keep the code in sync if you change the algorithm.
        if (!preg_match(self::META_CHARSET_PATTERN, $input, $matches)) {
            return null;
        }
        $label = strtolower(trim($matches['value']));
        return EncodingLookup::LABELS[$label] ?? null;
    }
}
