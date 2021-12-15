<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use RuntimeException;

/**
 * @see https://webidl.spec.whatwg.org/#idl-DOMException
 *
 * @property-read string $message
 * @property-read int $code
 */
abstract class DOMException extends RuntimeException
{
    // Legacy error codes, don't use them.
    /** @deprecated */
    const INDEX_SIZE_ERR = 1;
    /** @deprecated */
    const DOMSTRING_SIZE_ERR = 2;
    /** @deprecated */
    const HIERARCHY_REQUEST_ERR = 3;
    /** @deprecated */
    const WRONG_DOCUMENT_ERR = 4;
    /** @deprecated */
    const INVALID_CHARACTER_ERR = 5;
    /** @deprecated */
    const NO_DATA_ALLOWED_ERR = 6;
    /** @deprecated */
    const NO_MODIFICATION_ALLOWED_ERR = 7;
    /** @deprecated */
    const NOT_FOUND_ERR = 8;
    /** @deprecated */
    const NOT_SUPPORTED_ERR = 9;
    /** @deprecated */
    const INUSE_ATTRIBUTE_ERR = 10;
    /** @deprecated */
    const INVALID_STATE_ERR = 11;
    /** @deprecated */
    const SYNTAX_ERR = 12;
    /** @deprecated */
    const INVALID_MODIFICATION_ERR = 13;
    /** @deprecated */
    const NAMESPACE_ERR = 14;
    /** @deprecated */
    const INVALID_ACCESS_ERR = 15;
    /** @deprecated */
    const VALIDATION_ERR = 16;
    /** @deprecated */
    const TYPE_MISMATCH_ERR = 17;
    /** @deprecated */
    const SECURITY_ERR = 18;
    /** @deprecated */
    const NETWORK_ERR = 19;
    /** @deprecated */
    const ABORT_ERR = 20;
    /** @deprecated */
    const URL_MISMATCH_ERR = 21;
    /** @deprecated */
    const QUOTA_EXCEEDED_ERR = 22;
    /** @deprecated */
    const TIMEOUT_ERR = 23;
    /** @deprecated */
    const INVALID_NODE_TYPE_ERR = 24;
    /** @deprecated */
    const DATA_CLONE_ERR = 25;
}
