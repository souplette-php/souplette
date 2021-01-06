<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Exception;

use Throwable;

final class InvalidCharacter extends DomException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::INVALID_CHARACTER_ERROR, $previous);
    }
}