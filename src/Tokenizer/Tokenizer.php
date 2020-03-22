<?php declare(strict_types=1);
/**
 * This file was automatically generated.
 * All modifications will be lost.
 */
namespace ju1ius\HtmlParser\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token\Character;
use ju1ius\HtmlParser\Tokenizer\Token\Comment;
use ju1ius\HtmlParser\Tokenizer\Token\Doctype;
use ju1ius\HtmlParser\Tokenizer\Token\EndTag;
use ju1ius\HtmlParser\Tokenizer\Token\StartTag;

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
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Character($cc));
                    $this->state = TokenizerStates::DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DATA;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "&<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DATA;
                    goto DATA;
                }
            }
            break;
            case TokenizerStates::RCDATA:
            RCDATA: {
                if ($cc === '&') {
                    // Set the return state to the RCDATA state.
                    $this->returnState = TokenizerStates::RCDATA;
                    // Switch to the character reference state.
                    $this->state = TokenizerStates::CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE;
                } elseif ($cc === '<') {
                    // Switch to the RCDATA less-than sign state.
                    $this->state = TokenizerStates::RCDATA_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RCDATA_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    $this->state = TokenizerStates::RCDATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RCDATA;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "&<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::RCDATA;
                    goto RCDATA;
                }
            }
            break;
            case TokenizerStates::RAWTEXT:
            RAWTEXT: {
                if ($cc === '<') {
                    // Switch to the RAWTEXT less-than sign state.
                    $this->state = TokenizerStates::RAWTEXT_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RAWTEXT_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    $this->state = TokenizerStates::RAWTEXT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RAWTEXT;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::RAWTEXT;
                    goto RAWTEXT;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA:
            SCRIPT_DATA: {
                if ($cc === '<') {
                    // Switch to the script data less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::PLAINTEXT:
            PLAINTEXT: {
                if ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    $this->state = TokenizerStates::PLAINTEXT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto PLAINTEXT;
                } elseif ($cc === null) {
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::PLAINTEXT;
                    goto PLAINTEXT;
                }
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
                    $this->currentToken = new StartTag();
                    // Reconsume in the tag name state.
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                } elseif ($cc === '?') {
                    // This is an unexpected-question-mark-instead-of-tag-name parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_QUESTION_MARK_INSTEAD_OF_TAG_NAME, $this->position];
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Comment('');
                    // Reconsume in the bogus comment state.
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    goto BOGUS_COMMENT;
                } elseif ($cc === null) {
                    // This is an eof-before-tag-name parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_BEFORE_TAG_NAME, $this->position];
                    // Emit a U+003C LESS-THAN SIGN character token and an end-of-file token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    return false;
                } else {
                    // This is an invalid-first-character-of-tag-name parse error.
                    $this->parseErrors[] = [ParseErrors::INVALID_FIRST_CHARACTER_OF_TAG_NAME, $this->position];
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
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
                    $this->currentToken = new EndTag();
                    // Reconsume in the tag name state.
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                } elseif ($cc === '>') {
                    // This is a missing-end-tag-name parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_END_TAG_NAME, $this->position];
                    // Switch to the data state.
                    $this->state = TokenizerStates::DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DATA;
                } elseif ($cc === null) {
                    // This is an eof-before-tag-name parse error.
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token and an end-of-file token.
                    $this->tokenQueue->enqueue(new Character('</'));
                    return false;
                } else {
                    // This is an invalid-first-character-of-tag-name parse error.
                    $this->parseErrors[] = [ParseErrors::INVALID_FIRST_CHARACTER_OF_TAG_NAME, $this->position];
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Comment('');
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
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current tag token's tag name.
                    $this->currentToken->name .= "\u{FFFD}";
                    $this->state = TokenizerStates::TAG_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto TAG_NAME;
                } elseif ($cc === null) {
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current tag token's tag name.
                    $l = strcspn($this->input, "/> \t\f\n\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->name .= strtolower($chars);
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::TAG_NAME;
                    goto TAG_NAME;
                }
            }
            break;
            case TokenizerStates::RCDATA_LESS_THAN_SIGN:
            RCDATA_LESS_THAN_SIGN: {
                if ($cc === '/') {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    // Switch to the RCDATA end tag open state.
                    $this->state = TokenizerStates::RCDATA_END_TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RCDATA_END_TAG_OPEN;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Reconsume in the RCDATA state.
                    $this->state = TokenizerStates::RCDATA;
                    goto RCDATA;
                }
            }
            break;
            case TokenizerStates::RCDATA_END_TAG_OPEN:
            RCDATA_END_TAG_OPEN: {
                if (ctype_alpha($cc)) {
                    // Create a new end tag token, set its tag name to the empty string.
                    $this->currentToken = new EndTag();
                    // Reconsume in the RCDATA end tag name state.
                    $this->state = TokenizerStates::RCDATA_END_TAG_NAME;
                    goto RCDATA_END_TAG_NAME;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token and a U+002F SOLIDUS character token.
                    $this->tokenQueue->enqueue(new Character('</'));
                    // Reconsume in the RCDATA state.
                    $this->state = TokenizerStates::RCDATA;
                    goto RCDATA;
                }
            }
            break;
            case TokenizerStates::RCDATA_END_TAG_NAME:
            RCDATA_END_TAG_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the before attribute name state.
                        $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                        $cc = $this->input[++$this->position] ?? null;
                        goto BEFORE_ATTRIBUTE_NAME;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RCDATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '/') {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the self-closing start tag state.
                        $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SELF_CLOSING_START_TAG;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RCDATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '>') {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the data state and emit the current tag token.
                        $this->emitCurrentToken();
                        $this->state = TokenizerStates::DATA;
                        $cc = $this->input[++$this->position] ?? null;
                        goto DATA;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RCDATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif (ctype_alpha($cc)) {
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    // Append the lowercase version of the current input character to the current tag token's tag name.
                    $this->currentToken->name .= strtolower($chars);
                    // Append the current input character to the temporary buffer.
                    $this->temporaryBuffer .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::RCDATA_END_TAG_NAME;
                    goto RCDATA_END_TAG_NAME;
                } else {
                    RCDATA_END_TAG_NAME_ANYTHING_ELSE:
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token,
                    // and a character token for each of the characters in the temporary buffer (in the order they were added to the buffer).
                    $this->tokenQueue->enqueue(new Character('</' . $this->temporaryBuffer));
                    // Reconsume in the RCDATA state.
                    $this->state = TokenizerStates::RCDATA;
                    goto RCDATA;
                }
            }
            break;
            case TokenizerStates::RAWTEXT_LESS_THAN_SIGN:
            RAWTEXT_LESS_THAN_SIGN: {
                if ($cc === '/') {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    // Switch to the Switch to the RAWTEXT end tag open state.
                    $this->state = TokenizerStates::RAWTEXT_END_TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto RAWTEXT_END_TAG_OPEN;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Reconsume in the RAWTEXT state.
                    $this->state = TokenizerStates::RAWTEXT;
                    goto RAWTEXT;
                }
            }
            break;
            case TokenizerStates::RAWTEXT_END_TAG_OPEN:
            RAWTEXT_END_TAG_OPEN: {
                if (ctype_alpha($cc)) {
                    // Create a new end tag token, set its tag name to the empty string.
                    $this->currentToken = new EndTag();
                    // Reconsume in the RAWTEXT end tag name state.
                    $this->state = TokenizerStates::RAWTEXT_END_TAG_NAME;
                    goto RAWTEXT_END_TAG_NAME;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token and a U+002F SOLIDUS character token.
                    $this->tokenQueue->enqueue(new Character('</'));
                    // Reconsume in the RAWTEXT state.
                    $this->state = TokenizerStates::RAWTEXT;
                    goto RAWTEXT;
                }
            }
            break;
            case TokenizerStates::RAWTEXT_END_TAG_NAME:
            RAWTEXT_END_TAG_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the before attribute name state.
                        $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                        $cc = $this->input[++$this->position] ?? null;
                        goto BEFORE_ATTRIBUTE_NAME;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RAWTEXT_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '/') {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the self-closing start tag state.
                        $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SELF_CLOSING_START_TAG;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RAWTEXT_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '>') {
                    // If the current end tag token is an appropriate end tag token,
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        // then switch to the data state and emit the current tag token.
                        $this->emitCurrentToken();
                        $this->state = TokenizerStates::DATA;
                        $cc = $this->input[++$this->position] ?? null;
                        goto DATA;
                    } else {
                        // Otherwise, treat it as per the "anything else" entry below.
                        goto RAWTEXT_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif (ctype_alpha($cc)) {
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    // Append the lowercase version of the current input character to the current tag token's tag name.
                    $this->currentToken->name .= strtolower($chars);
                    // Append the current input character to the temporary buffer.
                    $this->temporaryBuffer .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::RAWTEXT_END_TAG_NAME;
                    goto RAWTEXT_END_TAG_NAME;
                } else {
                    RAWTEXT_END_TAG_NAME_ANYTHING_ELSE:
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token,
                    // and a character token for each of the characters in the temporary buffer (in the order they were added to the buffer).
                    $this->tokenQueue->enqueue(new Character('</' . $this->temporaryBuffer));
                    // Reconsume in the RAWTEXT state.
                    $this->state = TokenizerStates::RAWTEXT;
                    goto RAWTEXT;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_LESS_THAN_SIGN:
            SCRIPT_DATA_LESS_THAN_SIGN: {
                if ($cc === '/') {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    // Switch to the script data end tag open state.
                    $this->state = TokenizerStates::SCRIPT_DATA_END_TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_END_TAG_OPEN;
                } elseif ($cc === '!') {
                    // Emit a U+003C LESS-THAN SIGN character token and a U+0021 EXCLAMATION MARK character token.
                    $this->tokenQueue->enqueue(new Character('<!'));
                    // Switch to the script data escape start state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPE_START;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPE_START;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Reconsume in the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_END_TAG_OPEN:
            SCRIPT_DATA_END_TAG_OPEN: {
                if (ctype_alpha($cc)) {
                    // Create a new end tag token, set its tag name to the empty string.
                    $this->currentToken = new EndTag();
                    // Reconsume in the script data end tag name state.
                    $this->state = TokenizerStates::SCRIPT_DATA_END_TAG_NAME;
                    goto SCRIPT_DATA_END_TAG_NAME;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token and a U+002F SOLIDUS character token.
                    $this->tokenQueue->enqueue(new Character('</'));
                    // Reconsume in the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_END_TAG_NAME:
            SCRIPT_DATA_END_TAG_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // If the current end tag token is an appropriate end tag token, then switch to the before attribute name state.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                        $cc = $this->input[++$this->position] ?? null;
                        goto BEFORE_ATTRIBUTE_NAME;
                    } else {
                        goto SCRIPT_DATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '/') {
                    // If the current end tag token is an appropriate end tag token, then switch to the self-closing start tag state.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SELF_CLOSING_START_TAG;
                    } else {
                        goto SCRIPT_DATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '>') {
                    // If the current end tag token is an appropriate end tag token,
                    // then switch to the data state and emit the current tag token.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->emitCurrentToken();
                        $this->state = TokenizerStates::DATA;
                        ++$this->position;
                        return true;
                    } else {
                        goto SCRIPT_DATA_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif (ctype_alpha($cc)) {
                    // Append the lowercase version of the current input character to the current tag token's tag name.
                    // Append the current input character to the temporary buffer.
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->name .= strtolower($chars);
                    $this->temporaryBuffer .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_END_TAG_NAME;
                    goto SCRIPT_DATA_END_TAG_NAME;
                } else {
                    SCRIPT_DATA_END_TAG_NAME_ANYTHING_ELSE:
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token,
                    // and a character token for each of the characters in the temporary buffer (in the order they were added to the buffer).
                    $this->tokenQueue->enqueue(new Character('</' . $this->temporaryBuffer));
                    // Reconsume in the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPE_START:
            SCRIPT_DATA_ESCAPE_START: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data escape start dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPE_START_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPE_START_DASH;
                } else {
                    // Reconsume in the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPE_START_DASH:
            SCRIPT_DATA_ESCAPE_START_DASH: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data escaped dash dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_DASH_DASH;
                } else {
                    // Reconsume in the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    goto SCRIPT_DATA;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED:
            SCRIPT_DATA_ESCAPED: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data escaped dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_DASH;
                } elseif ($cc === '<') {
                    // Switch to the script data escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_DASH:
            SCRIPT_DATA_ESCAPED_DASH: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data escaped dash dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_DASH_DASH;
                } elseif ($cc === '<') {
                    // Switch to the script data escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    // Switch to the script data escaped state
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED;
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_DASH_DASH:
            SCRIPT_DATA_ESCAPED_DASH_DASH: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_DASH_DASH;
                } elseif ($cc === '<') {
                    // Switch to the script data escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === '>') {
                    // Emit a U+003E GREATER-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('>'));
                    // Switch to the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    // Switch to the script data escaped state
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED;
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<>\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN:
            SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN: {
                if ($cc === '/') {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    //  Switch to the script data escaped end tag open state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_OPEN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_ESCAPED_END_TAG_OPEN;
                } elseif (ctype_alpha($cc)) {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Reconsume in the script data double escape start state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_START;
                    goto SCRIPT_DATA_DOUBLE_ESCAPE_START;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Reconsume in the script data escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_OPEN:
            SCRIPT_DATA_ESCAPED_END_TAG_OPEN: {
                if (ctype_alpha($cc)) {
                    // Create a new end tag token, set its tag name to the empty string.
                    $this->currentToken = new EndTag();
                    // Reconsume in the script data escaped end tag name state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_NAME;
                    goto SCRIPT_DATA_ESCAPED_END_TAG_NAME;
                } else {
                    // Emit a U+003C LESS-THAN SIGN character token and a U+002F SOLIDUS character token.
                    $this->tokenQueue->enqueue(new Character('</'));
                    // Reconsume in the script data escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_NAME:
            SCRIPT_DATA_ESCAPED_END_TAG_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // If the current end tag token is an appropriate end tag token, then switch to the before attribute name state.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->state = TokenizerStates::BEFORE_ATTRIBUTE_NAME;
                        $cc = $this->input[++$this->position] ?? null;
                        goto BEFORE_ATTRIBUTE_NAME;
                    } else {
                        goto SCRIPT_DATA_ESCAPED_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '/') {
                    // If the current end tag token is an appropriate end tag token, then switch to the self-closing start tag state.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->state = TokenizerStates::SELF_CLOSING_START_TAG;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SELF_CLOSING_START_TAG;
                    } else {
                        goto SCRIPT_DATA_ESCAPED_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif ($cc === '>') {
                    // If the current end tag token is an appropriate end tag token,
                    // then switch to the data state and emit the current tag token.
                    // Otherwise, treat it as per the "anything else" entry below.
                    if ($this->currentToken->name === $this->appropriateEndTag) {
                        $this->emitCurrentToken();
                        $this->state = TokenizerStates::DATA;
                        ++$this->position;
                        return true;
                    } else {
                        goto SCRIPT_DATA_ESCAPED_END_TAG_NAME_ANYTHING_ELSE;
                    }
                } elseif (ctype_alpha($cc)) {
                    // Append the lowercase version of the current input character to the current tag token's tag name.
                    // Append the current input character to the temporary buffer.
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->name .= strtolower($chars);
                    $this->temporaryBuffer .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED_END_TAG_NAME;
                    goto SCRIPT_DATA_ESCAPED_END_TAG_NAME;
                } else {
                    SCRIPT_DATA_ESCAPED_END_TAG_NAME_ANYTHING_ELSE:
                    // Emit a U+003C LESS-THAN SIGN character token, a U+002F SOLIDUS character token,
                    // and a character token for each of the characters in the temporary buffer (in the order they were added to the buffer).
                    $this->tokenQueue->enqueue(new Character('</' . $this->temporaryBuffer));
                    // Reconsume in the script data escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_START:
            SCRIPT_DATA_DOUBLE_ESCAPE_START: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C" || $cc === '/' || $cc === '>') {
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Character($cc));
                    // If the temporary buffer is the string "script", then switch to the script data double escaped state.
                    if ($this->temporaryBuffer === 'script') {
                        $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SCRIPT_DATA_DOUBLE_ESCAPED;
                    } else {
                        // Otherwise, switch to the script data escaped state.
                        $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SCRIPT_DATA_ESCAPED;
                    }
                } elseif (ctype_alpha($cc)) {
                    // Append the lowercase version of the current input character to the temporary buffer.
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->temporaryBuffer .= strtolower($chars);
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_START;
                    goto SCRIPT_DATA_DOUBLE_ESCAPE_START;
                } else {
                    // Reconsume in the script data escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                    goto SCRIPT_DATA_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED:
            SCRIPT_DATA_DOUBLE_ESCAPED: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data double escaped dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_DASH;
                } elseif ($cc === '<') {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Switch to the script data double escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH:
            SCRIPT_DATA_DOUBLE_ESCAPED_DASH: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    // Switch to the script data double escaped dash dash state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH;
                } elseif ($cc === '<') {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Switch to the script data double escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    // Switch to the script data double escaped state
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH:
            SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH: {
                if ($cc === '-') {
                    // Emit a U+002D HYPHEN-MINUS character token.
                    $this->tokenQueue->enqueue(new Character('-'));
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH;
                } elseif ($cc === '<') {
                    // Emit a U+003C LESS-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('<'));
                    // Switch to the script data double escaped less-than sign state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN;
                } elseif ($cc === '>') {
                    // Emit a U+003E GREATER-THAN SIGN character token.
                    $this->tokenQueue->enqueue(new Character('>'));
                    // Switch to the script data state.
                    $this->state = TokenizerStates::SCRIPT_DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Emit a U+FFFD REPLACEMENT CHARACTER character token.
                    $this->tokenQueue->enqueue(new Character("\u{FFFD}"));
                    // Switch to the script data double escaped state
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                } elseif ($cc === null) {
                    // This is an eof-in-script-html-comment-like-text parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, "-<>\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    // Switch to the script data double escaped state.
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN:
            SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN: {
                if ($cc === '/') {
                    // Set the temporary buffer to the empty string.
                    $this->temporaryBuffer = '';
                    // Emit a U+002F SOLIDUS character token.
                    $this->tokenQueue->enqueue(new Character('/'));
                    // Switch to the script data double escape end state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto SCRIPT_DATA_DOUBLE_ESCAPE_END;
                } else {
                    // Reconsume in the script data double escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                }
            }
            break;
            case TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_END:
            SCRIPT_DATA_DOUBLE_ESCAPE_END: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C" || $cc === '/' || $cc === '>') {
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Character($cc));
                    // If the temporary buffer is the string "script", then switch to the script data escaped state.
                    if ($this->temporaryBuffer === 'script') {
                        $this->state = TokenizerStates::SCRIPT_DATA_ESCAPED;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SCRIPT_DATA_ESCAPED;
                    } else {
                        // Otherwise, switch to the script data double escaped state.
                        $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                        $cc = $this->input[++$this->position] ?? null;
                        goto SCRIPT_DATA_DOUBLE_ESCAPED;
                    }
                } elseif (ctype_alpha($cc)) {
                    // Append the lowercase version of the current input character to the temporary buffer.
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->temporaryBuffer .= strtolower($chars);
                    // Emit the current input character as a character token.
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPE_END;
                    goto SCRIPT_DATA_DOUBLE_ESCAPE_END;
                } else {
                    // Reconsume in the script data double escaped state.
                    $this->state = TokenizerStates::SCRIPT_DATA_DOUBLE_ESCAPED;
                    goto SCRIPT_DATA_DOUBLE_ESCAPED;
                }
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
                    // This is an unexpected-equals-sign-before-attribute-name parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_EQUALS_SIGN_BEFORE_ATTRIBUTE_NAME, $this->position];
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
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's name.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][0] .= "\u{FFFD}";
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_NAME;
                } elseif ($cc === '"' || $cc === '\'' || $cc === '<') {
                    // This is an unexpected-character-in-attribute-name parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_CHARACTER_IN_ATTRIBUTE_NAME, $this->position];
                    // Treat it as per the "anything else" entry below.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][0] .= $cc;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_NAME;
                } else {
                    // Append the current input character to the current attribute's name.
                    $l = strcspn($this->input, "=<>/'\"\0 \n\t\f", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
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
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
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
                    // This is a missing-attribute-value parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_ATTRIBUTE_VALUE, $this->position];
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
                    $this->state = TokenizerStates::CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_DOUBLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $l = strcspn($this->input, "\"&\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
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
                    $this->state = TokenizerStates::CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $l = strcspn($this->input, "'&\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
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
                    $this->state = TokenizerStates::CHARACTER_REFERENCE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CHARACTER_REFERENCE;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current tag token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current attribute's value.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= "\u{FFFD}";
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                } elseif ($cc === '"' || $cc === "'" || $cc === '<' || $cc === '=' || $cc === '`') {
                    // This is an unexpected-character-in-unquoted-attribute-value parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_CHARACTER_IN_UNQUOTED_ATTRIBUTE_VALUE, $this->position];
                    // Treat it as per the "anything else" entry below.
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $cc;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append the current input character to the current attribute's value.
                    $l = strcspn($this->input, "&\"'<>=`\0 \n\f\t", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
                    goto ATTRIBUTE_VALUE_UNQUOTED;
                }
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
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // This is a missing-whitespace-between-attributes parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_BETWEEN_ATTRIBUTES, $this->position];
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
                    // This is an eof-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_TAG, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // This is an unexpected-solidus-in-tag parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_SOLIDUS_IN_TAG, $this->position];
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
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the comment token's data.
                    $this->currentToken->data .= "\u{FFFD}";
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_COMMENT;
                } else {
                    // Append the current input character to the comment token's data.
                    $l = strcspn($this->input, ">\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->data .= $chars;
                    $this->state = TokenizerStates::BOGUS_COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_COMMENT;
                }
            }
            break;
            case TokenizerStates::MARKUP_DECLARATION_OPEN:
            MARKUP_DECLARATION_OPEN: {
                if (0 === substr_compare($this->input, '--', $this->position, 2)) {
                    // Consume those two characters
                    $this->position += 2;
                    // create a comment token whose data is the empty string,
                    $this->currentToken = new Comment('');
                    // and switch to the comment start state.
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::COMMENT_START;
                    goto COMMENT_START;
                } elseif (0 === substr_compare($this->input, 'DOCTYPE', $this->position, 7, true)) {
                    // Consume those characters
                    $this->position += 7;
                    // and switch to the DOCTYPE state.
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE;
                    goto DOCTYPE;
                } elseif (0 === substr_compare($this->input, '[CDATA[', $this->position, 7, true)) {
                    // Consume those characters.
                    $this->position += 7;
                    $cc = $this->input[$this->position] ?? null;
                    if (false) {
                        // TODO: If there is an adjusted current node and it is not an element in the HTML namespace,
                        // https://html.spec.whatwg.org/multipage/parsing.html#adjusted-current-node
                        // then switch to the CDATA section state.
                        $this->state = TokenizerStates::CDATA_SECTION;
                        goto CDATA_SECTION;
                    } else {
                        // this is a cdata-in-html-content parse error.
                        $this->parseErrors[] = [ParseErrors::CDATA_IN_HTML_CONTENT, $this->position];
                        // Create a comment token whose data is the "[CDATA[" string.
                        $this->currentToken = new Comment('[CDATA[');
                        // Switch to the bogus comment state.
                        $this->state = TokenizerStates::BOGUS_COMMENT;
                        goto BOGUS_COMMENT;
                    }
                } else {
                    // This is an incorrectly-opened-comment parse error.
                    $this->parseErrors[] = [ParseErrors::INCORRECTLY_OPENED_COMMENT, $this->position];
                    // Create a comment token whose data is the empty string.
                    $this->currentToken = new Comment('');
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
                    // This is an abrupt-closing-of-empty-comment parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_CLOSING_OF_EMPTY_COMMENT, $this->position];
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
                    // This is an abrupt-closing-of-empty-comment parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_CLOSING_OF_EMPTY_COMMENT, $this->position];
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-comment parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_COMMENT, $this->position];
                    // Emit the comment token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Append a U+002D HYPHEN-MINUS character (-) to the comment token's data.
                    $this->currentToken->data .= '-';
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
                    $this->currentToken->data .= $cc;
                    // Switch to the comment less-than sign state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN;
                } elseif ($cc === '-') {
                    // Switch to the comment end dash state.
                    $this->state = TokenizerStates::COMMENT_END_DASH;
                    goto COMMENT_END_DASH;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the comment token's data.
                    $this->currentToken->data .= "\u{FFFD}";
                    $this->state = TokenizerStates::COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT;
                } elseif ($cc === null) {
                    // This is an eof-in-comment parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_COMMENT, $this->position];
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the comment token's data.
                    $l = strcspn($this->input, "<-\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->data .= $chars;
                    $this->state = TokenizerStates::COMMENT;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::COMMENT_LESS_THAN_SIGN:
            COMMENT_LESS_THAN_SIGN: {
                if ($cc === '!') {
                    // Append the current input character to the comment token's data.
                    $this->currentToken->data .= $cc;
                    // Switch to the comment less-than sign bang state.
                    $this->state = TokenizerStates::COMMENT_LESS_THAN_SIGN_BANG;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_LESS_THAN_SIGN_BANG;
                } elseif ($cc === '<') {
                    // Append the current input character to the comment token's data.
                    $this->currentToken->data .= $cc;
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
                    // This is a nested-comment parse error.
                    $this->parseErrors[] = [ParseErrors::NESTED_COMMENT, $this->position];
                    // Reconsume in the comment end state.
                    $this->state = TokenizerStates::COMMENT_END;
                    goto COMMENT_END;
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
                    // This is an eof-in-comment parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_COMMENT, $this->position];
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append a U+002D HYPHEN-MINUS character (-) to the comment token's data.
                    $this->currentToken->data .= '-';
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
                    $this->currentToken->data .= '-';
                    $this->state = TokenizerStates::COMMENT_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END;
                } elseif ($cc === null) {
                    // This is an eof-in-comment parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_COMMENT, $this->position];
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append two U+002D HYPHEN-MINUS characters (-) to the comment token's data.
                    $this->currentToken->data .= '--';
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
                    $this->currentToken->data .= '--!';
                    // Switch to the comment end dash state.
                    $this->state = TokenizerStates::COMMENT_END_DASH;
                    $cc = $this->input[++$this->position] ?? null;
                    goto COMMENT_END_DASH;
                } elseif ($cc === '>') {
                    // This is an incorrectly-closed-comment parse error.
                    $this->parseErrors[] = [ParseErrors::INCORRECTLY_CLOSED_COMMENT, $this->position];
                    // Switch to the data state. Emit the comment token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-comment parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_COMMENT, $this->position];
                    // Emit the comment token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append two U+002D HYPHEN-MINUS characters (-) and a U+0021 EXCLAMATION MARK character (!) to the comment token's data.
                    $this->currentToken->data .= '--!';
                    // Reconsume in the comment state.
                    $this->state = TokenizerStates::COMMENT;
                    goto COMMENT;
                }
            }
            break;
            case TokenizerStates::DOCTYPE:
            DOCTYPE: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before DOCTYPE name state.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_NAME;
                } elseif ($cc === '>') {
                    // Reconsume in the before DOCTYPE name state.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_NAME;
                    goto BEFORE_DOCTYPE_NAME;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Create a new DOCTYPE token.
                    $token = new Doctype();
                    // Set its force-quirks flag to on.
                    $token->forceQuirks = true;
                    // Emit the token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($token);
                    return false;
                } else {
                    // This is a missing-whitespace-before-doctype-name parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_BEFORE_DOCTYPE_NAME, $this->position];
                    // Reconsume in the before DOCTYPE name state.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_NAME;
                    goto BEFORE_DOCTYPE_NAME;
                }
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_NAME:
            BEFORE_DOCTYPE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_NAME;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Create a new DOCTYPE token.
                    $this->currentToken = new Doctype();
                    // Set the token's name to a U+FFFD REPLACEMENT CHARACTER character.
                    $this->currentToken->name = "u\{FFFD}";
                    // Switch to the DOCTYPE name state.
                    $this->state = TokenizerStates::DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_NAME;
                } elseif ($cc === '>') {
                    // This is a missing-doctype-name parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_DOCTYPE_NAME, $this->position];
                    // Create a new DOCTYPE token.
                    $this->currentToken = new Doctype();
                    // Set its force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit the token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Create a new DOCTYPE token.
                    $this->currentToken = new Doctype();
                    // Set its force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit the token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Create a new DOCTYPE token.
                    $this->currentToken = new Doctype();
                    // Set the token's name to the current input character.
                    $this->currentToken->name = strtolower($cc);
                    // Switch to the DOCTYPE name state.
                    $this->state = TokenizerStates::DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_NAME;
                }
            }
            break;
            case TokenizerStates::DOCTYPE_NAME:
            DOCTYPE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the after DOCTYPE name state.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_NAME;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current DOCTYPE token's name.
                    $this->currentToken->name .= "u\{FFFD}";
                    $this->state = TokenizerStates::DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_NAME;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit the token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the current DOCTYPE token's name.
                    $l = strcspn($this->input, ">\0 \n\t\f", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->name .= strtolower($chars);
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE_NAME;
                    goto DOCTYPE_NAME;
                }
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_NAME:
            AFTER_DOCTYPE_NAME: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_NAME;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_NAME;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit the token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    if (0 === substr_compare($this->input, 'PUBLIC', $this->position, 6, true)) {
                        // consume those characters and switch to the after DOCTYPE public keyword state.
                        $this->position += 6;
                        $cc = $this->input[$this->position] ?? null;
                        $this->state = TokenizerStates::AFTER_DOCTYPE_PUBLIC_KEYWORD;
                        goto AFTER_DOCTYPE_PUBLIC_KEYWORD;
                    } elseif (0 === substr_compare($this->input, 'SYSTEM', $this->position, 6, true)) {
                        // consume those characters and switch to the after DOCTYPE system keyword state.
                        $this->position += 6;
                        $cc = $this->input[$this->position] ?? null;
                        $this->state = TokenizerStates::AFTER_DOCTYPE_SYSTEM_KEYWORD;
                        goto AFTER_DOCTYPE_SYSTEM_KEYWORD;
                    } else {
                        // This is an invalid-character-sequence-after-doctype-name parse error.
                        $this->parseErrors[] = [ParseErrors::INVALID_CHARACTER_SEQUENCE_AFTER_DOCTYPE_NAME, $this->position];
                        // Set the DOCTYPE token's force-quirks flag to on.
                        $this->currentToken->forceQuirks = true;
                        // Reconsume in the bogus DOCTYPE state.
                        $this->state = TokenizerStates::BOGUS_DOCTYPE;
                        goto BOGUS_DOCTYPE;
                    }
                }
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_PUBLIC_KEYWORD:
            AFTER_DOCTYPE_PUBLIC_KEYWORD: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before DOCTYPE public identifier state.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_PUBLIC_IDENTIFIER;
                } elseif ($cc === '"') {
                    // This is a missing-whitespace-after-doctype-public-keyword parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_AFTER_DOCTYPE_PUBLIC_KEYWORD, $this->position];
                    // Set the DOCTYPE token's public identifier to the empty string (not missing)
                    $this->currentToken->publicIdentifier = '';
                    // switch to the DOCTYPE public identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // This is a missing-whitespace-after-doctype-public-keyword parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_AFTER_DOCTYPE_PUBLIC_KEYWORD, $this->position];
                    // Set the DOCTYPE token's public identifier to the empty string (not missing)
                    $this->currentToken->publicIdentifier = '';
                    // switch to the DOCTYPE public identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === '>') {
                    // This is a missing-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER:
            BEFORE_DOCTYPE_PUBLIC_IDENTIFIER: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_PUBLIC_IDENTIFIER;
                } elseif ($cc === '"') {
                    // Set the DOCTYPE token's public identifier to the empty string (not missing)
                    $this->currentToken->publicIdentifier = '';
                    // switch to the DOCTYPE public identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // Set the DOCTYPE token's public identifier to the empty string (not missing)
                    $this->currentToken->publicIdentifier = '';
                    // switch to the DOCTYPE public identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === '>') {
                    // This is a missing-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED:
            DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED: {
                if ($cc === '"') {
                    // Switch to the after DOCTYPE public identifier state.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_PUBLIC_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_PUBLIC_IDENTIFIER;
                } elseif ($cc === '>') {
                    // This is an abrupt-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current DOCTYPE token's public identifier.
                    $this->currentToken->publicIdentifier .= "\u{FFFD}";
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the current DOCTYPE token's public identifier.
                    $l = strcspn($this->input, "\">\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->publicIdentifier .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED:
            DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED: {
                if ($cc === "'") {
                    // Switch to the after DOCTYPE public identifier state.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_PUBLIC_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_PUBLIC_IDENTIFIER;
                } elseif ($cc === '>') {
                    // This is an abrupt-doctype-public-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_DOCTYPE_PUBLIC_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current DOCTYPE token's public identifier.
                    $this->currentToken->publicIdentifier .= "\u{FFFD}";
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the current DOCTYPE token's public identifier.
                    $l = strcspn($this->input, "'>\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->publicIdentifier .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                    goto DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_PUBLIC_IDENTIFIER:
            AFTER_DOCTYPE_PUBLIC_IDENTIFIER: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the between DOCTYPE public and system identifiers state.
                    $this->state = TokenizerStates::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === '"') {
                    // This is a missing-whitespace-between-doctype-public-and-system-identifiers parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS, $this->position];
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // This is a missing-whitespace-between-doctype-public-and-system-identifiers parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS, $this->position];
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS:
            BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character
                    $this->state = TokenizerStates::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === '"') {
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_SYSTEM_KEYWORD:
            AFTER_DOCTYPE_SYSTEM_KEYWORD: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Switch to the before DOCTYPE system identifier state.
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_SYSTEM_IDENTIFIER;
                } elseif ($cc === '"') {
                    // This is a missing-whitespace-after-doctype-system-keyword parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_AFTER_DOCTYPE_SYSTEM_KEYWORD, $this->position];
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // This is a missing-whitespace-after-doctype-system-keyword parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_WHITESPACE_AFTER_DOCTYPE_SYSTEM_KEYWORD, $this->position];
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === '>') {
                    // This is a missing-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER:
            BEFORE_DOCTYPE_SYSTEM_IDENTIFIER: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character
                    $this->state = TokenizerStates::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BEFORE_DOCTYPE_SYSTEM_IDENTIFIER;
                } elseif ($cc === '"') {
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (double-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === "'") {
                    // Set the DOCTYPE token's system identifier to the empty string (not missing)
                    $this->currentToken->systemIdentifier = '';
                    // switch to the DOCTYPE system identifier (single-quoted) state.
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === '>') {
                    // This is a missing-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is a missing-quote-before-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_QUOTE_BEFORE_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Reconsume in the bogus DOCTYPE state.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED:
            DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED: {
                if ($cc === '"') {
                    // Switch to the after DOCTYPE system identifier state.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                } elseif ($cc === '>') {
                    // This is an abrupt-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current DOCTYPE token's system identifier.
                    $this->currentToken->systemIdentifier .= "\u{FFFD}";
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the current DOCTYPE token's system identifier.
                    $l = strcspn($this->input, "\">\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->systemIdentifier .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED:
            DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED: {
                if ($cc === "'") {
                    // Switch to the after DOCTYPE system identifier state.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                } elseif ($cc === '>') {
                    // This is an abrupt-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::ABRUPT_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Switch to the data state. Emit that DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Append a U+FFFD REPLACEMENT CHARACTER character to the current DOCTYPE token's system identifier.
                    $this->currentToken->systemIdentifier .= "\u{FFFD}";
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Append the current input character to the current DOCTYPE token's system identifier.
                    $l = strcspn($this->input, "'>\0", $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->currentToken->systemIdentifier .= $chars;
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                    goto DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED;
                }
            }
            break;
            case TokenizerStates::AFTER_DOCTYPE_SYSTEM_IDENTIFIER:
            AFTER_DOCTYPE_SYSTEM_IDENTIFIER: {
                if ($cc === ' ' || $cc === "\x0A" || $cc === "\x09" || $cc === "\x0C") {
                    // Ignore the character.
                    $this->state = TokenizerStates::AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                    $cc = $this->input[++$this->position] ?? null;
                    goto AFTER_DOCTYPE_SYSTEM_IDENTIFIER;
                } elseif ($cc === '>') {
                    // Switch to the data state. Emit the current DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === null) {
                    // This is an eof-in-doctype parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_DOCTYPE, $this->position];
                    // Set the DOCTYPE token's force-quirks flag to on.
                    $this->currentToken->forceQuirks = true;
                    // Emit that DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // This is an unexpected-character-after-doctype-system-identifier parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_CHARACTER_AFTER_DOCTYPE_SYSTEM_IDENTIFIER, $this->position];
                    // Reconsume in the bogus DOCTYPE state. (This does not set the DOCTYPE token's force-quirks flag to on.)
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::BOGUS_DOCTYPE:
            BOGUS_DOCTYPE: {
                if ($cc === '>') {
                    // Switch to the data state. Emit the DOCTYPE token.
                    $this->emitCurrentToken();
                    $this->state = TokenizerStates::DATA;
                    ++$this->position;
                    return true;
                } elseif ($cc === "\0") {
                    // This is an unexpected-null-character parse error.
                    $this->parseErrors[] = [ParseErrors::UNEXPECTED_NULL_CHARACTER, $this->position];
                    // Ignore the character.
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_DOCTYPE;
                } elseif ($cc === null) {
                    // Emit the DOCTYPE token. Emit an end-of-file token.
                    $this->tokenQueue->enqueue($this->currentToken);
                    return false;
                } else {
                    // Ignore the character
                    $this->state = TokenizerStates::BOGUS_DOCTYPE;
                    $cc = $this->input[++$this->position] ?? null;
                    goto BOGUS_DOCTYPE;
                }
            }
            break;
            case TokenizerStates::CDATA_SECTION:
            CDATA_SECTION: {
                // NOTE: U+0000 NULL characters are handled in the tree construction stage,
                // as part of the in foreign content insertion mode, which is the only place where CDATA sections can appear.
                if ($cc === ']') {
                    // Switch to the CDATA section bracket state.
                    $this->state = TokenizerStates::CDATA_SECTION_BRACKET;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CDATA_SECTION_BRACKET;
                } elseif ($cc === null) {
                    // This is an eof-in-cdata parse error.
                    $this->parseErrors[] = [ParseErrors::EOF_IN_CDATA, $this->position];
                    // Emit an end-of-file token.
                    return false;
                } else {
                    // Emit the current input character as a character token.
                    $l = strcspn($this->input, ']', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    $this->tokenQueue->enqueue(new Character($chars));
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::CDATA_SECTION;
                    goto CDATA_SECTION;
                }
            }
            break;
            case TokenizerStates::CDATA_SECTION_BRACKET:
            CDATA_SECTION_BRACKET: {
                if ($cc === ']') {
                    // Switch to the CDATA section end state.
                    $this->state = TokenizerStates::CDATA_SECTION_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CDATA_SECTION_END;
                } else {
                    // Emit a U+005D RIGHT SQUARE BRACKET character token.
                    $this->tokenQueue->enqueue(new Character(']'));
                    // Reconsume in the CDATA section state.
                    $this->state = TokenizerStates::CDATA_SECTION;
                    goto CDATA_SECTION;
                }
            }
            break;
            case TokenizerStates::CDATA_SECTION_END:
            CDATA_SECTION_END: {
                if ($cc === ']') {
                    // Emit a U+005D RIGHT SQUARE BRACKET character token.
                    $this->tokenQueue->enqueue(new Character(']'));
                    $this->state = TokenizerStates::CDATA_SECTION_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto CDATA_SECTION_END;
                } elseif ($cc === '>') {
                    // Switch to the data state.
                    $this->state = TokenizerStates::DATA;
                    $cc = $this->input[++$this->position] ?? null;
                    goto DATA;
                } else {
                    // Emit two U+005D RIGHT SQUARE BRACKET character tokens.
                    $this->tokenQueue->enqueue(new Character(']]'));
                    // Reconsume in the CDATA section state.
                    $this->state = TokenizerStates::CDATA_SECTION;
                    goto CDATA_SECTION;
                }
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
                $pos = $this->position;
                $node = $this->entitySearch;
                $buffer = '';
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
                    $buffer .= $c;
                    $pos++;
                }
                // At this point we have a string that starts with some characters that may match an entity
                // Try to find the longest entity the string will match to take care of &noti for instance.
                $node = $this->entitySearch;
                $lastTerminalIndex = null;
                for ($i = 0, $l = strlen($buffer); $i < $l; $i++) {
                    $c = $buffer[$i];
                    if (!isset($node->children[$c])) {
                        break;
                    }
                    $node = $node->children[$c];
                    if ($node->value) {
                        $lastTerminalIndex = $i;
                    }
                }
                if ($lastTerminalIndex !== null) {
                    $buffer = substr($buffer, 0, $lastTerminalIndex + 1);
                    $this->position += $lastTerminalIndex + 1;
                    if (
                        // If the character reference was consumed as part of an attribute,
                        ($this->returnState === TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED)
                        // and the last character matched is not a U+003B SEMICOLON character (;),
                        && $buffer[-1] !== ';'
                        // and the next input character is either a U+003D EQUALS SIGN character (=) or an ASCII alphanumeric,
                        && 1 === strspn($this->input, '='.Characters::ALNUM, $this->position, 1)
                    ) {
                        // then, for historical reasons, flush code points consumed as a character reference
                        $this->temporaryBuffer .= $buffer;
                        $this->flushCodePointsConsumedAsACharacterReference();
                        // and switch to the return state.
                        $this->state = $this->returnState;
                        goto INITIAL;
                    } else {
                        // Otherwise:
                        // 1. If the last character matched is not a U+003B SEMICOLON character (;),
                        if ($buffer[-1] !== ';') {
                            // This is a missing-semicolon-after-character-reference parse error.
                            $this->parseErrors[] = [ParseErrors::MISSING_SEMICOLON_AFTER_CHARACTER_REFERENCE, $this->position];
                        }
                        // 2. Set the temporary buffer to the empty string. Append the decoded character reference to the temporary buffer.
                        $this->temporaryBuffer = EntityLookup::NAMED_ENTITIES[$buffer];
                        // 3. Flush code points consumed as a character reference.
                        $this->flushCodePointsConsumedAsACharacterReference();
                        // Switch to the return state.
                        $this->state = $this->returnState;
                        goto INITIAL;
                    }
                } else {
                    $this->temporaryBuffer = '&';
                    // Flush code points consumed as a character reference.
                    $this->flushCodePointsConsumedAsACharacterReference();
                    // Switch to the ambiguous ampersand state.
                    $this->state = TokenizerStates::AMBIGUOUS_AMPERSAND;
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
                    // This is an absence-of-digits-in-numeric-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::ABSENCE_OF_DIGITS_IN_NUMERIC_CHARACTER_REFERENCE, $this->position];
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
                    // This is an absence-of-digits-in-numeric-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::ABSENCE_OF_DIGITS_IN_NUMERIC_CHARACTER_REFERENCE, $this->position];
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
                $l = strspn($this->input, 'abcdefABCDEF0123456789', $this->position);
                $chars = substr($this->input, $this->position, $l);
                $this->position += $l;
                $this->characterReferenceCode = hexdec($chars);
                $cc = $this->input[$this->position] ?? null;
                if ($cc === ';') {
                    // Switch to the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                } else {
                    // This is a missing-semicolon-after-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_SEMICOLON_AFTER_CHARACTER_REFERENCE, $this->position];
                    // Reconsume in the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                }
            }
            break;
            case TokenizerStates::DECIMAL_CHARACTER_REFERENCE:
            DECIMAL_CHARACTER_REFERENCE: {
                $l = strspn($this->input, '0123456789', $this->position);
                $chars = substr($this->input, $this->position, $l);
                $this->position += $l;
                $this->characterReferenceCode = (int)$chars;
                $cc = $this->input[$this->position] ?? null;
                if ($cc === ';') {
                    // Switch to the numeric character reference end state.
                    $this->state = TokenizerStates::NUMERIC_CHARACTER_REFERENCE_END;
                    $cc = $this->input[++$this->position] ?? null;
                    goto NUMERIC_CHARACTER_REFERENCE_END;
                } else {
                    // This is a missing-semicolon-after-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::MISSING_SEMICOLON_AFTER_CHARACTER_REFERENCE, $this->position];
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
                    // This is a null-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::NULL_CHARACTER_REFERENCE, $this->position];
                    // Set the character reference code to 0xFFFD.
                    $this->characterReferenceCode = 0xFFFD;
                } elseif ($refCode > 0x10FFFF) {
                    // This is a character-reference-outside-unicode-range parse error.
                    $this->parseErrors[] = [ParseErrors::CHARACTER_REFERENCE_OUTSIDE_UNICODE_RANGE, $this->position];
                    // Set the character reference code to 0xFFFD.
                    $this->characterReferenceCode = 0xFFFD;
                } elseif ($refCode >= 0xD800 && $refCode <= 0xDFFF) {
                    // A surrogate is a code point that is in the range U+D800 to U+DFFF, inclusive.
                    // This is a surrogate-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::SURROGATE_CHARACTER_REFERENCE, $this->position];
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
                    // This is a noncharacter-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::NONCHARACTER_CHARACTER_REFERENCE, $this->position];
                } elseif (
                    // the number is 0x0D
                    $refCode === 0x0D
                    // or a control that's not ASCII whitespace
                    || (
                        (
                            ($refCode >= 0x00 && $refCode <= 0x1F) || ($refCode >= 0x7F && $refCode <= 0x9F)
                        )
                        && !($refCode < 128 && ctype_space($refCode))
                    )
                ) {
                    // This is a control-character-reference parse error
                    $this->parseErrors[] = [ParseErrors::CONTROL_CHARACTER_REFERENCE, $this->position];
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
                    $l = strspn($this->input, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $this->position);
                    $chars = substr($this->input, $this->position, $l);
                    $this->position += $l;
                    // If the character reference was consumed as part of an attribute
                    if ($this->returnState === TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED || $this->returnState === TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED) {
                        // then append the current input character to the current attribute's value.
                        $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $chars;
                    } else {
                        // Otherwise, emit the current input character as a character token.
                        $this->tokenQueue->enqueue(new Character($chars));
                    }
                    $cc = $this->input[$this->position] ?? null;
                    $this->state = TokenizerStates::AMBIGUOUS_AMPERSAND;
                    goto AMBIGUOUS_AMPERSAND;
                } elseif ($cc === ';') {
                    // This is an unknown-named-character-reference parse error.
                    $this->parseErrors[] = [ParseErrors::UNKNOWN_NAMED_CHARACTER_REFERENCE, $this->position];
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
            default:
                throw new \LogicException("Unknown state: {$this->state}");
                break;
        }
        return true;
    }
}
