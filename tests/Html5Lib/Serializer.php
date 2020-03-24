<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Xml\XmlNameEscaper;

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
        } elseif ($node instanceof \DOMDocumentFragment) {
            $output[] = '#document-fragment';
        } elseif ($node instanceof \DOMDocumentType) {
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
        } elseif ($node instanceof \DOMComment) {
            $output[] = sprintf('|%s<!-- %s -->', $indent, $node->nodeValue);
        } elseif ($node instanceof \DOMText) {
            $output[] = sprintf('|%s"%s"', $indent, $node->nodeValue);
        } else {
            $localName = XmlNameEscaper::unescape($node->localName);
            if ($node->namespaceURI && $node->namespaceURI !== Namespaces::HTML) {
                $name = sprintf('%s %s', Namespaces::PREFIXES[$node->namespaceURI], $localName);
            } else {
                $name = $localName;
            }
            $output[] = sprintf('|%s<%s>', $indent, $name);
            $attributes = [];
            /** @var \DOMAttr $attr */
            foreach ($node->attributes as $name => $attr) {
                $nodeName = XmlNameEscaper::unescape($attr->nodeName);
                if ($attr->namespaceURI) {
                    $name = sprintf('%s %s', Namespaces::PREFIXES[$attr->namespaceURI], $nodeName);
                } else {
                    $name = $nodeName;
                }
                $attributes[$name] = $attr->nodeValue;
            }
            $attributes = $this->sortAttributes($attributes);
            foreach ($attributes as $name => $value) {
                $output[] = sprintf('|%s  %s="%s"', $indent, $name, $value);
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
}
