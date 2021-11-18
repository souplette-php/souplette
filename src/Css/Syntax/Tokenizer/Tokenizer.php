<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer;

use Souplette\Css\Syntax\Tokenizer\Token\AtKeyword;
use Souplette\Css\Syntax\Tokenizer\Token\BadString;
use Souplette\Css\Syntax\Tokenizer\Token\BadUrl;
use Souplette\Css\Syntax\Tokenizer\Token\CDC;
use Souplette\Css\Syntax\Tokenizer\Token\CDO;
use Souplette\Css\Syntax\Tokenizer\Token\Delimiter;
use Souplette\Css\Syntax\Tokenizer\Token\Dimension;
use Souplette\Css\Syntax\Tokenizer\Token\EOF;
use Souplette\Css\Syntax\Tokenizer\Token\Functional;
use Souplette\Css\Syntax\Tokenizer\Token\Hash;
use Souplette\Css\Syntax\Tokenizer\Token\Identifier;
use Souplette\Css\Syntax\Tokenizer\Token\Number;
use Souplette\Css\Syntax\Tokenizer\Token\NumericToken;
use Souplette\Css\Syntax\Tokenizer\Token\Percentage;
use Souplette\Css\Syntax\Tokenizer\Token\SingleCharToken;
use Souplette\Css\Syntax\Tokenizer\Token\Str;
use Souplette\Css\Syntax\Tokenizer\Token\Url;
use Souplette\Css\Syntax\Tokenizer\Token\Whitespace;

/**
 * @see https://www.w3.org/TR/css-syntax-3/#tokenization
 */
final class Tokenizer implements \IteratorAggregate
{
    private string $input;
    private int $position = 0;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    public function getIterator()
    {
        $this->position = 0;
        do {
            /** @var Token $token */
            $token = $this->consumeToken();
            yield $token;
        } while($token::TYPE !== TokenType::EOF);
    }

    public function consumeToken(): Token
    {
        $this->consumeComments();
        $pos = $this->position;
        $cc = $this->input[$pos] ?? null;
        if ($cc === ' ' || $cc === "\n" || $cc === "\t") {
            $l = strspn($this->input, " \n\t", $pos);
            $this->position += $l;
            return new Whitespace($pos);;
        }
        if ($cc === '"' || $cc === "'") {
            return $this->consumeStringToken();
        }
        if ($cc === '#') {
            if (preg_match(Patterns::HASH, $this->input, $m, 0, $pos)) {
                $this->position += strlen($m[0]);
                $token = new Hash($m['name'], $pos, $this->wouldStartAnIdentifier($m['name']));
                return $token;
            }
            ++$this->position;
            return new Delimiter('#', $pos);
        }
        if ($cc === '+') {
            if ($this->wouldStartANumber()) {
                return $this->consumeNumericToken();
            }
            ++$this->position;
            return new Delimiter($cc, $pos);
        }
        if ($cc === '-') {
            if ($this->wouldStartANumber()) {
                return $this->consumeNumericToken();
            }
            if (substr_compare($this->input, '-->', $this->position, 3) === 0) {
                $this->position += 3;
                return new CDC($pos);
            }
            if ($this->wouldStartAnIdentifier()) {
                return $this->consumeIdentLikeToken();
            }
            ++$this->position;
            return new Delimiter($cc, $pos);
        }
        if ($cc === '.') {
            if ($this->wouldStartANumber()) {
                return $this->consumeNumericToken();
            }
            ++$this->position;
            return new Delimiter($cc, $pos);
        }
        if ($cc === '<') {
            if (substr_compare($this->input, '<!--', $this->position, 4) === 0) {
                $this->position += 4;
                return new CDO($pos);
            }
            ++$this->position;
            return new Delimiter($cc, $pos);
        }
        if ($cc === '@') {
            ++$this->position;
            if ($this->wouldStartAnIdentifier()) {
                $name = $this->consumeName();
                return new AtKeyword($name, $pos);
            }
            return new Delimiter($cc, $pos);
        }
        if ($cc === '\\') {
            $nc = $this->input[$pos + 1] ?? null;
            if ($nc !== "\n") {
                return $this->consumeIdentLikeToken();
            }
            // TODO: parse error
            ++$this->position;
            return new Delimiter($cc, $pos);
        }
        if (isset(SingleCharToken::CHARS[$cc])) {
            ++$this->position;
            $class = SingleCharToken::CHARS[$cc];
            return new $class($pos);
        }
        if ($cc === null) {
            return new EOF($pos);
        }
        if (ctype_digit($cc)) {
            return $this->consumeNumericToken();
        }
        if (preg_match(Patterns::NAME_START_CODEPOINT, $cc)) {
            return $this->consumeIdentLikeToken();
        }
        // Since the previous rule consumes all non-ascii codepoints, we're safe here.
        ++$this->position;
        return new Delimiter((string)$cc, $pos);
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-comment
     */
    private function consumeComments(): void
    {
        while (preg_match(Patterns::COMMENT, $this->input, $m, 0, $this->position)) {
            $this->position += strlen($m[0]);
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-numeric-token
     * @return Dimension|Percentage|Number
     */
    private function consumeNumericToken(): NumericToken
    {
        $start = $this->position;
        // Consume a number and let number be the result.
        $number = $this->consumeNumber();
        $cc = $this->input[$this->position] ?? null;
        if ($this->wouldStartAnIdentifier()) {
            // If the next 3 input code points would start an identifier, then:
            // 1. Create a <dimension-token> with the same value and type flag as number, and a unit set initially to the empty string.
            // 2. Consume a name. Set the <dimension-token>’s unit to the returned value.
            // 3. Return the <dimension-token>.
            $unit = $this->consumeName();
            $token = new Dimension($number, $unit, $start);
            return $token;
        } elseif ($cc === '%') {
            // Otherwise, if the next input code point is '%', consume it.
            $this->position++;
            // Create a <percentage-token> with the same value as number, and return it.
            return new Percentage($number, $start);
        } else {
            // Otherwise, create a <number-token> with the same value and type flag as number, and return it.
            return new Number($number, $start);
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-ident-like-token
     * @return Functional|Url|BadUrl|Identifier
     */
    private function consumeIdentLikeToken(): Token
    {
        $start = $this->position;
        // Consume a name, and let string be the result.
        $string = $this->consumeName();
        $cc = $this->input[$this->position] ?? null;
        if (strcasecmp($string, 'url') === 0 && $cc === '(') {
            // If string’s value is an ASCII case-insensitive match for "url",
            // and the next input code point is "(", consume it.
            ++$this->position;
            // While the next two input code points are whitespace, consume the next input code point.
            $this->position += strspn($this->input, " \n\t", $this->position);
            // If the next one or two input code points are '"', "'", or whitespace followed by '"' or "'",
            // then create a <function-token> with its value set to string and return it.
            $cc = $this->input[$this->position] ?? null;
            if ($cc === '"' || $cc === "'") {
                return new Functional($string, $start);
            } else {
                // Otherwise, consume a url token, and return it.
                return $this->consumeUrlToken();
            }
        } elseif ($cc === '(') {
            // Otherwise, if the next input code point is "(", consume it.
            ++$this->position;
            // Create a <function-token> with its value set to string and return it.
            return new Functional($string, $start);
        } else {
            // Otherwise, create an <ident-token> with its value set to string and return it.
            return new Identifier($string, $start);
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-string-token
     * @return Str|BadString
     */
    private function consumeStringToken(): Token
    {
        if (preg_match(Patterns::STRING, $this->input, $m, 0, $this->position)) {
            $token = new Str($m['value'], $this->position);
            $this->position += strlen($m[0]);
            return $token;
        }
        preg_match(Patterns::BAD_STRING, $this->input, $m, 0, $this->position);
        // TODO: parse error
        $token = new BadString($m[0], $this->position);
        $this->position += strlen($m[0]);
        return $token;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-a-url-token
     * @return Url|BadUrl
     */
    private function consumeUrlToken(): Token
    {
        // Initially create a <url-token> with its value set to the empty string.
        if (preg_match(Patterns::URL, $this->input, $m, 0, $this->position)) {
            if ($m[0][-1] !== ')') {
                // TODO: parse error EOF in url
            }
            $token = new Url($m['url'], $this->position);
            $this->position += strlen($m[0]);
            return $token;
        }
        preg_match(Patterns::BAD_URL_REMNANTS, $this->input, $m, 0, $this->position);
        // TODO: parse error
        $token = new BadUrl($m[0], $this->position);
        $this->position += strlen($m[0]);
        return $token;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/index.html#consume-a-number
     */
    private function consumeNumber(): ?string
    {
        if (preg_match(Patterns::NUMBER, $this->input, $m, 0, $this->position)) {
            $this->position += strlen($m[0]);
            return $m[0];
        }
        return null;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/index.html#check-if-three-code-points-would-start-a-number
     */
    private function wouldStartANumber(): bool
    {
        return (bool)preg_match(Patterns::NUMBER_START, $this->input, $_, 0, $this->position);
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#check-if-three-code-points-would-start-an-identifier
     */
    private function wouldStartAnIdentifier(?string $value = null): bool
    {
        if ($value !== null) {
            return (bool)preg_match(Patterns::IDENT_START, $value);
        }
        return (bool)preg_match(Patterns::IDENT_START, $this->input, $_, 0, $this->position);
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-a-name
     */
    private function consumeName(): string
    {
        if (preg_match(Patterns::NAME, $this->input, $m, 0, $this->position)) {
            $this->position += strlen($m[0]);
            return $m[0];
        }

        return '';
    }
}
