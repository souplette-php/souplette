<?php declare(strict_types=1);

namespace Souplette\Dom\Serialization;

use Souplette\Dom\Attr;
use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;

class MarkupAccumulator
{
    private bool $asHTML;

    private string $markup = '';
    /** @var NamespaceContext[] */
    private array $namespaceStack = [];
    private int $prefixIndex = 1;

    public function __construct(
        private MarkupFormatterInterface $formatter,
        private ?NodeValidatorInterface $validator = null,
    ) {
        $this->asHTML = $formatter->getSerializationType() === SerializationType::HTML;
    }

    public function serialize(Node $root, bool $childrenOnly = false): string
    {
        $this->markup = '';
        $this->prefixIndex = 1;
        $this->namespaceStack = [new NamespaceContext()];
        $this->addPrefix('xml', Namespaces::XML);

        $this->serializeNode($root, $childrenOnly);
        return $this->markup;
    }

    private function serializeNode(Node $node, bool $childrenOnly = false)
    {
        if (!$node instanceof Element) {
            if (!$childrenOnly) {
                $this->appendStartMarkup($node);
            }
            for ($child = $node->_first; $child; $child = $child->_next) {
                $this->serializeNode($child, false);
            }
            return;
        }

        if ($this->shouldIgnoreElement($node)) return;
        $this->pushNamespaces($node);
        // TODO
        $prefixOverride = null;
        if (!$childrenOnly) {
            $prefixOverride = $this->appendElement($node);
        }
        $hasEndTag = !($this->asHTML && $this->elementCannotHaveEndTag($node));
        if ($hasEndTag) {
            // TODO: template->content
            for ($child = $node->_first; $child; $child = $child->_next) {
                $this->serializeNode($child, false);
            }
            if (!$childrenOnly) {
                $this->appendEndTag($node, $prefixOverride);
            }
        }
        $this->popNamespaces($node);
    }

    protected function shouldIgnoreElement(Element $element): bool
    {
        return false;
    }

    protected function shouldIgnoreAttribute(Element $element, Attr $attr): bool
    {
        return false;
    }

    private function addPrefix(string $prefix, string $namespace): void
    {
        $context = end($this->namespaceStack);
        $context->add($prefix, $namespace);
    }

    private function lookupNamespaceURI(?string $prefix): ?string
    {
        $context = end($this->namespaceStack);
        return $context->lookupNamespaceURI($prefix);
    }

    private function pushNamespaces(Element $element)
    {
        if ($this->asHTML) return;
        $context = end($this->namespaceStack);
        $this->namespaceStack[] = clone $context;
    }

    private function popNamespaces(Element $element)
    {
        if ($this->asHTML) return;
        array_pop($this->namespaceStack);
    }

    /**
     * Serialize a Node, without its children and its end tag.
     */
    private function appendStartMarkup(Node $node)
    {
        switch ($node->nodeType) {
            case Node::TEXT_NODE:
                $this->validator?->validateText($node);
                $this->markup .= $this->formatter->formatText($node);
                break;
            case Node::ELEMENT_NODE:
                break;
            case Node::ATTRIBUTE_NODE:
                $this->validator?->validateAttributeValue($node->_value);
                $this->markup .= $this->formatter->formatAttributeValue($node->_value);
                break;
            default:
                $this->validator?->validateNode($node);
                $this->markup .= $this->formatter->formatStartMarkup($node);
                break;
        }
    }

    /**
     * Returns serialized prefix. It should be passed to AppendEndTag()
     */
    private function appendElement(Element $node)
    {
        [$serializedPrefix, $ignoreNamespaceDefinition] = $this->appendStartTagOpen($node);
        foreach ($node->_attrs as $attr) {
            if ($ignoreNamespaceDefinition
                && $attr->namespaceURI === Namespaces::XMLNS
                && !$attr->prefix
            ) {
                // Drop xmlns= only if it's inconsistent with element's namespace.
                // https://github.com/w3c/DOM-Parsing/issues/47
                if ($attr->_value !== $node->namespaceURI) continue;
            }
            if (!$this->shouldIgnoreAttribute($node, $attr)) {
                $this->appendAttribute($node, $attr);
            }
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
        if ($this->asHTML) {
            $this->markup .= $this->formatter->formatStartTagOpen($element->prefix, $element->localName);
            return [$serializedPrefix, false];
        }
        // https://w3c.github.io/DOM-Parsing/#xml-serializing-an-element-node
        $this->validator?->validateElement($element);
        $namespaceContext = end($this->namespaceStack);
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
            $this->markup .= $this->formatter->formatStartTagOpen(null, $element->localName);
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
            $this->markup .= $this->formatter->formatStartTagOpen($candidatePrefix, $element->localName);
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
            $this->markup .= $this->formatter->formatStartTagOpen($prefix, $element->localName);;
            $serializedPrefix = $prefix;
            // 12.5.5. Append the following to markup, in the order listed:
            $this->markup .= ' ' . $this->formatter->formatAttribute('xmlns', $prefix, $ns);
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
            $this->markup .= $this->formatter->formatStartTagOpen($element->prefix, $element->localName);;
            // 12.6.5. Append the following to markup, in the order listed:
            $this->markup .= ' ' . $this->formatter->formatAttribute('', 'xmlns', $ns ?? '');
            return [$serializedPrefix, $ignoreNamespaceDefinition];
        }
        // 12.7. Otherwise, the node has a local default namespace that matches ns.
        // Append to qualified name the value of node's localName,
        // let the value of inherited ns be ns,
        // and append the value of qualified name to markup.
        $namespaceContext->contextNamespace = $ns;
        $this->markup .= $this->formatter->formatStartTagOpen($element->prefix, $element->localName);;
        return [$serializedPrefix, $ignoreNamespaceDefinition];
    }

    private function appendStartTagClose(Element $node)
    {
        $this->markup .= $this->formatter->formatStartTagClose($node);
    }

    private function appendEndTag(Element $node, ?string $prefixOverride)
    {
        $this->markup .= $this->formatter->formatEndMarkup($node, $node->localName, $prefixOverride ?: $node->prefix);
    }

    private function appendAttribute(Element $node, Attr $attr)
    {
        if ($this->asHTML) {
            $this->markup .= ' ' . $this->formatter->formatAttributeAsHTML($attr, $attr->_value);
        } else {
            $this->validator?->validateAttribute($attr);
            $this->appendAttributeAsXmlWithNamespace($node, $attr, $attr->_value);
        }
    }

    private function appendAttributeAsXmlWithNamespace(Element $node, Attr $attr, string $value)
    {
        // https://w3c.github.io/DOM-Parsing/#serializing-an-element-s-attributes
        // 3.3. Let attribute namespace be the value of attr's namespaceURI value.
        $attributeNamespace = $attr->namespaceURI;
        // 3.4. Let candidate prefix be null.
        $candidatePrefix = null;
        if (!$attributeNamespace) {
            $this->markup .= ' ';
            $this->markup .= $this->formatter->formatAttribute($candidatePrefix ?? '', $attr->localName, $value);
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
                    $this->markup .= $this->formatter->formatAttribute('xmlns', $candidatePrefix, $attributeNamespace);
                } else {
                    $this->appendNamespace($candidatePrefix, $attributeNamespace);
                }
            }
        }
        $this->markup .= ' ';
        $this->markup .= $this->formatter->formatAttribute($candidatePrefix ?? '', $attr->localName, $value);
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
                $this->markup .= $this->formatter->formatAttribute('', 'xmlns', $namespace);
            } else {
                $this->markup .= $this->formatter->formatAttribute('xmlns', $prefix, $namespace);
            }
        }
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
     * https://w3c.github.io/DOM-Parsing/#dfn-retrieving-a-preferred-prefix-string
     */
    private function retrievePreferredPrefix(string $ns, ?string $preferredPrefix): ?string
    {
        $nsForPreferred = $this->lookupNamespaceURI($preferredPrefix);
        // Preserve the prefix if the prefix is used in the scope and the namespace
        // for it is matches to the node's one.
        // This is equivalent to the following step in the specification:
        // 2.1. If prefix matches preferred prefix, then stop running these steps and
        // return prefix.

        // weak comparison is intentional so that null equals ''
        if ($preferredPrefix && $nsForPreferred && $ns == $nsForPreferred) {
            return $preferredPrefix;
        }
        $candidates = end($this->namespaceStack)->candidatePrefixes($ns);
        // Get the last effective prefix.
        //
        // <el1 xmlns:p="U1" xmlns:q="U1">
        //   <el2 xmlns:q="U2">
        //    el2.setAttributeNS(U1, 'n', 'v');
        // We should get 'p'.
        //
        // <el1 xmlns="U1">
        //  el1.setAttributeNS(U1, 'n', 'v');
        // We should not get '' for attributes.
        foreach (array_reverse($candidates) as $candidate) {
            $nsForCandidate = $this->lookupNamespaceURI($candidate);
            // weak comparison is intentional so that null equals ''
            if ($nsForCandidate == $ns) return $candidate;
        }
        // No prefixes for |ns|.
        // Preserve the prefix if the prefix is not used in the current scope.
        if ($preferredPrefix && !$nsForPreferred) return $preferredPrefix;
        // If a prefix is not specified, or the prefix is mapped to a
        // different namespace, we should generate new prefix.
        return null;
    }
}
