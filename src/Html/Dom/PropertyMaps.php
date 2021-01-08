<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use Souplette\Html\Dom\Api\HtmlDocumentInterface;
use Souplette\Html\Dom\Api\HtmlElementInterface;
use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Html\Dom\Api\ParentNodeInterface;

final class PropertyMaps
{
    private const PROPERTIES = [
        'GET' => [
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
        ],
        'SET' => [
            HtmlDocumentInterface::class => [
                'title' => 'setTitle',
            ],
            HtmlElementInterface::class => [
                'id' => 'setId',
                'className' => 'setClassName',
                'innerHTML' => 'setInnerHTML',
                'outerHTML' => 'setOuterHTML',
            ],
        ],
    ];

    private static array $cache = [
        'GET' => [],
        'SET' => [],
    ];

    public static function get($obj, $name)
    {
        if (!isset(self::$cache['GET'][$obj::class])) {
            self::populateCache('GET', $obj::class);
        }
        $method = self::$cache['GET'][$obj::class][$name];
        return $obj->{$method}();
    }

    public static function set($obj, $name, $value)
    {
        if (!isset(self::$cache['SET'][$obj::class])) {
            self::populateCache('SET', $obj::class);
        }
        $method = self::$cache['SET'][$obj::class][$name];
        return $obj->{$method}($value);
    }

    private static function populateCache(string $mode, string $class)
    {
        $props = [];
        foreach (class_implements($class) as $interface) {
            if (isset(self::PROPERTIES[$mode][$interface])) {
                $props = array_merge($props, self::PROPERTIES[$mode][$interface]);
            }
        }
        if (!$props) {
            throw new \LogicException(sprintf(
                'No %s dynamic properties found for class: %s',
                $mode,
                $class
            ));
        }
        self::$cache[$mode][$class] = $props;
    }
}
