<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

final class Characters
{
    const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const NUM = '01234567890';
    const ALNUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';
    const HEX = 'abcdefABCDEF01234567890';
    const WHITESPACE = " \n\t\f";
}
