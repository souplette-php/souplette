<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer;

/**
 * @see https://www.w3.org/TR/css-syntax-3/index.html#tokenizer-definitions
 *
 * digit: [0-9]
 * hex-digit: [A-Fa-f0-9]
 * non-ascii: [^\x00-\x7F]
 * letter: [A-Za-z]
 * name-start: letter | non-ascii | "_"
 * name: name-start | digit | "-"
 * non-printable: [\x00-\x08\x0B\x0E-\x1F\x7F]
 * newline: [\n]
 * whitespace: [ \n\t]
 * valid-escape: "\" [^\n]
 * escaped-codepoint: "\" ( hex-digit{1,6} whitespace? | EOF | anything )
 */
final class Patterns
{
    const NAME_START_CODEPOINT = <<<'REGEXP'
/\G
    [A-Za-z_]           # ascii letter or "_"
    | [^\x00-\x7F]      # or non-ascii
/x
REGEXP;

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#check-if-three-code-points-would-start-an-identifier
     */
    const IDENT_START = <<<'REGEXP'
/\G
   - (?:                                    # "-" followed by
        -                                   # "-"
        | (?: [A-Za-z_] | [^\x00-\x7F] )    # or name-start 
        | \\\\[^n]                          # or a valid escape
   )
   | (?: [A-Za-z_] | [^\x00-\x7F] )         # or a name-start
   | \\\\ [^n]                              # or a valid escape
/x
REGEXP;

    /**
     * @see https://www.w3.org/TR/css-syntax-3/index.html#check-if-three-code-points-would-start-a-number
     */
    const NUMBER_START = <<<'REGEXP'
/\G
    [+-]? \.? \d
/x
REGEXP;


    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-comment
     */
    const COMMENT = <<<'REGEXP'
~\G
    /\*                     # "/*" followed by
    (?: (?! \*/ ) . )*      # anything that's not "*/"
    (?: \*/ | \z)           # "*/" or EOF
~xs
REGEXP;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/index.html#consume-a-number
     */
    const NUMBER = <<<'REGEXP'
/\G
    [+-]?
    (?:
        \. \d+
        | \d+ \. \d+
        | \d+
    )
    (?: e [+-] \d+ )?
/xi
REGEXP;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-a-name
     */
    const NAME = <<<'REGEXP'
/\G
    (?:
        [a-z0-9_-] | [^\x00-\x7F]                       # a name codepoint
        | \\\\ (?: [a-f0-9]{1,6} \s? | \z | [^\n])      # or an escaped codepoint
    )+
/xi
REGEXP;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-the-remnants-of-a-bad-url
     */
    const BAD_URL_REMNANTS = <<<'REGEXP'
/\G
   (?:
        \\\\ (?: [a-f0-9]{1,6} \s? | \z | [^\n])        # an escaped codepoint
        | [^)]                                          # or anythins but ")"
   )+
   (?: \) | \z )                                        # ending with ")" or EOF
/xi
REGEXP;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-a-url-token
     */
    const URL = <<<'REGEXP'
/\G
    [ \n\t]*                                            # optional whitespace
    (?<url> (?:
        \\\\ (?: [a-f0-9]{1,6} \s? | \z | [^\n])        # an escaped codepoint
        | [^\x00-\x08\x0B\x0E-\x1F\x7F"')]              # or aything except a non-printable codepoint '"', "'" or ")"
    )+ )
    [ \n\t]*                                            # ending with optional whitespace
    (?: \) | \z )                                       # and either ")" or EOF
/xi
REGEXP;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-a-url-token
     */
    const STRING = <<<'REGEXP'
/\G
    (?<quote>["'])                                      # the delimiter
    (?<value> (?:
        \\\\ (?: [a-f0-9]{1,6} \s? | \z | .)            # an escaped codepoint (including escaped newline)
        | (?! \g<quote> ) [^\n]                         # anything that's not a newline or the delimiter
    )* )
    (?: \g<quote> | \z )                                # ending with the delimiter or EOF
/xi
REGEXP;

    const BAD_STRING = <<<'REGEXP'
/\G
    (["'])
    (?: (?! \1 ) [^\n] )*
/xi
REGEXP;

    const HASH = <<<'REGEXP'
/\G
    \#
    (?<name> (?:
        [a-z0-9_-] | [^\x00-\x7F]                       # a name codepoint
        | \\\\ (?: [a-f0-9]{1,6} \s? | \z | [^\n])      # or an escaped codepoint
    )+ )
/xi
REGEXP;


}
