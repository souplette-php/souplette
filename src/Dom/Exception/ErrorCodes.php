<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

/**
 * DOMException error codes.
 * @see https://github.com/php/php-src/blob/master/ext/dom/dom_fe.h
 * @see https://webidl.spec.whatwg.org/#idl-DOMException
 */
final class ErrorCodes
{
    // these are implemented by the PHP DOM extension
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
    const VALIDATION_ERROR = 16;

    // the following are from the spec
    const TYPE_MISMATCH_ERROR = 17;
    const SECURITY_ERROR = 18;
    const NETWORK_ERROR = 19;
    const ABORT_ERROR = 20;
    const URL_MISMATCH_ERROR = 21;
    const QUOTA_EXCEEDED_ERROR = 22;
    const TIMEOUT_ERROR = 23;
    const INVALID_NODE_TYPE_ERROR = 24;
    const DATA_CLONE_ERROR = 25;
}
