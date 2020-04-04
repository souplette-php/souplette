<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser;

use JoliPotage\Css\CssOm\CssAtRule;
use JoliPotage\Css\CssOm\CssDeclaration;
use JoliPotage\Css\CssOm\CssFunction;
use JoliPotage\Css\CssOm\CssQualifiedRule;
use JoliPotage\Css\CssOm\CssRule;
use JoliPotage\Css\CssOm\CssSimpleBlock;
use JoliPotage\Css\CssOm\CssStylesheet;
use JoliPotage\Css\Parser\Tokenizer\Token;
use JoliPotage\Css\Parser\Tokenizer\Tokenizer;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;
use JoliPotage\Css\Parser\TokenStream\TokenStream;

final class Parser
{
    private TokenStream $tokenStream;
    private bool $topLevel = true;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenStream = new TokenStream($tokenizer, 3);
    }

    public function parseStylesheet(): CssStylesheet
    {
        // 1. Create a new stylesheet.
        $stylesheet = new CssStylesheet();
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

    public function parseRule(): ?CssRule
    {
        $rule = null;
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is an <EOF-token>, return a syntax error.
        if ($token->type === TokenTypes::EOF) {
            // TODO: syntax error;
            return null;
        }
        // Otherwise, if the next input token is an <at-keyword-token>,
        // consume an at-rule, and let rule be the return value.
        if ($token->type === TokenTypes::AT_KEYWORD) {
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
        if ($token->type === TokenTypes::EOF) {
            return $rule;
        }
        // Otherwise, return a syntax error.
        // TODO: syntax error
        return null;
    }

    public function parseDeclaration(): ?CssDeclaration
    {
        // Note: Unlike "Parse a list of declarations", this parses only a declaration and not an at-rule.
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is not an <ident-token>, return a syntax error.
        if ($token->type !== TokenTypes::IDENT) {
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

    public function parseComponentValue()
    {
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is an <EOF-token>, return a syntax error.
        if ($token->type === TokenTypes::EOF) {
            // TODO: syntax error;
            return null;
        }
        // 3. Consume a component value and let value be the return value.
        $value = $this->consumeComponentValue();
        // 4. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 5. If the next input token is an <EOF-token>, return value. Otherwise, return a syntax error.
        if ($token->type === TokenTypes::EOF) {
            return $value;
        }
        // TODO: syntax error
        return null;
    }

    public function parseComponentValueList(): array
    {
        // 1. Repeatedly consume a component value until an <EOF-token> is returned,
        //    appending the returned values (except the final <EOF-token>) into a list.
        $list = [];
        do {
            $list[] = $this->consumeComponentValue();
            $token = $this->tokenStream->current();
        } while ($token->type !== TokenTypes::EOF);
        // Return the list.
        return $list;
    }

    public function parseCommaSeparatedComponentValueList(): array
    {
        // 1. Let list of cvls be an initially empty list of component value lists.
        $list = [];
        // 2. Repeatedly consume a component value until an <EOF-token> or <comma-token> is returned,
        //    appending the returned values (except the final <EOF-token> or <comma-token>) into a list.
        //    Append the list to list of cvls.
        //    If it was a <comma-token> that was returned, repeat this step.
        while (true) {
            $values = [];
            while (true) {
                $value = $this->consumeComponentValue();
                if ($value instanceof Token\EOF) {
                    $list[] = $values;
                    return $list;
                }
                if ($value instanceof Token\Comma) {
                    $list[] = $values;
                    break;
                }
                $values[] = $value;
            }
        }
        // 3. Return list of cvls.
        return $list;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-list-of-rules
     */
    private function consumeRuleList()
    {
        // Create an initially empty list of rules.
        $rules = [];
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token->type;
            if ($tt === TokenTypes::WHITESPACE) {
                // Do nothing.
                $this->tokenStream->consume();
                continue;
            }
            if ($tt === TokenTypes::EOF) {
                // Return the list of rules.
                return $rules;
            }
            if ($tt === TokenTypes::CDO || $tt === TokenTypes::CDC) {
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
            } elseif ($tt === TokenTypes::AT_KEYWORD) {
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
    private function consumeAtRule(): CssAtRule
    {
        // Consume the next input token.
        $token = $this->tokenStream->current();
        // Create a new at-rule with its name set to the value of the current input token,
        // its prelude initially set to an empty list,
        // and its value initially set to nothing.
        $rule = new CssAtRule($token->value);
        // Repeatedly consume the next input token:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token->type;
            if ($tt === TokenTypes::SEMICOLON) {
                // Return the at-rule.
                $this->tokenStream->consume();
                return $rule;
            }
            if ($tt === TokenTypes::EOF) {
                // TODO: This is a parse error.
                // Return the at-rule.
                return $rule;
            }
            if ($tt === TokenTypes::LCURLY) {
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
    private function consumeQualifiedRule(): ?CssQualifiedRule
    {
        // Create a new qualified rule with its prelude initially set to an empty list,
        // and its value initially set to nothing.
        $rule = new CssQualifiedRule();
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token->type;
            if ($tt === TokenTypes::EOF) {
                // TODO: This is a parse error.
                // Return nothing.
                return null;
            }
            if ($tt === TokenTypes::LCURLY) {
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
     * @return CssDeclaration[]
     */
    private function consumeDeclarationList(): array
    {
        // Create an initially empty list of declarations.
        $declarations = [];
        // Repeatedly consume the next input token:
        while (true) {
            $token = $this->tokenStream->current();
            $tt = $token->type;
            if ($tt === TokenTypes::WHITESPACE || $tt === TokenTypes::SEMICOLON) {
                // Do nothing.
                $this->tokenStream->consume();
                continue;
            }
            if ($tt === TokenTypes::EOF) {
                // Return the list of declarations.
                return $declarations;
            }
            if ($tt === TokenTypes::AT_KEYWORD) {
                // Reconsume the current input token.
                // Consume an at-rule.
                // Append the returned rule to the list of declarations.
                $declarations[] = $this->consumeAtRule();
            } elseif ($tt === TokenTypes::IDENT) {
                // Initialize a temporary list initially filled with the current input token.
                // As long as the next input token is anything other than a <semicolon-token> or <EOF-token>,
                // consume a component value and append it to the temporary list.
                // Consume a declaration from the temporary list.
                // If anything was returned, append it to the list of declarations.
            } else {
                // This is a parse error.
                // Reconsume the current input token.
                // As long as the next input token is anything other than a <semicolon-token> or <EOF-token>,
                // consume a component value and throw away the returned value.
                while (true) {
                    if ($tt === TokenTypes::SEMICOLON || $tt === TokenTypes::EOF) {
                        $this->tokenStream->consume();
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
    private function consumeDeclaration(): ?CssDeclaration
    {
        // Note: This algorithm assumes that the next input token has already been checked to be an <ident-token>.
        // Consume the next input token.
        $token = $this->tokenStream->current();
        // Create a new declaration with its name set to the value of the current input token
        // and its value initially set to the empty list.
        $declaration = new CssDeclaration($token->value);
        // 1. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 2. If the next input token is anything other than a <colon-token>, this is a parse error. Return nothing.
        //    Otherwise, consume the next input token.
        if ($token->type !== TokenTypes::COLON) {
            // TODO: parse error
            return null;
        }
        // 3. While the next input token is a <whitespace-token>, consume the next input token.
        $token = $this->tokenStream->skipWhitespace();
        // 4. As long as the next input token is anything other than an <EOF-token>,
        //    consume a component value and append it to the declaration’s value.
        while ($token->type !== TokenTypes::EOF) {
            $value = $this->consumeComponentValue();
            $declaration->body[] = $value;
        }
        // 5. If the last two non-<whitespace-token>s in the declaration’s value
        //    are a <delim-token> with the value "!" followed by an <ident-token> with a value
        //    that is an ASCII case-insensitive match for "important",
        //    remove them from the declaration’s value and set the declaration’s important flag to true.
        // 6. While the last token in the declaration’s value is a <whitespace-token>, remove that token.
        // 7. Return the declaration.
        return $declaration;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-component-value
     * @return CssFunction|CssSimpleBlock|Token
     */
    private function consumeComponentValue()
    {
        // Consume the next input token.
        $token = $this->tokenStream->current();
        $tt = $token->type;
        // If the current input token is a <{-token>, <[-token>, or <(-token>, consume a simple block and return it.
        if ($tt === TokenTypes::LCURLY || $tt === TokenTypes::LBRACK || $tt === TokenTypes::LPAREN) {
            return $this->consumeSimpleBlock();
        }
        // Otherwise, if the current input token is a <function-token>, consume a function and return it.
        if ($tt === TokenTypes::FUNCTION) {
            return $this->consumeFunction();
        }
        // Otherwise, return the current input token.
        $this->tokenStream->consume();
        return $token;
    }

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#consume-simple-block
     * @return CssSimpleBlock
     */
    private function consumeSimpleBlock(): CssSimpleBlock
    {
        // Note: This algorithm assumes that the current input token has already been checked to be an {, [, or ( token.
        $token = $this->tokenStream->current();
        switch ($token->type) {
            case TokenTypes::LCURLY:
                $endToken = TokenTypes::RCURLY;
                break;
            case TokenTypes::LPAREN:
                $endToken = TokenTypes::RPAREN;
                break;
            case TokenTypes::LBRACK:
                $endToken = TokenTypes::RBRACK;
                break;
        }
        // Create a simple block with its associated token set to the current input token
        // and with a value with is initially an empty list.
        $block = new CssSimpleBlock($token->value);
        // Repeatedly consume the next input token and process it as follows:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            if ($token->type === $endToken) {
                $this->tokenStream->consume();
                return $block;
            }
            if ($token->type === TokenTypes::EOF) {
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
     * @return CssFunction
     */
    private function consumeFunction(): CssFunction
    {
        // Note: This algorithm assumes that the current input token has already been checked to be a <function-token>.
        $token = $this->tokenStream->current();
        // Create a function with a name equal to the value of the current input token,
        // and with a value which is initially an empty list.
        $fn = new CssFunction($token->value);
        // Repeatedly consume the next input token and process it as follows:
        $this->tokenStream->consume();
        while (true) {
            $token = $this->tokenStream->current();
            if ($token->type === TokenTypes::RPAREN) {
                $this->tokenStream->consume();
                return $fn;
            }
            if ($token->type === TokenTypes::EOF) {
                // TODO: parse error
                return $fn;
            }
            // Reconsume the current input token.
            // Consume a component value and append the returned value to the function’s value.
            $value = $this->consumeComponentValue();
            $fn->arguments[] = $value;
        }
    }

    private function skipWhitespace()
    {
        $token = $this->tokenStream->current();
        while ($token->type === TokenTypes::WHITESPACE) {
            $token = $this->tokenStream->consume();
        }
    }
}
