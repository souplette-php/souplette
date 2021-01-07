<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use Souplette\Html\Dom\Api\HtmlDocumentInterface;
use Souplette\Html\Dom\Api\HtmlElementInterface;
use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Html\Dom\Api\ParentNodeInterface;

final class PropertyMaps
{
    public static function get($obj, $name)
    {
        foreach (class_implements($obj) as $interface) {
            if (isset(self::READ[$interface][$name])) {
                $method = self::READ[$interface][$name];
                return $obj->{$method}();
            }
        }
    }

    public static function set($obj, $name, $value)
    {
        foreach (class_implements($obj) as $interface) {
            if (isset(self::WRITE[$interface][$name])) {
                $method = self::WRITE[$interface][$name];
                return $obj->{$method}($value);
            }
        }
    }

    const READ = [
        HtmlNodeInterface::class => [
            'parentElement' => 'getParentElement',
        ],
        ParentNodeInterface::class => [
            'children' => 'getChildren',
        ],
        HtmlDocumentInterface::class => [
            'mode' => 'getMode',
            'compatMode' => 'getCompatMode',
            'head' => 'getHead',
            'body' => 'getBody',
            'title' => 'getTitle',
        ],
        HtmlElementInterface::class => [
            'id' => 'getId',
            'className' => 'getClassName',
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
            'id' => 'setId',
            'className' => 'setClassName',
            'innerHTML' => 'setInnerHTML',
            'outerHTML' => 'setOuterHTML',
        ],
    ];
}
