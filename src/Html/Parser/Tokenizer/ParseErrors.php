<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parse-errors
 */
final class ParseErrors
{
    const ABRUPT_CLOSING_OF_EMPTY_COMMENT = 'abrupt-closing-of-empty-comment';
    const ABRUPT_DOCTYPE_PUBLIC_IDENTIFIER = 'abrupt-doctype-public-identifier';
    const ABRUPT_DOCTYPE_SYSTEM_IDENTIFIER = 'abrupt-doctype-system-identifier';
    const ABSENCE_OF_DIGITS_IN_NUMERIC_CHARACTER_REFERENCE = 'absence-of-digits-in-numeric-character-reference';
    const CDATA_IN_HTML_CONTENT = 'cdata-in-html-content';
    const CHARACTER_REFERENCE_OUTSIDE_UNICODE_RANGE = 'character-reference-outside-unicode-range';
    const CONTROL_CHARACTER_IN_INPUT_STREAM = 'control-character-in-input-stream';
    const CONTROL_CHARACTER_REFERENCE = 'control-character-reference';
    const DUPLICATE_ATTRIBUTE = 'duplicate-attribute';
    const END_TAG_WITH_ATTRIBUTES = 'end-tag-with-attributes';
    const END_TAG_WITH_TRAILING_SOLIDUS = 'end-tag-with-trailing-solidus';
    const EOF_BEFORE_TAG_NAME = 'eof-before-tag-name';
    const EOF_IN_CDATA = 'eof-in-cdata';
    const EOF_IN_COMMENT = 'eof-in-comment';
    const EOF_IN_DOCTYPE = 'eof-in-doctype';
    const EOF_IN_SCRIPT_HTML_COMMENT_LIKE_TEXT = 'eof-in-script-html-comment-like-text';
    const EOF_IN_TAG = 'eof-in-tag';
    const INCORRECTLY_CLOSED_COMMENT = 'incorrectly-closed-comment';
    const INCORRECTLY_OPENED_COMMENT = 'incorrectly-opened-comment';
    const INVALID_CHARACTER_SEQUENCE_AFTER_DOCTYPE_NAME = 'invalid-character-sequence-after-doctype-name';
    const INVALID_FIRST_CHARACTER_OF_TAG_NAME = 'invalid-first-character-of-tag-name';
    const MISSING_ATTRIBUTE_VALUE = 'missing-attribute-value';
    const MISSING_DOCTYPE_NAME = 'missing-doctype-name';
    const MISSING_DOCTYPE_PUBLIC_IDENTIFIER = 'missing-doctype-public-identifier';
    const MISSING_DOCTYPE_SYSTEM_IDENTIFIER = 'missing-doctype-system-identifier';
    const MISSING_END_TAG_NAME = 'missing-end-tag-name';
    const MISSING_QUOTE_BEFORE_DOCTYPE_PUBLIC_IDENTIFIER = 'missing-quote-before-doctype-public-identifier';
    const MISSING_QUOTE_BEFORE_DOCTYPE_SYSTEM_IDENTIFIER = 'missing-quote-before-doctype-system-identifier';
    const MISSING_SEMICOLON_AFTER_CHARACTER_REFERENCE = 'missing-semicolon-after-character-reference';
    const MISSING_WHITESPACE_AFTER_DOCTYPE_PUBLIC_KEYWORD = 'missing-whitespace-after-doctype-public-keyword';
    const MISSING_WHITESPACE_AFTER_DOCTYPE_SYSTEM_KEYWORD = 'missing-whitespace-after-doctype-system-keyword';
    const MISSING_WHITESPACE_BEFORE_DOCTYPE_NAME = 'missing-whitespace-before-doctype-name';
    const MISSING_WHITESPACE_BETWEEN_ATTRIBUTES = 'missing-whitespace-between-attributes';
    const MISSING_WHITESPACE_BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS = 'missing-whitespace-between-doctype-public-and-system-identifiers';
    const NESTED_COMMENT = 'nested-comment';
    const NONCHARACTER_CHARACTER_REFERENCE = 'noncharacter-character-reference';
    const NONCHARACTER_IN_INPUT_STREAM = 'noncharacter-in-input-stream';
    const NON_VOID_HTML_ELEMENT_START_TAG_WITH_TRAILING_SOLIDUS = 'non-void-html-element-start-tag-with-trailing-solidus';
    const NULL_CHARACTER_REFERENCE = 'null-character-reference';
    const SURROGATE_CHARACTER_REFERENCE = 'surrogate-character-reference';
    const SURROGATE_IN_INPUT_STREAM = 'surrogate-in-input-stream';
    const UNEXPECTED_CHARACTER_AFTER_DOCTYPE_SYSTEM_IDENTIFIER = 'unexpected-character-after-doctype-system-identifier';
    const UNEXPECTED_CHARACTER_IN_ATTRIBUTE_NAME = 'unexpected-character-in-attribute-name';
    const UNEXPECTED_CHARACTER_IN_UNQUOTED_ATTRIBUTE_VALUE = 'unexpected-character-in-unquoted-attribute-value';
    const UNEXPECTED_EQUALS_SIGN_BEFORE_ATTRIBUTE_NAME = 'unexpected-equals-sign-before-attribute-name';
    const UNEXPECTED_NULL_CHARACTER = 'unexpected-null-character';
    const UNEXPECTED_QUESTION_MARK_INSTEAD_OF_TAG_NAME = 'unexpected-question-mark-instead-of-tag-name';
    const UNEXPECTED_SOLIDUS_IN_TAG = 'unexpected-solidus-in-tag';
    const UNKNOWN_NAMED_CHARACTER_REFERENCE = 'unknown-named-character-reference';
}
