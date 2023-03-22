<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax;

use Souplette\CSS\Syntax\Node\CSSAtRule;
use Souplette\CSS\Syntax\Node\CSSDeclaration;
use Souplette\CSS\Syntax\Node\CSSFunction;
use Souplette\CSS\Syntax\Node\CSSQualifiedRule;
use Souplette\CSS\Syntax\Node\CSSRule;
use Souplette\CSS\Syntax\Node\CSSSimpleBlock;
use Souplette\CSS\Syntax\Node\CSSStylesheet;
use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\Tokenizer;
use Souplette\CSS\Syntax\Tokenizer\TokenType;
use Souplette\CSS\Syntax\TokenStream\TokenRange;
use Souplette\CSS\Syntax\TokenStream\TokenStream;
use Souplette\CSS\Syntax\TokenStream\TokenStreamInterface;

final class Parser
{
    private TokenStreamInterface $tokenStream;
    private bool $topLevel = true;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenStream = new TokenStream($tokenizer, 3);
    }

    public function parseStylesheet(): CSSStylesheet
    {
        // 1. Create a new stylesheet.
        $stylesheet = new CSSStylesheet();
        // 2. Consume a list of rules from the stream of tokens, with the top-level flag set.
        $this->topLevel = true;
        // Let the return value be rules.
        $rules = $this->consumeRuleList();
        // 3. Assign rules to the stylesheet’s value.
        $stylesheet->rules = $rules;
        // 4. Return the stylesheet.
        return $stylesheet;
    }

    public function parseRuleList(): array
    {
        // 1. Consume a list of rules from the stream of tokens, with the top-level flag unset.
        $this->topLevel = false;
        // 2. Return the returned list.
        return $this->consumeRuleList();
    }

    public function parseRule(): ?CSSRule
    {
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is an <EOF-token>, return a syntax error.
        if ($token::TYPE === TokenType::EOF) {
            // TODO: syntax error;
            return null;
        }
        $rule = null;
        // Otherwise, if the next input token is an <at-keyword-token>,
        // consume an at-rule, and let rule be the return value.
        if ($token::TYPE === TokenType::AT_KEYWORD) {
            $rule = $this->consumeAtRule();
        } else {
            // Otherwise, consume a qualified rule and let rule be the return value.
            // If nothing was returned, return a syntax error.
            $rule = $this->consumeQualifiedRule();
            if (!$rule) {
                // TODO: syntax error
                return null;
            }
        }
        // 3. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 4. If the next input token is an <EOF-token>, return rule.
        if ($token::TYPE === TokenType::EOF) {
            return $rule;
        }
        // Otherwise, return a syntax error.
        // TODO: syntax error
        return null;
    }

    public function parseDeclaration(): ?CSSDeclaration
    {
        // Note: Unlike "Parse a list of declarations", this parses only a declaration and not an at-rule.
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is not an <ident-token>, return a syntax error.
        if ($token::TYPE !== TokenType::IDENT) {
            // TODO: syntax error
            return null;
        }
        // 3. Consume a declaration. If anything was returned, return it.
        if ($declaration = $this->consumeDeclaration()) {
            return $declaration;
        }
        // Otherwise, return a syntax error.
        // TODO: syntax error
        return null;
    }

    public function parseDeclarationList(): array
    {
        // Note: Despite the name, this actually parses a mixed list of declarations and at-rules,
        // as CSS 2.1 does for @page.
        // Unexpected at-rules (which could be all of them, in a given context) are invalid
        // and should be ignored by the consumer.
        // 1. Consume a list of declarations.
        // 2. Return the returned list.
        return $this->consumeDeclarationList();
    }

    public function parseComponentValue(): ?SyntaxNode
    {
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is an <EOF-token>, return a syntax error.
        if ($token::TYPE === TokenType::EOF) {
            // TODO: syntax error;
            return null;
        }
        // 3. Consume a component value and let value be the return value.
        $value = $this->consumeComponentValue();
        // 4. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 5. If the next input token is an <EOF-token>, return value. Otherwise, return a syntax error.
        if ($token::TYPE === TokenType::EOF) {
            return $value;
        }
        // TODO: syntax error
        return null;
    }

    public function parseComponentValueList(): array
    {
        // 1. Repeatedly consume a component value until an <EOF-token> is returned,
        //    appending the returned values (except the final <EOF-token>) into a list.
        $values = [];
        do {
            if ($value = $this->consumeComponentValue()) {
                $values[] = $value;
            }
            $token = $this->tokenStream->current();
        } while ($token::TYPE !== TokenType::EOF);
        // Return the list.
        return $values;
    }

    public function parseCommaSeparatedComponentValueList(): array
    {
        // 1. Let list of csv be an initially empty list of component value lists.
        $list = [];
        // 2. Repeatedly consume a component value until an <EOF-token> or <comma-token> is returned,
        //    appending the returned values (except the final <EOF-token> or <comma-token>) into a list.
        //    Append the list to list of csv.
        //    If it was a <comma-token> that was returned, repeat this step.
        while (true) {
            $values = [];
            while (true) {
                $value = $this->consumeComponentValue();
                if ($value instanceof Token\EOF) {
                    array_push($list, ...$values);
                    return $list;
                }
                if ($value instanceof Token\Comma) {
                    array_push($list, ...$values);
                    break;
                }
                $values[] = $value;
            }
        }
        // 3. Return list of csv.
        return $list;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-list-of-rules
     */
    private function consumeRuleList(): array
    {
        // Create an initially empty list of rules.
        $rules = [];
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token::TYPE;
            if ($tt === TokenType::WHITESPACE) {
                // Do nothing.
                $this->tokenStream->consume();
                continue;
            }
            if ($tt === TokenType::EOF) {
                // Return the list of rules.
                return $rules;
            }
            if ($tt === TokenType::CDO || $tt === TokenType::CDC) {
                if ($this->topLevel) {
                    // If the top-level flag is set, do nothing.
                    $this->tokenStream->consume();
                    continue;
                }
                // Otherwise, reconsume the current input token. Consume a qualified rule.
                // If anything is returned, append it to the list of rules.
                if ($rule = $this->consumeQualifiedRule()) {
                    $rules[] = $rule;
                }
            } else if ($tt === TokenType::AT_KEYWORD) {
                // Reconsume the current input token.
                // Consume an at-rule, and append the returned value to the list of rules.
                $rules[] = $this->consumeAtRule();
            } else {
                // Reconsume the current input token.
                // Consume a qualified rule. If anything is returned, append it to the list of rules.
                if ($rule = $this->consumeQualifiedRule()) {
                    $rules[] = $rule;
                }
            }
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-at-rule
     */
    private function consumeAtRule(): CSSAtRule
    {
        // Consume the next input token.
        $token = $this->tokenStream->current();
        // Create a new at-rule with its name set to the value of the current input token,
        // its prelude initially set to an empty list,
        // and its value initially set to nothing.
        $rule = new CSSAtRule($token->value);
        // Repeatedly consume the next input token:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token::TYPE;
            if ($tt === TokenType::SEMICOLON) {
                // Return the at-rule.
                $this->tokenStream->consume();
                return $rule;
            }
            if ($tt === TokenType::EOF) {
                // TODO: This is a parse error.
                // Return the at-rule.
                return $rule;
            }
            if ($tt === TokenType::LCURLY) {
                // Consume a simple block and assign it to the at-rule’s block. Return the at-rule.
                $rule->body = $this->consumeSimpleBlock();
                return $rule;
            }
            if (false /* simple block with an associated token of <{-token> */) {
                // Assign the block to the at-rule’s block. Return the at-rule.
                return $rule;
            }
            // Reconsume the current input token.
            // Consume a component value.
            $value = $this->consumeComponentValue();
            // Append the returned value to the at-rule’s prelude.
            $rule->prelude[] = $value;
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-qualified-rule
     */
    private function consumeQualifiedRule(): ?CSSQualifiedRule
    {
        // Create a new qualified rule with its prelude initially set to an empty list,
        // and its value initially set to nothing.
        $rule = new CSSQualifiedRule();
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token::TYPE;
            if ($tt === TokenType::EOF) {
                // TODO: This is a parse error.
                // Return nothing.
                return null;
            }
            if ($tt === TokenType::LCURLY) {
                // Consume a simple block and assign it to the qualified rule’s block.
                // Return the qualified rule.
                $rule->body = $this->consumeSimpleBlock();
                return $rule;
            }
            if (false /* simple block with an associated token of <{-token> */) {
                // Assign the block to the qualified rule’s block.
                // Return the qualified rule.
                return $rule;
            }
            // Reconsume the current input token.
            // Consume a component value.
            $value = $this->consumeComponentValue();
            // Append the returned value to the qualified rule’s prelude.
            $rule->prelude[] = $value;
        }

        return null;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-list-of-declarations
     * @return CSSDeclaration[]
     */
    private function consumeDeclarationList(): array
    {
        // Create an initially empty list of declarations.
        $declarations = [];
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token::TYPE;
            if ($tt === TokenType::WHITESPACE || $tt === TokenType::SEMICOLON) {
                // Do nothing.
                $this->tokenStream->consume();
                continue;
            }
            if ($tt === TokenType::EOF) {
                // Return the list of declarations.
                return $declarations;
            }
            if ($tt === TokenType::AT_KEYWORD) {
                // Reconsume the current input token.
                // Consume an at-rule.
                // Append the returned rule to the list of declarations.
                $declarations[] = $this->consumeAtRule();
            } else if ($tt === TokenType::IDENT) {
                // Initialize a temporary list initially filled with the current input token.
                $tmp = [$token];
                // As long as the next input token is anything other than a <semicolon-token> or <EOF-token>,
                // consume a component value and append it to the temporary list.
                while (true) {
                    $next = $this->tokenStream->lookahead();
                    if ($next::TYPE === TokenType::SEMICOLON || $next::TYPE === TokenType::EOF) {
                        $this->tokenStream->consume();
                        break;
                    }
                    $tmp[] = $this->tokenStream->consume();
                }
                // Consume a declaration from the temporary list.
                $stream = $this->tokenStream;
                $this->tokenStream = new TokenRange($tmp);
                $decl = $this->consumeDeclaration();
                $this->tokenStream = $stream;
                // If anything was returned, append it to the list of declarations.
                if ($decl) $declarations[] = $decl;
            } else {
                // This is a parse error.
                // Reconsume the current input token.
                // As long as the next input token is anything other than a <semicolon-token> or <EOF-token>,
                // consume a component value and throw away the returned value.
                while (true) {
                    $next = $this->tokenStream->lookahead();
                    if ($next::TYPE === TokenType::SEMICOLON || $next::TYPE === TokenType::EOF) {
                        break;
                    }
                    $this->consumeComponentValue();
                }
            }
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-declaration
     */
    private function consumeDeclaration(): ?CSSDeclaration
    {
        // Note: This algorithm assumes that the next input token has already been checked to be an <ident-token>.
        // Consume the next input token.
        $token = $this->tokenStream->current();
        $this->tokenStream->consume();
        // Create a new declaration with its name set to the value of the current input token
        // and its value initially set to the empty list.
        $name = $token->value;
        $body = [];
        $important = false;
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is anything other than a <colon-token>, this is a parse error. Return nothing.
        //    Otherwise, consume the next input token.
        if ($token::TYPE !== TokenType::COLON) {
            // TODO: parse error
            return null;
        }
        // 3. While the next input token is a <whitespace-token>, consume the next input token.
        $this->tokenStream->consume();
        $token = $this->tokenStream->skipWhitespace();
        // 4. As long as the next input token is anything other than an <EOF-token>,
        //    consume a component value and append it to the declaration’s value.
        while ($token && $token::TYPE !== TokenType::EOF) {
            $value = $this->consumeComponentValue();
            $body[] = $value;
            $token = $this->tokenStream->current();
        }
        // 5. If the last two non-<whitespace-token>s in the declaration’s value
        //    are a <delim-token> with the value "!" followed by an <ident-token> with a value
        //    that is an ASCII case-insensitive match for "important",
        //    remove them from the declaration’s value and set the declaration’s important flag to true.
        // 6. While the last token in the declaration’s value is a <whitespace-token>, remove that token.
        while (true) {
            $lastKey = array_key_last($body);
            $last = $body[$lastKey] ?? null;
            if ($last instanceof Token\Whitespace) {
                array_pop($body);
            } else if ($last instanceof Token\Identifier && strcasecmp($last->value, 'important') === 0) {
                $bang = $body[$lastKey - 1] ?? null;
                if ($bang instanceof Token\Delimiter && $bang->value === '!') {
                    array_pop($body);
                    array_pop($body);
                    $important = true;
                }
            } else {
                break;
            }
        }
        // 7. Return the declaration.
        return new CSSDeclaration($name, $body, $important);
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-component-value
     */
    private function consumeComponentValue(): CSSFunction|CSSSimpleBlock|Token
    {
        // Consume the next input token.
        $token = $this->tokenStream->current();
        $tt = $token::TYPE;
        // If the current input token is a <{-token>, <[-token>, or <(-token>, consume a simple block and return it.
        if ($tt === TokenType::LCURLY || $tt === TokenType::LBRACK || $tt === TokenType::LPAREN) {
            return $this->consumeSimpleBlock();
        }
        // Otherwise, if the current input token is a <function-token>, consume a function and return it.
        if ($tt === TokenType::FUNCTION) {
            return $this->consumeFunction();
        }
        // Otherwise, return the current input token.
        $this->tokenStream->consume();
        return $token;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-simple-block
     * @return CSSSimpleBlock
     */
    private function consumeSimpleBlock(): CSSSimpleBlock
    {
        // Note:
        // This algorithm assumes that the current input token has already been checked to be an {, [, or ( token.
        $token = $this->tokenStream->current();
        $endToken = match ($token::TYPE) {
            TokenType::LCURLY => TokenType::RCURLY,
            TokenType::LPAREN => TokenType::RPAREN,
            TokenType::LBRACK => TokenType::RBRACK,
        };
        // Create a simple block with its associated token set to the current input token
        // and with a value with is initially an empty list.
        $block = new CSSSimpleBlock($token->value);
        // Repeatedly consume the next input token and process it as follows:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            if ($token::TYPE === $endToken) {
                $this->tokenStream->consume();
                return $block;
            }
            if ($token::TYPE === TokenType::EOF) {
                // TODO: parse error
                return $block;
            }
            // Reconsume the current input token.
            // Consume a component value and append it to the value of the block.
            $value = $this->consumeComponentValue();
            $block->body[] = $value;
        }
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-function
     * @return CSSFunction
     */
    private function consumeFunction(): CSSFunction
    {
        // Note: This algorithm assumes that the current input token has already been checked to be a <function-token>.
        $token = $this->tokenStream->current();
        // Create a function with a name equal to the value of the current input token,
        // and with a value which is initially an empty list.
        $fn = new CSSFunction($token->value);
        // Repeatedly consume the next input token and process it as follows:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            if ($token::TYPE === TokenType::RPAREN) {
                $this->tokenStream->consume();
                return $fn;
            }
            if ($token::TYPE === TokenType::EOF) {
                // TODO: parse error
                return $fn;
            }
            // Reconsume the current input token.
            // Consume a component value and append the returned value to the function’s value.
            $value = $this->consumeComponentValue();
            $fn->arguments[] = $value;
        }
    }
}
