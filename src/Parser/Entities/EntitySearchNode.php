<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser\Entities;

class EntitySearchNode
{
    /**
     * @var EntitySearchNode[]
     */
    public $children = [];
    /**
     * @var string
     */
    public $value;
}
