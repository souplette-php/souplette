<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

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

    protected function parse(string $input): array
    {
        $tests = [];
        $currentTest = [];
        $currentSection = null;
        $lines = preg_split('/\n/', $input, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            if ($line[0] === '#') {
                $heading = trim(substr($line, 1));
                if ($currentTest && $heading === $this->testHeading) {
                    $tests[] = $this->normalizeTestData($currentTest);
                    $currentTest = [];
                }
                $currentSection = $heading;
                $currentTest[$currentSection] = '';
            } elseif ($currentSection !== null) {
                $currentTest[$currentSection] .= sprintf("%s\n", $line);
            }
        }
        if ($currentTest) {
            $tests[] = $this->normalizeTestData($currentTest);
        }

        return $tests;
    }

    private function normalizeTestData(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = rtrim($value, "\n");
        }
        return $data;
    }
}
