<?php declare(strict_types=1);
/**
 * This file was automatically generated.
 * All modifications will be lost.
 */
namespace ju1ius\HtmlParser\Parser;

final class Tokenizer extends AbstractTokenizer
{
    public function nextToken(): bool
    {
        $cc = $this->input[$this->position] ?? null;
        switch ($this->state) {
            case TokenizerStates::DATA:
            DATA: {
                if ($cc === '&') {
                    // TODO: Set the return state to the data state.
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE_IN_DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE_IN_DATA;
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
                    // TODO: Set the return state to the attribute value (double-quoted) state.
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
                    // TODO: Set the return state to the attribute value (single-quoted) state.
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
                    // TODO: Set the return state to the attribute value (unquoted) state.
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
                throw new \Exception('Not Implemented: BOGUS_COMMENT');
            }
            break;
            case TokenizerStates::CONTINUE_BOGUS_COMMENT:
            CONTINUE_BOGUS_COMMENT: {
                throw new \Exception('Not Implemented: CONTINUE_BOGUS_COMMENT');
            }
            break;
            case TokenizerStates::MARKUP_DECLARATION_OPEN:
            MARKUP_DECLARATION_OPEN: {
                throw new \Exception('Not Implemented: MARKUP_DECLARATION_OPEN');
            }
            break;
            case TokenizerStates::COMMENT_START:
            COMMENT_START: {
                throw new \Exception('Not Implemented: COMMENT_START');
            }
            break;
            case TokenizerStates::COMMENT_START_DASH:
            COMMENT_START_DASH: {
                throw new \Exception('Not Implemented: COMMENT_START_DASH');
            }
            break;
            case TokenizerStates::COMMENT:
            COMMENT: {
                throw new \Exception('Not Implemented: COMMENT');
            }
            break;
            case TokenizerStates::COMMENT_END_DASH:
            COMMENT_END_DASH: {
                throw new \Exception('Not Implemented: COMMENT_END_DASH');
            }
            break;
            case TokenizerStates::COMMENT_END:
            COMMENT_END: {
                throw new \Exception('Not Implemented: COMMENT_END');
            }
            break;
            case TokenizerStates::COMMENT_END_BANG:
            COMMENT_END_BANG: {
                throw new \Exception('Not Implemented: COMMENT_END_BANG');
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
            default:
                throw new \LogicException("Unknown state: {$this->state}");
                break;
        }
        return true;
    }
}
