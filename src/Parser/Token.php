<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

final class Token
{
    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $selfClosing = false;

    /**
     * @var array|null
     */
    public $attributes;

    /**
     * @var bool
     */
    public $forceQuirks;

    /**
     * @var string
     */
    public $publicIdentifier;

    /**
     * @var string
     */
    public $systemIdentifier;

    public function __construct(int $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public static function doctype(string $name, ?string $publicId = null, ?string $systemId = null): self
    {
        $token = new self(TokenTypes::DOCTYPE, '');
        $token->name = $name;
        $token->publicIdentifier = $publicId;
        $token->systemIdentifier = $systemId;
        return $token;
    }

    public static function character(string $value): self
    {
        return new self(TokenTypes::CHARACTER, $value);
    }

    public static function comment(string $value): self
    {
        return new self(TokenTypes::COMMENT, $value);
    }

    public static function startTag(string $name, bool $selfClosing = false, ?array $attributes = null): self
    {
        $token = new self(TokenTypes::START_TAG, $name);
        $token->selfClosing = $selfClosing;
        $token->attributes = $attributes;

        return $token;
    }

    public static function endTag(string $name): self
    {
        return new self(TokenTypes::END_TAG, $name);
    }
}
