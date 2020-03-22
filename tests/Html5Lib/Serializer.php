<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Namespaces;

final class Serializer
{
    public function serialize(\DOMDocument $doc): string
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
            if ($node->namespaceURI && $node->namespaceURI !== Namespaces::HTML) {
                $name = sprintf('%s %s', Namespaces::PREFIXES[$node->namespaceURI], $node->localName);
            } else {
                $name = $node->localName;
            }
            $output[] = sprintf('|%s<%s>', $indent, $name);
            $attributes = [];
            /** @var \DOMAttr $attr */
            foreach ($node->attributes as $name => $attr) {
                if ($attr->namespaceURI) {
                    $name = sprintf('%s %s', Namespaces::PREFIXES[$attr->namespaceURI], $attr->nodeName);
                } else {
                    $name = $attr->nodeName;
                }
                $attributes[$name] = $attr->nodeValue;
            }
            ksort($attributes);
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
}
