<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Node\ClassSelector;
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
use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Node\PseudoClassSelector;
use Souplette\Css\Selectors\Node\RelativeSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Node\UniversalSelector;
use Souplette\Css\Syntax\AnPlusBParser;
use Souplette\Css\Syntax\Exception\UnexpectedToken;
use Souplette\Css\Syntax\Tokenizer\TokenTypes;
use Souplette\Css\Syntax\TokenStream\TokenStreamInterface;

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

    private bool $isParsingAttribute = false;

    public function __construct(private TokenStreamInterface $tokenStream)
    {
    }

    public function parseSelectorList(): SelectorList
    {
        // <selector-list> = <complex-selector-list>
        // <complex-selector-list> = <complex-selector>#
        $selectors = [];
        $selectors[] = $this->parseComplexSelector();
        while ($this->tokenStream->current()->type === TokenTypes::COMMA) {
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
        while ($this->tokenStream->current()->type === TokenTypes::COMMA) {
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
                return new ComplexSelector($selector);
            }
            $compound = $this->parseCompoundSelector();
            if (!$compound) {
                return new ComplexSelector($selector);
            }
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
                || $tt === TokenTypes::LBRACK
                || ($tt === TokenTypes::DELIM && $token->value === '.')
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
            return new TypeSelector($tagName, $prefix);
        }
        $prefix = $this->parseNamespacePrefix();
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::DELIM && $token->value === '*') {
            $this->tokenStream->consume();
            if ($prefix === '*') {
                return new UniversalSelector();
            }
            return new TypeSelector('*', $prefix);
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

    private function parseNamespacePrefix(): ?string
    {
        // <ns-prefix> = [ <ident-token> | '*' ]? '|'
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::DELIM && $token->value === '|') {
            $this->tokenStream->consume();
            return null;
        }

        $la = $this->tokenStream->lookahead();
        if ($la->type === TokenTypes::DELIM && $la->value === '|') {
            $la2 = $this->tokenStream->lookahead(2);
            if ($token->type === TokenTypes::DELIM && $token->value === '*') {
                $this->tokenStream->consume(2);
                return '*';
            }
            if ($token->type === TokenTypes::IDENT) {
                $this->tokenStream->consume(2);
                return $token->value;
            }
        }

        return '*';
    }

    private function parseSubclassSelector(): SimpleSelector
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
            throw UnexpectedToken::expecting($nextToken, TokenTypes::IDENT);
        }
        if ($token->type === TokenTypes::LBRACK) {
            return $this->parseAttributeSelector();
        }
        if ($token->type === TokenTypes::COLON) {
            return $this->parsePseudoClassSelector();
        }

        throw UnexpectedToken::expectingOneOf($token, TokenTypes::HASH, TokenTypes::DELIM, TokenTypes::LBRACK, TokenTypes::COLON);
    }

    private function parseAttributeSelector(): ?AttributeSelector
    {
        // '[' <wq-name> ']'
        // | '[' <wq-name> <attr-matcher> [ <string-token> | <ident-token> ] <attr-modifier>? ']'
        $this->tokenStream->eat(TokenTypes::LBRACK);
        $this->tokenStream->skipWhitespace();
        $this->isParsingAttribute = true;

        $qualifiedName = $this->parseQualifiedName();
        if (!$qualifiedName) {
            // TODO: parse error
        }
        [$prefix, $attribute] = $qualifiedName;
        $token = $this->tokenStream->skipWhitespace();
        if ($token->type === TokenTypes::RBRACK) {
            $this->tokenStream->consume();
            return new AttributeSelector($attribute, $prefix);
        }
        $operator = $this->parseAttributeMatcher();
        $this->tokenStream->skipWhitespace();
        $token = $this->tokenStream->expectOneOf(TokenTypes::STRING, TokenTypes::IDENT);
        $value = $token->value;
        $token = $this->tokenStream->consumeAndSkipWhitespace();
        $forceCase = null;
        if ($token->type === TokenTypes::IDENT) {
            if (strcasecmp($token->value,'i') === 0 || strcasecmp($token->value, 's') === 0) {
                $forceCase = $token->value;
                $this->tokenStream->consumeAndSkipWhitespace();
            }
        }
        $this->tokenStream->eat(TokenTypes::RBRACK);
        $this->isParsingAttribute = false;

        return new AttributeSelector($attribute, $prefix, $operator, $value, $forceCase);
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

    private function parsePseudoClassSelector(): PseudoClassSelector
    {
        // <pseudo-class-selector> = ':' <ident-token> | ':' <function-token> <any-value> ')'
        $this->tokenStream->eat(TokenTypes::COLON);
        $token = $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::FUNCTION);
        if ($token->type === TokenTypes::IDENT) {
            $this->tokenStream->consume();
            return new PseudoClassSelector($token->value);
        }
        return $this->parseFunctionalSelector();
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

    private function parseFunctionalSelector(): FunctionalSelector
    {
        $token = $this->tokenStream->expect(TokenTypes::FUNCTION);
        $name = $token->value;
        $this->tokenStream->consumeAndSkipWhitespace();
        switch (strtolower($name)) {
            // logical combinators
            case 'is':
            case 'matches':
                return $this->parseMatchesAny();
            case 'not':
                return $this->parseMatchesNone();
            case 'where':
                return $this->parseWhere();
            case 'has':
                return $this->parseHas();
            // linguistic
            case 'dir':
                return $this->parseDir();
            case 'lang':
                return $this->parseLang();
            // child-indexed
            case 'nth-child':
                return $this->parseNthChild();
            case 'nth-last-child':
                return $this->parseNthChild(true);
            // typed child-indexed
            case 'nth-of-type':
                return $this->parseNthOfType();
            case 'nth-last-of-type':
                return $this->parseNthOfType(true);
            // grid structural
            case 'nth-col':
                return $this->parseNthCol();
            case 'nth-last-col':
                return $this->parseNthCol(true);
            default:
                $args = $this->tokenStream->consumeAnyValue(TokenTypes::RPAREN);
                $this->tokenStream->eat(TokenTypes::RPAREN);
                return new FunctionalSelector($name, $args);
        }
    }

    private function parseMatchesAny(): Is
    {
        $selectors = $this->parseSelectorList();
        return new Is($selectors);
    }

    private function parseMatchesNone(): Not
    {
        $selectors = $this->parseSelectorList();
        return new Not($selectors);
    }

    private function parseWhere(): Where
    {
        $selectors = $this->parseSelectorList();
        return new Where($selectors);
    }

    private function parseHas(): Has
    {
        $selectors = $this->parseRelativeSelectorList();
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
        while ($token->type === TokenTypes::COMMA) {
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
     * @return NthChild|NthLastChild
     * @throws UnexpectedToken
     */
    private function parseNthChild(bool $last = false)
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN, TokenTypes::IDENT]);
        $anPlusB = $parser->parse();
        $selectors = null;
        $token = $this->tokenStream->current();
        if ($token->type === TokenTypes::IDENT && strcasecmp($token->value, 'of') === 0) {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            // TODO: create a new selector parser ?
            $selectors = $this->parseRelativeSelectorList();
            $this->tokenStream->skipWhitespace();
        }
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastChild($anPlusB, $selectors) : new NthChild($anPlusB, $selectors);
    }

    private function parseNthOfType(bool $last = false)
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN]);
        $anPlusB = $parser->parse();
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastOfType($anPlusB) : new NthOfType($anPlusB);
    }

    private function parseNthCol(bool $last = false)
    {
        $parser = new AnPlusBParser($this->tokenStream, [TokenTypes::RPAREN]);
        $anPlusB = $parser->parse();
        $this->tokenStream->eat(TokenTypes::RPAREN);

        return $last ? new NthLastCol($anPlusB) : new NthCol($anPlusB);
    }

    private function consumeToFunctionEnd()
    {
        $this->tokenStream->consumeAndSkipWhitespace();
        $this->tokenStream->eat(TokenTypes::RPAREN);
    }
}
