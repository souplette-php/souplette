<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer;

enum TokenType
{
    case UNKNOWN;
    case EOF;
    case DOCTYPE;
    case START_TAG;
    case END_TAG;
    case COMMENT;
    case CHARACTER;

    /**
     * Convenience method for printing a token type.
     *
     * @codeCoverageIgnore
     */
    public static function nameOf(Token|TokenType $tokenOrType): string
    {
        if ($tokenOrType instanceof Token) {
            $type = $tokenOrType::TYPE;
        } else {
            $type = $tokenOrType;
        }

        return $type->name;
    }
}
