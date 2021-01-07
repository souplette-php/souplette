<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib;

final class DataFile extends TestFile
{
    /**
     * @var string
     */
    private $testHeading;
    /**
     * @var string
     */
    private $encoding;

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
        foreach (file($fileName) as $line) {
            if ($line && $line[0] === '#') {
                $heading = trim(substr($line, 1));
                if ($currentTest && $heading === $this->testHeading) {
                    $currentTest[$currentSection] = substr($currentTest[$currentSection], 0, -1);
                    $tests[] = $this->normalizeTestData($currentTest);
                    $currentTest = [];
                }
                $currentSection = $heading;
                $currentTest[$currentSection] = '';
            } elseif ($currentSection !== null) {
                $currentTest[$currentSection] .= $line;
            }
        }
        if ($currentTest) {
            $tests[] = $this->normalizeTestData($currentTest);
        }

        return $tests;
    }

    private function normalizeTestData(array $test)
    {
        foreach ($test as $section => $value) {
            if ($value && $value[-1] === "\n") {
                $test[$section] = substr($value, 0, -1);
            }
        }
        return $test;
    }
}
