<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Attr;
use Souplette\Dom\Comment;
use Souplette\Dom\Document;
use Souplette\Dom\DocumentFragment;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\InvalidStateError;
use Souplette\Dom\Internal\BaseNode;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Dom\Traversal\NodeTraversal;
use Souplette\Xml\Serializer\NamespacePrefixMap as PrefixMap;

/**
 * @see https://w3c.github.io/DOM-Parsing/#xml-serialization
 */
final class Serializer extends BaseNode
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

    private const VOID_ELEMENTS = [
        'area' => true,
        'base' => true,
        'basefont' => true,
        'bgsound' => true,
        'br' => true,
        'col' => true,
        'embed' => true,
        'frame' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'keygen' => true,
        'link' => true,
        'menuitem' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true,
        'wbr' => true,
    ];

    private int $prefixIndex = 1;
    private bool $requireWellFormed = true;

    /**
     * @throws InvalidStateError
     */
    public function serialize(Node $node, bool $requireWellFormed = true): string
    {
        $this->requireWellFormed = $requireWellFormed;
        $this->prefixIndex = 1;
        $prefixMap = new PrefixMap();
        $prefixMap->add('xml', Namespaces::XML);

        try {
            return $this->serializeNode($node, null, $prefixMap);
        } catch (\Throwable $err) {
            throw new InvalidStateError($err->getMessage(), $err);
        }
    }

    /**
     * @throws DomException
     */
    private function serializeNode(Node $node, ?string $namespace, PrefixMap $prefixMap): string
    {
        if ($node instanceof Element) {
            return $this->serializeElement($node, $namespace, $prefixMap);
        }
        if ($node instanceof Document) {
            return $this->serializeDocument($node, $namespace, $prefixMap);
        }
        if ($node instanceof Comment) {
            return $this->serializeComment($node);
        }
        if ($node instanceof Text) {
            return $this->serializeText($node);
        }
        if ($node instanceof DocumentFragment) {
            return $this->serializeDocumentFragment($node, $namespace, $prefixMap);
        }
        if ($node instanceof DocumentType) {
            return $this->serializeDocumentType($node);
        }
        if ($node instanceof ProcessingInstruction) {
            return $this->serializeProcessingInstruction($node);
        }
        if ($node instanceof Attr) {
            return '';
        }

        throw new \TypeError(sprintf('Unsupported node type "%s".', get_debug_type($node)));
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-an-element-node
     * @throws DomException
     */
    private function serializeElement(
        Element $node,
        ?string $namespace,
        PrefixMap $prefixMap,
    ): string {
        // 1.
        if ($this->requireWellFormed && (
            str_contains($node->localName, ':')
            || !preg_match(QName::NAME_PATTERN, $node->localName)
        )) {
            throw new DomException('Element local name contains invalid characters.');
        }
        // 2, 3, 4, 5, 6, 7, 8, 9
        $markup = '<';
        $qualifiedName = '';
        $skipEndTag = false;
        $ignoreNamespaceDefinitionAttribute = false;
        $map = $prefixMap->copy();
        $localPrefixMap = [];
        $localDefaultNamespace = $this->recordNamespaceInformation($node, $map, $localPrefixMap);
        $inheritedNamespace = $namespace;
        $ns = $node->namespaceURI;
        // 11.
        if ($ns === $inheritedNamespace) {
            // 11.1
            if ($localDefaultNamespace !== null) $ignoreNamespaceDefinitionAttribute = true;
            // 11.2
            if ($ns === Namespaces::XML) {
                $qualifiedName .= "xml:{$node->localName}";
            } else {
                // 11.3
                $qualifiedName .= $node->localName;
            }
            // 11.4
            $markup .= $qualifiedName;
        } else {
            // 12.1
            $prefix = $node->prefix;
            // 12.2
            $candidatePrefix = $map->retrievePreferredPrefix($prefix, $ns);
            // 12.3
            if ($prefix === 'xmlns') {
                // 12.3.1
                if ($this->requireWellFormed) {
                    throw new DomException('Element has an xmlns prefix.');
                }
                // 12.3.2
                $candidatePrefix = $prefix;
            }
            // 12.4
            if ($candidatePrefix !== null) {
                // 12.4.1
                $qualifiedName .= "{$candidatePrefix}:{$node->localName}";
                // 12.4.2
                if ($localDefaultNamespace !== null && $localDefaultNamespace !== Namespaces::XML) {
                    $inheritedNamespace = $localDefaultNamespace ?: null;
                }
                // 12.4.3
                $markup .= $qualifiedName;
            } else if ($prefix !== null) {
                // 12.5.1
                if (isset($localPrefixMap[$prefix])) {
                    $prefix = $this->generatePrefix($map, $ns);
                }
                // 12.5.2
                $map->add($prefix, $ns);
                // 12.5.3
                $qualifiedName .= "{$prefix}:{$node->localName}";
                // 12.5.4
                $markup .= $qualifiedName;
                // 12.5.5
                $markup .= sprintf(' xmlns:%s="%s"', $prefix, $this->serializeAttributeValue($ns));
                if ($localDefaultNamespace !== null) {
                    $inheritedNamespace = $localDefaultNamespace ?: null;
                }
            } else if ($localDefaultNamespace === null || $localDefaultNamespace !== $ns) {
                // 12.6.1
                $ignoreNamespaceDefinitionAttribute = true;
                $qualifiedName .= $node->localName;
                $inheritedNamespace = $ns;
                $markup .= $qualifiedName;
                $markup .= sprintf(' xmlns="%s"', $this->serializeAttributeValue($ns));
            } else {
                // 12.7
                $qualifiedName .= $node->localName;
                $inheritedNamespace = $ns;
                $markup .= $qualifiedName;
            }
        }
        // 13.
        $markup .= $this->serializeAttributes($node, $map, $localPrefixMap, $ignoreNamespaceDefinitionAttribute);
        // 14.
        if ($ns === Namespaces::HTML && !$node->hasChildNodes() && isset(self::VOID_ELEMENTS[$node->localName])) {
            $markup .= ' /';
            $skipEndTag = true;
        }
        // 15.
        if ($ns !== Namespaces::HTML && !$node->hasChildNodes()) {
            $markup .= '/';
            $skipEndTag = true;
        }
        // 16.
        $markup .= '>';
        // 17.
        if ($skipEndTag) {
            return $markup;
        }
        // 18. TODO: template elements
        // 19.
        foreach (NodeTraversal::childrenOf($node) as $child) {
            $markup .= $this->serializeNode($child, $inheritedNamespace, $map);
        }
        // 20.
        $markup .= "</{$qualifiedName}>";
        return $markup;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#serializing-an-element-s-attributes
     * @throws DomException
     */
    private function serializeAttributes(
        Element $node,
        PrefixMap $map,
        array $localPrefixMap,
        bool $ignoreNamespaceDefinitionAttribute,
    ): string {
        $result = '';
        $localNameSet = [];
        // 3.
        foreach ($node->attrs as $attr) {
            // 3.1
            if ($this->requireWellFormed && isset($localNameSet[$attr->namespaceURI][$attr->localName])) {
                throw new DomException('Duplicate attribute.');
            }
            // 3.2
            $localNameSet[$attr->namespaceURI][$attr->localName] = true;
            // 3.3
            $attributeNamespace = $attr->namespaceURI;
            // 3.4
            $candidatePrefix = null;
            // 3.5
            if ($attributeNamespace !== null) {
                // 3.5.1
                $candidatePrefix = $map->retrievePreferredPrefix($attr->prefix, $attributeNamespace);
                // 3.5.2
                if ($attributeNamespace === Namespaces::XMLNS) {
                    // 3.5.2.1
                    if ($attr->value === Namespaces::XML) continue;
                    if ($attr->prefix === null && $ignoreNamespaceDefinitionAttribute) continue;
                    if ($attr->prefix && (
                        $attr->value !== $localPrefixMap[$attr->localName] ?? null
                    )) {
                        continue;
                    }
                    // TODO: WTF???
                    // 3.5.2.2
                    if ($this->requireWellFormed && $attr->value === Namespaces::XMLNS) {
                        throw new DomException('The XMLNS namespace is reserved.');
                    }
                    // 3.5.2.3
                    if ($this->requireWellFormed && $attr->value === '') {
                        throw new DomException(
                            'Namespace prefix declarations cannot be used to undeclare a namespace'
                            . ' (use a default namespace declaration instead).'
                        );
                    }
                    // 3.5.2.4
                    if ($attr->prefix === 'xmlns') $candidatePrefix = 'xmlns';
                } else {
                    // 3.5.3.1
                    //$candidatePrefix = $attr->lookupPrefix($attributeNamespace);
                    $candidatePrefix = $this->generatePrefix($map, $attributeNamespace);
                    // 3.5.3.2
                    $result .= sprintf(
                        ' xmlns:%s="%s"',
                        $candidatePrefix,
                        $this->serializeAttributeValue($attributeNamespace),
                    );
                }
            }
            // 3.6
            $result .= ' ';
            // 3.7
            if ($candidatePrefix) $result .= "{$candidatePrefix}:";
            // 3.8
            if ($this->requireWellFormed && (
                str_contains($attr->localName, ':')
                || !preg_match(QName::NAME_PATTERN, $attr->localName)
                || ($attr->localName === 'xmlns' && $attributeNamespace === null)
            )) {
                throw new DomException(sprintf(
                    'Invalid attribute name: "%s"',
                    $attr->localName,
                ));
            }
            // 3.9
            $result .= sprintf(
                '%s="%s"',
                $attr->localName,
                $this->serializeAttributeValue($attr->value),
            );
        }
        // 4.
        return $result;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-serializing-an-attribute-value
     * @throws DomException
     */
    private function serializeAttributeValue(string $value): string
    {
        if (!$value) return '';
        if ($this->requireWellFormed && !preg_match(self::VALID_TEXT, $value)) {
            throw new DomException('Attribute value contains illegal characters.');
        }

        return strtr($value, [
            '&' => '&amp;',
            '"' => '&quot;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#xml-serializing-a-document-node
     * @throws DomException
     */
    private function serializeDocument(
        Document $node,
        ?string $namespace,
        PrefixMap $prefixMap,
    ): string {
        if ($this->requireWellFormed && !$node->getDocumentElement()) {
            throw new DomException('Missing document element node.');
        }

        $markup = '';
        foreach (NodeTraversal::childrenOf($node) as $child) {
            $markup .= $this->serializeNode($child, $namespace, $prefixMap);
        }

        return $markup;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-a-comment-node
     * @throws DomException
     */
    private function serializeComment(Comment $node): string
    {
        $markup = $node->value;
        if ($this->requireWellFormed && (
            !preg_match(self::VALID_TEXT, $markup)
            || preg_match('/--|-$/', $markup)
        )) {
            throw new DomException('Comment node contains invalid characters.');
        }

        return "<!--{$markup}-->";
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-a-text-node
     * @throws DomException
     */
    private function serializeText(Text $node): string
    {
        $markup = $node->value;
        if (!$markup) return '';
        if ($this->requireWellFormed && !preg_match(self::VALID_TEXT, $markup)) {
            throw new DomException('Text node contains invalid characters.');
        }

        return strtr($markup, [
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-a-documentfragment-node
     * @throws DomException
     */
    private function serializeDocumentFragment(DocumentFragment $node, ?string $namespace, PrefixMap $prefixMap): string
    {
        $markup = '';
        foreach (NodeTraversal::childrenOf($node) as $child) {
            $markup .= $this->serializeNode($child, $namespace, $prefixMap);
        }

        return $markup;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-a-documenttype-node
     */
    private function serializeDocumentType(DocumentType $node): string {
        if ($this->requireWellFormed) {
            if ($node->publicId && !preg_match(self::VALID_DOCTYPE_PUBLIC_ID, $node->publicId)) {
                throw new DomException('Doctype public ID contains invalid characters.');
            }
            if ($node->systemId && (!preg_match(self::VALID_DOCTYPE_SYSTEM_ID, $node->systemId))) {
                throw new DomException('Doctype system ID contains invalid characters.');
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

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-xml-serializing-a-processinginstruction-node
     * @throws DomException
     */
    private function serializeProcessingInstruction(ProcessingInstruction $node): string {
        $data = $node->value;
        if ($this->requireWellFormed) {
            if (str_contains($node->target, ':') || strcasecmp($node->target, 'xml') === 0) {
                throw new DomException('Processing instruction target contains invalid characters.');
            }
            if (str_contains($data, '?>') || !preg_match(self::VALID_TEXT, $data)) {
                throw new DomException('Processing instruction data contains invalid characters.');
            }
        }

        return "<?{$node->target} {$data}?>";
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-recording-the-namespace-information
     */
    private function recordNamespaceInformation(Element $element, PrefixMap $map, array &$localMap): ?string
    {
        $defaultNamespace = null;
        foreach ($element->attrs as $attr) {
            if ($attr->namespaceURI === Namespaces::XMLNS) {
                if (!$attr->prefix) {
                    $defaultNamespace = $attr->value;
                    continue;
                }
                $prefixDefinition = $attr->localName;
                $namespaceDefinition = $attr->value;
                if ($namespaceDefinition === Namespaces::XML) {
                    continue;
                }
                if (!$namespaceDefinition) $namespaceDefinition = null;
                if ($map->has($prefixDefinition, $namespaceDefinition)) {
                    continue;
                }
                $map->add($prefixDefinition, $namespaceDefinition);
                $localMap[$prefixDefinition] = $namespaceDefinition ?? '';
            }
        }
        return $defaultNamespace;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#generating-namespace-prefixes
     */
    private function generatePrefix(PrefixMap $map, string $newNamespace): string
    {
        $prefix = sprintf('ns%d', $this->prefixIndex++);
        $map->add($prefix, $newNamespace);
        return $prefix;
    }
}
