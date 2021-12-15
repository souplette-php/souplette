<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer;

use Souplette\CSS\Syntax\SyntaxNode;

abstract class Token extends SyntaxNode
{
    const TYPE = TokenType::INVALID;
    public int $position;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#representation
     */
    public string $representation;
}
