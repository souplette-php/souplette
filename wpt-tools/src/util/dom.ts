
export const enum NodeType {
  ELEMENT = 1,
  ATTRIBUTE = 1,
  TEXT = 3,
  CDATA = 4,
  ENTITY_REFERENCE = 5,
  ENTITY = 6,
  COMMENT = 8,
  DOCUMENT = 9,
  DOCUMENT_TYPE = 10,
  DOCUMENT_FRAGMENT = 11,
  NOTATION = 12,
}

export const enum Namespace {
  HTML = 'http://www.w3.org/1999/xhtml',
  SVG = 'http://www.w3.org/2000/svg',
  MATHML = 'http://www.w3.org/1998/Math/MathML',
  XLINK = 'http://www.w3.org/1999/xlink',
  XML = 'http://www.w3.org/XML/1998/namespace',
  XMLNS = 'http://www.w3.org/2000/xmlns/',
}

export const Prefixes: Record<string, Namespace> = {
  html: Namespace.HTML,
  svg: Namespace.SVG,
  math: Namespace.MATHML,
  xlink: Namespace.XLINK,
  xml: Namespace.XML,
  xmlns: Namespace.XMLNS,
}

export const ReversePrefixes = Object.entries(Prefixes)
  .reduce<Record<string, string>>((x, [k, v]) => {
    x[v.toString()] = k
    return x
  }, {})
