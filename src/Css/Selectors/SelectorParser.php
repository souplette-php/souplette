<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use Souplette\Css\Selectors\Node\Combinators;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\Functional\Dir;
use Souplette\Css\Selectors\Node\Functional\Has;
use Souplette\Css\Selectors\Node\Functional\Is;
use Souplette\Css\Selectors\Node\Functional\Lang;
use Souplette\Css\Selectors\Node\Functional\Not;
use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Node\Functional\NthCol;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Functional\NthLastCol;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Node\Functional\Where;
use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\RelativeSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\AttributeSelector;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Syntax\AnPlusBParser;
use Souplette\Css\Syntax\Exception\UnexpectedToken;
use Souplette\Css\Syntax\Exception\UnexpectedValue;
use Souplette\Css\Syntax\Tokenizer\TokenTypes;
use Souplette\Css\Syntax\TokenStream\TokenRange;
use Souplette\Css\Syntax\TokenStream\TokenStreamInterface;

/**
 * @see https://drafts.csswg.org/selectors/#grammar
 */
final class SelectorParser
{
    private const LEGACY_PSEUDO_ELEMENTS = [
        'before' => true,
        'after' => true,
        'first-line' => true,
        'first-letter' => true,
    ];

    private const SIMPLE_COMBINATORS = [
        Combinators::CHILD => Combinators::CHILD,
        Combinators::NEXT_SIBLING => Combinators::NEXT_SIBLING,
        Combinators::SUBSEQUENT_SIBLING => Combinators::SUBSEQUENT_SIBLING,
    ];

    /**
     * @var string[]
     */
    private const ATTRIBUTE_MATCHERS = [
        '~' => AttributeSelector::OPERATOR_INCLUDES,
        '|' => AttributeSelector::OPERATOR_DASH_MATCH,
        '^' => AttributeSelector::OPERATOR_PREFIX_MATCH,
        '$' => AttributeSelector::OPERATOR_SUFFIX_MATCH,
        '*' => AttributeSelector::OPERATOR_SUBSTRING_MATCH,
    ];

    private string $defaultNamespace = '*';

    public function __construct(private TokenStreamInterface $tokenStream)
    {
    }

    public function setDefaultNamespace(string $namespace)
    {
        $this->defaultNamespace = $namespace;
    }

    public function parseSelectorList(): SelectorList
    {
        // <selector-list> = <complex-selector-list>
        // <complex-selector-list> = <complex-selector>#
        $selectors = [];
        $selectors[] = $this->parseComplexSelector();
        while ($this->tokenStream->current()::TYPE === TokenTypes::COMMA) {
            $this->tokenStream->consume();
            $selectors[] = $this->parseComplexSelector();
        }

        return new SelectorList($selectors);
    }

    public function parseRelativeSelectorList(): SelectorList
    {
        // <relative-selector-list> = <relative-selector>#
        $selectors = [];
        $selectors[] = $this->parseRelativeSelector();
        while ($this->tokenStream->current()::TYPE === TokenTypes::COMMA) {
            $this->tokenStream->consume();
            $selectors[] = $this->parseRelativeSelector();
        }

        return new SelectorList($selectors);
    }

    private function parseRelativeSelector(): Selector
    {
        // <relative-selector> = <combinator>? <complex-selector>
        $combinator = $this->parseCombinator();
        $selector = $this->parseComplexSelector();
        if ($combinator) {
            return new RelativeSelector($combinator, $selector);
        }

        return $selector;
    }

    private function parseComplexSelector(): ComplexSelector
    {
        // <compound-selector> [ <combinator>? <compound-selector> ]*
        $this->tokenStream->skipWhitespace();
        $selector = $this->parseCompoundSelector();
        while (true) {
            $combinator = $this->parseCombinator();
            if (!$combinator) {
                return $selector instanceof ComplexSelector ? $selector : new ComplexSelector($selector);
            }
            $compound = $this->parseCompoundSelector();
            if (!$compound) {
                // TODO: ParseError ?
                return $selector instanceof ComplexSelector ? $selector : new ComplexSelector($selector);
            }
            // NOTE: left-associativity is required for the Xpath translator to work
            // if we were to change that, we should refactor both.
            $selector = new ComplexSelector($selector, $combinator, $compound);
        }
        $this->tokenStream->skipWhitespace();

        return $selector;
    }

    private function parseCompoundSelector(): ?CompoundSelector
    {
        // [ <type-selector>? <subclass-selector>* [ <pseudo-element-selector> <pseudo-class-selector>* ]* ]!
        $selectors = [];
        $token = $this->tokenStream->current();
        if (
            $token::TYPE === TokenTypes::IDENT
            || ($token::TYPE === TokenTypes::DELIM && ($token->value === '*' || $token->value === '|'))
        ) {
            $selectors[] = $this->parseTypeSelector();
        }

        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token::TYPE;
            if (
                $tt === TokenTypes::HASH
                || $tt === TokenTypes::LBRACK
                || ($tt === TokenTypes::DELIM && $token->value === '.')
                || ($tt === TokenTypes::COLON && $this->tokenStream->lookahead()::TYPE !== TokenTypes::COLON)
            ) {
                $selectors[] = $this->parseSubclassSelector();
            } else {
                break;
            }
        }

        while (true) {
            $token = $this->tokenStream->current();
            if ($token::TYPE !== TokenTypes::COLON) {
                break;
            }
            $selectors[] = $this->parsePseudoElementSelector();
            $token = $this->tokenStream->current();
            while ($token::TYPE === TokenTypes::COLON) {
                $selectors[] = $this->parsePseudoClassSelector();
                $token = $this->tokenStream->current();
            }
        }

        return $selectors ? new CompoundSelector($selectors) : null;
    }

    private function parseTypeSelector(): TypeSelector
    {
        // <type-selector> = <wq-name> | <ns-prefix>? '*'
        [$namespace, $localName] = $this->parseQualifiedName(true, $this->defaultNamespace);
        return $localName === '*' ? new UniversalSelector($namespace) : new TypeSelector($localName, $namespace);
    }

    private function parseQualifiedName(bool $allowStar = false, ?string $defaultNamespace = null): ?array
    {
        // <wq-name> = <ns-prefix>? <ident-token>
        $token = $this->tokenStream->current();

        // handle the "|" <ident-or-star> case
        if ($token::TYPE === TokenTypes::DELIM && $token->value === '|') {
            $token = $this->tokenStream->consume();
            if (
                $token::TYPE === TokenTypes::IDENT
                || ($allowStar && $token::TYPE === TokenTypes::DELIM && $token->value === '*')
            ) {
                $this->tokenStream->consume();
                return [null, $token->value];
            }
            throw UnexpectedToken::expectingOneOf($token, TokenTypes::IDENT, TokenTypes::DELIM);
        }

        // handle the <ns-prefix> "|" <ident-or-star> case
        $la = $this->tokenStream->lookahead();
        if ($la::TYPE === TokenTypes::DELIM && $la->value === '|') {
            $la2 = $this->tokenStream->lookahead(2);
            if (
                $la2::TYPE === TokenTypes::IDENT
                || ($allowStar && $la2::TYPE === TokenTypes::DELIM && $la2->value === '*')
            ) {
                if (
                    $token::TYPE === TokenTypes::IDENT
                    || ($token::TYPE === TokenTypes::DELIM && $token->value === '*')
                ) {
                    $namespace = $token->value;
                    $localName = $la2->value;
                    $this->tokenStream->consume(3);
                    return [$namespace, $localName];
                }
                throw UnexpectedToken::expectingOneOf($token, TokenTypes::IDENT, TokenTypes::DELIM);
            }
            // no valid local name found, fallback to the no-namespace case
        }

        // handle the <ident-or-star> case
        if (
            $token::TYPE === TokenTypes::IDENT
            || ($allowStar && $token::TYPE === TokenTypes::DELIM && $token->value === '*')
        ) {
            $this->tokenStream->consume();
            return [$defaultNamespace, $token->value];
        }

        throw UnexpectedToken::expectingOneOf($token, TokenTypes::IDENT, TokenTypes::DELIM);
    }

    private function parseSubclassSelector(): SimpleSelector
    {
        // <subclass-selector> = <id-selector> | <class-selector> | <attribute-selector> | <pseudo-class-selector>
        // <id-selector> = <hash-token>
        // <class-selector> = '.' <ident-token>
        $token = $this->tokenStream->current();
        if ($token::TYPE === TokenTypes::HASH) {
            $this->tokenStream->consume();
            return new IdSelector($token->value);
        }
        if ($token::TYPE === TokenTypes::DELIM && $token->value === '.') {
            $nextToken = $this->tokenStream->lookahead();
            if ($nextToken::TYPE === TokenTypes::IDENT) {
                $this->tokenStream->consume(2);
                return new ClassSelector($nextToken->value);
            }
            throw UnexpectedToken::expecting($nextToken, TokenTypes::IDENT);
        }
        if ($token::TYPE === TokenTypes::LBRACK) {
            return $this->parseAttributeSelector();
        }
        if ($token::TYPE === TokenTypes::COLON) {
            return $this->parsePseudoClassSelector();
        }

        throw UnexpectedToken::expectingOneOf($token, TokenTypes::HASH, TokenTypes::DELIM, TokenTypes::LBRACK, TokenTypes::COLON);
    }

    private function parseAttributeSelector(): ?AttributeSelector
    {
        // '[' <wq-name> ']'
        // | '[' <wq-name> <attr-matcher> [ <string-token> | <ident-token> ] <attr-modifier>? ']'
        // '[' <ns-prefix>? <ident-token> [ <attr-matcher> [ <string-token> | <ident-token> ] <attr-modifier>? ]? ']'
        $this->tokenStream->eat(TokenTypes::LBRACK);
        $this->tokenStream->skipWhitespace();

        [$namespace, $localName] = $this->parseQualifiedName(false);

        $token = $this->tokenStream->skipWhitespace();
        if ($token::TYPE === TokenTypes::RBRACK) {
            $this->tokenStream->consume();
            return new AttributeSelector($localName, $namespace);
        }

        $operator = $this->parseAttributeMatcher();
        $this->tokenStream->skipWhitespace();
        $token = $this->tokenStream->expectOneOf(TokenTypes::STRING, TokenTypes::IDENT);
        $value = $token->value;
        $token = $this->tokenStream->consumeAndSkipWhitespace();
        $forceCase = null;
        if ($token::TYPE === TokenTypes::IDENT) {
            if (
                strcasecmp($token->value,AttributeSelector::CASE_FORCE_INSENSITIVE) === 0
                || strcasecmp($token->value, AttributeSelector::CASE_FORCE_SENSITIVE) === 0
            ) {
                $forceCase = $token->value;
                $this->tokenStream->consumeAndSkipWhitespace();
            }
        }
        $this->tokenStream->eat(TokenTypes::RBRACK);

        return new AttributeSelector($localName, $namespace, $operator, $value, $forceCase);
    }

    private function parseAttributeMatcher(): string
    {
        $token = $this->tokenStream->expect(TokenTypes::DELIM);
        if ($token->value === AttributeSelector::OPERATOR_EQUALS) {
            $this->tokenStream->consume();
            return AttributeSelector::OPERATOR_EQUALS;
        }
        $operator = self::ATTRIBUTE_MATCHERS[$token->value] ?? null;
        if (!$operator) {
            throw UnexpectedValue::expectingOneOf($token->value, AttributeSelector::OPERATOR_EQUALS, ...self::ATTRIBUTE_MATCHERS);
        }
        $this->tokenStream->consume();
        $this->tokenStream->eatValue(TokenTypes::DELIM, AttributeSelector::OPERATOR_EQUALS);

        return $operator;
    }

    private function parsePseudoClassSelector(): PseudoClassSelector|PseudoElementSelector|FunctionalSelector
    {
        // <pseudo-class-selector> = ':' <ident-token> | ':' <function-token> <any-value> ')'
        $this->tokenStream->eat(TokenTypes::COLON);
        $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::FUNCTION);
        if ($token::TYPE === TokenTypes::IDENT) {
            $this->tokenStream->consume();
            $pseudoClass = $token->value;
            if (isset(self::LEGACY_PSEUDO_ELEMENTS[$pseudoClass])) {
                return new PseudoElementSelector($pseudoClass);
            }
            return new PseudoClassSelector($pseudoClass);
        }
        return $this->parseFunctionalSelector();
    }

    private function parseCombinator(): ?string
    {
        // <combinator> = '>' | '+' | '~' | [ '|' '|' ]
        $seenWhitespace = false;
        $token = $this->tokenStream->current();
        while (true) {
            if ($token::TYPE === TokenTypes::WHITESPACE) {
                $seenWhitespace = true;
                $token = $this->tokenStream->skipWhitespace();
                continue;
            } elseif ($token::TYPE === TokenTypes::DELIM) {
                if (isset(self::SIMPLE_COMBINATORS[$token->value])) {
                    $this->tokenStream->consumeAndSkipWhitespace();
                    return self::SIMPLE_COMBINATORS[$token->value];
                }
                if ($token->value === '|') {
                    $nextToken = $this->tokenStream->lookahead();
                    if ($nextToken::TYPE === TokenTypes::DELIM && $nextToken->value === '|') {
                        $this->tokenStream->consume(2);
                        $this->tokenStream->skipWhitespace();
                        return Combinators::COLUMN;
                    }
                }
            }
            break;
        }

        return $seenWhitespace ? Combinators::DESCENDANT : null;
    }

    private function parsePseudoElementSelector(): PseudoElementSelector|FunctionalSelector
    {
        $this->tokenStream->eat(TokenTypes::COLON);
        $this->tokenStream->eat(TokenTypes::COLON);
        $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::FUNCTION);
        if ($token::TYPE === TokenTypes::IDENT) {
            $this->tokenStream->consume();
            return new PseudoElementSelector($token->value);
        }
        return $this->parseFunctionalSelector();
    }

    private function parseFunctionalSelector(): FunctionalSelector
    {
        $token = $this->tokenStream->expect(TokenTypes::FUNCTION);
        $name = strtolower($token->value);
        $this->tokenStream->consumeAndSkipWhitespace();
        return match ($name) {
            'is', 'matches' => $this->parseMatchesAny(),
            'not' => $this->parseMatchesNone(),
            'where' => $this->parseWhere(),
            'has' => $this->parseHas(),
            // linguistic
            'dir' => $this->parseDir(),
            'lang' => $this->parseLang(),
            // child-indexed
            'nth-child' => $this->parseNthChild(),
            'nth-last-child' => $this->parseNthChild(true),
            // typed child-indexed
            'nth-of-type' => $this->parseNthOfType(),
            'nth-last-of-type' => $this->parseNthOfType(true),
            // grid structural
            'nth-col' => $this->parseNthCol(),
            'nth-last-col' => $this->parseNthCol(true),
            default => $this->parseUnknownFunctionalSelector($name),
        };
    }

    private function parseUnknownFunctionalSelector(string $name): FunctionalSelector
    {
        $args = $this->tokenStream->consumeAnyValue(TokenTypes::RPAREN);
        $this->tokenStream->eat(TokenTypes::RPAREN);
        return new FunctionalSelector($name, $args);
    }

    /**
     * @see https://www.w3.org/TR/selectors-4/#matches
     */
    private function parseMatchesAny(): Is
    {
        $selectors = $this->parseForgivingSelectorList();
        return new Is($selectors);
    }

    /**
     * @see https://www.w3.org/TR/selectors-4/#negation
     */
    private function parseMatchesNone(): Not
    {
        $selectors = $this->parseSelectorList();
        return new Not($selectors);
    }

    /**
     * @see https://www.w3.org/TR/selectors-4/#zero-matches
     */
    private function parseWhere(): Where
    {
        $selectors = $this->parseForgivingSelectorList();
        return new Where($selectors);
    }

    /**
     * @see https://www.w3.org/TR/selectors-4/#relational
     */
    private function parseHas(): Has
    {
        $selectors = $this->parseForgivingRelativeSelectorList();
        return new Has($selectors);
    }

    /**
     * @see https://drafts.csswg.org/selectors/#the-dir-pseudo
     */
    private function parseDir(): Dir
    {
        $token = $this->tokenStream->expect(TokenTypes::IDENT);
        $this->consumeToFunctionEnd();
        return new Dir($token->value);
    }

    /**
     * @see https://drafts.csswg.org/selectors/#the-lang-pseudo
     */
    private function parseLang(): Lang
    {
        $languages = [];
        $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::STRING);
        $languages[] = $token->value;
        $token = $this->tokenStream->consumeAndSkipWhitespace();
        while ($token::TYPE === TokenTypes::COMMA) {
            $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::STRING);
            $languages[] = $token->value;
            $token = $this->tokenStream->consumeAndSkipWhitespace();
        }
        $this->tokenStream->skipWhitespace();
        $this->tokenStream->eat(TokenTypes::RPAREN);
        return new Lang(...$languages);
    }

    /**
     * @see https://drafts.csswg.org/selectors/#the-nth-child-pseudo
     * @param bool $last
     * @return NthLastChild|NthChild
     * @throws UnexpectedToken
     */
    private function parseNthChild(bool $last = false): NthLastChild|NthChild
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN, TokenTypes::IDENT]);
        $anPlusB = $parser->parse();
        $selectors = null;
        $token = $this->tokenStream->current();
        if ($token::TYPE === TokenTypes::IDENT && strcasecmp($token->value, 'of') === 0) {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            // TODO: create a new selector parser ?
            $selectors = $this->parseRelativeSelectorList();
            $this->tokenStream->skipWhitespace();
        }
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastChild($anPlusB, $selectors) : new NthChild($anPlusB, $selectors);
    }

    private function parseNthOfType(bool $last = false): NthLastOfType|NthOfType
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN]);
        $anPlusB = $parser->parse();
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastOfType($anPlusB) : new NthOfType($anPlusB);
    }

    private function parseNthCol(bool $last = false): NthLastCol|NthCol
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN]);
        $anPlusB = $parser->parse();
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastCol($anPlusB) : new NthCol($anPlusB);
    }

    /**
     * @see https://drafts.csswg.org/selectors-4/#typedef-forgiving-selector-list
     * @return SelectorList
     */
    private function parseForgivingSelectorList(): SelectorList
    {
        $tokenStream = $this->tokenStream;
        $block = $this->consumeNestedBlock();

        $selectors = [];
        while (true) {
            $token = $block->current();
            if ($token::TYPE === TokenTypes::EOF) break;
            try {
                $this->tokenStream = $this->consumeNestedArgument($block);
                $selectors[] = $this->parseComplexSelector();
            } catch (\Throwable) {
                // we forgive
                continue;
            }
        }

        $this->tokenStream = $tokenStream;
        return new SelectorList($selectors);
    }

    /**
     * @see https://drafts.csswg.org/selectors-4/#typedef-forgiving-relative-selector-list
     * @return SelectorList
     */
    private function parseForgivingRelativeSelectorList(): SelectorList
    {
        $tokenStream = $this->tokenStream;
        $block = $this->consumeNestedBlock();

        $selectors = [];
        while (true) {
            $token = $block->current();
            if ($token::TYPE === TokenTypes::EOF) break;
            try {
                $this->tokenStream = $this->consumeNestedArgument($block);
                $selectors[] = $this->parseRelativeSelector();
            } catch (\Throwable) {
                // we forgive
                continue;
            }
        }

        $this->tokenStream = $tokenStream;
        return new SelectorList($selectors);
    }

    private function consumeToFunctionEnd()
    {
        $this->tokenStream->consumeAndSkipWhitespace();
        $this->tokenStream->eat(TokenTypes::RPAREN);
    }

    private function consumeNestedBlock(): TokenRange
    {
        $tokens = $this->tokenStream->consumeAnyValue(TokenTypes::RPAREN);
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return new TokenRange($tokens);
    }

    private function consumeNestedArgument(TokenRange $range): TokenRange
    {
        $tokens = $range->consumeAnyValue(TokenTypes::COMMA);
        if ($range->current()::TYPE === TokenTypes::COMMA) {
            $range->consume();
        }
        return new TokenRange($tokens);
    }
}
