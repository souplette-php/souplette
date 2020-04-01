<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use JoliPotage\Html\Dom\Api\HtmlDocumentInterface;
use JoliPotage\Html\Dom\Api\HtmlElementInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\Api\ParentNodeInterface;

final class PropertyMaps
{
    const READ = [
        NonDocumentTypeChildNodeInterface::class => [
            'previousElementSibling' => 'getPreviousElementSibling',
            'nextElementSibling' => 'getNextElementSibling',
        ],
        ParentNodeInterface::class => [
            'children' => 'getChildren',
            'firstElementChild' => 'getFirstElementChild',
            'lastElementChild' => 'getLastElementChild',
        ],
        HtmlDocumentInterface::class => [
            'mode' => 'getMode',
            'compatMode' => 'getCompatMode',
            'head' => 'getHead',
            'body' => 'getBody',
            'title' => 'getTitle',
        ],
        HtmlElementInterface::class => [
            'innerHTML' => 'getInnerHTML',
            'outerHTML' => 'getOuterHTML',
            'classList' => 'getClassList',
        ],
    ];

    const WRITE = [
        HtmlDocumentInterface::class => [
            'title' => 'setTitle',
        ],
        HtmlElementInterface::class => [
            'innerHTML' => 'setInnerHTML',
            'outerHTML' => 'setOuterHTML',
        ],
    ];
}
