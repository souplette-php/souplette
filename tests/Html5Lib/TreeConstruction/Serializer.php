<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\TreeConstruction;

use Souplette\Dom\Namespaces;
use Souplette\Xml\XmlNameEscaper;

final class Serializer
{
    public function serialize(\DOMNode $doc): string
    {
        $output = [];
        $this->serializeNode($doc, $output);

        return implode("\n", $output);
    }

    private function serializeNode(\DOMNode $node, array &$output, int $depth = 0)
    {
        $indent = str_repeat('  ', $depth);
        if ($node instanceof \DOMDocument) {
            $output[] = '#document';
        } else if ($node instanceof \DOMDocumentFragment) {
            $output[] = '#document-fragment';
        } else if ($node instanceof \DOMDocumentType) {
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
        } else if ($node instanceof \DOMComment) {
            $output[] = sprintf('|%s<!-- %s -->', $indent, $node->data);
        } else if ($node instanceof \DOMText) {
            $output[] = sprintf('|%s"%s"', $indent, $node->data);
        } else {
            $output[] = sprintf('|%s<%s>', $indent, $this->serializeTagName($node));
            $attributes = [];
            /** @var \DOMAttr $attr */
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

    private function serializeTagName(\DOMElement $node): string
    {
        if ($node->namespaceURI && $node->namespaceURI !== Namespaces::HTML) {
            $localName = XmlNameEscaper::unescape($node->localName);
            $name = sprintf('%s %s', Namespaces::TO_PREFIX[$node->namespaceURI], $localName);
        } else {
            $name = XmlNameEscaper::unescape($node->tagName);
        }
        return $name;
    }

    private function serializeAttributeName(\DOMElement $node, \DOMAttr $attr): string
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
