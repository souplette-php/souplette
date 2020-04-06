# HTML
- [ ] Tokenizer character buffering to reduce the number of token objects ?
- [ ] Check places where we could use chars_(while|until) instead of consuming one byte at a time.
    Typically when the spec says « Ignore the character »
- [ ] TreeBuilder namespace handling
    - [ ] Use `DOMElement->tagName` instead of `localName` where adequate
- [ ] TreeBuilder parse errors
- [ ] Template elements
    - [x] Dom Implementation
    - [ ] Tree Builder rules
    - [ ] Serialization
- [ ] Serializer
    - [x] boolean attributes
- [ ] DOM
    - [ ] refactor TokenList to:
        1. work even when using `$element->getAttributeNode('class')->value = 'foo'`
        2. be even more lazy (no instantiation while parsing)
        3. maybe use WeakRef ?
    - [ ] HTMLCollection

## Notes

### Namespaces:

#### Elements
Since we always namespace elements and therefore use `DOMDocument::createElementNS`,
an element with a tag name like "abc:xyz" will always end up with the prefix "abc" and local name "xyz".
This is different from the standard HTML DOM implementation where it would end up with
the prefix "" and the local name "abc:xyz", although it would still have the html namespace.
Therefore, unknown HTML elements in this form should always be referred to by their `$tagName` or `$nodeName` property.


#### Attributes:
On html elements, an attribute in the form foo:bar should have the localName "foo:bar", no prefix and no namespace.
DOMElement::setAttribute() is fine with that, except for the xml prefix, e.g.
$element->setAttribute('xml:lang', 'en') will return an attribute node with the
localName "lang", prefix "xml" and XML namespace.
Creating attribute nodes via $document->createAttribute('xml:lang') is a workaround, but then
$attribute->value = "foo&" fails because of wrong entity parsing...
ATM it seems like a minor gotcha to have "xml:*" prefixed attributes in the XML namespace.

### CDATA:
the tokenizer needs to know about the parser's adjustedCurrentNode.
ATM we update it's value twice for each invocation of the tree construction dispatcher.
Since the information is needed only in one tokenizer state, the tokenizer may act on the parser
to retrieve the information. We should investigate that.

# CSS

- [ ] Syntax Module Level 3
    - [x] Tokenizer
    - [x] Parser
- [ ] Selectors Module Level 4
    - [ ] Parser
    - [ ] Nodes
    - [ ] XPath converter
    - [ ] Query executor
- [ ] CSSOM
- [ ] CSS Values and Units Module Level 4
- [ ] Media Queries Module
- [ ] Paged Media Module
- [ ] CSS Typed OM
