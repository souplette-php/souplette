<?php declare(strict_types=1);

namespace Souplette\Dom\Serialization;

use Souplette\Dom\Attr;
use Souplette\Dom\CDATASection;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Html\Serializer\Elements;

class MarkupFormatter implements MarkupFormatterInterface
{
    protected SerializationType $serializationType;

    public function __construct(SerializationType $serializationType)
    {
        $this->serializationType = $serializationType;
    }

    public function getSerializationType(): SerializationType
    {
        return $this->serializationType;
    }

    public function formatStartMarkup(Node $node): string
    {
        return match ($node->nodeType) {
            Node::DOCUMENT_NODE => $this->formatXMLDeclaration($node),
            Node::COMMENT_NODE => $this->formatComment($node),
            Node::DOCUMENT_TYPE_NODE => $this->formatDocumentType($node),
            Node::PROCESSING_INSTRUCTION_NODE => $this->formatProcessingInstruction($node),
            Node::CDATA_SECTION_NODE => $this->formatCDATASection($node),
            Node::DOCUMENT_FRAGMENT_NODE => '',
        };
    }

    public function formatEndMarkup(Element $node, string $localName, ?string $prefix = null): string
    {
        if ($this->shouldSelfClose($node)
            || (!$node->_first && $this->elementCannotHaveEndTag($node))
        ) {
            return '';
        }
        $qualifiedName = $prefix ? "{$prefix}:{$localName}" : $localName;
        return sprintf('</%s>', $qualifiedName);
    }

    public function formatStartTagOpen(?string $prefix, string $localName): string
    {
        if ($prefix) return "<{$prefix}:{$localName}";
        return "<{$localName}";
    }

    public function formatStartTagClose(Element $node): string
    {
        if ($this->shouldSelfClose($node)) {
            return $node->isHTML ? ' />' : '/>';
        }
        return '>';
    }

    public function formatAttribute(string $prefix, string $localName, string $value): string
    {
        $name = $prefix ? "{$prefix}:{$localName}" : $localName;
        return sprintf('%s="%s"', $name, $this->formatAttributeValue($value));
    }

    public function formatAttributeValue(string $value): string
    {
        if (!$value) return '';

        return match ($this->serializationType) {
            SerializationType::XML => strtr($value, [
                '&' => '&amp;',
                '"' => '&quot;',
                '<' => '&lt;',
                '>' => '&gt;',
            ]),
            SerializationType::HTML => strtr($value, [
                '&' => '&amp;',
                "\u{00A0}" => '&nbsp;',
                '"' => '&quot;',
            ]),
        };
    }

    public function formatAttributeAsHTML(Attr $attr, string $value): string
    {
        // https://html.spec.whatwg.org/C/#attribute's-serialised-name
        if ($attr->namespaceURI === Namespaces::XMLNS) {
            if (!$attr->prefix && $attr->localName !== 'xmlns') {
                return $this->formatAttribute('xmlns', $attr->localName, $attr->_value);
            }
        } else if ($attr->namespaceURI === Namespaces::XML) {
            return $this->formatAttribute('xml', $attr->localName, $attr->_value);
        } else if ($attr->namespaceURI === Namespaces::XLINK) {
            return $this->formatAttribute('xlink', $attr->localName, $attr->_value);
        }
        return $this->formatAttribute($attr->prefix, $attr->localName, $attr->_value);
    }

    public function formatAttributeAsXMLWithoutNamespace(Attr $attr, string $value): string
    {
        $namespace = $attr->namespaceURI;
        $candidatePrefix = $attr->prefix;
        if ($namespace === Namespaces::XMLNS) {
            if (!$attr->prefix && $attr->localName !== 'xmlns') {
                $candidatePrefix = 'xmlns';
            } else if ($namespace === Namespaces::XML) {
                if (!$candidatePrefix) $candidatePrefix = 'xml';
            } else if ($namespace === Namespaces::XLINK) {
                if (!$candidatePrefix) $candidatePrefix = 'xlink';
            }
        }
        return $this->formatAttribute($candidatePrefix, $attr->localName, $attr->_value);
    }

    public function formatText(Text $node): string
    {
        if (!$node->_value) return '';

        return match ($this->serializationType) {
            SerializationType::XML => strtr($node->_value, [
                '&' => '&amp;',
                '<' => '&lt;',
                '>' => '&gt;',
            ]),
            SerializationType::HTML => strtr($node->_value, [
                '&' => '&amp;',
                "\u{00A0}" => '&nbsp;',
                '<' => '&lt;',
                '>' => '&gt;',
            ]),
        };
    }

    public function formatCDATASection(CDATASection $node): string
    {
        // CDATA content is not escaped, but XMLSerializer (and possibly other callers)
        // should raise an exception if it includes "]]>".
        return "<![CDATA[{$node->_value}]]>";
    }

    public function formatComment(Comment $node): string
    {
        // Comment content is not escaped, but XMLSerializer (and possibly other callers)
        // should raise an exception if it includes "--".
        return "<!--{$node->_value}-->";
    }

    public function formatDocumentType(DocumentType $node): string
    {
        if (!$node->name) return '';

        $markup = "<!DOCTYPE {$node->name}";
        if ($node->publicId) {
            $markup .= sprintf(' PUBLIC "%s"', $node->publicId);
        }
        if ($node->systemId && !$node->publicId) {
            $markup .= ' SYSTEM';
        }
        if ($node->systemId) {
            $markup .= sprintf(' "%s"', $node->systemId);
        }

        return $markup . '>';
    }

    public function formatProcessingInstruction(ProcessingInstruction $node): string
    {
        return "<?{$node->target} {$node->_value}?>";
    }

    public function formatXMLDeclaration(Document $node): string
    {
        if (!$node->_hasXmlDeclaration || $this->serializationType !== SerializationType::XML) {
            return '';
        }
        return sprintf(
            '<?xml version="%s" encoding="%s" standalone="%s" ?>',
            $node->_xmlVersion,
            $node->encoding ?: 'UTF-8',
            $node->_xmlStandalone ? 'yes' : 'no',
        );
    }

    /**
     * Rules of self-closure
     * 1. No elements in an HTML document use the self-closing syntax.
     * 2. Elements w/ children never self-close because they use a separate end tag.
     * 3. HTML elements not listed in spec will close with a separate end tag.
     * 4. Other elements self-close.
     */
    protected function shouldSelfClose(Element $element): bool
    {
        if ($this->serializationType === SerializationType::HTML) return false;
        if ($element->_first) return false;
        if ($element->isHTML && !$this->elementCannotHaveEndTag($element)) return false;
        return true;
    }

    private function elementCannotHaveEndTag(Element $node): bool
    {
        if (!$node->isHTML) return false;
        if (isset(Elements::VOID_ELEMENTS[$node->localName])) return true;
        // TODO
        return false;
    }
}
