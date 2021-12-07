<?php declare(strict_types=1);

namespace Souplette\Html;

use Souplette\Dom\Attr;
use Souplette\Dom\Comment;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Internal\BaseNode;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Html\Serializer\Elements;
use Souplette\Xml\XmlNameEscaper;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#serialising-html-fragments
 */
final class Serializer extends BaseNode
{
    private const BLANK_NAMESPACES = [
        Namespaces::HTML => true,
        Namespaces::MATHML => true,
        Namespaces::SVG => true,
    ];

    public function serialize(Node $node): string
    {
        // 1. If the node serializes as void, then return the empty string.
        if ($node instanceof Element && isset(Elements::VOID_ELEMENTS[$node->localName])) {
            return '';
        }
        // 2. Let s be a string, and initialize it to the empty string.
        $s = '';
        // TODO: 3. If the node is a template element,
        // then let the node instead be the template element's template contents (a DocumentFragment node).
        if (!$node->first) {
            return $s;
        }
        // 4. For each child node of the node, in tree order, run the following steps:
        for ($currentNode = $node->first; $currentNode; $currentNode = $currentNode->next) {
            // 4.1. Let current node be the child node being processed.
            // 4.2 Append the appropriate string from the following list to s:
            if ($currentNode instanceof Element) {
                $s .= $this->serializeElement($currentNode);
            } else if ($currentNode instanceof Text) {
                // If the parent of current node is a style, script, xmp, iframe, noembed, noframes, or plaintext element,
                // or if the parent of current node is a noscript element and scripting is enabled for the node,
                // then append the value of current node's data IDL attribute literally.
                $parent = $currentNode->parent;
                $parentName = $parent->localName ?? null;
                if (isset(Elements::RCDATA_ELEMENTS[$parentName])) {
                    $s .= $currentNode->value;
                } else {
                    // Otherwise, append the value of current node's data IDL attribute, escaped as described below.
                    $s .= $this->escapeString($currentNode->value);
                }
            } else if ($currentNode instanceof Comment) {
                // Append the literal string "<!--" ,
                // followed by the value of current node's data IDL attribute,
                // followed by the literal string "-->".
                $s .= "<!--{$currentNode->value}-->";
            } else if ($currentNode instanceof ProcessingInstruction) {
                // Append the literal string "<?",
                // followed by the value of current node's target IDL attribute,
                // followed by a single U+0020 SPACE character,
                // followed by the value of current node's data IDL attribute,
                // followed by a single U+003E GREATER-THAN SIGN character (>).
                $s .= "<?{$currentNode->target} {$currentNode->value}>";
            } else if ($currentNode instanceof DocumentType) {
                // Append the literal string "<!DOCTYPE",
                // followed by a space (U+0020 SPACE),
                // followed by the value of current node's name IDL attribute,
                // followed by the literal string ">" (U+003E GREATER-THAN SIGN).
                $s .= "<!DOCTYPE {$currentNode->name}>";
            }
        }

        return $s;
    }

    public function serializeElement(Element $node): string
    {
        $s = '';
        // If current node is an element in the HTML namespace, the MathML namespace, or the SVG namespace,
        // then let tagName be current node's local name. Otherwise, let tagName be current node's qualified name.
        if (isset(self::BLANK_NAMESPACES[$node->namespaceURI])) {
            $tagName = $node->localName;
        } else {
            $tagName = $node->tagName;
        }
        $tagName = XmlNameEscaper::unescape($tagName);
        // Append a U+003C LESS-THAN SIGN character (<), followed by tagName.
        $s .= "<{$tagName}";
        // NOTE: next spec step is skipped since we do not support custom elements.
        // If current node's `is` value is not null, and the element does not have an `is` attribute in its attribute list,
        // then append the string " is="", followed by current node's is value escaped as described below in attribute mode,
        // followed by a U+0022 QUOTATION MARK character (").
        foreach ($node->attrs as $attr) {
            // For each attribute that the element has, append a U+0020 SPACE character,
            // the attribute's serialized name as described below,
            // a U+003D EQUALS SIGN character (=), a U+0022 QUOTATION MARK character ("),
            // the attribute's value, escaped as described below in attribute mode,
            // and a second U+0022 QUOTATION MARK character (").
            $s .= ' ' . $this->serializeAttribute($tagName, $attr);
        }
        // Append a U+003E GREATER-THAN SIGN character (>).
        $s .= '>';
        // If current node serializes as void, then continue on to the next child node at this point.
        if (isset(Elements::VOID_ELEMENTS[$node->localName])) {
            return $s;
        }
        // Append the value of running the HTML fragment serialization algorithm on the current node element
        // (thus recursing into this algorithm for that element),
        // followed by a U+003C LESS-THAN SIGN character (<), a U+002F SOLIDUS character (/),
        // tagName again, and finally a U+003E GREATER-THAN SIGN character (>).
        $s .= $this->serialize($node);
        $s .= "</{$tagName}>";
        return $s;
    }

    private function serializeAttribute(string $tagName, Attr $attr): string
    {
        $name = $this->serializeAttributeName($attr);
        $canonicalName = strtolower($name);
        $isBoolean = isset(Elements::BOOLEAN_ATTRIBUTES['*'][$canonicalName])
            || isset(Elements::BOOLEAN_ATTRIBUTES[$tagName][$canonicalName]);
        if ($isBoolean && (
            $attr->value === '' ||
            strcasecmp($attr->value, $canonicalName) === 0
        )) {
            return $name;
        }
        return sprintf(
            '%s="%s"',
            XmlNameEscaper::unescape($name),
            $this->escapeString($attr->value, true)
        );
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#attribute's-serialised-name
     */
    private function serializeAttributeName(Attr $attr): string
    {
        return match ($attr->namespaceURI) {
            Namespaces::XML => "xml:{$attr->localName}",
            Namespaces::XMLNS => $attr->localName === 'xmlns' ? 'xmlns' : "xmlns:{$attr->localName}",
            Namespaces::XLINK => "xlink:{$attr->localName}",
            null, '' => $attr->localName,
            default => $attr->nodeName,
        };
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
