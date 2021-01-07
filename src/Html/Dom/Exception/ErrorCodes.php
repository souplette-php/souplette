<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Exception;

/**
 * DOMException error codes.
 * @see https://github.com/php/php-src/blob/master/ext/dom/dom_fe.h
 */
final class ErrorCodes
{
    const PHP_ERROR = 0;
    const INDEX_SIZE_ERROR = 1;
    const DOMSTRING_SIZE_ERROR = 2;
    const HIERARCHY_REQUEST_ERROR = 3;
    const WRONG_DOCUMENT_ERROR = 4;
    const INVALID_CHARACTER_ERROR = 5;
    const NO_DATA_ALLOWED_ERROR = 6;
    const NO_MODIFICATION_ALLOWED_ERROR = 7;
    const NOT_FOUND_ERROR = 8;
    const NOT_SUPPORTED_ERROR = 9;
    const INUSE_ATTRIBUTE_ERROR = 10;
    // Introduced in DOM Level 2:
    const INVALID_STATE_ERROR = 11;
    // Introduced in DOM Level 2:
    const SYNTAX_ERROR = 12;
    // Introduced in DOM Level 2:
    const INVALID_MODIFICATION_ERROR = 13;
    // Introduced in DOM Level 2:
    const NAMESPACE_ERROR = 14;
    // Introduced in DOM Level 2:
    const INVALID_ACCESS_ERROR = 15;
    // Introduced in DOM Level 3:
    const VALIDATION_ERR = 16;
}
