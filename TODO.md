- [x] Tokenizer States
- [ ] Tokenizer Whitespace tokens
- [ ] Check places where we could use chars_(while|until) instead of consuming one byte at a time.
    Typically when the spec says « Ignore the character »
- [x] TreeBuilder States
- [ ] TreeBuilder fragment case
- [ ] TreeBuilder namespace handling
- [ ] TreeBuilder parse errors
- [x] Encoding sniffing
- [x] Encoding switching

Memos:
    - Attributes:
        On html elements, an attribute in the form foo:bar should have the localName "foo:bar", no prefix and no namespace.
        DOMElement::setAttribute() is fine with that, except for the xml prefix, e.g.
        $element->setAttribute('xml:lang', 'en') will return an attribute node with the
        localName "lang", prefix "xml" and XML namespace.
        Creating attribute nodes via $document->createAttribute('xml:lang') is a workaround, but then
        $attribute->value = "foo&" fails because of wrong entity parsing...
        ATM it seems like a minor gotcha to have "xml:*" prefixed attributes in the XML namespace.
