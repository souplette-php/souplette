<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Serializer;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Xml\XmlNameEscaper;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#serialising-html-fragments
 */
final class Serializer
{
    private const BLANK_NAMESPACES = [
        Namespaces::HTML => true,
        Namespaces::MATHML => true,
        Namespaces::SVG => true,
    ];

    public function serialize(\DOMNode $node): string
    {
        // 1. If the node serializes as void, then return the empty string.
        if (isset(Elements::VOID_ELEMENTS[$node->localName])) {
            return '';
        }
        // 2. Let s be a string, and initialize it to the empty string.
        $s = '';
        // TODO: 3. If the node is a template element,
        // then let the node instead be the template element's template contents (a DocumentFragment node).
        if (!$node->hasChildNodes()) {
            return $s;
        }
        // 4. For each child node of the node, in tree order, run the following steps:
        foreach ($node->childNodes as $currentNode) {
            // 4.1. Let current node be the child node being processed.
            // 4.2 Append the appropriate string from the following list to s:
            if ($currentNode instanceof \DOMElement) {
                // If current node is an element in the HTML namespace, the MathML namespace, or the SVG namespace,
                // then let tagName be current node's local name. Otherwise, let tagName be current node's qualified name.
                if (isset(self::BLANK_NAMESPACES[$currentNode->namespaceURI])) {
                    $tagName = $currentNode->localName;
                } else {
                    $tagName = $currentNode->tagName;
                }
                $tagName = XmlNameEscaper::unescape($tagName);
                // Append a U+003C LESS-THAN SIGN character (<), followed by tagName.
                $s .= "<{$tagName}";
                // NOTE: next spec step is skipped since we do not support custom elements.
                // TODO: If current node's `is` value is not null, and the element does not have an `is` attribute in its attribute list,
                // then append the string " is="", followed by current node's is value escaped as described below in attribute mode,
                // followed by a U+0022 QUOTATION MARK character (").
                foreach ($currentNode->attributes as $attr) {
                    // For each attribute that the element has, append a U+0020 SPACE character,
                    // the attribute's serialized name as described below,
                    // a U+003D EQUALS SIGN character (=), a U+0022 QUOTATION MARK character ("),
                    // the attribute's value, escaped as described below in attribute mode,
                    // and a second U+0022 QUOTATION MARK character (").
                    $s .= sprintf(
                        ' %s="%s"',
                        XmlNameEscaper::unescape($this->serializeAttributeName($attr)),
                        $this->escapeString($attr->value, true)
                    );
                }
                // Append a U+003E GREATER-THAN SIGN character (>).
                $s .= '>';
                // If current node serializes as void, then continue on to the next child node at this point.
                if (isset(Elements::VOID_ELEMENTS[$currentNode->localName])) {
                    continue;
                }
                // Append the value of running the HTML fragment serialization algorithm on the current node element
                // (thus recursing into this algorithm for that element),
                // followed by a U+003C LESS-THAN SIGN character (<), a U+002F SOLIDUS character (/),
                // tagName again, and finally a U+003E GREATER-THAN SIGN character (>).
                $s .= $this->serialize($currentNode);
                $s .= "</{$tagName}>";
            } elseif ($currentNode instanceof \DOMText) {
                // If the parent of current node is a style, script, xmp, iframe, noembed, noframes, or plaintext element,
                // or if the parent of current node is a noscript element and scripting is enabled for the node,
                // then append the value of current node's data IDL attribute literally.
                $parent = $currentNode->parentNode;
                $parentName = $parent->localName ?? null;
                if (isset(Elements::RCDATA_ELEMENTS[$parentName])) {
                    $s .= $currentNode->data;
                } else {
                    // Otherwise, append the value of current node's data IDL attribute, escaped as described below.
                    $s .= $this->escapeString($currentNode->data);
                }
            } elseif ($currentNode instanceof \DOMComment) {
                // Append the literal string "<!--" ,
                // followed by the value of current node's data IDL attribute,
                // followed by the literal string "-->".
                $s .= "<!--{$currentNode->data}-->";
            } elseif ($currentNode instanceof \DOMProcessingInstruction) {
                // Append the literal string "<?",
                // followed by the value of current node's target IDL attribute,
                // followed by a single U+0020 SPACE character,
                // followed by the value of current node's data IDL attribute,
                // followed by a single U+003E GREATER-THAN SIGN character (>).
                $s .= "<?{$currentNode->target} {$currentNode->data}>";
            } elseif ($currentNode instanceof \DOMDocumentType) {
                // Append the literal string "<!DOCTYPE",
                // followed by a space (U+0020 SPACE),
                // followed by the value of current node's name IDL attribute,
                // followed by the literal string ">" (U+003E GREATER-THAN SIGN).
                $s .= "<!DOCTYPE {$currentNode->name}>";
            }
        }

        return $s;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#attribute's-serialised-name
     * @param \DOMAttr $attr
     * @return string
     */
    private function serializeAttributeName(\DOMAttr $attr): string
    {
        $ns = $attr->namespaceURI;
        if (!$ns) {
            return $attr->localName;
        } elseif ($ns === Namespaces::XML) {
            return "xml:{$attr->localName}" ;
        } elseif ($ns === Namespaces::XMLNS) {
            return $attr->localName === 'xmlns' ? 'xmlns' : "xmlns:{$attr->localName}";
        } elseif ($ns === Namespaces::XLINK) {
            return "xlink:{$attr->localName}";
        } else {
            return $attr->nodeName;
        }
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#escapingString
     *
     * @param string $input
     * @param bool $attributeMode
     * @return string
     */
    private function escapeString(string $input, bool $attributeMode = false): string
    {
        if ($attributeMode) {
            return strtr($input, [
                '&' => '&amp;',
                "\u{00A0}" => '&nbsp;',
                '"' => '&quot;',
            ]);
        }

        return strtr($input, [
            '&' => '&amp;',
            "\u{00A0}" => '&nbsp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }
}
