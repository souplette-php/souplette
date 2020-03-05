<?php declare(strict_types=1);
/**
 * This file was automatically generated.
 * All modifications will be lost.
 */
namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Parser\Entities\EntityLookup;

final class Tokenizer extends AbstractTokenizer
{
    public function nextToken(): bool
    {
        INITIAL:
        $cc = $this->input[$this->position] ?? null;
        switch ($this->state) {
            case TokenizerStates::DATA:
            DATA: {
                if ($cc === '&') {
                    // Set the return state to the data state.
                    $this->returnState = TokenizerStates::DATA;
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE;
                } elseif ($cc === '<') {
                    // Switch to the tag open state.
                    $this->state = TokenizerStates::TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto TAG_OPEN;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, $cc));
                    $this->state = TokenizerStates::DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DATA;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $chars = $this->charsUntil("&<\0");
                    $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, $chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DATA;
                    goto DATA;
                }
            }
            break;
            case TokenizerStates::CHARACTER_REFERENCE_IN_DATA:
            CHARACTER_REFERENCE_IN_DATA: {
                throw new \Exception('Not Implemented: CHARACTER_REFERENCE_IN_DATA');
            }
            break;
            case TokenizerStates::RCDATA:
            RCDATA: {
                throw new \Exception('Not Implemented: RCDATA');
            }
            break;
            case TokenizerStates::CHARACTER_REFERENCE_IN_RCDATA:
            CHARACTER_REFERENCE_IN_RCDATA: {
                throw new \Exception('Not Implemented: CHARACTER_REFERENCE_IN_RCDATA');
            }
            break;
            case TokenizerStates::RAWTEXT:
            RAWTEXT: {
                throw new \Exception('Not Implemented: RAWTEXT');
            }
            break;
            case TokenizerStates::SCRIPT_DATA:
            SCRIPT_DATA: {
                throw new \Exception('Not Implemented: SCRIPT_DATA');
            }
            break;
            case TokenizerStates::PLAINTEXT:
            PLAINTEXT: {
                throw new \Exception('Not Implemented: PLAINTEXT');
            }
            break;
            case TokenizerStates::TAG_OPEN:
            TAG_OPEN: {
                if ($cc === '!') {
                    // Switch to the markup declaration open state.
                    $this->state = TokenizerStates::MARKUP_DECLARATION_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto MARKUP_DECLARATION_OPEN;
                } elseif ($cc === '/') {
                    // Switch to the end tag open state.
                    $this->state = TokenizerStates::END_TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto END_TAG_OPEN;
                } elseif (ctype_alpha($cc)) {
                    // Create a new start tag token, set its tag name to the empty string.
                    $this->currentToken = new Token(TokenTypes::START_TAG, '');
                    // Reconsume in the tag name state.
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                } elseif ($cc === '?') {
                    // TODO: This is an unexpected-question-mark-instead-of-tag-name parse error.
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Token(TokenTypes::COMMENT, '');
                    // Reconsume in the bogus comment state.
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    goto BOGUS_COMMENT;
                } elseif ($cc === null) {
                    // TODO: This is an eof-before-tag-name parse error.
                    // Emit a U+003C LESS-THAN SIGN character token and an end-of-file token.
                    $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, '<'));
                    return false;
                } else {
                    // TODO: This is an invalid-first-character-of-tag-name parse error.
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, '<'));
                    // Reconsume in the data state.
                    $this->state = TokenizerStates::DATA;
                    goto DATA;
                }
            }
            break;
            case TokenizerStates::END_TAG_OPEN:
            END_TAG_OPEN: {
                if (ctype_alpha($cc)) {
                    // Create a new end tag token, set its tag name to the empty string.
                    $this->currentToken = new Token(TokenTypes::END_TAG, '');
                    // Reconsume in the tag name state.
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                } elseif ($cc === '>') {
                    // TODO: This is a missing-end-tag-name parse error.
                    // Switch to the data state.
                    $this->state = TokenizerStates::DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DATA;
                } elseif ($cc === null) {
                    // This is an eof-before-tag-name parse error.
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token and an end-of-file token.
                    $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, '</'));
                    return false;
                } else {
                    // TODO: This is an invalid-first-character-of-tag-name parse error.
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Token(TokenTypes::COMMENT, '');
                    // Reconsume in the bogus comment state.
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    goto BOGUS_COMMENT;
                }
            }
            break;
            case TokenizerStates::TAG_NAME:
            TAG_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before attribute name state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_NAME;
                } elseif ($cc === '/') {
                    // Switch to the self-closing start tag state.
                    $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SELF_CLOSING_START_TAG;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current tag token's tag name.
                    $this->currentToken->value .= "\u{FFFD}";
                    $this->state = TokenizerStates::TAG_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto TAG_NAME;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current tag token's tag name.
                    $chars = $this->charsUntil("/> \t\f\n\0");
                    $this->currentToken->value .= strtolower($chars);
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                }
            }
            break;
            case TokenizerStates::RCDATA_LESS_THAN_SIGN:
            RCDATA_LESS_THAN_SIGN: {
                throw new \Exception('Not Implemented: RCDATA_LESS_THAN_SIGN');
            }
            break;
            case TokenizerStates::RCDATA_END_TAG_OPEN:
            RCDATA_END_TAG_OPEN: {
                throw new \Exception('Not Implemented: RCDATA_END_TAG_OPEN');
            }
            break;
            case TokenizerStates::RCDATA_END_TAG_NAME:
            RCDATA_END_TAG_NAME: {
                throw new \Exception('Not Implemented: RCDATA_END_TAG_NAME');
            }
            break;
            case TokenizerStates::RAWTEXT_LESS_THAN_SIGN:
            RAWTEXT_LESS_THAN_SIGN: {
                throw new \Exception('Not Implemented: RAWTEXT_LESS_THAN_SIGN');
            }
            break;
            case TokenizerStates::RAWTEXT_END_TAG_OPEN:
            RAWTEXT_END_TAG_OPEN: {
                throw new \Exception('Not Implemented: RAWTEXT_END_TAG_OPEN');
            }
            break;
            case TokenizerStates::RAWTEXT_END_TAG_NAME:
            RAWTEXT_END_TAG_NAME: {
                throw new \Exception('Not Implemented: RAWTEXT_END_TAG_NAME');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_LESS_THAN_SIGN:
            SCRIPT_DATA_LESS_THAN_SIGN: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_LESS_THAN_SIGN');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_END_TAG_OPEN:
            SCRIPT_DATA_END_TAG_OPEN: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_END_TAG_OPEN');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_END_TAG_NAME:
            SCRIPT_DATA_END_TAG_NAME: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_END_TAG_NAME');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPE_START:
            SCRIPT_DATA_ESCAPE_START: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPE_START');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPE_START_DASH:
            SCRIPT_DATA_ESCAPE_START_DASH: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPE_START_DASH');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED:
            SCRIPT_DATA_ESCAPED: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_DASH:
            SCRIPT_DATA_ESCAPED_DASH: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED_DASH');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_DASH_DASH:
            SCRIPT_DATA_ESCAPED_DASH_DASH: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED_DASH_DASH');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN:
            SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_OPEN:
            SCRIPT_DATA_ESCAPED_END_TAG_OPEN: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED_END_TAG_OPEN');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_NAME:
            SCRIPT_DATA_ESCAPED_END_TAG_NAME: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_ESCAPED_END_TAG_NAME');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_START:
            SCRIPT_DATA_DOUBLE_ESCAPE_START: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPE_START');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED:
            SCRIPT_DATA_DOUBLE_ESCAPED: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPED');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH:
            SCRIPT_DATA_DOUBLE_ESCAPED_DASH: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPED_DASH');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH:
            SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN:
            SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN');
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_END:
            SCRIPT_DATA_DOUBLE_ESCAPE_END: {
                throw new \Exception('Not Implemented: SCRIPT_DATA_DOUBLE_ESCAPE_END');
            }
            break;
            case TokenizerStates::BEFORE_ATTRIBUTE_NAME:
            BEFORE_ATTRIBUTE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_NAME;
                } elseif ($cc === '/' || $cc === '>' || $cc === null) {
                    // Reconsume in the after attribute name state.
                    $this->state = TokenizerStates::AFTER_ATTRIBUTE_NAME;
                    goto AFTER_ATTRIBUTE_NAME;
                } elseif ($cc === '=') {
                    // TODO: This is an unexpected-equals-sign-before-attribute-name parse error.
                    // Start a new attribute in the current tag token. Set that attribute's name to the current input character, and its value to the empty string.
                    $this->currentToken->attributes[] = [$cc, ''];
                    // Switch to the attribute name state.
                    $this->state = TokenizerStates::ATTRIBUTE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_NAME;
                } else {
                    // Start a new attribute in the current tag token. Set that attribute name and value to the empty string.
                    $this->currentToken->attributes[] = ['', ''];
                    // Reconsume in the attribute name state.
                    $this->state = TokenizerStates::ATTRIBUTE_NAME;
                    goto ATTRIBUTE_NAME;
                }
            }
            break;
            case TokenizerStates::ATTRIBUTE_NAME:
            ATTRIBUTE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C" || $cc === '/' || $cc === '>' || $cc === null) {
                    // Reconsume in the after attribute name state.
                    $this->state = TokenizerStates::AFTER_ATTRIBUTE_NAME;
                    goto AFTER_ATTRIBUTE_NAME;
                } elseif ($cc === '=') {
                    // Switch to the before attribute value state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_VALUE;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's name.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][0] .= "\u{FFFD}";
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_NAME;
                } elseif ($cc === '"' || $cc === '\'' || $cc === '<') {
                    // TODO: This is an unexpected-character-in-attribute-name parse error.
                    // Treat it as per the "anything else" entry below.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][0] .= $cc;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_NAME;
                } else {
                    // Append the current input character to the current attribute's name.
                    $chars = $this->charsUntil("=<>/'\"\0 \n\t\f");
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][0] .= strtolower($chars);
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::ATTRIBUTE_NAME;
                    goto ATTRIBUTE_NAME;
                }
            }
            break;
            case TokenizerStates::AFTER_ATTRIBUTE_NAME:
            AFTER_ATTRIBUTE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_ATTRIBUTE_NAME;
                } elseif ($cc === '/') {
                    // Switch to the self-closing start tag state.
                    $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SELF_CLOSING_START_TAG;
                } elseif ($cc === '=') {
                    // Switch to the before attribute value state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_VALUE;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Start a new attribute in the current tag token. Set that attribute name and value to the empty string.
                    $this->currentToken->attributes[] = ['', ''];
                    // Reconsume in the attribute name state.
                    $this->state = TokenizerStates::ATTRIBUTE_NAME;
                    goto ATTRIBUTE_NAME;
                }
            }
            break;
            case TokenizerStates::BEFORE_ATTRIBUTE_VALUE:
            BEFORE_ATTRIBUTE_VALUE: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_VALUE;
                } elseif ($cc === '"') {
                    // Switch to the attribute value (double-quoted) state.
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // Switch to the attribute value (single-quoted) state.
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_SINGLE_QUOTED;
                } elseif ($cc === '>') {
                    // TODO: This is a missing-attribute-value parse error.
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } else {
                    // Reconsume in the attribute value (unquoted) state.
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                }
            }
            break;
            case TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED:
            ATTRIBUTE_VALUE_DOUBLE_QUOTED: {
                if ($cc === '"') {
                    // Switch to the after attribute value (quoted) state.
                    $this->state = TokenizerStates::AFTER_ATTRIBUTE_VALUE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_ATTRIBUTE_VALUE_QUOTED;
                } elseif ($cc === '&') {
                    // Set the return state to the attribute value (double-quoted) state.
                    $this->returnState = TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $chars = $this->charsUntil("\"&\0");
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                    goto ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED:
            ATTRIBUTE_VALUE_SINGLE_QUOTED: {
                if ($cc === "'") {
                    // Switch to the after attribute value (quoted) state.
                    $this->state = TokenizerStates::AFTER_ATTRIBUTE_VALUE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_ATTRIBUTE_VALUE_QUOTED;
                } elseif ($cc === '&') {
                    // Set the return state to the attribute value (single-quoted) state.
                    $this->returnState = TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED;
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $chars = $this->charsUntil("'&\0");
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED;
                    goto ATTRIBUTE_VALUE_SINGLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED:
            ATTRIBUTE_VALUE_UNQUOTED: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before attribute name state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_NAME;
                } elseif ($cc === '&') {
                    // Set the return state to the attribute value (unquoted) state.
                    $this->returnState = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                } elseif ($cc === '"' || $cc === "'" || $cc === '<' || $cc === '=' || $cc === '`') {
                    // TODO: This is an unexpected-character-in-unquoted-attribute-value parse error.
                    // Treat it as per the "anything else" entry below.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $cc;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $chars = $this->charsUntil("&\"'<>=`\0 \n\f\t");
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                }
            }
            break;
            case TokenizerStates::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE:
            CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE: {
                throw new \Exception('Not Implemented: CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE');
            }
            break;
            case TokenizerStates::AFTER_ATTRIBUTE_VALUE_QUOTED:
            AFTER_ATTRIBUTE_VALUE_QUOTED: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before attribute name state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_ATTRIBUTE_NAME;
                } elseif ($cc === '/') {
                    // Switch to the self-closing start tag state.
                    $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SELF_CLOSING_START_TAG;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // TODO: This is a missing-whitespace-between-attributes parse error.
                    // Reconsume in the before attribute name state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                    goto BEFORE_ATTRIBUTE_NAME;
                }
            }
            break;
            case TokenizerStates::SELF_CLOSING_START_TAG:
            SELF_CLOSING_START_TAG: {
                if ($cc === '>') {
                    // Set the self-closing flag of the current tag token.
                    $this->currentToken->selfClosing = true;
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-tag parse error.
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // TODO: This is an unexpected-solidus-in-tag parse error.
                    // Reconsume in the before attribute name state.
                    $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                    goto BEFORE_ATTRIBUTE_NAME;
                }
            }
            break;
            case TokenizerStates::BOGUS_COMMENT:
            BOGUS_COMMENT: {
                if ($cc === '>') {
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // Emit the comment. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the comment token's data.
                    $this->currentToken->value .= "\u{FFFD}";
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_COMMENT;
                } else {
                    // Append the current input character to the comment token's data.
                    $chars = $this->charsUntil(">\0");
                    $this->currentToken->value .= $chars;
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_COMMENT;
                }
            }
            break;
            case TokenizerStates::CONTINUE_BOGUS_COMMENT:
            CONTINUE_BOGUS_COMMENT: {
                throw new \Exception('Not Implemented: CONTINUE_BOGUS_COMMENT');
            }
            break;
            case TokenizerStates::MARKUP_DECLARATION_OPEN:
            MARKUP_DECLARATION_OPEN: {
                if (strpos($this->input, '--', $this->position) === $this->position) {
                    // Consume those two characters
                    $this->position += 1;
                    // create a comment token whose data is the empty string,
                    $this->currentToken = new Token(TokenTypes::COMMENT, '');
                    // and switch to the comment start state.
                    $this->state = TokenizerStates::COMMENT_START;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_START;
                } elseif (stripos($this->input, 'DOCTYPE', $this->position) === $this->position) {
                    // Consume those characters
                    $this->position += 7;
                    // and switch to the DOCTYPE state.
                    $this->state = TokenizerStates::DOCTYPE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE;
                } elseif (strpos($this->input, '[CDATA[', $this->position) === $this->position) {
                    // Consume those characters.
                    $this->position += 7;
                    if (false) {
                        // TODO: If there is an adjusted current node and it is not an element in the HTML namespace,
                        // https://html.spec.whatwg.org/multipage/parsing.html#adjusted-current-node
                        // then switch to the CDATA section state.
                        $this->state = TokenizerStates::CDATA_SECTION;
                        $cc = $this->input[++$this->position] ?? null;
                        goto CDATA_SECTION;
                    } else {
                        // TODO: this is a cdata-in-html-content parse error.
                        // Create a comment token whose data is the "[CDATA[" string.
                        $this->currentToken = new Token(TokenTypes::COMMENT, '[CDATA[');
                        // Switch to the bogus comment state.
                        $this->state = TokenizerStates::BOGUS_COMMENT;
                        $cc = $this->input[++$this->position] ?? null;
                        goto BOGUS_COMMENT;
                    }
                } else {
                    // TODO: This is an incorrectly-opened-comment parse error.
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Token(TokenTypes::COMMENT, '');
                    // Switch to the bogus comment state (don't consume anything in the current state).
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    goto BOGUS_COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_START:
            COMMENT_START: {
                if ($cc === '-') {
                    // Switch to the comment start dash state.
                    $this->state = TokenizerStates::COMMENT_START_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_START_DASH;
                } elseif ($cc === '>') {
                    // TODO: This is an abrupt-closing-of-empty-comment parse error.
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } else {
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_START_DASH:
            COMMENT_START_DASH: {
                if ($cc === '-') {
                    // Switch to the comment end state.
                    $this->state = TokenizerStates::COMMENT_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END;
                } elseif ($cc === '>') {
                    // TODO: This is an abrupt-closing-of-empty-comment parse error.
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-comment parse error.
                    // Emit the comment token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append a U+002D HYPHEN-MINUS character (-) to the comment token's data.
                    $this->currentToken->value .= '-';
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT:
            COMMENT: {
                if ($cc === '<') {
                    // Append the current input character to the comment token's data.
                    $this->currentToken->value .= $cc;
                    // Switch to the comment less-than sign state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN;
                } elseif ($cc === '-') {
                    // Switch to the comment end dash state.
                    $this->state = TokenizerStates::COMMENT_END_DASH;
                    goto COMMENT_END_DASH;
                } elseif ($cc === "\0") {
                    // TODO: This is an unexpected-null-character parse error.
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the comment token's data.
                    $this->currentToken->value .= "\u{FFFD}";
                    $this->state = TokenizerStates::COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-comment parse error.
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the comment token's data.
                    $chars = $this->charsUntil("<-\0");
                    $this->currentToken->value .= $chars;
                    $this->state = TokenizerStates::COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_END_DASH:
            COMMENT_END_DASH: {
                if ($cc === '-') {
                    // Switch to the comment end state
                    $this->state = TokenizerStates::COMMENT_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-comment parse error.
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append a U+002D HYPHEN-MINUS character (-) to the comment token's data.
                    $this->currentToken->value .= '-';
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_END:
            COMMENT_END: {
                if ($cc === '>') {
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === '!') {
                    // Switch to the comment end bang state.
                    $this->state = TokenizerStates::COMMENT_END_BANG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END_BANG;
                } elseif ($cc === '-') {
                    // Append a U+002D HYPHEN-MINUS character (-) to the comment token's data.
                    $this->currentToken->value .= '-';
                    $this->state = TokenizerStates::COMMENT_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-comment parse error.
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append two U+002D HYPHEN-MINUS characters (-) to the comment token's data.
                    $this->currentToken->value .= '--';
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_END_BANG:
            COMMENT_END_BANG: {
                if ($cc === '-') {
                    // Append two U+002D HYPHEN-MINUS characters (-) and a U+0021 EXCLAMATION MARK character (!) to the comment token's data.
                    $this->currentToken->value .= '--!';
                    // Switch to the comment end dash state.
                    $this->state = TokenizerStates::COMMENT_END_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END_DASH;
                } elseif ($cc === '>') {
                    // TODO: This is an incorrectly-closed-comment parse error.
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // TODO: This is an eof-in-comment parse error.
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append two U+002D HYPHEN-MINUS characters (-) and a U+0021 EXCLAMATION MARK character (!) to the comment token's data.
                    $this->currentToken->value .= '--!';
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::DOCTYPE:
            DOCTYPE: {
                throw new \Exception('Not Implemented: DOCTYPE');
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_NAME:
            BEFORE_DOCTYPE_NAME: {
                throw new \Exception('Not Implemented: BEFORE_DOCTYPE_NAME');
            }
            break;
            case TokenizerStates::DOCTYPE_NAME:
            DOCTYPE_NAME: {
                throw new \Exception('Not Implemented: DOCTYPE_NAME');
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_NAME:
            AFTER_DOCTYPE_NAME: {
                throw new \Exception('Not Implemented: AFTER_DOCTYPE_NAME');
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_PUBLIC_KEYWORD:
            AFTER_DOCTYPE_PUBLIC_KEYWORD: {
                throw new \Exception('Not Implemented: AFTER_DOCTYPE_PUBLIC_KEYWORD');
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER:
            BEFORE_DOCTYPE_PUBLIC_IDENTIFIER: {
                throw new \Exception('Not Implemented: BEFORE_DOCTYPE_PUBLIC_IDENTIFIER');
            }
            break;
            case TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED:
            DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED: {
                throw new \Exception('Not Implemented: DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED');
            }
            break;
            case TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED:
            DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED: {
                throw new \Exception('Not Implemented: DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED');
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_PUBLIC_IDENTIFIER:
            AFTER_DOCTYPE_PUBLIC_IDENTIFIER: {
                throw new \Exception('Not Implemented: AFTER_DOCTYPE_PUBLIC_IDENTIFIER');
            }
            break;
            case TokenizerStates::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS:
            BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS: {
                throw new \Exception('Not Implemented: BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS');
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_SYSTEM_KEYWORD:
            AFTER_DOCTYPE_SYSTEM_KEYWORD: {
                throw new \Exception('Not Implemented: AFTER_DOCTYPE_SYSTEM_KEYWORD');
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER:
            BEFORE_DOCTYPE_SYSTEM_IDENTIFIER: {
                throw new \Exception('Not Implemented: BEFORE_DOCTYPE_SYSTEM_IDENTIFIER');
            }
            break;
            case TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED:
            DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED: {
                throw new \Exception('Not Implemented: DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED');
            }
            break;
            case TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED:
            DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED: {
                throw new \Exception('Not Implemented: DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED');
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_SYSTEM_IDENTIFIER:
            AFTER_DOCTYPE_SYSTEM_IDENTIFIER: {
                throw new \Exception('Not Implemented: AFTER_DOCTYPE_SYSTEM_IDENTIFIER');
            }
            break;
            case TokenizerStates::BOGUS_DOCTYPE:
            BOGUS_DOCTYPE: {
                throw new \Exception('Not Implemented: BOGUS_DOCTYPE');
            }
            break;
            case TokenizerStates::CDATA_SECTION:
            CDATA_SECTION: {
                throw new \Exception('Not Implemented: CDATA_SECTION');
            }
            break;
            case TokenizerStates::CDATA_SECTION_BRACKET:
            CDATA_SECTION_BRACKET: {
                throw new \Exception('Not Implemented: CDATA_SECTION_BRACKET');
            }
            break;
            case TokenizerStates::CDATA_SECTION_END:
            CDATA_SECTION_END: {
                throw new \Exception('Not Implemented: CDATA_SECTION_END');
            }
            break;
            case TokenizerStates::CHARACTER_REFERENCE:
            CHARACTER_REFERENCE: {
                // Set the temporary buffer to the empty string. Append a U+0026 AMPERSAND (&) character to the temporary buffer.
                $this->temporaryBuffer = '&';
                if (ctype_alnum($cc)) {
                    // Reconsume in the named character reference state.
                    $this->state = TokenizerStates::NAMED_CHARACTER_REFERENCE;
                    goto NAMED_CHARACTER_REFERENCE;
                } elseif ($cc === '#') {
                    // Append the current input character to the temporary buffer.
                    $this->temporaryBuffer .= $cc;
                    // Switch to the numeric character reference state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto NUMERIC_CHARACTER_REFERENCE;
                } else {
                    // Flush code points consumed as a character reference.
                    $this->flushCodePointsConsumedAsACharacterReference();
                    // Reconsume in the return state.
                    $this->state = $this->returnState;
                    goto INITIAL;
                }
            }
            break;
            case TokenizerStates::NAMED_CHARACTER_REFERENCE:
            NAMED_CHARACTER_REFERENCE: {
                // Consume the maximum number of characters possible,
                // with the consumed characters matching one of the identifiers of the named character references table (in a case-sensitive manner).
                // Append each character to the temporary buffer when it's consumed.
                $entity = null;
                $pos = $this->position;
                $node = $this->entitySearch;
                // Consume characters and compare these to a substring of the entity names until the substring no longer matches.
                while (true) {
                    $c = $this->input[$pos] ?? null;
                    if ($c === null) {
                        break;
                    }
                    if (!isset($node->children[$c])) {
                        break;
                    }
                    $node = $node->children[$c];
                    $this->temporaryBuffer .= $c;
                    $pos++;
                }
                // At this point we have a string that starts with some characters that may match an entity
                // Try to find the longest entity the string will match to take care of &noti for instance.
                $node = $this->entitySearch;
                $lastTerminalIndex = null;
                for ($i = 1; $i < strlen($this->temporaryBuffer); $i++) {
                    $c = $this->temporaryBuffer[$i];
                    if (!isset($node->children[$c])) {
                        break;
                    }
                    $node = $node->children[$c];
                    if ($node->value) {
                        $lastTerminalIndex = $i;
                    }
                }
                if ($lastTerminalIndex !== null) {
                    $entity = substr($this->temporaryBuffer, 1, $lastTerminalIndex);
                    $this->position += strlen($entity);
                }
                if ($entity !== null) {
                    if (
                        // If the character reference was consumed as part of an attribute,
                        $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED
                        // and the last character matched is not a U+003B SEMICOLON character (;),
                        && $this->temporaryBuffer[-1] === ';'
                        // and the next input character is either a U+003D EQUALS SIGN character (=) or an ASCII alphanumeric,
                        && 1 === strspn($this->input, '='.Characters::ALNUM, $this->position + 1, 1)
                    ) {
                        // then, for historical reasons, flush code points consumed as a character reference
                        $this->flushCodePointsConsumedAsACharacterReference();
                        // and switch to the return state.
                        $this->state = $this->returnState;
                        goto INITIAL;
                    } else {
                        // Otherwise:
                        // 1. If the last character matched is not a U+003B SEMICOLON character (;),
                        if ($this->temporaryBuffer[-1] !== ';') {
                            // TODO: this is a missing-semicolon-after-character-reference parse error.
                        }
                        // 2. Set the temporary buffer to the empty string. Append the decoded character reference to the temporary buffer.
                        $this->temporaryBuffer = EntityLookup::NAMED_ENTITIES[$entity];
                        // 3. Flush code points consumed as a character reference.
                        $this->flushCodePointsConsumedAsACharacterReference();
                        // Switch to the return state.
                        $this->state = $this->returnState;
                        goto INITIAL;
                    }
                } else {
                    // Flush code points consumed as a character reference.
                    $this->flushCodePointsConsumedAsACharacterReference();
                    // Switch to the ambiguous ampersand state.
                    $this->state = TokenizerStates::AMBIGUOUS_AMPERSAND;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AMBIGUOUS_AMPERSAND;
                }
            }
            break;
            case TokenizerStates::NUMERIC_CHARACTER_REFERENCE:
            NUMERIC_CHARACTER_REFERENCE: {
                // Set the character reference code to zero (0).
                $this->characterReferenceCode = 0;
                if ($cc === 'x' || $cc === 'X') {
                    // Append the current input character to the temporary buffer.
                    $this->temporaryBuffer .= $cc;
                    // Switch to the hexadecimal character reference start state.
                    $this->state = TokenizerStates::HEXADECIMAL_CHARACTER_REFERENCE_START;
                    $cc = $this->input[++$this->position] ?? null;
                    goto HEXADECIMAL_CHARACTER_REFERENCE_START;
                } else {
                    // Reconsume in the decimal character reference start state.
                    $this->state = TokenizerStates::DECIMAL_CHARACTER_REFERENCE_START;
                    goto DECIMAL_CHARACTER_REFERENCE_START;
                }
            }
            break;
            case TokenizerStates::HEXADECIMAL_CHARACTER_REFERENCE_START:
            HEXADECIMAL_CHARACTER_REFERENCE_START: {
                if (ctype_xdigit($cc)) {
                    // Reconsume in the hexadecimal character reference state.
                    $this->state = TokenizerStates::HEXADECIMAL_CHARACTER_REFERENCE;
                    goto HEXADECIMAL_CHARACTER_REFERENCE;
                } else {
                    // TODO: This is an absence-of-digits-in-numeric-character-reference parse error.
                    // Flush code points consumed as a character reference.
                    $this->flushCodePointsConsumedAsACharacterReference();
                    // Reconsume in the return state.
                    $this->state = $this->returnState;
                    goto INITIAL;
                }
            }
            break;
            case TokenizerStates::DECIMAL_CHARACTER_REFERENCE_START:
            DECIMAL_CHARACTER_REFERENCE_START: {
                if (ctype_digit($cc)) {
                    // Reconsume in the decimal character reference state.
                    $this->state = TokenizerStates::DECIMAL_CHARACTER_REFERENCE;
                    goto DECIMAL_CHARACTER_REFERENCE;
                } else {
                    // TODO: This is an absence-of-digits-in-numeric-character-reference parse error.
                    // Flush code points consumed as a character reference.
                    $this->flushCodePointsConsumedAsACharacterReference();
                    // Reconsume in the return state.
                    $this->state = $this->returnState;
                    goto INITIAL;
                }
            }
            break;
            case TokenizerStates::HEXADECIMAL_CHARACTER_REFERENCE:
            HEXADECIMAL_CHARACTER_REFERENCE: {
                $chars = $this->charsWhile(Characters::HEX);
                $this->characterReferenceCode = hexdec($chars);
                $cc = $this->input[$this->position];
                if ($cc === ';') {
                    // Switch to the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                } else {
                    // TODO: This is a missing-semicolon-after-character-reference parse error.
                    // Reconsume in the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                }
            }
            break;
            case TokenizerStates::DECIMAL_CHARACTER_REFERENCE:
            DECIMAL_CHARACTER_REFERENCE: {
                $chars = $this->charsWhile(Characters::NUM);
                $this->characterReferenceCode = (int)$chars;
                $cc = $this->input[$this->position];
                if ($cc === ';') {
                    // Switch to the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                } else {
                    // TODO: This is a missing-semicolon-after-character-reference parse error.
                    // Reconsume in the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                }
            }
            break;
            case TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END:
            NUMERIC_CHARACTER_REFERENCE_END: {
                $refCode = $this->characterReferenceCode;
                if ($refCode === 0x00) {
                    // TODO: this is a null-character-reference parse error.
                    // Set the character reference code to 0xFFFD.
                    $this->characterReferenceCode = 0xFFFD;
                } elseif ($refCode > 0x10FFFF) {
                    // TODO: this is a character-reference-outside-unicode-range parse error.
                    // Set the character reference code to 0xFFFD.
                    $this->characterReferenceCode = 0xFFFD;
                } elseif ($refCode >= 0xD800 && $refCode <= 0xDFFF) {
                    // A surrogate is a code point that is in the range U+D800 to U+DFFF, inclusive.
                    // TODO: this is a surrogate-character-reference parse error.
                    // Set the character reference code to 0xFFFD.
                    $this->characterReferenceCode = 0xFFFD;
                } elseif (
                    // If the number is a noncharacter
                    ($refCode >= 0xFDD0 && $refCode <= 0xFDEF)
                    || $refCode === 0x0FFFE || $refCode === 0x0FFFF
                    || $refCode === 0x1FFFE || $refCode === 0x1FFFF
                    || $refCode === 0x2FFFE || $refCode === 0x2FFFF
                    || $refCode === 0x3FFFE || $refCode === 0x3FFFF
                    || $refCode === 0x4FFFE || $refCode === 0x4FFFF
                    || $refCode === 0x5FFFE || $refCode === 0x5FFFF
                    || $refCode === 0x6FFFE || $refCode === 0x6FFFF
                    || $refCode === 0x7FFFE || $refCode === 0x7FFFF
                    || $refCode === 0x8FFFE || $refCode === 0x8FFFF
                    || $refCode === 0x9FFFE || $refCode === 0x9FFFF
                    || $refCode === 0xAFFFE || $refCode === 0xAFFFF
                    || $refCode === 0xBFFFE || $refCode === 0xBFFFF
                    || $refCode === 0xCFFFE || $refCode === 0xCFFFF
                    || $refCode === 0xDFFFE || $refCode === 0xDFFFF
                    || $refCode === 0xEFFFE || $refCode === 0xEFFFF
                    || $refCode === 0xFFFFE || $refCode === 0xFFFFF
                    || $refCode === 0x10FFFE || $refCode === 0x10FFFF
                ) {
                    // TODO: this is a noncharacter-character-reference parse error.
                } elseif (
                    // the number is 0x0D
                    $refCode === 0x0D
                    // or a control that's not ASCII whitespace
                    || (
                        (
                            ($refCode >= 0x00 && $refCode <= 0x1F) || ($refCode >= 0x7F && $refCode <= 0x9F)
                        )
                        && ($refCode < 128 && !ctype_space($refCode))
                    )
                ) {
                    // TODO: then this is a control-character-reference parse error
                    if (isset(EntityLookup::NUMERIC_CTRL_REPLACEMENTS[$refCode])) {
                        $this->characterReferenceCode = EntityLookup::NUMERIC_CTRL_REPLACEMENTS[$refCode];
                    }
                }
                // Set the temporary buffer to the empty string.
                // Append a code point equal to the character reference code to the temporary buffer.
                $this->temporaryBuffer = \IntlChar::chr($this->characterReferenceCode);
                // Flush code points consumed as a character reference.
                $this->flushCodePointsConsumedAsACharacterReference();
                // Switch to the return state.
                $this->state = $this->returnState;
                goto INITIAL;
            }
            break;
            case TokenizerStates::AMBIGUOUS_AMPERSAND:
            AMBIGUOUS_AMPERSAND: {
                if (ctype_alnum($cc)) {
                    // If the character reference was consumed as part of an attribute
                    if ($this->returnState === TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED) {
                        // then append the current input character to the current attribute's value.
                        $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $cc;
                    } else {
                        // Otherwise, emit the current input character as a character token.
                        $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, $cc));
                    }
                } elseif ($cc === ';') {
                    // TODO: This is an unknown-named-character-reference parse error.
                    // Reconsume in the return state.
                    $this->state = $this->returnState;
                    goto INITIAL;
                } else {
                    // Reconsume in the return state.
                    $this->state = $this->returnState;
                    goto INITIAL;
                }
            }
            break;
            case TokenizerStates::COMMENT_LESS_THAN_SIGN:
            COMMENT_LESS_THAN_SIGN: {
                if ($cc === '!') {
                    // Append the current input character to the comment token's data.
                    $this->currentToken->value .= $cc;
                    // Switch to the comment less-than sign bang state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN_BANG;
                } elseif ($cc === '<') {
                    // Append the current input character to the comment token's data.
                    $this->currentToken->value .= $cc;
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN;
                } else {
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG:
            COMMENT_LESS_THAN_SIGN_BANG: {
                if ($cc === '-') {
                    // Switch to the comment less-than sign bang dash state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN_BANG_DASH;
                } else {
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG_DASH:
            COMMENT_LESS_THAN_SIGN_BANG_DASH: {
                if ($cc === '-') {
                    // Switch to the comment less-than sign bang dash dash state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN_BANG_DASH_DASH;
                } else {
                    // Reconsume in the comment end dash state.
                    $this->state = TokenizerStates::COMMENT_END_DASH;
                    goto COMMENT_END_DASH;
                }
            }
            break;
            case TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG_DASH_DASH:
            COMMENT_LESS_THAN_SIGN_BANG_DASH_DASH: {
                if ($cc === '>' || $cc === null) {
                    // Reconsume in the comment end state.
                    $this->state = TokenizerStates::COMMENT_END;
                    goto COMMENT_END;
                } else {
                    // TODO: This is a nested-comment parse error.
                    // Reconsume in the comment end state.
                    $this->state = TokenizerStates::COMMENT_END;
                    goto COMMENT_END;
                }
            }
            break;
            default:
                throw new \LogicException("Unknown state: {$this->state}");
                break;
        }
        return true;
    }
}
