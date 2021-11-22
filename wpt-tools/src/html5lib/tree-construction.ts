import {parseDataFile} from './data-file'
import {Namespace, NodeType, Prefixes, ReversePrefixes} from '../util/dom'

export interface TreeConstructionTest {
  id: string
  line: number
  input: string
  output: string
  errors: string[]
  contextElement?: {localName: string, namespaceURI: string}
}

interface ResultNode {
  nodeType: NodeType
  nodeName: string
  children: any[]
}

interface CharacterDataResult extends ResultNode {
  nodeType: NodeType.COMMENT | NodeType.TEXT | NodeType.CDATA
  data: string
}

type AttributeResult = {name: string, value: string, namespaceURI: Namespace}

interface ElementResult extends ResultNode {
  nodeType: NodeType.ELEMENT
  namespaceURI: Namespace
  attributes: AttributeResult[]
  children: Array<ElementResult|CharacterDataResult>
  content?: FragmentResult
}

interface DocumentTypeResult extends ResultNode {
  nodeType: NodeType.DOCUMENT_TYPE
  publicId: string
  systemId: string
}

interface DocumentResult extends ResultNode {
  nodeType: NodeType.DOCUMENT
  children: Array<DocumentTypeResult|ElementResult|CharacterDataResult>
}

interface FragmentResult extends ResultNode {
  nodeType: NodeType.DOCUMENT_FRAGMENT
  children: Array<ElementResult|CharacterDataResult>
}

type AnyResult = CharacterDataResult
  | ElementResult
  | DocumentTypeResult
  | DocumentResult
  | FragmentResult

export type TreeConstructionResult = FragmentResult|DocumentResult


export function parseTreeConstructionTest(rawData: string) {
  return parseDataFile(rawData)
    .map(({id, line, sections}, index) => {
      const input = sections['data'].join('\n')
      let contextElement: TreeConstructionTest['contextElement']
      if ('document-fragment' in sections) {
        const context = sections['document-fragment'].join().trim().split(' ')
        if (context.length === 2) {
          const [prefix, localName] = context
          contextElement = {localName, namespaceURI: Prefixes[prefix] ?? Namespace.HTML}
        } else {
          contextElement = {localName: context[0], namespaceURI: Namespace.HTML}
        }
      }
      return {
        id: index.toString(),
        line,
        input,
        errors: sections['errors'],
        contextElement,
        output: [contextElement ? '#document-fragment' : '#document']
          .concat(sections['document'])
          .join('\n'),
      }
    })
}


export function serializeResult(result: TreeConstructionResult): string {
  const output: string[] = []
  serializeResultNode(result, output, 0)
  return output.join('\n')
}

function serializeResultNode(node: AnyResult, output: string[], depth: number = 0): void {
  const indent = depth ? `| ${'  '.repeat(depth - 1)}` : ''
  switch (node.nodeType) {
    case NodeType.DOCUMENT:
    case NodeType.DOCUMENT_FRAGMENT:
      output.push(node.nodeName)
      break
    case NodeType.DOCUMENT_TYPE: {
      const {nodeName, publicId, systemId} = node
      const type = [nodeName, publicId, systemId].filter(Boolean).join(' ')
      output.push(`${indent}<!DOCTYPE ${type}>`)
      break
    }
    case NodeType.COMMENT:
      output.push(`${indent}<!-- ${node.data} -->`)
      break
    case NodeType.TEXT:
      output.push(`${indent}"${node.data}"`)
      break
    case NodeType.ELEMENT: {
      const tagName = serializeTagName(node)
      output.push(`${indent}<${tagName}>`)
      const attrs = node.attributes
        .map(attr => ({
          ...attr,
          name: serializeAttributeName(node, attr),
        }))
        .sort((a, b) => a.name.localeCompare(b.name))
        .map(({name, value}) => `${indent}  ${name}="${value}"`)
      output.push(...attrs)
      if (tagName === 'template' && node.content) {
        output.push(`${indent}  content`)
        serializeResultNode(node.content, output, depth + 1)
      }
      break
    }
    default:
      break
  }
  for (const child of node.children) {
    serializeResultNode(child, output, depth + 1)
  }
}

function serializeTagName(node: ElementResult) {
  const {nodeName, namespaceURI} = node
  if (namespaceURI && namespaceURI !== Namespace.HTML) {
    return `${ReversePrefixes[namespaceURI]} ${nodeName.toLowerCase()}`
  }
  return nodeName.toLowerCase()
}

function serializeAttributeName(node: ElementResult, attr: AttributeResult): string {
  if (node.namespaceURI === Namespace.HTML && attr.namespaceURI === Namespace.HTML) {
    return attr.name
  }
  if (attr.namespaceURI) {
    return `${ReversePrefixes[attr.namespaceURI]} ${attr.name}`
  }
  return attr.name
}
