<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token\Character;
use ju1ius\HtmlParser\Tokenizer\Token\Comment;
use ju1ius\HtmlParser\Tokenizer\Token\Doctype;
use ju1ius\HtmlParser\Tokenizer\Token\EndTag;
use ju1ius\HtmlParser\Tokenizer\Token\StartTag;

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
        $token = new Character();
        $token->data = $data;

        return $token;
    }

    public static function comment(string $data): Comment
    {
        $token = new Comment();
        $token->data = $data;

        return $token;
    }

    public static function startTag(string $name, bool $selfClosing = false, ?array $attributes = null): StartTag
    {
        $token = new StartTag();
        $token->name = $name;
        $token->selfClosing = $selfClosing;
        $token->attributes = $attributes;

        return $token;
    }

    public static function endTag(string $name): EndTag
    {
        $token = new EndTag();
        $token->name = $name;

        return $token;
    }
}
