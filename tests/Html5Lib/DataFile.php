<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib;

final class DataFile extends TestFile
{
    private string $testHeading;
    private string $encoding;

    public function __construct(string $fileName, string $testHeading = 'data', string $encoding = 'utf-8')
    {
        $this->testHeading = $testHeading;
        $this->encoding = $encoding;
        parent::__construct($fileName);
    }

    /**
     * Converts the output of $test['document'] to the format used in the test cases.
     *
     * @param string $data
     * @return string
     */
    public function convertExpected(string $data): string
    {
        return preg_replace('/^\| /m', '', $data);
    }

    protected function parse(string $fileName): array
    {
        $tests = [];
        $currentTest = [];
        $currentSection = null;
        foreach (file($fileName) as $lineno => $line) {
            if ($line && $line[0] === '#') {
                $heading = trim(substr($line, 1));
                if ($heading === $this->testHeading) {
                    if ($currentTest) {
                        // normalize and push current test
                        $currentTest[$currentSection] = substr($currentTest[$currentSection], 0, -1);
                        $tests[] = $this->normalizeTestData($currentTest);
                    }
                    $currentTest = [
                        'metadata' => ['line' => $lineno + 1],
                    ];
                }
                $currentSection = $heading;
                $currentTest[$currentSection] = '';
            } else if ($currentSection !== null) {
                $currentTest[$currentSection] .= $line;
            }
        }
        if ($currentTest) {
            $tests[] = $this->normalizeTestData($currentTest);
        }

        return $tests;
    }

    private function normalizeTestData(array $test): array
    {
        foreach ($test as $section => $value) {
            if ($section === 'metadata') {
                continue;
            }
            if ($value && $value[-1] === "\n") {
                $test[$section] = substr($value, 0, -1);
            }
        }
        return $test;
    }
}
