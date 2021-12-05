<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\TreeConstruction;

use Souplette\Dom\Namespaces;
use Souplette\Dom\Node\Attr;
use Souplette\Dom\Node\Comment;
use Souplette\Dom\Node\Document;
use Souplette\Dom\Node\DocumentFragment;
use Souplette\Dom\Node\DocumentType;
use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Node;
use Souplette\Dom\Node\Text;
use Souplette\Xml\XmlNameEscaper;

final class Serializer
{
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
        } else {
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
            $localName = XmlNameEscaper::unescape($node->localName);
            $name = sprintf('%s %s', Namespaces::TO_PREFIX[$node->namespaceURI], $localName);
        } else {
            $name = XmlNameEscaper::unescape($node->localName);
        }
        return $name;
    }

    private function serializeAttributeName(Element $node, Attr $attr): string
    {
        if ($node->namespaceURI === Namespaces::HTML && $attr->namespaceURI === Namespaces::XML) {
            $name = XmlNameEscaper::escape($attr->nodeName);
        } else if ($attr->namespaceURI) {
            $name = sprintf('%s %s', Namespaces::TO_PREFIX[$attr->namespaceURI], XmlNameEscaper::unescape($attr->localName));
        } else {
            $name = XmlNameEscaper::unescape($attr->localName);
        }
        return $name;
    }
}
