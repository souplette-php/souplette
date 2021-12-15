<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib\TreeConstruction;

use Souplette\DOM\Attr;
use Souplette\DOM\Comment;
use Souplette\DOM\Document;
use Souplette\DOM\DocumentFragment;
use Souplette\DOM\DocumentType;
use Souplette\DOM\Element;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\DOM\Text;

final class Serializer
{
    const NS_TO_PREFIX = [
        Namespaces::HTML => 'html',
        Namespaces::MATHML => 'math',
        Namespaces::SVG => 'svg',
        Namespaces::XLINK => 'xlink',
        Namespaces::XML => 'xml',
        Namespaces::XMLNS => 'xmlns',
    ];

    public function serialize(Node $doc): string
    {
        $output = [];
        $this->serializeNode($doc, $output);

        return implode("\n", $output);
    }

    private function serializeNode(Node $node, array &$output, int $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        if ($node instanceof Document) {
            $output[] = '#document';
        } else if ($node instanceof DocumentFragment) {
            $output[] = '#document-fragment';
        } else if ($node instanceof DocumentType) {
            if ($node->name) {
                if ($node->publicId || $node->systemId) {
                    $output[] = sprintf(
                        '|%s<!DOCTYPE %s "%s" "%s">',
                        $indent,
                        $node->name,
                        $node->publicId ?? '',
                        $node->systemId ?? ''
                    );
                } else {
                    $output[] = sprintf('|%s<!DOCTYPE %s>', $indent, $node->name);
                }
            } else {
                $output[] = sprintf('|%s<!DOCTYPE >', $indent);
            }
        } else if ($node instanceof Comment) {
            $output[] = sprintf('|%s<!-- %s -->', $indent, $node->data);
        } else if ($node instanceof Text) {
            $output[] = sprintf('|%s"%s"', $indent, $node->data);
        } else if ($node instanceof Element) {
            $output[] = sprintf('|%s<%s>', $indent, $this->serializeTagName($node));
            $attributes = [];
            /** @var Attr $attr */
            foreach ($node->attributes as $attr) {
                $name = $this->serializeAttributeName($node, $attr);
                $attributes[$name] = $attr->value;
            }
            $attributes = $this->sortAttributes($attributes);
            foreach ($attributes as $name => $value) {
                $output[] = sprintf('|%s  %s="%s"', $indent, $name, $value);
            }
            if ($node->localName === 'template' && $node->namespaceURI === Namespaces::HTML) {
                $output[] = sprintf('|%s  content', $indent);
                $depth++;
                //if ($node instanceof HtmlTemplateElement) {
                //    foreach ($node->content->childNodes as $child) {
                //        $this->serializeNode($child, $output, $depth + 1);
                //    }
                //}
            }
        }
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->serializeNode($child, $output, $depth + 1);
            }
        }
    }

    private function sortAttributes(array $attrs): array
    {
        $names = array_map(function($name) {
            return (string)$name;
        }, array_keys($attrs));
        sort($names);

        return array_reduce($names, function($acc, $name) use ($attrs) {
            $acc[$name] = $attrs[$name];
            return $acc;
        }, []);
    }

    private function serializeTagName(Element $node): string
    {
        if ($node->namespaceURI && $node->namespaceURI !== Namespaces::HTML) {
            $name = sprintf('%s %s', self::NS_TO_PREFIX[$node->namespaceURI], $node->localName);
        } else {
            $name = $node->localName;
        }
        return $name;
    }

    private function serializeAttributeName(Element $node, Attr $attr): string
    {
        if ($node->namespaceURI === Namespaces::HTML && $attr->namespaceURI === Namespaces::XML) {
            $name = $attr->name;
        } else if ($attr->namespaceURI) {
            $name = sprintf('%s %s', self::NS_TO_PREFIX[$attr->namespaceURI], $attr->localName);
        } else {
            $name = $attr->localName;
        }
        return $name;
    }
}
