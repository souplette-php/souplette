<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer;

use Souplette\Css\Syntax\SyntaxNode;

abstract class Token extends SyntaxNode
{
    public int $type;
    public int $position;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#representation
     */
    public string $representation;
}
