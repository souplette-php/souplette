<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token\Character;
use ju1ius\HtmlParser\Tokenizer\Token\Comment;
use ju1ius\HtmlParser\Tokenizer\Token\Doctype;
use ju1ius\HtmlParser\Tokenizer\Token\EndTag;
use ju1ius\HtmlParser\Tokenizer\Token\StartTag;

/**
 * Static constructor methods are to be used for tests only.
 *
 * @codeCoverageIgnore
 */
abstract class Token
{
    /**
     * @var int
     */
    public $type;

    public static function doctype(string $name, ?string $publicId = null, ?string $systemId = null, bool $forceQuirks = false): Doctype
    {
        $token = new Doctype();
        $token->name = $name;
        $token->forceQuirks = $forceQuirks;
        $token->publicIdentifier = $publicId;
        $token->systemIdentifier = $systemId;

        return $token;
    }

    public static function character(string $data): Character
    {
        return new Character($data);
    }

    public static function comment(string $data): Comment
    {
        return new Comment($data);
    }

    public static function startTag(string $name, bool $selfClosing = false, ?array $attributes = null): StartTag
    {
        $token = new StartTag($name);
        $token->selfClosing = $selfClosing;
        $token->attributes = $attributes;

        return $token;
    }

    public static function endTag(string $name): EndTag
    {
        return new EndTag($name);
    }
}
