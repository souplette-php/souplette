
export interface TestData {
  id: string
  line: number
  sections: Record<string, string[]>
}

export class ParseError extends Error {
}

export function parseDataFile(rawData: string, testHeading: string = 'data'): TestData[] {
  const tests = []
  let currentTest: TestData | null = null
  let currentSection: string = ''
  const lines = rawData.trim().split(/\n+/)

  for (const [lineno, line] of lines.entries()) {
    if (line.charAt(0) === '#') {
      const heading = line.trim().slice(1)
      if (heading === testHeading) {
        if (currentTest) tests.push(currentTest)
        currentTest = {id: '', line: lineno, sections: {}}
      }
      if (!currentTest) {
        throw new ParseError(`Expected heading "${testHeading}" before data on line ${lineno}: "${line}"`)
      }
      currentSection = heading
      currentTest.sections[currentSection] = []
    } else if (currentSection) {
      if (!currentTest) {
        throw new ParseError(`Expected heading "${testHeading}" before data on line ${lineno}: "${line}"`)
      }
      currentTest.sections[currentSection].push(line)
    }
  }
  if (currentTest) {
    tests.push(currentTest)
  }
  return tests
}
