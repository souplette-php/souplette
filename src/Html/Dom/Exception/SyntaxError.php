<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Exception;

use Throwable;

final class SyntaxError extends DomException
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::SYNTAX_ERROR, $previous);
    }
}
