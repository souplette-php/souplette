<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm;

final class CssSimpleBlock extends CssValue
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
    public array $body;

    public function __construct(string $name, array $body = [])
    {
        $this->name = $name;
        $this->body = $body;
    }
}
