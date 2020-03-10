<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

final class Characters
{
    const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const NUM = '0123456789';
    const ALNUM = self::ALPHA . self::NUM;
    const HEX = 'abcdefABCDEF0123456789';
    const WHITESPACE = " \n\t\f";
}