<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm;

final class CssQualifiedRule extends CssRule
{
    /**
     * @var CssValue[]
     */
    public array $prelude = [];
    /**
     * @var CssSimpleBlock
     */
    public ?CssSimpleBlock $body = null;
}
