<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser;

use JoliPotage\Css\CssOm\Selector\AttributeSelector;
use JoliPotage\Css\CssOm\Selector\ClassSelector;
use JoliPotage\Css\CssOm\Selector\Combinators;
use JoliPotage\Css\CssOm\Selector\ComplexSelector;
use JoliPotage\Css\CssOm\Selector\CompoundSelector;
use JoliPotage\Css\CssOm\Selector\FunctionalSelector;
use JoliPotage\Css\CssOm\Selector\IdSelector;
use JoliPotage\Css\CssOm\Selector\PseudoClassSelector;
use JoliPotage\Css\CssOm\Selector\Selector;
use JoliPotage\Css\CssOm\Selector\TypeSelector;
use JoliPotage\Css\CssOm\Selector\UniversalSelector;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;
use JoliPotage\Css\Parser\TokenStream\TokenStreamInterface;

/**
 * @see https://drafts.csswg.org/selectors/#grammar
 */
final class SelectorParser
{
    private const SUBCLASS_SELECTOR_START = [
        TokenTypes::HASH => true,
        TokenTypes::COLON => true,
        TokenTypes::DELIM => [
            '.' => true,
            '[' => true,
        ],
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
        '$' => AttributeSelector::OPERATOR_PREFIX_MATCH,
        '*' => AttributeSelector::OPERATOR_SUBSTRING_MATCH,
    ];

    private TokenStreamInterface $tokenStream;

    public function __construct(TokenStreamInterface $tokenStream)
    {
        $this->tokenStream = $tokenStream;
    }

    /**
     * @return array
     */
    public function parseSelectorList()
    {
        // <selector-list> = <complex-selector-list>
        // <complex-selector-list> = <complex-selector>#
        $selectors = [];
        $selectors[] = $this->parseComplexSelector();
        while ($this->tokenStream->current()->type === TokenTypes::COMMA) {
            $this->tokenStream->consume();
            $selectors[] = $this->parseComplexSelector();
        }

        return $selectors;
    }

    private function parseComplexSelector(): Selector
    {
        // <compound-selector> [ <combinator>? <compound-selector> ]*
        $this->tokenStream->skipWhitespace();
        $selector = $this->parseCompoundSelector();
        while (true) {
            $combinator = $this->parseCombinator();
            if (!$combinator) {
                return $selector;
            }
            $combined = $this->parseCompoundSelector();
            if (!$combined) {
                return $selector;
            }
            $selector = new ComplexSelector($selector, $combinator, $combined);
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
            $token->type === TokenTypes::IDENT
            || ($token->type === TokenTypes::DELIM && ($token->value === '*' || $token->value === '|'))
        ) {
            $selectors[] = $this->parseTypeSelector();
        }

        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token->type;
            if (
                $tt === TokenTypes::HASH
                || $tt === TokenTypes::COLON
                || ($tt === TokenTypes::DELIM && ($token->value === '.' || $token->value === '['))
            ) {
                $selectors[] = $this->parseSubclassSelector();
            } else {
                break;
            }
        }

        while (true) {
            $token = $this->tokenStream->current();
            if ($token->type !== TokenTypes::COLON) {
                break;
            }
            $selectors[] = $this->parsePseudoElementSelector();
            $token = $this->tokenStream->current();
            while ($token->type === TokenTypes::COLON) {
                $selectors[] = $this->parsePseudoClassSelector();
            }
        }

        return $selectors ? new CompoundSelector($selectors) : null;
    }

    private function parseTypeSelector(): TypeSelector
    {
        // <type-selector> = <wq-name> | <ns-prefix>? '*'
        if ($qualifiedName = $this->parseQualifiedName()) {
            [$prefix, $tagName] = $qualifiedName;
            return new TypeSelector($prefix, $tagName);
        }
        $prefix = $this->parseNamespacePrefix();
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::DELIM && $token->value === '*') {
            $this->tokenStream->consume();
            if ($prefix === '*') {
                return new UniversalSelector();
            }
            return new TypeSelector($prefix, '*');
        }

        return new UniversalSelector();
    }

    private function parseQualifiedName(): ?array
    {
        // <wq-name> = <ns-prefix>? <ident-token>
        $prefix = $this->parseNamespacePrefix();
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::IDENT) {
            $this->tokenStream->consume();
            return [$prefix, $token->value];
        }

        return null;
    }

    private function parseNamespacePrefix(): string
    {
        // <ns-prefix> = [ <ident-token> | '*' ]? '|'
        $prefix = '*';
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::DELIM && $token->value === '|') {
            $this->tokenStream->consume();
            return $prefix;
        }

        $nextToken = $this->tokenStream->lookahead();
        if ($nextToken->type === TokenTypes::DELIM && $nextToken->value === '|') {
            if ($token->type === TokenTypes::DELIM && $token->value === '*') {
                $this->tokenStream->consume(2);
                return '*';
            }
            if ($token->type === TokenTypes::IDENT) {
                $this->tokenStream->consume(2);
                return $token->value;
            }
        }

        return $prefix;
    }

    private function parseSubclassSelector(): Selector
    {
        // <subclass-selector> = <id-selector> | <class-selector> | <attribute-selector> | <pseudo-class-selector>
        // <id-selector> = <hash-token>
        // <class-selector> = '.' <ident-token>
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::HASH) {
            $this->tokenStream->consume();
            return new IdSelector($token->value);
        }
        if ($token->type === TokenTypes::DELIM && $token->value === '.') {
            $nextToken = $this->tokenStream->lookahead();
            if ($nextToken->type === TokenTypes::IDENT) {
                $this->tokenStream->consume(2);
                return new ClassSelector($nextToken->value);
            }
            throw $this->tokenStream->unexpectedToken($nextToken->type, TokenTypes::IDENT);
        }
        if ($token->type === TokenTypes::LBRACK) {
            return $this->parseAttributeSelector();
        }
        if ($token->type === TokenTypes::COLON) {
            return $this->parsePseudoClassSelector();
        }

        throw $this->tokenStream->unexpectedToken(
            $token->type,
            TokenTypes::HASH, TokenTypes::DELIM, TokenTypes::LBRACK, TokenTypes::COLON
        );
    }

    private function parseAttributeSelector(): ?AttributeSelector
    {
        // '[' <wq-name> ']'
        // | '[' <wq-name> <attr-matcher> [ <string-token> | <ident-token> ] <attr-modifier>? ']'
        $this->tokenStream->eat(TokenTypes::LBRACK);
        $this->tokenStream->skipWhitespace();
        $qualifiedName = $this->parseQualifiedName();
        if (!$qualifiedName) {
            // TODO: parse error
        }
        [$prefix, $attribute] = $qualifiedName;
        $token = $this->tokenStream->skipWhitespace();
        if ($token->type === TokenTypes::RBRACK) {
            $this->tokenStream->consume();
            return new AttributeSelector($prefix, $attribute);
        }
        $operator = $this->parseAttributeMatcher();
        $this->tokenStream->skipWhitespace();
        $token = $this->tokenStream->expectOneOf(TokenTypes::STRING, TokenTypes::IDENT);
        $value = $token->value;
        $this->tokenStream->consumeAndSkipWhitespace();
        $this->tokenStream->eat(TokenTypes::RBRACK);
        $this->tokenStream->consume();
    }

    private function parseAttributeMatcher(): string
    {
        $token = $this->tokenStream->expect(TokenTypes::DELIM);
        if ($token->value === '=') {
            $this->tokenStream->consume();
            return AttributeSelector::OPERATOR_EQUALS;
        }
        $operator = self::ATTRIBUTE_MATCHERS[$token->value] ?? null;
        if ($operator === null) {
            throw $this->unexpectedValue($token->value, '=', ...self::ATTRIBUTE_MATCHERS);
        }
        $this->tokenStream->consume();
        $this->tokenStream->eatValue(TokenTypes::DELIM, '=');

        return $operator;
    }

    private function parsePseudoClassSelector()
    {
        // <pseudo-class-selector> = ':' <ident-token> | ':' <function-token> <any-value> ')'
        $token = $this->tokenStream->eat(TokenTypes::COLON);
        $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::FUNCTION);
        if ($token->type === TokenTypes::IDENT) {
            $this->tokenStream->consume();
            return new PseudoClassSelector($token->value);
        }
        $name = $token->value;
        $this->tokenStream->consume();
        $args = $this->tokenStream->consumeAnyValue(TokenTypes::RPAREN);
        $this->tokenStream->eat(TokenTypes::RPAREN);
        return new FunctionalSelector($name, $args);
    }

    private function parseCombinator(): ?string
    {
        // <combinator> = '>' | '+' | '~' | [ '|' '|' ]
        $seenWhitespace = false;
        $token = $this->tokenStream->current();
        while (true) {
            if ($token->type === TokenTypes::WHITESPACE) {
                $seenWhitespace = true;
                $token = $this->tokenStream->skipWhitespace();
                continue;
            } elseif ($token->type === TokenTypes::DELIM) {
                if (isset(self::SIMPLE_COMBINATORS[$token->value])) {
                    $this->tokenStream->consumeAndSkipWhitespace();
                    return self::SIMPLE_COMBINATORS[$token->value];
                }
                if ($token->value === '|') {
                    $nextToken = $this->tokenStream->lookahead();
                    if ($nextToken->type === TokenTypes::DELIM && $nextToken->value === '|') {
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

    private function parsePseudoElementSelector()
    {
        $this->tokenStream->eat(TokenTypes::COLON);
        return $this->parsePseudoClassSelector();
    }
}
