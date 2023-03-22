<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer;

enum TokenKind
{
    case Unknown;
    case EOF;
    case Doctype;
    case StartTag;
    case EndTag;
    case Comment;
    case Characters;

    /**
     * Convenience method for printing a token type.
     *
     * @codeCoverageIgnore
     */
    public static function nameOf(Token|TokenKind $tokenOrType): string
    {
        if ($tokenOrType instanceof Token) {
            $type = $tokenOrType::KIND;
        } else {
            $type = $tokenOrType;
        }

        return $type->name;
    }
}
