<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

use Souplette\CSS\Syntax\SyntaxNode;

final class CSSSimpleBlock extends CSSValue
{
    const END_TOKENS = [
        '{' => '}',
        '(' => ')',
        '[' => ']',
    ];

    /**
     * "{}" or "[]" or "()"
     * @var string
     */
    public string $name;
    /**
     * @var SyntaxNode[]
     */
    public array $body;

    public function __construct(string $name, array $body = [])
    {
        $this->name = $name;
        $this->body = $body;
    }
}
