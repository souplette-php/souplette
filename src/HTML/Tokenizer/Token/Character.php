<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;

final class Character extends Token
{
    const KIND = TokenKind::Characters;

    public function __construct(
        public string $data = '',
    ) {
    }

    /**
     * Removes leading whitespace from the token,
     * and returns true if there is remaining character data left, or false otherwise.
     */
    public function removeLeadingWhitespace(): bool
    {
        $this->data = ltrim($this->data, " \n\t\f");
        return \strlen($this->data) !== 0;
    }
}
