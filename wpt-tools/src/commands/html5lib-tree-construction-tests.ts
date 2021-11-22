import {readdir, readFile, stat, writeFile} from 'fs/promises'
import {resolve as resolvePath, relative as relativePath} from 'path'

import {Argv} from 'yargs'
import puppeteer, {Browser, Page} from 'puppeteer'
import async from 'async'

import project from '../util/project'
import {
  parseTreeConstructionTest,
  serializeResult,
  TreeConstructionResult,
  TreeConstructionTest,
} from '../html5lib/tree-construction'

export const command = 'test:tree-construction [files...]'

export const describe = 'Runs the WPT tree construction tests.'

interface Arguments {
  files: string[]
  single?: string
  stopOnFailure: boolean
  generateXfails: boolean
  verbose: boolean
  jobs: number
}

type Options = Omit<Arguments, 'files' | 'single'>
type XFails = Array<{file: string, test: TreeConstructionTest}>

export const builder = (yargs: Argv) => {
  yargs
    .option('single', {
      alias: 's',
      describe: 'Runs a single test inside the first provided data file.',
      type: 'string',
    })
    .option('stop-on-failure', {
      describe: 'Stops execution on the first failed test.',
      type: 'boolean',
    })
    .option('verbose', {
      alias: 'v',
      type: 'boolean',
    })
    .option('generate-xfails', {
      alias: 'x',
      describe: 'Generates a JSON file of tests that failed for use in Souplette own unit tests.',
      conflicts: ['single', 'stop-on-failure'],
      type: 'boolean',
    })
    .option('jobs', {
      alias: 'j',
      describe: 'Number of parallel processes to run.',
      type: 'number',
      default: 4,
    })
}

export const handler = async (args: Arguments) => {
  let {single, files, ...options} = args
  const root = await project.root()

  const browser = await puppeteer.launch()
  const version = await browser.version()

  if (single) {
    await runSingle(browser, files[0], single, options)
    await browser.close()
    return
  }

  if (args.generateXfails || !files.length) {
    files = [
      resolvePath(root, 'tests/resources/html5lib-tests/tree-construction'),
    ]
  }
  const {stats, failures} = await runMultiple(browser, files, options)
  await browser.close()

  console.log(stats)
  if (args.generateXfails) {
    await generateXFails(failures, version)
  }
}

function logFailure(file: string, test: TreeConstructionTest, output: string, options: Options) {
  console.log(`FAIL: Test n°${test.id} on line ${test.line} of ${file}`)
  if (options.stopOnFailure || options.verbose) {
    console.log(`Test input: ${test.input}\n`)
    if (test.contextElement) {
      const {localName, namespaceURI} = test.contextElement
      console.log(`Context element: <${localName} xmlns="${namespaceURI}">\n`)
    }
    console.log(output)
    console.error(`EXPECTED:`)
    console.log(test.output)
  }
}

async function generateXFails(failures: XFails, chromeVersion: string) {
  const root = await project.root()
  const resourcePath = resolvePath(root, 'tests/resources')
  const testsPath = resolvePath(resourcePath, 'html5lib-tests/tree-construction')
  const xfails = failures.map(({file, test: {id, line}}) => ({
    file: relativePath(testsPath, file),
    id,
    line,
    browser: chromeVersion,
  })).sort((a, b) => {
    let result = a.file.localeCompare(b.file)
    if (result === 0) a.id.localeCompare(b.id)
    return result
  })
  const payload = JSON.stringify(xfails, null, 2)
  const outFile = resolvePath(resourcePath, 'xfails/wpt-tree-construction.json')
  await writeFile(outFile, payload)
}

async function runMultiple(browser: Browser, paths: string[], options: Options) {
  const stats = {
    tests: 0,
    failed: 0,
    passed: 0,
  }
  const failures: XFails = []
  for await (const fileName of collectFiles(paths)) {
    const tests = await parseTestFile(fileName)
    stats.tests += tests.length
    await async.eachLimit(tests, options.jobs, async (test) => {
      const page = await browser.newPage()
      const result = await runTest(page, test)
      const output = serializeResult(result)
      if (output !== test.output) {
        stats.failed++
        failures.push({
          file: fileName,
          test,
        })
        logFailure(fileName, test, output, options)
        if (options.stopOnFailure) {
          process.exit(2)
        }
      } else {
        stats.passed++
      }
      await page.close()
    })
  }
  return {stats, failures}
}

async function runSingle(browser: Browser, fileName: string, testId: string, options: Options) {
  const tests = await parseTestFile(fileName)
  const test = tests.find(test => test.id === testId)
  if (!test) {
    console.error(`Could not find test with id "${testId}" in ${fileName}.`)
    return
  }
  console.log(`Running test n°${testId} on line ${test.line}`)
  const page = await browser.newPage()
  const result = await runTest(page, test)
  const output = serializeResult(result)
  if (output !== test.output) {
    logFailure(fileName, test, output, options)
  } else {
    console.log('PASS')
  }
}

async function runTest(page: Page, test: TreeConstructionTest) {
  if (!test.contextElement) {
    await page.setContent(test.input, {waitUntil: 'domcontentloaded'})
  }
  return page.evaluate((context?: {localName: string, namespaceURI: string}, input?: string) => {
    let subject: Node
    if (context) {
      const {localName, namespaceURI} = context
      const node = document.createElementNS(namespaceURI, localName)
      node.innerHTML = input!
      subject = document.createDocumentFragment()
      Array.from(node.childNodes).forEach(child => subject.appendChild(child))
    } else {
      subject = document
    }

    return toJson(subject) as TreeConstructionResult

    function toJson(node: Node): Record<string, any> {
      const {nodeType, nodeName, childNodes} = node
      let props: Record<string, any> = {
        nodeType,
        nodeName,
        children: Array.from(childNodes, n => toJson(n))
      }
      if (node instanceof DocumentType) {
        const {publicId, systemId} = node
        return {...props, publicId, systemId}
      }
      if (node instanceof CharacterData) {
        const {data} = node
        return {...props, data}
      }
      if (node instanceof Element) {
        const {namespaceURI, attributes} = node as Element
        if (node instanceof HTMLTemplateElement) {
          props.content = toJson(node.content)
        }
        return {
          ...props,
          namespaceURI,
          attributes: Array.from(attributes, ({name, value, namespaceURI}) => ({name, value, namespaceURI}))
        }
      }
      return props
    }
  }, test.contextElement ?? null, test.input)
}

async function parseTestFile(fileName: string) {
  const rawData = await readFile(fileName, {encoding: 'utf-8'})
  return parseTreeConstructionTest(rawData)
}

async function* collectFiles(paths: string[]): AsyncGenerator<string> {
  for (const path of paths) {
    const stats = await stat(path)
    if (stats.isFile() && path.endsWith('.dat')) {
      yield resolvePath(path)
    } else if (stats.isDirectory()) {
      const files = await readdir(path)
      for await (const f of collectFiles(files.map(name => resolvePath(path, name)))) {
        yield f
      }
    }
  }
}
