<?php declare(strict_types=1);

namespace Souplette\Html;

use Souplette\Dom\Attr;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentFragment;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Html\Serializer\Elements;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#serialising-html-fragments
 */
final class HtmlSerializer
{
    private const BLANK_NAMESPACES = [
        Namespaces::HTML => true,
        Namespaces::MATHML => true,
        Namespaces::SVG => true,
    ];

    public function serialize(Node $node): string
    {
        $s = '';
        if ($node instanceof Element) {
            $s .= $this->serializeElement($node);
        } else if ($node instanceof Text) {
            $s .= $this->serializeText($node);
        } else if ($node instanceof Comment) {
            $s .= $this->serializeComment($node);
        } else if ($node instanceof ProcessingInstruction) {
            $s .= $this->serializeProcessingInstruction($node);
        } else if ($node instanceof DocumentType) {
            $s .= $this->serializeDocumentType($node);
        } else if ($node instanceof Document) {
            $s .= $this->serializeFragment($node);
        } else if ($node instanceof DocumentFragment) {
            $s .= $this->serializeFragment($node);
        }
        return $s;
    }

    public function serializeFragment(Node $node): string
    {
        // 1. If the node serializes as void, then return the empty string.
        if ($node instanceof Element && $node->isHTML && isset(Elements::VOID_ELEMENTS[$node->localName])) {
            return '';
        }
        // 2. Let s be a string, and initialize it to the empty string.
        $s = '';
        // TODO: 3. If the node is a template element,
        // then let the node instead be the template element's template contents (a DocumentFragment node).
        if (!$node->_first) {
            return $s;
        }
        // 4. For each child node of the node, in tree order, run the following steps:
        for ($child = $node->_first; $child; $child = $child->_next) {
            // 4.1. Let current node be the child node being processed.
            // 4.2 Append the appropriate string from the following list to s:
            $s .= $this->serialize($child);
        }

        return $s;
    }

    private function serializeElement(Element $node): string
    {
        $s = '';
        // If current node is an element in the HTML namespace, the MathML namespace, or the SVG namespace,
        // then let tagName be current node's local name. Otherwise, let tagName be current node's qualified name.
        if (isset(self::BLANK_NAMESPACES[$node->namespaceURI])) {
            $tagName = $node->localName;
        } else {
            $tagName = $node->qualifiedName;
        }
        // Append a U+003C LESS-THAN SIGN character (<), followed by tagName.
        $s .= "<{$tagName}";
        // NOTE: next spec step is skipped since we do not support custom elements.
        // If current node's `is` value is not null, and the element does not have an `is` attribute in its attribute list,
        // then append the string " is="", followed by current node's is value escaped as described below in attribute mode,
        // followed by a U+0022 QUOTATION MARK character (").
        foreach ($node->_attrs as $attr) {
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
        $s .= $this->serializeFragment($node);
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
            $attr->_value === '' ||
            strcasecmp($attr->_value, $canonicalName) === 0
        )) {
            return $name;
        }
        return sprintf('%s="%s"', $name, $this->escapeString($attr->_value, true));
    }

    public function serializeText(Text $node): string
    {
        // If the parent of current node is a style, script, xmp, iframe, noembed, noframes, or plaintext element,
        // or if the parent of current node is a noscript element and scripting is enabled for the node,
        // then append the value of current node's data IDL attribute literally.
        $parent = $node->_parent;
        if ($parent->isHTML && isset(Elements::RCDATA_ELEMENTS[$parent->localName])) {
            return $node->_value;
        }
        // Otherwise, append the value of current node's data IDL attribute, escaped as described below.
        return $this->escapeString($node->_value);
    }

    public function serializeComment(Comment $node): string
    {
        // Append the literal string "<!--" ,
        // followed by the value of current node's data IDL attribute,
        // followed by the literal string "-->".
        return "<!--{$node->_value}-->";
    }

    private function serializeProcessingInstruction(ProcessingInstruction $node): string
    {
        // Append the literal string "<?",
        // followed by the value of current node's target IDL attribute,
        // followed by a single U+0020 SPACE character,
        // followed by the value of current node's data IDL attribute,
        // followed by a single U+003E GREATER-THAN SIGN character (>).
        return "<?{$node->target} {$node->_value}>";
    }

    public function serializeDocumentType(DocumentType $node): string
    {
        // Append the literal string "<!DOCTYPE",
        // followed by a space (U+0020 SPACE),
        // followed by the value of current node's name IDL attribute,
        // followed by the literal string ">" (U+003E GREATER-THAN SIGN).
        return "<!DOCTYPE {$node->name}>";
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
            default => $attr->name,
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
