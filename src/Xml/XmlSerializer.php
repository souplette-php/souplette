<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Attr;
use Souplette\Dom\CDATASection;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\InvalidStateError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Html\Serializer\Elements;
use Souplette\Xml\Serializer\NamespaceContext;
use SplStack;

/**
 * @see https://w3c.github.io/DOM-Parsing/#xml-serialization
 */
final class XmlSerializer
{
    private const VALID_TEXT = <<<'REGEXP'
    /
        ^
        [\x{09}\x{0A}\x{0D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}] *
        $
    /Sux
    REGEXP;

    private const VALID_DOCTYPE_PUBLIC_ID = <<<'REGEXP'
    ~
        ^
        [\x{20}\x{0D}\x{0A}A-Za-z0-9'()+,./:=?;!*#@$_%-]*
        $  
    ~x
    REGEXP;

    private const VALID_DOCTYPE_SYSTEM_ID = <<<'REGEXP'
    /
        ^
        [\x{09}\x{0A}\x{0D}\x{20}\x{21}\x{23}-\x{26}\x{28}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}] *
        $
    /Sux
    REGEXP;

    private bool $requireWellFormed = false;
    private string $markup = '';
    /** @var SplStack<NamespaceContext> */
    private SplStack $namespaceStack;
    private int $prefixIndex = 1;

    /**
     * @throws InvalidStateError
     */
    public function serialize(Node $node, bool $requireWellFormed = false): string
    {
        $this->initialize($requireWellFormed);
        try {
            $this->serializeNode($node);
            return $this->markup;
        } catch (DomException $err) {
            throw new InvalidStateError($err->getMessage(), $err);
        }
    }

    /**
     * @throws InvalidStateError
     */
    public function serializeFragment(Element $node, bool $requireWellFormed = false): string
    {
        $this->initialize($requireWellFormed);
        try {
            $this->serializeNode($node, true);
            return $this->markup;
        } catch (DomException $err) {
            throw new InvalidStateError($err->getMessage(), $err);
        }
    }

    private function initialize(bool $requireWellFormed = false): void
    {
        $this->markup = '';
        $this->prefixIndex = 1;
        $this->namespaceStack = new SplStack();
        $this->namespaceStack->push(new NamespaceContext());
        $this->addPrefix('xml', Namespaces::XML);
        $this->requireWellFormed = $requireWellFormed;
    }

    private function serializeNode(Node $node, bool $childrenOnly = false)
    {
        if (!$node instanceof Element) {
            if (!$childrenOnly) {
                $this->markup .= match ($node->nodeType) {
                    Node::TEXT_NODE => $this->serializeText($node),
                    Node::DOCUMENT_NODE => $this->serializeDocument($node),
                    Node::COMMENT_NODE => $this->serializeComment($node),
                    Node::DOCUMENT_TYPE_NODE => $this->serializeDocumentType($node),
                    Node::PROCESSING_INSTRUCTION_NODE => $this->serializeProcessingInstruction($node),
                    Node::CDATA_SECTION_NODE => $this->serializeCDATASection($node),
                    Node::DOCUMENT_FRAGMENT_NODE => '',
                    Node::ATTRIBUTE_NODE => $this->serializeAttributeValue($node->_value),
                };
            }
            for ($child = $node->_first; $child; $child = $child->_next) {
                $this->serializeNode($child, false);
            }
            return;
        }

        $context = $this->namespaceStack->top();
        $this->namespaceStack->push(clone $context);

        $prefixOverride = null;
        if (!$childrenOnly) {
            $prefixOverride = $this->appendElement($node);
        }
        // TODO: template->content
        for ($child = $node->_first; $child; $child = $child->_next) {
            $this->serializeNode($child, false);
        }
        if (!$childrenOnly) {
            $this->appendEndTag($node, $prefixOverride);
        }

        $this->namespaceStack->pop();
    }

    private function addPrefix(string $prefix, string $namespace): void
    {
        $this->namespaceStack->top()->add($prefix, $namespace);
    }

    private function lookupNamespaceURI(?string $prefix): ?string
    {
        return $this->namespaceStack->top()->lookupNamespaceURI($prefix);
    }

    private function retrievePreferredPrefix(string $ns, ?string $preferredPrefix): ?string
    {
        return $this->namespaceStack->top()->retrievePreferredPrefix($ns, $preferredPrefix);
    }

    private function generatePrefix(string $namespace): string
    {
        do {
            $prefix = "ns{$this->prefixIndex}";
            $this->prefixIndex++;
        } while ($this->lookupNamespaceURI($prefix));
        $this->addPrefix($prefix, $namespace);
        return $prefix;
    }

    /**
     * Returns serialized prefix. It should be passed to AppendEndTag()
     */
    private function appendElement(Element $node)
    {
        if ($this->requireWellFormed) {
            if (str_contains($node->localName, ':')
                || !preg_match(QName::NAME_PATTERN, $node->localName)
            ) {
                throw new SyntaxError(sprintf(
                    'Element local name contains invalid characters: "%s".',
                    $node->localName,
                ));
            }
            if ($node->prefix === 'xmlns') {
                throw new NamespaceError('Element has an xmlns prefix.');
            }
        }

        [$serializedPrefix, $ignoreNamespaceDefinition] = $this->appendStartTagOpen($node);

        $attributeSet = [];
        foreach ($node->_attrs as $attr) {
            if ($ignoreNamespaceDefinition
                && $attr->namespaceURI === Namespaces::XMLNS
                && !$attr->prefix
            ) {
                // Drop xmlns= only if it's inconsistent with element's namespace.
                // https://github.com/w3c/DOM-Parsing/issues/47
                if ($attr->_value !== $node->namespaceURI) continue;
            }
            if ($this->requireWellFormed) {
                if (isset($attributeSet[$attr->namespaceURI][$attr->localName])) {
                    throw new InvalidStateError(sprintf(
                        'Duplicate attribute "%s".',
                        $attr->name,
                    ));
                }
                $attributeSet[$attr->namespaceURI][$attr->localName] = true;
            }

            $this->appendAttribute($node, $attr);
        }

        $this->appendStartTagClose($node);

        return $serializedPrefix;
    }

    /**
     * Returns 'ignore namespace definition attribute' flag and the serialized prefix.
     * If the flag is true, we should not serialize xmlns="..." on the element.
     * The prefix should be used in end tag serialization.
     *
     * @return array{string, bool}
     */
    private function appendStartTagOpen(Element $element): array
    {
        $serializedPrefix = $element->prefix;
        // https://w3c.github.io/DOM-Parsing/#xml-serializing-an-element-node
        //TODO: $this->validator?->validateElement($element);
        $namespaceContext = $this->namespaceStack->top();
        // 5. Let ignore namespace definition attribute be a boolean flag with value false.
        $ignoreNamespaceDefinition = false;
        // 8. Let local default namespace be the result of recording the namespace
        // information for node given map and local prefixes map.
        $localDefaultNamespace = $namespaceContext->recordNamespaceInformation($element);
        // 9. Let inherited ns be a copy of namespace.
        $inheritedNs = $namespaceContext->contextNamespace;
        // 10. Let ns be the value of node's namespaceURI attribute.
        $ns = $element->namespaceURI;
        // 11. If inherited ns is equal to ns, then:
        if ($inheritedNs === $ns) {
            // 11.1. If local default namespace is not null,
            // then set ignore namespace definition attribute to true.
            if ($localDefaultNamespace) $ignoreNamespaceDefinition = true;
            // 11.3. Otherwise, append to qualified name the value of node's
            // localName. The node's prefix if it exists, is dropped.

            // 11.4. Append the value of qualified name to markup.
            $this->markup .= $this->formatStartTagOpen(null, $element->localName);
            return [null, $ignoreNamespaceDefinition];
        }
        // 12. Otherwise, inherited ns is not equal to ns (the node's own namespace is
        // different from the context namespace of its parent). Run these sub-steps:
        // 12.1. Let prefix be the value of node's prefix attribute.
        $prefix = $element->prefix;
        // 12.2. Let candidate prefix be the result of retrieving a preferred prefix
        // string prefix from map given namespace ns.
        $candidatePrefix = null;
        if ($ns && ($prefix || $ns !== $localDefaultNamespace)) {
            $candidatePrefix = $this->retrievePreferredPrefix($ns, $prefix);
        }
        // 12.4. if candidate prefix is not null (a namespace prefix is defined which maps to ns), then:
        if ($candidatePrefix && $this->lookupNamespaceURI($candidatePrefix)) {
            // 12.4.1. Append to qualified name the concatenation of candidate prefix,
            // ":" (U+003A COLON), and node's localName.
            // 12.4.3. Append the value of qualified name to markup.
            $this->markup .= $this->formatStartTagOpen($candidatePrefix, $element->localName);
            $serializedPrefix = $candidatePrefix;
            // 12.4.2. If the local default namespace is not null (there exists a
            // locally-defined default namespace declaration attribute) and its value is
            // not the XML namespace, then let inherited ns get the value of local
            // default namespace unless the local default namespace is the empty string
            // in which case let it get null (the context namespace is changed to the
            // declared default, rather than this node's own namespace).
            if ($localDefaultNamespace !== Namespaces::XML) {
                $namespaceContext->inheritLocalDefaultNamespace($localDefaultNamespace);
            }
            return [$serializedPrefix, $ignoreNamespaceDefinition];
        }
        // 12.5. Otherwise, if prefix is not null, then:
        if ($prefix) {
            // 12.5.1. If the local prefixes map contains a key matching prefix, then
            // let prefix be the result of generating a prefix providing as input map,
            // ns, and prefix index
            if ($element->hasAttribute("xmlns:{$prefix}")) {
                $prefix = $this->generatePrefix($ns);
            } else {
                // 12.5.2. Add prefix to map given namespace ns.
                $this->addPrefix($prefix, $ns);
            }
            // 12.5.3. Append to qualified name the concatenation of prefix, ":" (U+003A COLON), and node's localName.
            // 12.5.4. Append the value of qualified name to markup.
            $this->markup .= $this->formatStartTagOpen($prefix, $element->localName);;
            $serializedPrefix = $prefix;
            // 12.5.5. Append the following to markup, in the order listed:
            $this->markup .= ' ' . $this->formatAttribute('xmlns', $prefix, $ns);
            // 12.5.5.7. If local default namespace is not null (there exists a
            // locally-defined default namespace declaration attribute), then let
            // inherited ns get the value of local default namespace unless the local
            // default namespace is the empty string in which case let it get null.
            $namespaceContext->inheritLocalDefaultNamespace($localDefaultNamespace);
            return [$serializedPrefix, $ignoreNamespaceDefinition];
        }
        // 12.6. Otherwise, if local default namespace is null, or local default
        // namespace is not null and its value is not equal to ns, then:
        if (!$localDefaultNamespace || $localDefaultNamespace !== $ns) {
            // 12.6.1. Set the ignore namespace definition attribute flag to true.
            $ignoreNamespaceDefinition = true;
            // 12.6.3. Let the value of inherited ns be ns.
            $namespaceContext->contextNamespace = $ns;
            // 12.6.4. Append the value of qualified name to markup.
            $this->markup .= $this->formatStartTagOpen($element->prefix, $element->localName);;
            // 12.6.5. Append the following to markup, in the order listed:
            $this->markup .= ' ' . $this->formatAttribute('', 'xmlns', $ns ?? '');
            return [$serializedPrefix, $ignoreNamespaceDefinition];
        }
        // 12.7. Otherwise, the node has a local default namespace that matches ns.
        // Append to qualified name the value of node's localName,
        // let the value of inherited ns be ns,
        // and append the value of qualified name to markup.
        $namespaceContext->contextNamespace = $ns;
        $this->markup .= $this->formatStartTagOpen($element->prefix, $element->localName);;
        return [$serializedPrefix, $ignoreNamespaceDefinition];
    }

    private function appendStartTagClose(Element $node)
    {
        if ($this->shouldSelfClose($node)) {
            $this->markup .= $node->isHTML ? ' />' : '/>';
            return;
        }
        $this->markup .= '>';
    }

    private function appendEndTag(Element $node, ?string $prefixOverride)
    {
        if ($this->shouldSelfClose($node)) {
            return;
        }
        $prefix = $prefixOverride ?: $node->prefix;
        $qualifiedName = $prefix ? "{$prefix}:{$node->localName}" : $node->localName;
        $this->markup .= sprintf('</%s>', $qualifiedName);
    }

    /**
     * Rules of self-closure
     * 1. No elements in an HTML document use the self-closing syntax.
     * 2. Elements w/ children never self-close because they use a separate end tag.
     * 3. HTML elements not listed in spec will close with a separate end tag.
     * 4. Other elements self-close.
     */
    private function shouldSelfClose(Element $element): bool
    {
        if ($element->_first) return false;
        if ($element->isHTML) {
            return isset(Elements::VOID_ELEMENTS[$element->localName]);
        }
        return true;
    }

    private function appendAttribute(Element $node, Attr $attr)
    {
        // https://w3c.github.io/DOM-Parsing/#serializing-an-element-s-attributes
        if ($this->requireWellFormed) {
            if (str_contains($attr->localName, ':')
                || !preg_match(QName::NAME_PATTERN, $attr->localName)
            ) {
                throw new SyntaxError(sprintf(
                    'Invalid attribute name: "%s"',
                    $attr->localName,
                ));
            }
        }
        // 3.3. Let attribute namespace be the value of attr's namespaceURI value.
        $attributeNamespace = $attr->namespaceURI;
        // 3.4. Let candidate prefix be null.
        $candidatePrefix = null;
        if (!$attributeNamespace) {
            if ($this->requireWellFormed && $attr->localName === 'xmlns') {
                throw new NamespaceError(
                    'Attribute having local name "xmlns" must be in the XMLNS namespace.'
                );
            }
            $this->markup .= ' ';
            $this->markup .= $this->formatAttribute($candidatePrefix, $attr->localName, $attr->_value);
            return;
        }
        // 3.5. If attribute namespace is not null, then run these sub-steps:
        // 3.5.1. Let candidate prefix be the result of retrieving a preferred
        // prefix string from map given namespace attribute namespace with preferred
        // prefix being attr's prefix value.
        $candidatePrefix = $this->retrievePreferredPrefix($attributeNamespace, $attr->prefix);
        // 3.5.2. If the value of attribute namespace is the XMLNS namespace, then run these steps:
        if ($attributeNamespace === Namespaces::XMLNS) {
            if ($attr->_value === Namespaces::XML) return;
            if (!$attr->prefix && $attr->localName !== 'xmlns') {
                $candidatePrefix = 'xmlns';
            }
            if ($this->requireWellFormed) {
                // 3.5.2.2
                if ($attr->_value === Namespaces::XMLNS) {
                    throw new NamespaceError('The XMLNS namespace is reserved.');
                }
                // 3.5.2.3
                if (!$attr->_value) {
                    throw new NamespaceError(
                        'Namespace prefix declarations cannot be used to undeclare a namespace'
                        . ' (use a default namespace declaration instead).'
                    );
                }
            }
        } else {
            // 3.5.3. Otherwise, the attribute namespace in not the XMLNS namespace.
            // Run these steps:
            if ($this->shouldAddNamespaceAttribute($attr, $candidatePrefix)) {
                if (!$candidatePrefix || $this->lookupNamespaceURI($candidatePrefix)) {
                    // 3.5.3.1. Let candidate prefix be the result of generating a prefix
                    // providing map, attribute namespace, and prefix index as input.
                    $candidatePrefix = $this->generatePrefix($attributeNamespace);
                    // 3.5.3.2. Append the following to result, in the order listed:
                    $this->markup .= ' ';
                    $this->markup .= $this->formatAttribute('xmlns', $candidatePrefix, $attributeNamespace);
                } else {
                    $this->appendNamespace($candidatePrefix, $attributeNamespace);
                }
            }
        }
        $this->markup .= ' ';
        $this->markup .= $this->formatAttribute($candidatePrefix ?? '', $attr->localName, $attr->_value);
    }

    private function shouldAddNamespaceAttribute(Attr $attr, ?string $candidatePrefix): bool
    {
        // Attributes without a prefix will need one generated for them,
        // and an xmlns attribute for that prefix.
        if (!$candidatePrefix) return true;
        // weak comparison is intentional so that null equals ''
        return $attr->namespaceURI != $this->lookupNamespaceURI($candidatePrefix);
    }

    private function appendNamespace(string $prefix, string $namespace)
    {
        $uri = $this->lookupNamespaceURI($prefix);
        // weak comparison is intentional so that null equals ''
        if ($uri != $namespace) {
            $this->addPrefix($prefix, $namespace);
            $this->markup .= ' ';
            if (!$prefix) {
                $this->markup .= $this->formatAttribute('', 'xmlns', $namespace);
            } else {
                $this->markup .= $this->formatAttribute('xmlns', $prefix, $namespace);
            }
        }
    }

    private function formatStartTagOpen(?string $prefix, string $localName): string
    {
        if ($prefix) return "<{$prefix}:{$localName}";
        return "<{$localName}";
    }

    private function formatAttribute(?string $prefix, string $localName, string $value): string
    {
        $name = $prefix ? "{$prefix}:{$localName}" : $localName;
        return sprintf('%s="%s"', $name, $this->serializeAttributeValue($value));
    }

    private function serializeAttributeValue(string $value): string
    {
        if (!$value) return '';
        if ($this->requireWellFormed) {
            if (!preg_match(self::VALID_TEXT, $value)) {
                throw new SyntaxError(sprintf(
                    'Attribute value contains illegal characters: "%s".',
                    $value,
                ));
            }
        }

        return strtr($value, [
            '&' => '&amp;',
            '"' => '&quot;',
            '<' => '&lt;',
            '>' => '&gt;',
            // https://github.com/w3c/DOM-Parsing/issues/59
            "\n" => '&#xA;',
            "\t" => '&#x9;',
            "\r" => '&#xD;',
        ]);
    }

    private function serializeCDATASection(CDATASection $node): string
    {
        if ($this->requireWellFormed && $node->_value) {
            if (str_contains($node->_value, ']]>')) {
                throw new SyntaxError('CDATA section contains "]]>".');
            }
            if (!preg_match(self::VALID_TEXT, $node->_value)) {
                throw new SyntaxError('CDATA section contains invalid characters.');
            }
        }
        return "<![CDATA[{$node->_value}]]>";
    }

    private function serializeComment(Comment $node): string
    {
        if ($this->requireWellFormed && $node->_value) {
            if (!preg_match(self::VALID_TEXT, $node->_value)
                || preg_match('/--|-$/', $node->_value)
            ) {
                throw new SyntaxError('Comment node contains invalid characters.');
            }
        }
        return "<!--{$node->_value}-->";
    }

    private function serializeDocumentType(DocumentType $node): string
    {
        if (!$node->name) return '';

        if ($this->requireWellFormed) {
            if ($node->publicId && !preg_match(self::VALID_DOCTYPE_PUBLIC_ID, $node->publicId)) {
                throw new SyntaxError('Doctype public ID contains invalid characters.');
            }
            if ($node->systemId && (!preg_match(self::VALID_DOCTYPE_SYSTEM_ID, $node->systemId))) {
                throw new SyntaxError('Doctype system ID contains invalid characters.');
            }
        }

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

    private function serializeProcessingInstruction(ProcessingInstruction $node): string
    {
        if ($this->requireWellFormed) {
            if (str_contains($node->target, ':')) {
                throw new SyntaxError('Processing instruction target contains invalid characters.');
            }
            if (strcasecmp($node->target, 'xml') === 0) {
                throw new SyntaxError('Processing instruction target cannot be "xml".');
            }
            if (str_contains($node->_value, '?>') || !preg_match(self::VALID_TEXT, $node->_value)) {
                throw new SyntaxError('Processing instruction data contains invalid characters.');
            }
        }
        return "<?{$node->target} {$node->_value}?>";
    }

    private function serializeDocument(Document $node): string
    {
        if ($this->requireWellFormed) {
            if (!$node->getDocumentElement()) {
                throw new InvalidStateError('Missing document element node.');
            }
        }
        if (!$node->_hasXmlDeclaration) {
            return '';
        }
        return sprintf(
            '<?xml version="%s" encoding="%s" standalone="%s" ?>',
            $node->_xmlVersion,
            $node->characterSet ?: 'UTF-8',
            $node->_xmlStandalone ? 'yes' : 'no',
        );
    }

    private function serializeText(Text $node): string
    {
        if (!$node->_value) return '';
        if ($this->requireWellFormed) {
            if (!preg_match(self::VALID_TEXT, $node->_value)) {
                throw new SyntaxError('Text node contains invalid characters.');
            }
        }
        return strtr($node->_value, [
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }
}
